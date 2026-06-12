<?php

namespace App\Http\Controllers;

use App\Models\Anggota;
use App\Models\RekeningSimpanan;
use App\Models\ProdukSimpanan;
use App\Models\TransaksiSimpanan;
use App\Models\Pinbuk;
use App\Models\Jurnal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\Simpanan\RekeningExport;
use App\Exports\Simpanan\TransaksiExport;
use App\Exports\Simpanan\RekapExport;
use App\Exports\Simpanan\SetoranExport;
use App\Exports\Simpanan\PenarikanExport;
use App\Exports\Simpanan\RegistSimpananExport;
use App\Exports\Simpanan\PinbukExport;
use App\Exports\Simpanan\StatementExport;
use App\Imports\TransaksiImport;
use App\Http\Requests\Simpanan\StoreTransaksiRequest;
use App\Http\Requests\Simpanan\StorePinbukRequest;
use App\Http\Requests\Simpanan\RekeningBaruRequest;

class SimpananController extends Controller
{
    use \App\Traits\SimpananJurnal;

    /**
     * a. Input simpanan — form setor/tarik
     */
    public function create(Request $request)
    {
        $jenis = $request->input('jenis', 'setoran');
        $search = $request->input('anggota');
        $jenisSimpanan = $request->input('jenis_simpanan');

        $anggota = null;
        $rekeningList = [];
        $anggotaResults = [];

        if ($search) {
            // Try UUID first (direct link from rekening page)
            $anggota = Anggota::find($search);

            if (!$anggota) {
                // Search by name, no_anggota, or NIK
                $anggotaResults = Anggota::where('status', 'aktif')
                    ->where(function ($q) use ($search) {
                        $q->where('nama_lengkap', 'like', "%{$search}%")
                          ->orWhere('no_anggota', 'like', "%{$search}%")
                          ->orWhere('nik', 'like', "%{$search}%");
                    })
                    ->limit(10)
                    ->get();

                // Auto-select if only one result
                if ($anggotaResults->count() === 1) {
                    $anggota = $anggotaResults->first();
                    $anggotaResults = [];
                }
            }

            if ($anggota) {
                $rekeningQuery = RekeningSimpanan::where('anggota_id', $anggota->id)
                    ->where('status', 'aktif')
                    ->with('produk');
                
                if ($jenisSimpanan) {
                    $rekeningQuery->whereHas('produk', fn($q) => $q->where('jenis', $jenisSimpanan));
                }
                
                $rekeningList = $rekeningQuery->get();
            }
        }

        return view('simpanan.create', compact('jenis', 'anggota', 'rekeningList', 'anggotaResults', 'jenisSimpanan'));
    }

