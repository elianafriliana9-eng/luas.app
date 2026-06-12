<?php

namespace App\Http\Controllers;

use App\Models\Anggota;
use App\Models\SimpananBerjangka;
use App\Models\TransaksiSimpanan;
use App\Models\RekeningSimpanan;
use App\Models\ProdukSimpanan;
use App\Traits\SimpananJurnal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SimpananBerjangkaController extends Controller
{
    use SimpananJurnal;

    public function index(Request $request)
    {
        $query = SimpananBerjangka::with('anggota');

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }
        if ($search = $request->input('search')) {
            $query->where('no_deposito', 'like', "%{$search}%")
                ->orWhereHas('anggota', fn($q) => $q->where('nama_lengkap', 'like', "%{$search}%"));
        }

        $deposito = $query->latest()->paginate(15)->withQueryString();
        return view('simpanan_berjangka.index', compact('deposito'));
    }

    public function create()
    {
        $anggota = Anggota::where('status', 'aktif')->orderBy('nama_lengkap')->get();
        return view('simpanan_berjangka.create', compact('anggota'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'anggota_id' => 'required|uuid|exists:anggota,id',
            'nominal' => 'required|numeric|min:1000000',
            'jangka_bulan' => 'required|integer|in:1,3,6,12,24',
            'bunga_pa' => 'required|numeric|min:0.01|max:20',
            'auto_perpanjang' => 'sometimes|boolean',
        ]);

        $anggota = Anggota::findOrFail($validated['anggota_id']);

        DB::beginTransaction();
        try {
            $tanggalMulai = now();
            $tanggalJT = now()->addMonths((int) $validated['jangka_bulan']);

            $count = SimpananBerjangka::count() + 1;
            $noDeposito = 'DP-' . now()->format('ymd') . '-' . str_pad($count, 5, '0', STR_PAD_LEFT);

            SimpananBerjangka::create([
                'anggota_id' => $anggota->id,
                'no_deposito' => $noDeposito,
                'nominal' => $validated['nominal'],
                'jangka_bulan' => $validated['jangka_bulan'],
                'bunga_pa' => $validated['bunga_pa'],
                'tanggal_mulai' => $tanggalMulai,
                'tanggal_jatuh_tempo' => $tanggalJT,
                'status' => 'aktif',
                'auto_perpanjang' => $request->boolean('auto_perpanjang'),
            ]);

            DB::commit();
            return redirect()->route('simpanan-berjangka.index')
                ->with('success', "Deposito {$noDeposito} berhasil dibuat.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal: ' . $e->getMessage()])->withInput();
        }
    }

    public function show($id)
    {
        $deposito = SimpananBerjangka::with('anggota')->findOrFail($id);
        return view('simpanan_berjangka.show', compact('deposito'));
    }

    public function cairForm($id)
    {
        $deposito = SimpananBerjangka::with('anggota')->findOrFail($id);
        if (!in_array($deposito->status, ['jatuh_tempo', 'aktif'])) {
            return back()->with('error', 'Deposito sudah dicairkan.');
        }
        return view('simpanan_berjangka.cair', compact('deposito'));
    }

    public function cairSubmit(Request $request, $id)
    {
        $deposito = SimpananBerjangka::with('anggota')->findOrFail($id);

        if (!in_array($deposito->status, ['jatuh_tempo', 'aktif'])) {
            return back()->with('error', 'Deposito sudah dicairkan.');
        }

        $validated = $request->validate([
            'transfer_ke_rekening' => 'required|boolean',
            'keterangan' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $totalBunga = $deposito->nominal * ($deposito->bunga_pa / 100) * ($deposito->jangka_bulan / 12);
            $totalCair = $deposito->nominal + round($totalBunga, 2);

            if ($validated['transfer_ke_rekening']) {
                $produkSukarela = ProdukSimpanan::where('jenis', 'sukarela')->first();
                if ($produkSukarela) {
                    $rekening = RekeningSimpanan::firstOrCreate(
                        [
                            'anggota_id' => $deposito->anggota_id,
                            'produk_id' => $produkSukarela->id,
                        ],
                        [
                            'no_rekening' => 'R-' . now()->format('ymd') . '-' . strtoupper(substr(uniqid(), -6)),
                            'saldo' => 0,
                            'status' => 'aktif',
                            'tanggal_buka' => now(),
                        ]
                    );

                    $saldoSebelum = $rekening->saldo;
                    $rekening->saldo += $totalCair;
                    $rekening->save();

                    $transaksi = TransaksiSimpanan::create([
                        'rekening_id' => $rekening->id,
                        'user_id' => auth()->id(),
                        'no_transaksi' => 'DPC-' . now()->format('ymd') . '-' . strtoupper(substr(uniqid(), -6)),
                        'jenis' => 'setoran',
                        'nominal' => $totalCair,
                        'saldo_sebelum' => $saldoSebelum,
                        'saldo_sesudah' => $rekening->saldo,
                        'keterangan' => 'Pencairan deposito ' . $deposito->no_deposito . ' (' . ($validated['keterangan'] ?? '') . ')',
                        'channel' => 'teller',
                        'status_approval' => 'approved',
                        'approved_by' => auth()->id(),
                        'approved_at' => now(),
                    ]);

                    $this->buatJurnalSetoran($transaksi);
                }
            }

            $deposito->status = 'cair';
            $deposito->save();

            DB::commit();
            return redirect()->route('simpanan-berjangka.index')
                ->with('success', "Deposito {$deposito->no_deposito} berhasil dicairkan. Total: Rp " . number_format($totalCair, 0, ',', '.'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal: ' . $e->getMessage()]);
        }
    }
}
