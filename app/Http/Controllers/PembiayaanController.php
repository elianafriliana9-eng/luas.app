<?php

namespace App\Http\Controllers;

use App\Models\Anggota;
use App\Models\Pembiayaan;
use App\Models\PengajuanPembiayaan;
use App\Models\ProdukPembiayaan;
use App\Models\JadwalAngsuran;
use App\Models\TransaksiPembiayaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PembiayaanController extends Controller
{
    /**
     * a. Simulasi pembiayaan
     */
    public function simulasi(Request $request)
    {
        $nominal = $request->input('nominal', 0);
        $jangka = $request->input('jangka', 12);
        $bunga = $request->input('bunga', 10);
        $metode = $request->input('metode', 'flat');

        $angsuranPokok = 0;
        $angsuranBunga = 0;
        $totalAngsuran = 0;
        $totalBunga = 0;

        if ($nominal > 0 && $jangka > 0) {
            if ($metode === 'flat') {
                $angsuranPokok = $nominal / $jangka;
                $totalBunga = $nominal * ($bunga / 100) * ($jangka / 12);
                $angsuranBunga = $totalBunga / $jangka;
                $totalAngsuran = $angsuranPokok + $angsuranBunga;
            } else {
                // Anuitas
                $rate = $bunga / 100 / 12;
                $totalAngsuran = $nominal * ($rate * pow(1 + $rate, $jangka)) / (pow(1 + $rate, $jangka) - 1);
                $totalBunga = ($totalAngsuran * $jangka) - $nominal;
                $angsuranBunga = 0; // varies
            }
        }

        $produkList = ProdukPembiayaan::where('aktif', true)->get();

        return view('pembiayaan.simulasi', compact(
            'nominal', 'jangka', 'bunga', 'metode',
            'angsuranPokok', 'angsuranBunga', 'totalAngsuran', 'totalBunga',
            'produkList'
        ));
    }

    /**
     * a. Hitung simulasi (AJAX)
     */
    public function simulasiHitung(Request $request)
    {
        $request->validate([
            'nominal' => 'required|numeric|min:0',
            'jangka' => 'required|integer|min:1',
            'bunga' => 'required|numeric|min:0',
            'metode' => 'required|in:flat,anuitas',
        ]);

        $nominal = $request->input('nominal');
        $jangka = $request->input('jangka');
        $bunga = $request->input('bunga');
        $metode = $request->input('metode');

        if ($metode === 'flat') {
            $angsuranPokok = $nominal / $jangka;
            $totalBunga = $nominal * ($bunga / 100) * ($jangka / 12);
            $angsuranBunga = $totalBunga / $jangka;
            $totalAngsuran = $angsuranPokok + $angsuranBunga;
        } else {
            $rate = $bunga / 100 / 12;
            if ($rate == 0) {
                $totalAngsuran = $nominal / $jangka;
            } else {
                $totalAngsuran = $nominal * ($rate * pow(1 + $rate, $jangka)) / (pow(1 + $rate, $jangka) - 1);
            }
            $angsuranPokok = 0;
            $angsuranBunga = 0;
            $totalBunga = ($totalAngsuran * $jangka) - $nominal;
        }

        return response()->json([
            'angsuran_pokok' => $angsuranPokok,
            'angsuran_bunga' => $angsuranBunga,
            'total_angsuran' => $totalAngsuran,
            'total_bunga' => $totalBunga,
            'total_bayar' => $nominal + $totalBunga,
        ]);
    }

    /**
     * b. Form input pengajuan pembiayaan
     */
    public function createPengajuan()
    {
        $anggotaList = Anggota::where('status', 'aktif')->orderBy('nama_lengkap')->get();
        $produkList = ProdukPembiayaan::where('aktif', true)->get();
        return view('pembiayaan.pengajuan_create', compact('anggotaList', 'produkList'));
    }

    /**
     * b. Simpan pengajuan
     */
    public function storePengajuan(Request $request)
    {
        $validated = $request->validate([
            'anggota_id' => 'required|uuid|exists:anggota,id',
            'produk_id' => 'required|uuid|exists:produk_pembiayaan,id',
            'nominal_diajukan' => 'required|numeric|min:0',
            'jangka_bulan' => 'required|integer|min:1|max:60',
            'tujuan' => 'required|in:modal_kerja,konsumtif,investasi,pendidikan,kesehatan',
            'catatan' => 'nullable|string|max:500',
            'auto_potong_gaji' => 'nullable|boolean',
        ]);

        $pengajuan = PengajuanPembiayaan::create([
            'anggota_id' => $validated['anggota_id'],
            'produk_id' => $validated['produk_id'],
            'no_pengajuan' => 'PJN-' . now()->format('ymd') . '-' . strtoupper(substr(uniqid(), -6)),
            'nominal_diajukan' => $validated['nominal_diajukan'],
            'jangka_bulan' => $validated['jangka_bulan'],
            'tujuan' => $validated['tujuan'],
            'status_approval' => 'pending',
            'catatan' => $validated['catatan'] ?? null,
        ]);

        return redirect()->route('pembiayaan.pengajuan')->with('success', 'Pengajuan pembiayaan berhasil diajukan!');
    }

    /**
     * b. Daftar pengajuan (redirect to registrasi)
     */
    public function pengajuan()
    {
        return view('pembiayaan.pengajuan');
    }

    /**
     * c. Registrasi pembiayaan (list pending approval)
     */
    public function registrasi(Request $request)
    {
        $query = PengajuanPembiayaan::with(['anggota', 'produk', 'approver', 'pembiayaan']);

        // Filter status
        if ($status = $request->input('status')) {
            $query->where('status_approval', $status);
        }

        // Filter search
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('no_pengajuan', 'like', "%{$search}%")
                  ->orWhereHas('anggota', fn($q2) => $q2->where('nama_lengkap', 'like', "%{$search}%"));
            });
        }

        $pengajuan = $query->latest('created_at')->paginate(15)->withQueryString();

        return view('pembiayaan.registrasi', compact('pengajuan'));
    }

    /**
     * c. Approve pengajuan & registrasi pembiayaan
     */
    public function approvePengajuan(Request $request, $id)
    {
        $request->validate([
            'action' => 'required|in:approve,reject',
            'nominal_disetujui' => 'nullable|numeric|min:0',
            'bunga_pa' => 'nullable|numeric|min:0|max:100',
            'catatan_approval' => 'nullable|string|max:500',
        ]);

        $pengajuan = PengajuanPembiayaan::with('produk')->findOrFail($id);

        DB::beginTransaction();
        try {
            if ($request->action === 'approve') {
                $nominalDisetujui = $request->input('nominal_disetujui', $pengajuan->nominal_diajukan);
                $bungaPa = $request->input('bunga_pa', $pengajuan->produk->bunga_pa);

                $pengajuan->status_approval = 'disetujui';
                $pengajuan->approved_by = auth()->id();
                $pengajuan->approved_at = now();
                $pengajuan->catatan = $request->input('catatan_approval', $pengajuan->catatan);
                $pengajuan->save();

                // Auto-create pembiayaan record
                Pembiayaan::create([
                    'pengajuan_id' => $pengajuan->id,
                    'anggota_id' => $pengajuan->anggota_id,
                    'no_pembiayaan' => 'PMB-' . now()->format('ymd') . '-' . strtoupper(substr(uniqid(), -6)),
                    'nominal_disetujui' => $nominalDisetujui,
                    'nominal_cair' => 0, // belum cair
                    'jangka_bulan' => $pengajuan->jangka_bulan,
                    'bunga_pa' => $bungaPa,
                    'metode_hitung' => $pengajuan->produk->skema_bunga,
                    'angsuran_pokok' => $nominalDisetujui / $pengajuan->jangka_bulan,
                    'angsuran_bunga' => $nominalDisetujui * ($bungaPa / 100) / 12,
                    'saldo_pokok' => $nominalDisetujui,
                    'saldo_bunga' => $nominalDisetujui * ($bungaPa / 100) * ($pengajuan->jangka_bulan / 12),
                    'kolektibilitas' => 1,
                    'status' => 'disetujui',
                ]);

                $message = 'Pengajuan disetujui dan pembiayaan berhasil diregistrasi!';
            } else {
                $pengajuan->status_approval = 'ditolak';
                $pengajuan->approved_by = auth()->id();
                $pengajuan->approved_at = now();
                $pengajuan->catatan = $request->input('catatan_approval', 'Ditolak admin');
                $pengajuan->save();

                $message = 'Pengajuan ditolak.';
            }

            DB::commit();
            return back()->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    /**
     * c. Generate jadwal angsuran (setelah pencairan)
     */
    public function generateJadwal($pembiayaanId)
    {
        $pembiayaan = Pembiayaan::with('pengajuan.anggota')->findOrFail($pembiayaanId);

        DB::beginTransaction();
        try {
            // Delete existing jadwal
            JadwalAngsuran::where('pembiayaan_id', $pembiayaan->id)->delete();

            $saldo = $pembiayaan->nominal_cair;
            $anggota = $pembiayaan->anggota;
            $tanggalMulai = $pembiayaan->tanggal_cair ?? now();
            $tanggalGajian = $anggota->tanggal_gajian ?? 25;

            for ($i = 1; $i <= $pembiayaan->jangka_bulan; $i++) {
                $tanggalJt = clone $tanggalMulai;
                $tanggalJt->addMonths($i);
                // Set to payday
                $tanggalJt->setDay(min($tanggalGajian, $tanggalJt->daysInMonth));

                $saldo -= $pembiayaan->angsuran_pokok;

                JadwalAngsuran::create([
                    'pembiayaan_id' => $pembiayaan->id,
                    'ke' => $i,
                    'tanggal_jatuh_tempo' => $tanggalJt,
                    'pokok' => $pembiayaan->angsuran_pokok,
                    'bunga' => $pembiayaan->angsuran_bunga,
                    'total' => $pembiayaan->angsuran_pokok + $pembiayaan->angsuran_bunga,
                    'saldo_akhir' => max(0, $saldo),
                    'status' => 'belum',
                ]);
            }

            DB::commit();
            return back()->with('success', 'Jadwal angsuran berhasil digenerate!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    /**
     * d. Cetak SP3 (Surat Perjanjian Pinjaman)
     */
    public function cetakSP3($id)
    {
        $pembiayaan = Pembiayaan::with(['anggota', 'pengajuan.produk', 'jadwalAngsuran'])->findOrFail($id);
        return view('pembiayaan.cetak.sp3', compact('pembiayaan'));
    }

    /**
     * e. Cetak perjanjian kredit
     */
    public function cetakPerjanjian($id)
    {
        $pembiayaan = Pembiayaan::with(['anggota', 'pengajuan.produk', 'jadwalAngsuran'])->findOrFail($id);
        return view('pembiayaan.cetak.perjanjian', compact('pembiayaan'));
    }

    /**
     * f. Pencairan pembiayaan
     */
    public function pencairanForm($id)
    {
        $pembiayaan = Pembiayaan::with(['anggota', 'pengajuan.produk'])->findOrFail($id);

        if ($pembiayaan->status !== 'disetujui') {
            return back()->with('error', 'Pembiayaan belum disetujui.');
        }

        return view('pembiayaan.pencairan', compact('pembiayaan'));
    }

    public function pencairanSubmit(Request $request, $id)
    {
        $validated = $request->validate([
            'nominal_cair' => 'required|numeric|min:0',
            'tanggal_cair' => 'required|date',
            'metode_cair' => 'required|in:transfer,tunai',
            'catatan' => 'nullable|string|max:500',
        ]);

        $pembiayaan = Pembiayaan::findOrFail($id);

        DB::beginTransaction();
        try {
            $pembiayaan->nominal_cair = $validated['nominal_cair'];
            $pembiayaan->tanggal_cair = $validated['tanggal_cair'];
            $pembiayaan->status = 'aktif';
            $pembiayaan->save();

            // Generate jadwal angsuran
            $this->generateJadwalSilent($pembiayaan);

            // Create transaction record
            TransaksiPembiayaan::create([
                'pembiayaan_id' => $pembiayaan->id,
                'no_transaksi' => 'CAIR-' . now()->format('ymd') . '-' . strtoupper(substr(uniqid(), -6)),
                'jenis' => 'pencairan',
                'nominal_pokok' => $validated['nominal_cair'],
                'nominal_bunga' => 0,
                'nominal_denda' => 0,
                'total' => $validated['nominal_cair'],
                'channel' => $validated['metode_cair'],
            ]);

            DB::commit();
            return redirect()->route('pembiayaan.transaksi')->with('success', 'Pencairan berhasil! Jadwal angsuran otomatis digenerate.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    private function generateJadwalSilent($pembiayaan)
    {
        JadwalAngsuran::where('pembiayaan_id', $pembiayaan->id)->delete();

        $saldo = $pembiayaan->nominal_cair;
        $anggota = $pembiayaan->anggota;
        $tanggalMulai = $pembiayaan->tanggal_cair;
        $tanggalGajian = $anggota->tanggal_gajian ?? 25;

        for ($i = 1; $i <= $pembiayaan->jangka_bulan; $i++) {
            $tanggalJt = clone $tanggalMulai;
            $tanggalJt->addMonths($i);
            $tanggalJt->setDay(min($tanggalGajian, $tanggalJt->daysInMonth));
            $saldo -= $pembiayaan->angsuran_pokok;

            JadwalAngsuran::create([
                'pembiayaan_id' => $pembiayaan->id,
                'ke' => $i,
                'tanggal_jatuh_tempo' => $tanggalJt,
                'pokok' => $pembiayaan->angsuran_pokok,
                'bunga' => $pembiayaan->angsuran_bunga,
                'total' => $pembiayaan->angsuran_pokok + $pembiayaan->angsuran_bunga,
                'saldo_akhir' => max(0, $saldo),
                'status' => 'belum',
            ]);
        }
    }

    /**
     * g. Pelunasan pembiayaan
     */
    public function pelunasanForm($id)
    {
        $pembiayaan = Pembiayaan::with(['anggota', 'jadwalAngsuran'])->findOrFail($id);

        if ($pembiayaan->status !== 'aktif') {
            return back()->with('error', 'Pembiayaan tidak aktif.');
        }

        $sisaPokok = $pembiayaan->saldo_pokok;
        $sisaBunga = $pembiayaan->saldo_bunga;
        $totalPelunasan = $sisaPokok + $sisaBunga;

        return view('pembiayaan.pelunasan', compact('pembiayaan', 'sisaPokok', 'sisaBunga', 'totalPelunasan'));
    }

    public function pelunasanSubmit(Request $request, $id)
    {
        $validated = $request->validate([
            'nominal_bayar' => 'required|numeric|min:0',
            'tanggal_bayar' => 'required|date',
            'metode_bayar' => 'required|in:transfer,tunai,potong_gaji',
            'catatan' => 'nullable|string|max:500',
        ]);

        $pembiayaan = Pembiayaan::with('jadwalAngsuran')->findOrFail($id);

        DB::beginTransaction();
        try {
            // Mark all remaining installments as paid
            foreach ($pembiayaan->jadwalAngsuran->where('status', 'belum') as $jadwal) {
                $jadwal->status = 'lunas';
                $jadwal->tanggal_bayar = $validated['tanggal_bayar'];
                $jadwal->save();
            }

            // Update pembiayaan
            $pembiayaan->saldo_pokok = 0;
            $pembiayaan->saldo_bunga = 0;
            $pembiayaan->status = 'lunas';
            $pembiayaan->tanggal_lunas = $validated['tanggal_bayar'];
            $pembiayaan->save();

            // Create transaction
            TransaksiPembiayaan::create([
                'pembiayaan_id' => $pembiayaan->id,
                'no_transaksi' => 'LUNAS-' . now()->format('ymd') . '-' . strtoupper(substr(uniqid(), -6)),
                'jenis' => 'pelunasan',
                'nominal_pokok' => $validated['nominal_bayar'],
                'nominal_bunga' => 0,
                'nominal_denda' => 0,
                'total' => $validated['nominal_bayar'],
                'channel' => $validated['metode_bayar'],
            ]);

            DB::commit();
            return redirect()->route('pembiayaan.transaksi')->with('success', 'Pembiayaan berhasil dilunasi!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    /**
     * h. Transaksi angsuran (bayar per angsuran)
     */
    public function bayarAngsuran($jadwalId)
    {
        $jadwal = JadwalAngsuran::with(['pembiayaan.anggota'])->findOrFail($jadwalId);

        if ($jadwal->status === 'lunas') {
            return back()->with('error', 'Angsuran sudah lunas.');
        }

        DB::beginTransaction();
        try {
            $pembiayaan = $jadwal->pembiayaan;

            $jadwal->status = 'lunas';
            $jadwal->tanggal_bayar = now();
            $jadwal->save();

            // Update saldo
            $pembiayaan->saldo_pokok -= $jadwal->pokok;
            $pembiayaan->saldo_bunga -= $jadwal->bunga;

            if ($pembiayaan->saldo_pokok <= 0) {
                $pembiayaan->status = 'lunas';
                $pembiayaan->tanggal_lunas = now();
            }
            $pembiayaan->save();

            // Create transaction
            TransaksiPembiayaan::create([
                'pembiayaan_id' => $pembiayaan->id,
                'jadwal_id' => $jadwal->id,
                'no_transaksi' => 'ANG-' . now()->format('ymd') . '-' . strtoupper(substr(uniqid(), -6)),
                'jenis' => 'angsuran',
                'nominal_pokok' => $jadwal->pokok,
                'nominal_bunga' => $jadwal->bunga,
                'nominal_denda' => 0,
                'total' => $jadwal->total,
                'channel' => 'admin',
            ]);

            DB::commit();
            return back()->with('success', 'Angsuran ke-' . $jadwal->ke . ' berhasil dibayar!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    /**
     * List semua transaksi pembiayaan
     */
    public function index(Request $request)
    {
        $query = Pembiayaan::with(['anggota', 'pengajuan']);

        if ($search = $request->input('search')) {
            $query->where('no_pembiayaan', 'like', "%{$search}%")
                  ->orWhereHas('anggota', fn($q) => $q->where('nama_lengkap', 'like', "%{$search}%"));
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $pembiayaan = $query->latest('tanggal_akad')->paginate(15)->withQueryString();
        $totalOutstanding = Pembiayaan::whereIn('status', ['aktif', 'macet'])->sum('saldo_pokok');
        $totalPinjaman = Pembiayaan::whereIn('status', ['aktif', 'macet'])->count();

        return view('pembiayaan.index', compact('pembiayaan', 'totalOutstanding', 'totalPinjaman'));
    }

    /**
     * h. Transaksi pembiayaan (semua jenis)
     */
    public function transaksi(Request $request)
    {
        $query = TransaksiPembiayaan::with(['pembiayaan.anggota']);

        if ($search = $request->input('search')) {
            $query->where('no_transaksi', 'like', "%{$search}%")
                  ->orWhereHas('pembiayaan.anggota', fn($q) => $q->where('nama_lengkap', 'like', "%{$search}%"));
        }

        if ($jenis = $request->input('jenis')) {
            $query->where('jenis', $jenis);
        }

        $transaksi = $query->latest('created_at')->paginate(15)->withQueryString();

        return view('pembiayaan.transaksi', compact('transaksi'));
    }

    /**
     * i. Laporan pengajuan
     */
    public function laporanPengajuan(Request $request)
    {
        $query = PengajuanPembiayaan::with(['anggota', 'produk', 'approver']);

        if ($status = $request->input('status')) {
            $query->where('status_approval', $status);
        }
        if ($from = $request->input('from')) $query->whereDate('created_at', '>=', $from);
        if ($to = $request->input('to')) $query->whereDate('created_at', '<=', $to);

        $pengajuan = $query->latest('created_at')->get();

        return view('pembiayaan.laporan.pengajuan', compact('pengajuan'));
    }

    /**
     * i. Laporan registrasi
     */
    public function laporanRegistrasi(Request $request)
    {
        $query = Pembiayaan::with(['anggota', 'pengajuan.produk']);

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }
        if ($from = $request->input('from')) $query->whereDate('tanggal_akad', '>=', $from);
        if ($to = $request->input('to')) $query->whereDate('tanggal_akad', '<=', $to);

        $pembiayaan = $query->latest('tanggal_akad')->get();

        return view('pembiayaan.laporan.registrasi', compact('pembiayaan'));
    }

    /**
     * i. Laporan pembiayaan aktif
     */
    public function laporanPembiayaan(Request $request)
    {
        $query = Pembiayaan::with(['anggota']);

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }
        if ($kolektibilitas = $request->input('kolektibilitas')) {
            $query->where('kolektibilitas', $kolektibilitas);
        }

        $pembiayaan = $query->latest('tanggal_akad')->get();

        return view('pembiayaan.laporan.pembiayaan', compact('pembiayaan'));
    }

    /**
     * i. Laporan pencairan
     */
    public function laporanPencairan(Request $request)
    {
        $query = TransaksiPembiayaan::where('jenis', 'pencairan')
            ->with(['pembiayaan.anggota']);

        if ($from = $request->input('from')) $query->whereDate('created_at', '>=', $from);
        if ($to = $request->input('to')) $query->whereDate('created_at', '<=', $to);

        $transaksi = $query->latest('created_at')->get();

        return view('pembiayaan.laporan.pencairan', compact('transaksi'));
    }
}