    /**
     * a. & b. Proses transaksi simpanan
     */
    public function store(StoreTransaksiRequest $request)
    {
        $validated = $request->validated();

        $rekening = RekeningSimpanan::with('produk')->findOrFail($validated['rekening_id']);

        // Validasi rekening aktif
        if ($rekening->status !== 'aktif') {
            return back()->withErrors(['rekening_id' => 'Rekening dalam status ' . $rekening->status . '. Tidak bisa transaksi.'])->withInput();
        }

        // Validasi penarikan
        if ($validated['jenis'] === 'penarikan' && $rekening->saldo < $validated['nominal']) {
            return back()->withErrors(['nominal' => 'Saldo tidak cukup. Saldo saat ini: Rp ' . number_format($rekening->saldo, 0, ',', '.')])->withInput();
        }

        // Validasi minimal saldo dan produk
        $produk = $rekening->produk;
        if ($validated['jenis'] === 'penarikan') {
            if ($produk && in_array($produk->jenis, ['pokok', 'wajib'])) {
                return back()->withErrors(['nominal' => 'Simpanan Pokok dan Wajib tidak dapat ditarik selama Anda masih menjadi anggota aktif.'])->withInput();
            }

            $sisa = $rekening->saldo - $validated['nominal'];
            if ($produk && $produk->jenis !== 'pokok' && $produk->minimal_saldo > 0 && $sisa < $produk->minimal_saldo) {
                return back()->withErrors(['nominal' => 'Penarikan gagal. Saldo tidak boleh di bawah minimal Rp ' . number_format($produk->minimal_saldo, 0, ',', '.')])->withInput();
            }
        }

        // Approval logic: penarikan > 1jt perlu approval
        $needApproval = ($validated['jenis'] === 'penarikan' && $validated['nominal'] > 1000000);

        DB::beginTransaction();
        try {
            $saldoSebelum = $rekening->saldo;
            $saldoSesudah = $validated['jenis'] === 'setoran'
                ? $saldoSebelum + $validated['nominal']
                : $saldoSebelum - $validated['nominal'];

            // Update saldo
            $rekening->saldo = $saldoSesudah;
            $rekening->save();

            // Create transaksi record
            $transaksi = TransaksiSimpanan::create([
                'rekening_id' => $rekening->id,
                'user_id' => auth()->id(),
                'no_transaksi' => 'TRX-' . now()->format('ymd') . '-' . strtoupper(substr(uniqid(), -6)),
                'jenis' => $validated['jenis'],
                'nominal' => $validated['nominal'],
                'saldo_sebelum' => $saldoSebelum,
                'saldo_sesudah' => $saldoSesudah,
                'keterangan' => $validated['keterangan'] ?? ($validated['jenis'] === 'setoran' ? 'Setoran tunai' : 'Penarikan tunai'),
                'channel' => 'teller',
                'status_approval' => $needApproval ? 'pending' : 'approved',
                'approved_by' => $needApproval ? null : auth()->id(),
                'approved_at' => $needApproval ? null : now(),
            ]);

            if ($validated['jenis'] === 'setoran') {
                $this->buatJurnalSetoran($transaksi);
            } elseif ($transaksi->status_approval === 'approved') {
                $this->buatJurnalPenarikan($transaksi);
            }

            DB::commit();

            $message = $validated['jenis'] === 'setoran'
                ? 'Setoran berhasil diproses!'
                : ($needApproval ? 'Penarikan diajukan, menunggu approval.' : 'Penarikan berhasil diproses!');

            return redirect()->route('simpanan.create', ['anggota' => $validated['anggota_id'], 'jenis' => $validated['jenis']])
                ->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal memproses: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * b. List semua transaksi simpanan
     */
    public function index(Request $request)
    {
        $query = TransaksiSimpanan::with(['rekening.anggota', 'rekening.produk', 'user', 'approvedBy']);

        // Filter jenis_simpanan (pokok, wajib, sukarela)
        if ($jenisSimpanan = $request->input('jenis_simpanan')) {
            $query->whereHas('rekening.produk', fn($q) => $q->where('jenis', $jenisSimpanan));
        }

        // Filter jenis
        if ($jenis = $request->input('jenis')) {
            $query->where('jenis', $jenis);
        }

        // Filter status
        if ($status = $request->input('status')) {
            if ($status === 'dibatalkan') {
                $query->where('dibatalkan', true);
            } else {
                $query->where('dibatalkan', false)->where('status_approval', $status);
            }
        }

        // Filter tanggal
        if ($from = $request->input('from')) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to = $request->input('to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        // Filter rekening/anggota
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('no_transaksi', 'like', "%{$search}%")
                  ->orWhereHas('rekening.anggota', fn($q2) => $q2->where('nama_lengkap', 'like', "%{$search}%"));
            });
        }

        $transaksi = $query->latest('created_at')->paginate(15)->withQueryString();

        return view('simpanan.index', compact('transaksi'));
    }

    /**
     * c. Approval penarikan
     */
    public function approval()
    {
        $pending = TransaksiSimpanan::where('dibatalkan', false)
            ->where('status_approval', 'pending')
            ->with(['rekening.anggota', 'rekening.produk', 'user'])
            ->latest('created_at')
            ->get();

        return view('simpanan.approval', compact('pending'));
    }

