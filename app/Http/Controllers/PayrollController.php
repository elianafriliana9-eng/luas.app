<?php

namespace App\Http\Controllers;

use App\Models\Anggota;
use App\Models\Pembiayaan;
use App\Models\PotonganGaji;
use App\Models\PayLater;
use App\Models\JadwalAngsuran;
use App\Models\TransaksiPembiayaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PayrollController extends Controller
{
    /**
     * Dashboard payroll — daftar karyawan dengan potongan gaji aktif.
     */
    public function index()
    {
        $karyawan = Anggota::where('status', 'aktif')
            ->whereNotNull('gaji_pokok')
            ->with(['pembiayaan' => function ($q) {
                $q->where('status', 'aktif')
                  ->where('auto_potong_gaji', true);
            }])
            ->orderBy('departemen')
            ->orderBy('nama_lengkap')
            ->paginate(20);

        return view('payroll.index', compact('karyawan'));
    }

    /**
     * Detail potongan gaji per karyawan.
     */
    public function detail($anggotaId)
    {
        $anggota = Anggota::with(['cabang', 'pembiayaan.jadwalAngsuran'])->findOrFail($anggotaId);
        $potonganHistory = PotonganGaji::where('anggota_id', $anggotaId)
            ->with(['pembiayaan', 'jadwalAngsuran'])
            ->orderBy('periode', 'desc')
            ->paginate(20);

        return view('payroll.detail', compact('anggota', 'potonganHistory'));
    }

    /**
     * Proses potongan gaji untuk periode tertentu.
     */
    public function prosesPotongan(Request $request)
    {
        $request->validate([
            'periode' => 'required|date_format:Y-m-01',
        ]);

        $periode = $request->input('periode');
        $processedCount = 0;
        $failedCount = 0;
        $errors = [];

        // Get all active pembiayaan with auto_potong_gaji
        $pembiayaanList = Pembiayaan::where('status', 'aktif')
            ->where('auto_potong_gaji', true)
            ->where('bulan_tersisa_potongan', '>', 0)
            ->with(['anggota', 'jadwalAngsuran'])
            ->get();

        DB::beginTransaction();
        try {
            foreach ($pembiayaanList as $pembiayaan) {
                $anggota = $pembiayaan->anggota;

                // Check if already processed for this period
                $existing = PotonganGaji::where('anggota_id', $anggota->id)
                    ->where('periode', $periode)
                    ->where('pembiayaan_id', $pembiayaan->id)
                    ->first();

                if ($existing) continue;

                // Find the next unpaid installment
                $nextInstallment = $pembiayaan->jadwalAngsuran()
                    ->where('status', 'belum')
                    ->orderBy('tanggal_jatuh_tempo', 'asc')
                    ->first();

                if (!$nextInstallment) {
                    $pembiayaan->status = 'lunas';
                    $pembiayaan->save();
                    continue;
                }

                $nominalPotongan = $nextInstallment->total;
                $gajiDiterima = ($anggota->gaji_pokok ?? 0) - $nominalPotongan;

                // Create potongan record
                $potongan = PotonganGaji::create([
                    'anggota_id' => $anggota->id,
                    'pembiayaan_id' => $pembiayaan->id,
                    'jadwal_angsuran_id' => $nextInstallment->id,
                    'periode' => $periode,
                    'gaji_bruto' => $anggota->gaji_pokok,
                    'nominal_potongan' => $nominalPotongan,
                    'gaji_diterima' => $gajiDiterima,
                    'jenis_potongan' => 'angsuran_pokok',
                    'status' => 'diproses',
                    'processed_at' => now(),
                ]);

                // Mark installment as paid
                $nextInstallment->status = 'lunas';
                $nextInstallment->tanggal_bayar = now();
                $nextInstallment->save();

                // Update pembiayaan balance
                $pembiayaan->saldo_pokok -= $nextInstallment->angsuran_pokok;
                $pembiayaan->saldo_bunga -= $nextInstallment->angsuran_bunga;
                $pembiayaan->bulan_tersisa_potongan = max(0, ($pembiayaan->bulan_tersisa_potongan ?? 1) - 1);

                if ($pembiayaan->saldo_pokok <= 0) {
                    $pembiayaan->status = 'lunas';
                    $pembiayaan->tanggal_lunas = now();
                }
                $pembiayaan->save();

                // Create transaction record
                TransaksiPembiayaan::create([
                    'pembiayaan_id' => $pembiayaan->id,
                    'jadwal_id' => $nextInstallment->id,
                    'no_transaksi' => 'PAYROLL-' . now()->format('ymd') . '-' . strtoupper(substr(uniqid(), -6)),
                    'jenis' => 'angsuran',
                    'nominal_pokok' => $nextInstallment->angsuran_pokok,
                    'nominal_bunga' => $nextInstallment->angsuran_bunga,
                    'nominal_denda' => 0,
                    'total' => $nextInstallment->total,
                    'channel' => 'potong_gaji',
                    'ref_payment' => $potongan->id,
                ]);

                $processedCount++;
            }

            DB::commit();

            return redirect()->route('payroll.index')
                ->with('success', "Berhasil memproses {$processedCount} potongan gaji. Gagal: {$failedCount}");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal memproses potongan gaji: ' . $e->getMessage());
        }
    }

    /**
     * Daftar Pay Later yang menunggu approval.
     */
    public function payLaterPending()
    {
        $payLaterList = PayLater::where('status', 'pending')
            ->with(['anggota', 'pembiayaan', 'jadwalAngsuran'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('payroll.pay_later_pending', compact('payLaterList'));
    }

    /**
     * Approve pay later request.
     */
    public function approvePayLater($id)
    {
        $payLater = PayLater::findOrFail($id);
        $payLater->status = 'approved';
        $payLater->approved_by = auth()->id();
        $payLater->approved_at = now();
        $payLater->save();

        return redirect()->back()->with('success', 'Pay Later disetujui.');
    }

    /**
     * Reject pay later request.
     */
    public function rejectPayLater($id)
    {
        $payLater = PayLater::findOrFail($id);
        $payLater->status = 'rejected';
        $payLater->approved_by = auth()->id();
        $payLater->approved_at = now();
        $payLater->save();

        return redirect()->back()->with('success', 'Pay Later ditolak.');
    }

    /**
     * Process approved pay later — mark as paid.
     */
    public function processPayLater($id)
    {
        $payLater = PayLater::with(['pembiayaan.jadwalAngsuran'])->findOrFail($id);

        if ($payLater->status !== 'approved') {
            return redirect()->back()->with('error', 'Pay Later belum disetujui.');
        }

        DB::beginTransaction();
        try {
            $pembiayaan = $payLater->pembiayaan;
            $jadwal = $payLater->jadwalAngsuran ?? $pembiayaan->jadwalAngsuran()->where('status', 'belum')->first();

            if (!$jadwal) {
                return redirect()->back()->with('error', 'Tidak ada angsuran yang belum dibayar.');
            }

            $jadwal->status = 'lunas';
            $jadwal->tanggal_bayar = now();
            $jadwal->save();

            $pembiayaan->saldo_pokok -= $jadwal->angsuran_pokok;
            $pembiayaan->saldo_bunga -= $jadwal->angsuran_bunga;

            if ($pembiayaan->saldo_pokok <= 0) {
                $pembiayaan->status = 'lunas';
                $pembiayaan->tanggal_lunas = now();
            }
            $pembiayaan->save();

            TransaksiPembiayaan::create([
                'pembiayaan_id' => $pembiayaan->id,
                'jadwal_id' => $jadwal->id,
                'no_transaksi' => $payLater->no_transaksi,
                'jenis' => $payLater->jenis,
                'nominal_pokok' => $jadwal->angsuran_pokok,
                'nominal_bunga' => $jadwal->angsuran_bunga,
                'nominal_denda' => 0,
                'total' => $payLater->nominal,
                'channel' => 'mobile',
                'ref_payment' => $payLater->id,
            ]);

            $payLater->status = 'lunas';
            $payLater->lunas_at = now();
            $payLater->save();

            DB::commit();

            return redirect()->back()->with('success', 'Pembayaran Pay Later berhasil diproses.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal memproses: ' . $e->getMessage());
        }
    }
}