    /**
     * c. Approve / Reject penarikan
     */
    public function approveTransaksi(Request $request, $id)
    {
        $request->validate([
            'action' => 'required|in:approve,reject',
        ]);

        $transaksi = TransaksiSimpanan::with('rekening')->findOrFail($id);

        DB::beginTransaction();
        try {
            if ($request->action === 'approve') {
                $transaksi->status_approval = 'approved';
                $transaksi->approved_by = auth()->id();
                $transaksi->approved_at = now();
                $transaksi->save();
                $this->buatJurnalPenarikan($transaksi->fresh());
                $message = 'Transaksi disetujui.';
            } else {
                // Reject — reverse saldo
                $rekening = $transaksi->rekening;
                $saldoSebelum = $rekening->saldo;
                $saldoSesudah = $transaksi->jenis === 'setoran'
                    ? $saldoSebelum - $transaksi->nominal
                    : $saldoSebelum + $transaksi->nominal;

                $rekening->saldo = $saldoSesudah;
                $rekening->save();

                $transaksi->status_approval = 'rejected';
                $transaksi->saldo_sebelum = $saldoSebelum;
                $transaksi->saldo_sesudah = $saldoSesudah;
                $transaksi->approved_by = auth()->id();
                $transaksi->approved_at = now();
                $transaksi->save();

                $message = 'Transaksi ditolak. Saldo dikembalikan.';
            }

            DB::commit();
            return back()->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    /**
     * d. Pinbuk — form & proses
     */
    public function pinbukForm()
    {
        $anggota = Anggota::where('status', 'aktif')->orderBy('nama_lengkap')->get();
        return view('simpanan.pinbuk', compact('anggota'));
    }

    public function pinbukStore(StorePinbukRequest $request)
    {
        $validated = $request->validated();

        $sumber = RekeningSimpanan::with('produk')->findOrFail($validated['rekening_sumber_id']);
        $tujuan = RekeningSimpanan::findOrFail($validated['rekening_tujuan_id']);

        if ($sumber->produk && in_array($sumber->produk->jenis, ['pokok', 'wajib'])) {
            return back()->withErrors(['rekening_sumber_id' => 'Simpanan Pokok dan Wajib tidak dapat dipindahbukukan selama Anda masih menjadi anggota aktif.'])->withInput();
        }

        if ($sumber->status !== 'aktif') {
            return back()->withErrors(['rekening_sumber_id' => 'Rekening sumber tidak aktif.'])->withInput();
        }
        if ($tujuan->status !== 'aktif') {
            return back()->withErrors(['rekening_tujuan_id' => 'Rekening tujuan tidak aktif.'])->withInput();
        }
        if ($sumber->saldo < $validated['nominal']) {
            return back()->withErrors(['nominal' => 'Saldo tidak cukup. Saldo sumber: Rp ' . number_format($sumber->saldo, 0, ',', '.')])->withInput();
        }

        $needApproval = $validated['nominal'] > 1000000;

        DB::beginTransaction();
        try {
            // Kurangi sumber
            $sumberSaldoSebelum = $sumber->saldo;
            $sumber->saldo = $sumberSaldoSebelum - $validated['nominal'];
            $sumber->save();

            // Tambah tujuan
            $tujuanSaldoSebelum = $tujuan->saldo;
            $tujuan->saldo = $tujuanSaldoSebelum + $validated['nominal'];
            $tujuan->save();

            // Create pinbuk record
            $pinbuk = Pinbuk::create([
                'rekening_sumber_id' => $sumber->id,
                'rekening_tujuan_id' => $tujuan->id,
                'user_id' => auth()->id(),
                'no_transaksi' => 'PMB-' . now()->format('ymd') . '-' . strtoupper(substr(uniqid(), -6)),
                'nominal' => $validated['nominal'],
                'keterangan' => $validated['keterangan'] ?? 'Pemindahbukuan',
                'status_approval' => $needApproval ? 'pending' : 'approved',
                'approved_by' => $needApproval ? null : auth()->id(),
                'approved_at' => $needApproval ? null : now(),
            ]);

            // Create transaksi simpanan for both
            TransaksiSimpanan::create([
                'rekening_id' => $sumber->id,
                'user_id' => auth()->id(),
                'no_transaksi' => $pinbuk->no_transaksi . '-K',
                'jenis' => 'pinbuk_keluar',
                'nominal' => $validated['nominal'],
                'saldo_sebelum' => $sumberSaldoSebelum,
                'saldo_sesudah' => $sumber->saldo,
                'keterangan' => 'Pinbuk ke ' . $tujuan->no_rekening . ' — ' . ($validated['keterangan'] ?? ''),
                'channel' => 'teller',
                'status_approval' => $needApproval ? 'pending' : 'approved',
                'approved_by' => $needApproval ? null : auth()->id(),
                'approved_at' => $needApproval ? null : now(),
            ]);

            TransaksiSimpanan::create([
                'rekening_id' => $tujuan->id,
                'user_id' => auth()->id(),
                'no_transaksi' => $pinbuk->no_transaksi . '-M',
                'jenis' => 'pinbuk_masuk',
                'nominal' => $validated['nominal'],
                'saldo_sebelum' => $tujuanSaldoSebelum,
                'saldo_sesudah' => $tujuan->saldo,
                'keterangan' => 'Pinbuk dari ' . $sumber->no_rekening . ' — ' . ($validated['keterangan'] ?? ''),
                'channel' => 'teller',
                'status_approval' => $needApproval ? 'pending' : 'approved',
                'approved_by' => $needApproval ? null : auth()->id(),
                'approved_at' => $needApproval ? null : now(),
            ]);

            if ($pinbuk->status_approval === 'approved') {
                $this->buatJurnalPinbuk(
                    $sumber->id, $tujuan->id,
                    (float) $validated['nominal'],
                    auth()->id(),
                    $pinbuk->no_transaksi
                );
            }

            DB::commit();

            $message = $needApproval
                ? 'Pemindahbukuan diajukan, menunggu approval.'
                : 'Pemindahbukuan berhasil diproses!';

            return redirect()->route('simpanan.index')->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Pinbuk Approval List
     */
    public function pinbukApproval()
    {
        $pending = Pinbuk::with(['rekeningSumber.anggota', 'rekeningTujuan.anggota', 'user'])
            ->where('status_approval', 'pending')
            ->latest()
            ->paginate(15);

        return view('simpanan.pinbuk_approval', compact('pending'));
    }

    /**
     * Approve Pinbuk
     */
    public function pinbukApprove($id)
    {
        $pinbuk = Pinbuk::with(['rekeningSumber', 'rekeningTujuan'])->findOrFail($id);

        if ($pinbuk->status_approval !== 'pending') {
            return back()->with('error', 'Pinbuk sudah diproses sebelumnya.');
        }

        DB::beginTransaction();
        try {
            $pinbuk->update([
                'status_approval' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            TransaksiSimpanan::where('no_transaksi', 'like', $pinbuk->no_transaksi . '%')
                ->where('status_approval', 'pending')
                ->update([
                    'status_approval' => 'approved',
                    'approved_by' => auth()->id(),
                    'approved_at' => now(),
                ]);

            $this->buatJurnalPinbuk(
                $pinbuk->rekening_sumber_id,
                $pinbuk->rekening_tujuan_id,
                (float) $pinbuk->nominal,
                auth()->id(),
                $pinbuk->no_transaksi
            );

            DB::commit();
            return back()->with('success', 'Pinbuk disetujui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    /**
     * Reject Pinbuk (reverse saldo)
     */
    public function pinbukReject($id)
    {
        $pinbuk = Pinbuk::with(['rekeningSumber', 'rekeningTujuan'])->findOrFail($id);

        if ($pinbuk->status_approval !== 'pending') {
            return back()->with('error', 'Pinbuk sudah diproses sebelumnya.');
        }

        DB::beginTransaction();
        try {
            // Reverse saldo (sumber gets back, tujuan gives back)
            $pinbuk->rekeningSumber->saldo += $pinbuk->nominal;
            $pinbuk->rekeningSumber->save();

            $pinbuk->rekeningTujuan->saldo -= $pinbuk->nominal;
            $pinbuk->rekeningTujuan->save();

            $pinbuk->update([
                'status_approval' => 'rejected',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            TransaksiSimpanan::where('no_transaksi', 'like', $pinbuk->no_transaksi . '%')
                ->where('status_approval', 'pending')
                ->update([
                    'status_approval' => 'rejected',
                    'approved_by' => auth()->id(),
                    'approved_at' => now(),
                ]);

            DB::commit();
            return back()->with('success', 'Pinbuk ditolak, saldo dikembalikan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    /**
     * Form Buka Rekening Baru
     */
    public function rekeningBaruForm()
    {
        $anggota = Anggota::where('status', 'aktif')->orderBy('nama_lengkap')->get();
        $produk = ProdukSimpanan::where('aktif', true)->get();
        return view('simpanan.rekening_baru', compact('anggota', 'produk'));
    }

    /**
     * Proses Buka Rekening Baru
     */
    public function rekeningBaruStore(RekeningBaruRequest $request)
    {
        $validated = $request->validated();

        $anggota = Anggota::findOrFail($validated['anggota_id']);
        $produk = ProdukSimpanan::findOrFail($validated['produk_id']);

        $exists = RekeningSimpanan::where('anggota_id', $anggota->id)
            ->where('produk_id', $produk->id)
            ->whereIn('status', ['aktif', 'blokir'])
            ->exists();

        if ($exists) {
            return back()->withErrors(['produk_id' => 'Anggota sudah memiliki rekening ' . $produk->nama . ' yang aktif.'])->withInput();
        }

        RekeningSimpanan::create([
            'anggota_id' => $anggota->id,
            'produk_id' => $produk->id,
            'no_rekening' => RekeningSimpanan::generateNoRekening($produk, $anggota->cabang),
            'saldo' => 0,
            'status' => 'aktif',
            'tanggal_buka' => now(),
        ]);

        return redirect()->route('simpanan.rekening')
            ->with('success', 'Rekening ' . $produk->nama . ' berhasil dibuka untuk ' . $anggota->nama_lengkap);
    }

    /**
     * e. Cancel transaksi
     */
    public function cancelForm($id)
    {
        $transaksi = TransaksiSimpanan::with(['rekening.anggota', 'rekening.produk', 'user'])->findOrFail($id);
        return view('simpanan.cancel', compact('transaksi'));
    }

    public function cancelSubmit(Request $request, $id)
    {
        $validated = $request->validate([
            'alasan' => 'required|string|max:500',
        ]);

        $transaksi = TransaksiSimpanan::with('rekening')->findOrFail($id);

        // Cannot cancel if already cancelled
        if ($transaksi->dibatalkan) {
            return back()->with('error', 'Transaksi sudah dibatalkan sebelumnya.');
        }

        DB::beginTransaction();
        try {
            $rekening = $transaksi->rekening;

            // Reverse saldo
            $saldoSebelum = $rekening->saldo;
            $saldoSesudah = $transaksi->jenis === 'setoran' || $transaksi->jenis === 'pinbuk_masuk'
                ? $saldoSebelum - $transaksi->nominal
                : $saldoSebelum + $transaksi->nominal;

            $rekening->saldo = $saldoSesudah;
            $rekening->save();

            // Mark as cancelled
            $transaksi->dibatalkan = true;
            $transaksi->dibatalkan_by = auth()->id();
            $transaksi->dibatalkan_at = now();
            $transaksi->jenis_pembatalan = $transaksi->jenis;
            $transaksi->saldo_sebelum = $saldoSebelum;
            $transaksi->saldo_sesudah = $saldoSesudah;
            $transaksi->save();

            DB::commit();
            return redirect()->route('simpanan.transaksi')->with('success', 'Transaksi berhasil dibatalkan. Saldo dikembalikan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    /**
     * f. Upload data simpanan (Excel import)
     */
    public function uploadForm()
    {
        return view('simpanan.upload');
    }

    public function uploadProcess(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:10240',
        ]);

        try {
            $import = new TransaksiImport();
            Excel::import($import, $request->file('file'));
            $hasil = $import->getHasil();

            $message = "Import selesai. Berhasil: {$hasil['berhasil']}, Gagal: {$hasil['gagal']}.";
            if (!empty($hasil['errors'])) {
                $message .= ' ' . implode(' | ', array_slice($hasil['errors'], 0, 5));
            }

            return back()->with('success', $message);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal import: ' . $e->getMessage());
        }
    }

    /**
     * Blokir tabungan
     */
    public function blokirForm($id)
    {
        $rekening = RekeningSimpanan::with(['anggota', 'produk'])->findOrFail($id);
        return view('simpanan.blokir', compact('rekening'));
    }

    public function blokirSubmit(Request $request, $id)
    {
        $validated = $request->validate([
            'alasan' => 'required|string|max:500',
        ]);

        $rekening = RekeningSimpanan::findOrFail($id);
        $rekening->status = 'blokir';
        $rekening->save();

        return back()->with('success', 'Rekening berhasil diblokir.');
    }

    public function bukaBlokir($id)
    {
        $rekening = RekeningSimpanan::findOrFail($id);
        $rekening->status = 'aktif';
        $rekening->save();

        return back()->with('success', 'Blokir rekening dibuka.');
    }

    /**
     * Tutup rekening
     */
    public function tutupForm($id)
    {
        $rekening = RekeningSimpanan::with(['anggota', 'produk'])->findOrFail($id);
        return view('simpanan.tutup', compact('rekening'));
    }

    public function tutupSubmit(Request $request, $id)
    {
        $validated = $request->validate([
            'alasan' => 'required|string|max:500',
        ]);

        $rekening = RekeningSimpanan::findOrFail($id);

        if ($rekening->saldo > 0) {
            return back()->withErrors(['saldo' => 'Rekening masih memiliki saldo Rp ' . number_format($rekening->saldo, 0, ',', '.') . '. Tarik terlebih dahulu.']);
        }

        $rekening->status = 'tutup';
        $rekening->tanggal_tutup = now();
        $rekening->save();

        return back()->with('success', 'Rekening berhasil ditutup.');
    }

    /**
     * g. Laporan — Statement simpanan per rekening
     */
    public function statement($id, Request $request)
    {
        $rekening = RekeningSimpanan::with(['anggota', 'produk'])->findOrFail($id);

        $query = TransaksiSimpanan::where('rekening_id', $id)
            ->where('dibatalkan', false)
            ->with(['user']);

        if ($from = $request->input('from')) $query->whereDate('created_at', '>=', $from);
        if ($to = $request->input('to')) $query->whereDate('created_at', '<=', $to);

        $transaksi = $query->orderBy('created_at', 'desc')->get();

        return view('simpanan.laporan.statement', compact('rekening', 'transaksi'));
    }

    /**
     * g. Laporan rekap simpanan
     */
    public function laporanRekap(Request $request)
    {
        $query = RekeningSimpanan::with(['anggota', 'produk'])->where('status', 'aktif');

        if ($produkId = $request->input('produk_id')) {
            $query->where('produk_id', $produkId);
        }
        if ($cabangId = $request->input('cabang_id')) {
            $query->whereHas('anggota', fn($q) => $q->where('cabang_id', $cabangId));
        }

        $rekening = $query->orderByDesc('no_rekening')->get();
        $produkList = ProdukSimpanan::where('aktif', true)->get();
        $cabangList = \App\Models\Cabang::where('aktif', true)->get();

        return view('simpanan.laporan.rekap', compact('rekening', 'produkList', 'cabangList'));
    }

    /**
     * g. Laporan penarikan
     */
    public function laporanPenarikan(Request $request)
    {
        $query = TransaksiSimpanan::where('jenis', 'penarikan')
            ->where('dibatalkan', false)
            ->with(['rekening.anggota']);

        if ($from = $request->input('from')) $query->whereDate('created_at', '>=', $from);
        if ($to = $request->input('to')) $query->whereDate('created_at', '<=', $to);

        $transaksi = $query->orderByDesc('created_at')->get();
        $title = 'Penarikan';

        return view('simpanan.laporan.generic', compact('transaksi', 'title'));
    }

    /**
     * g. Laporan regist simpanan
     */
    public function laporanRegist()
    {
        $rekening = RekeningSimpanan::with(['anggota', 'produk'])->orderBy('tanggal_buka', 'desc')->get();
        return view('simpanan.laporan.regist', compact('rekening'));
    }

    /**
     * g. Laporan setoran
     */
    public function laporanSetoran(Request $request)
    {
        $query = TransaksiSimpanan::where('jenis', 'setoran')
            ->where('dibatalkan', false)
            ->with(['rekening.anggota']);

        if ($from = $request->input('from')) $query->whereDate('created_at', '>=', $from);
        if ($to = $request->input('to')) $query->whereDate('created_at', '<=', $to);

        $transaksi = $query->orderByDesc('created_at')->get();
        $title = 'Setoran';

        return view('simpanan.laporan.generic', compact('transaksi', 'title'));
    }

    /**
     * g. Laporan pinbuk
     */
    public function laporanPinbuk(Request $request)
    {
        $query = Pinbuk::with(['rekeningSumber.anggota', 'rekeningTujuan.anggota', 'approvedBy']);

        if ($status = $request->input('status')) {
            $query->where('status_approval', $status);
        }
        if ($from = $request->input('from')) $query->whereDate('created_at', '>=', $from);
        if ($to = $request->input('to')) $query->whereDate('created_at', '<=', $to);

        $pinbukList = $query->orderByDesc('created_at')->get();

        return view('simpanan.laporan.pinbuk', compact('pinbukList'));
    }

    /**
     * h. Export Rekening Simpanan ke Excel
     */
    public function exportRekening(Request $request)
    {
        $filters = $request->only(['search', 'status']);
        return $this->excelDownload(new RekeningExport($filters), 'rekening-simpanan-' . date('Y-m-d') . '.xlsx');
    }

    /**
     * h. Export Transaksi Simpanan ke Excel
     */
    public function exportTransaksi(Request $request)
    {
        $filters = $request->only(['search', 'jenis', 'status', 'jenis_simpanan', 'from', 'to']);
        return $this->excelDownload(new TransaksiExport($filters), 'transaksi-simpanan-' . date('Y-m-d') . '.xlsx');
    }

    /**
     * h. Export Rekap Simpanan ke Excel
     */
    public function exportRekap(Request $request)
    {
        $filters = $request->only(['produk_id', 'cabang_id']);
        return $this->excelDownload(new RekapExport($filters), 'rekap-simpanan-' . date('Y-m-d') . '.xlsx');
    }

    /**
     * Export Laporan Setoran
     */
    public function exportSetoran(Request $request)
    {
        $filters = $request->only(['from', 'to']);
        return $this->excelDownload(new SetoranExport($filters), 'laporan-setoran-' . date('Y-m-d') . '.xlsx');
    }

    /**
     * Export Laporan Penarikan
     */
    public function exportPenarikan(Request $request)
    {
        $filters = $request->only(['from', 'to']);
        return $this->excelDownload(new PenarikanExport($filters), 'laporan-penarikan-' . date('Y-m-d') . '.xlsx');
    }

    /**
     * Export Laporan Registrasi Simpanan
     */
    public function exportRegist()
    {
        return $this->excelDownload(new RegistSimpananExport(), 'registrasi-simpanan-' . date('Y-m-d') . '.xlsx');
    }

    /**
     * Export Laporan Pinbuk
     */
    public function exportPinbuk(Request $request)
    {
        $filters = $request->only(['status', 'from', 'to']);
        return $this->excelDownload(new PinbukExport($filters), 'laporan-pinbuk-' . date('Y-m-d') . '.xlsx');
    }

    /**
     * Export Statement Rekening
     */
    public function exportStatement($id, Request $request)
    {
        $filters = $request->only(['from', 'to']);
        return $this->excelDownload(new StatementExport($id, $filters), 'statement-' . $id . '-' . date('Y-m-d') . '.xlsx');
    }

    /**
     * i. Download template Excel untuk import transaksi
     */
    public function downloadTemplate()
    {
        return $this->excelDownload(
            new \App\Exports\Simpanan\TemplateTransaksiExport(),
            'template-import-transaksi.xlsx'
        );
    }

    /**
     * List rekening
     */
    public function rekening(Request $request)
    {
        $query = RekeningSimpanan::with(['anggota', 'produk']);

        if ($jenisSimpanan = $request->input('jenis_simpanan')) {
            $query->whereHas('produk', fn($q) => $q->where('jenis', $jenisSimpanan));
        }

        if ($search = $request->input('search')) {
            $query->where('no_rekening', 'like', "%{$search}%")
                  ->orWhereHas('anggota', fn($q) => $q->where('nama_lengkap', 'like', "%{$search}%"));
        }
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $rekening = $query->latest()->paginate(15)->withQueryString();
        $totalSaldo = RekeningSimpanan::where('status', 'aktif')->sum('saldo');
        $totalRekening = RekeningSimpanan::where('status', 'aktif')->count();

        return view('simpanan.rekening', compact('rekening', 'totalSaldo', 'totalRekening'));
    }

    /**
     * List transaksi (redirect to index)
     */
    public function transaksi(Request $request)
    {
        return redirect()->route('simpanan.index', $request->query());
    }
}
