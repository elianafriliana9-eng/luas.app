<?php

namespace App\Http\Controllers;

use App\Models\Anggota;
use App\Models\RekeningSimpanan;
use App\Models\TransaksiSimpanan;
use App\Models\Cabang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\Anggota\AnggotaExport;
use App\Exports\Anggota\SaldoExport;
use App\Exports\Anggota\ProfilExport;
use App\Imports\AnggotaImport;
use App\Imports\MasterDataImport;
use App\Http\Requests\StoreAnggotaRequest;
use App\Http\Requests\UpdateAnggotaRequest;

class AnggotaController extends Controller
{
    /**
     * a. & c. List anggota — dengan filter status & cabang
     */
    public function index(Request $request)
    {
        $query = Anggota::query()->with('cabang');

        // Filter search
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('no_anggota', 'like', "%{$search}%")
                  ->orWhere('nik', 'like', "%{$search}%")
                  ->orWhere('no_pegawai', 'like', "%{$search}%");
            });
        }

        // Filter status
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // Filter cabang
        if ($cabangId = $request->input('cabang_id')) {
            $query->where('cabang_id', $cabangId);
        }

        // Filter perusahaan
        if ($perusahaanId = $request->input('perusahaan_id')) {
            $query->where('perusahaan_id', $perusahaanId);
        }

        $anggota = $query->latest('tanggal_masuk')->paginate(15)->withQueryString();
        $cabangs = Cabang::where('aktif', true)->get();
        $perusahaans = \App\Models\Perusahaan::where('aktif', true)->orderBy('nama')->get();

        return view('anggota.index', compact('anggota', 'cabangs', 'perusahaans'));
    }

    /**
     * Detail anggota — profil, rekening, history transaksi
     */
    public function show($id)
    {
        $anggota = Anggota::with(['cabang', 'rekeningSimpanan.produk', 'pembiayaan.jadwalAngsuran'])->findOrFail($id);

        // Total simpanan
        $totalSimpanan = $anggota->rekeningSimpanan->sum('saldo');

        // History transaksi simpanan (last 20)
        $rekeningIds = $anggota->rekeningSimpanan->pluck('id');
        $historyTransaksi = TransaksiSimpanan::whereIn('rekening_id', $rekeningIds)
            ->with(['rekening.produk'])
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        // Pembiayaan aktif
        $pembiayaanAktif = $anggota->pembiayaan->where('status', 'aktif');

        return view('anggota.show', compact('anggota', 'totalSimpanan', 'historyTransaksi', 'pembiayaanAktif'));
    }

    /**
     * b. Form tambah anggota baru
     */
    public function create()
    {
        $cabangs = Cabang::where('aktif', true)->get();
        $perusahaans = \App\Models\Perusahaan::where('aktif', true)->orderBy('nama')->get();
        return view('anggota.create', compact('cabangs', 'perusahaans'));
    }

    /**
     * a. Simpan anggota baru
     */
    public function store(StoreAnggotaRequest $request)
    {
        $validated = $request->validated();

        DB::beginTransaction();
        try {
            // Upload files
            if ($request->hasFile('foto_ktp')) {
                $validated['foto_ktp'] = $request->file('foto_ktp')->store('anggota/ktp', 'public');
            }
            if ($request->hasFile('foto_selfie')) {
                $validated['foto_selfie'] = $request->file('foto_selfie')->store('anggota/selfie', 'public');
            }

            // Auto-generate no_anggota
            $validated['no_anggota'] = 'ANG-' . now()->year . '-' . str_pad(Anggota::count() + 1, 4, '0', STR_PAD_LEFT);
            // Teller creates as pending_aktif, admin/super_admin creates as langsung aktif
            $validated['status'] = in_array(auth()->user()->role, ['super_admin', 'admin']) ? 'aktif' : 'pending_aktif';
            $validated['tanggal_masuk'] = now()->format('Y-m-d');
            $validated['password'] = bcrypt('123456'); // Default password

            $anggota = Anggota::create($validated);

            // Auto-create rekening simpanan (only if langsung aktif)
            if ($anggota->status === 'aktif') {
                $this->createRekeningDefault($anggota);
            }

            DB::commit();

            $msg = $anggota->status === 'pending_aktif'
                ? 'Anggota berhasil didaftarkan! Menunggu approval admin.'
                : 'Anggota berhasil ditambahkan!';

            return redirect()->route('anggota.show', $anggota->id)
                ->with('success', $msg);
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('anggota.create')
                ->withErrors(['error' => 'Gagal menyimpan: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Form edit anggota
     */
    public function edit($id)
    {
        $anggota = Anggota::findOrFail($id);
        $cabangs = Cabang::where('aktif', true)->get();
        $perusahaans = \App\Models\Perusahaan::where('aktif', true)->orderBy('nama')->get();
        return view('anggota.edit', compact('anggota', 'cabangs', 'perusahaans'));
    }

    /**
     * Update anggota
     */
    public function update(UpdateAnggotaRequest $request, $id)
    {
        $anggota = Anggota::findOrFail($id);

        $validated = $request->validated();

        if ($request->hasFile('foto_ktp')) {
            if ($anggota->foto_ktp) Storage::disk('public')->delete($anggota->foto_ktp);
            $validated['foto_ktp'] = $request->file('foto_ktp')->store('anggota/ktp', 'public');
        }
        if ($request->hasFile('foto_selfie')) {
            if ($anggota->foto_selfie) Storage::disk('public')->delete($anggota->foto_selfie);
            $validated['foto_selfie'] = $request->file('foto_selfie')->store('anggota/selfie', 'public');
        }

        $anggota->update($validated);

        return redirect()->route('anggota.show', $anggota->id)
            ->with('success', 'Data anggota berhasil diupdate!');
    }

    /**
     * e. Form pengajuan anggota keluar
     */
    public function keluarForm($id)
    {
        $anggota = Anggota::findOrFail($id);
        return view('anggota.keluar', compact('anggota'));
    }

    /**
     * e. Proses pengajuan anggota keluar
     */
    public function keluarSubmit(Request $request, $id)
    {
        $anggota = Anggota::findOrFail($id);

        $validated = $request->validate([
            'tanggal_keluar' => 'required|date|after_or_equal:today',
            'alasan_keluar' => 'required|string',
        ]);

        $anggota->update([
            'status' => 'pengajuan_keluar',
            'tanggal_keluar' => $validated['tanggal_keluar'],
        ]);

        return redirect()->route('anggota.show', $id)
            ->with('success', 'Pengajuan anggota keluar berhasil diajukan, menunggu approval.');
    }

    /**
     * f. Approval anggota keluar
     */
    public function approvalKeluar()
    {
        $pending = Anggota::where('status', 'pengajuan_keluar')
            ->with('cabang')
            ->orderBy('tanggal_keluar', 'asc')
            ->get();

        return view('anggota.approval_keluar', compact('pending'));
    }

    /**
     * f. Approve anggota keluar
     */
    public function approveKeluar($id)
    {
        $anggota = Anggota::findOrFail($id);

        DB::beginTransaction();
        try {
            $anggota->update([
                'status' => 'keluar',
                'tanggal_keluar' => now(),
            ]);

            // Tarik semua saldo simpanan secara otomatis
            $rekenings = RekeningSimpanan::where('anggota_id', $anggota->id)
                ->where('status', 'aktif')
                ->get();

            foreach ($rekenings as $rekening) {
                if ($rekening->saldo > 0) {
                    $saldoTarik = $rekening->saldo;
                    
                    TransaksiSimpanan::create([
                        'rekening_id' => $rekening->id,
                        'user_id' => auth()->id(),
                        'no_transaksi' => 'TRX-' . now()->format('ymd') . '-' . strtoupper(substr(uniqid(), -6)),
                        'jenis' => 'penarikan',
                        'nominal' => $saldoTarik,
                        'saldo_sebelum' => $saldoTarik,
                        'saldo_sesudah' => 0,
                        'keterangan' => 'Penarikan otomatis karena anggota keluar',
                        'channel' => 'admin',
                        'status_approval' => 'approved',
                        'approved_by' => auth()->id(),
                        'approved_at' => now(),
                    ]);
                }

                $rekening->update([
                    'saldo' => 0,
                    'status' => 'tutup'
                ]);
            }

            DB::commit();

            return back()->with('success', "Anggota {$anggota->nama_lengkap} berhasil diapprove keluar.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal approve: ' . $e->getMessage());
        }
    }

    /**
     * f. Reject anggota keluar
     */
    public function rejectKeluar($id)
    {
        $anggota = Anggota::findOrFail($id);
        $anggota->update([
            'status' => 'aktif',
        ]);

        return back()->with('success', 'Pengajuan keluar ditolak. Anggota kembali aktif.');
    }

    // ─── Approval Anggota Baru (Pending Aktif) ─────────────────────────────

    /**
     * Daftar anggota yang menunggu approval
     */
    public function pendingApproval()
    {
        $pending = Anggota::where('status', 'pending_aktif')
            ->with('cabang')
            ->orderBy('created_at', 'asc')
            ->get();

        return view('anggota.pending_approval', compact('pending'));
    }

    /**
     * Approve anggota baru (pending_aktif -> aktif)
     */
    public function approveAnggota($id)
    {
        $anggota = Anggota::findOrFail($id);

        if ($anggota->status !== 'pending_aktif') {
            return back()->with('error', 'Status anggota bukan pending_aktif.');
        }

        DB::beginTransaction();
        try {
            $anggota->update([
                'status' => 'aktif',
                'tanggal_masuk' => now()->format('Y-m-d'),
            ]);

            // Buat rekening default
            $this->createRekeningDefault($anggota);

            DB::commit();

            return redirect()->route('anggota.pending_approval')
                ->with('success', "Anggota {$anggota->nama_lengkap} berhasil diaktifkan!");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal approve: ' . $e->getMessage());
        }
    }

    /**
     * Reject anggota baru (pending_aktif -> ditolak)
     */
    public function rejectAnggota($id)
    {
        $anggota = Anggota::findOrFail($id);

        if ($anggota->status !== 'pending_aktif') {
            return back()->with('error', 'Status anggota bukan pending_aktif.');
        }

        $anggota->update([
            'status' => 'tidak_aktif',
            'alasan_keluar' => 'Pendaftaran ditolak oleh admin',
        ]);

        return redirect()->route('anggota.pending_approval')
            ->with('success', "Anggota {$anggota->nama_lengkap} ditolak.");
    }

    /**
     * Export PDF data anggota keluar
     */
    public function historyTransaksi($id)
    {
        $anggota = Anggota::findOrFail($id);
        $rekeningIds = $anggota->rekeningSimpanan->pluck('id');

        $history = TransaksiSimpanan::whereIn('rekening_id', $rekeningIds)
            ->with(['rekening.produk'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('anggota.history', compact('anggota', 'history'));
    }

    /**
     * d. List saldo anggota
     */
    public function saldo(Request $request)
    {
        $query = Anggota::where('status', 'aktif')
            ->with(['cabang', 'rekeningSimpanan.produk']);

        // Filter
        if ($cabangId = $request->input('cabang_id')) {
            $query->where('cabang_id', $cabangId);
        }
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('no_anggota', 'like', "%{$search}%");
            });
        }

        $anggota = $query->orderBy('nama_lengkap')->paginate(15)->withQueryString();
        $cabangs = Cabang::where('aktif', true)->get();

        return view('anggota.saldo', compact('anggota', 'cabangs'));
    }

    /**
     * h. Laporan saldo anggota
     */
    public function laporanSaldo(Request $request)
    {
        $query = Anggota::with(['cabang', 'rekeningSimpanan.produk']);

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }
        if ($cabangId = $request->input('cabang_id')) {
            $query->where('cabang_id', $cabangId);
        }

        $anggota = $query->orderBy('nama_lengkap')->get();
        $cabangs = Cabang::where('aktif', true)->get();

        return view('anggota.laporan.saldo', compact('anggota', 'cabangs'));
    }

    /**
     * h. Laporan profil anggota
     */
    public function laporanProfil(Request $request)
    {
        $query = Anggota::with(['cabang']);

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }
        if ($perusahaanId = $request->input('perusahaan_id')) {
            $query->where('perusahaan_id', $perusahaanId);
        }

        $anggota = $query->orderBy('nama_lengkap')->get();
        $perusahaans = \App\Models\Perusahaan::where('aktif', true)->orderBy('nama')->get();

        return view('anggota.laporan.profil', compact('anggota', 'perusahaans'));
    }

    /**
     * h. Laporan rekap anggota
     */
    public function laporanRekap()
    {
        $totalAnggota = Anggota::count();
        $anggotaAktif = Anggota::where('status', 'aktif')->count();
        $anggotaKeluar = Anggota::where('status', 'keluar')->count();
        $totalSimpanan = Anggota::with('rekeningSimpanan')->get()->sum(fn ($a) => $a->rekeningSimpanan->sum('saldo'));

        $perCabang = Anggota::selectRaw('cabang_id, COUNT(*) as total, SUM(CASE WHEN status = "aktif" THEN 1 ELSE 0 END) as aktif')
            ->groupBy('cabang_id')
            ->with('cabang')
            ->get();

        $perPerusahaan = \App\Models\Perusahaan::withCount(['anggota' => function ($q) {
            $q->where('status', '!=', 'keluar');
        }])->orderByDesc('anggota_count')->get();

        return view('anggota.laporan.rekap', compact(
            'totalAnggota', 'anggotaAktif', 'anggotaKeluar', 'totalSimpanan',
            'perCabang', 'perPerusahaan'
        ));
    }

    /**
     * h. Laporan anggota keluar
     */
    public function laporanKeluar(Request $request)
    {
        $query = Anggota::where('status', 'keluar')
            ->with('cabang')
            ->orderBy('tanggal_keluar', 'desc');

        if ($from = $request->input('from')) {
            $query->whereDate('tanggal_keluar', '>=', $from);
        }
        if ($to = $request->input('to')) {
            $query->whereDate('tanggal_keluar', '<=', $to);
        }

        $anggota = $query->get();

        return view('anggota.laporan.keluar', compact('anggota'));
    }

    /**
     * Helper: Auto-create default rekening saat anggota baru
     */
    private function createRekeningDefault(Anggota $anggota): void
    {
        $produkSimpanan = \App\Models\ProdukSimpanan::where('aktif', true)->get();

        foreach ($produkSimpanan as $produk) {
            RekeningSimpanan::create([
                'anggota_id' => $anggota->id,
                'produk_id' => $produk->id,
                'no_rekening' => RekeningSimpanan::generateNoRekening($produk, $anggota->cabang),
                'saldo' => 0,
                'status' => 'aktif',
                'tanggal_buka' => now()->format('Y-m-d'),
            ]);
        }

        // Generate Jadwal Potongan Gaji untuk Simpanan Pokok (150.000 dicicil 3x @ 50.000)
        // Periode cicilan mengacu ke tanggal gajian (jika ada) atau akhir bulan
        $gaji = $anggota->gaji_pokok ?? 0;
        $tglGajian = $anggota->tanggal_gajian;
        $bulanMulai = now()->startOfMonth()->addMonth();
        for ($i = 0; $i < 3; $i++) {
            $periode = $bulanMulai->copy()->addMonths($i);
            if ($tglGajian) {
                $hari = (int) $tglGajian;
                $periode->day(min($hari, $periode->daysInMonth));
            } else {
                $periode->endOfMonth();
            }
            \App\Models\PotonganGaji::create([
                'anggota_id' => $anggota->id,
                'periode' => $periode->format('Y-m-d'),
                'gaji_bruto' => $gaji,
                'nominal_potongan' => 50000,
                'gaji_diterima' => max(0, $gaji - 50000),
                'jenis_potongan' => 'simpanan',
                'status' => 'pending',
                'keterangan' => 'Cicilan Simpanan Pokok (' . ($i + 1) . '/3)',
            ]);
        }
    }

    /**
     * Export Data Anggota ke Excel
     */
    public function exportAnggota(Request $request)
    {
        $filters = $request->only(['search', 'status', 'cabang_id', 'perusahaan_id']);
        return $this->excelDownload(new AnggotaExport($filters), 'data-anggota-' . date('Y-m-d') . '.xlsx');
    }

    /**
     * Export Saldo Anggota ke Excel
     */
    public function exportSaldo(Request $request)
    {
        $filters = $request->only(['search', 'cabang_id']);
        return $this->excelDownload(new SaldoExport($filters), 'saldo-anggota-' . date('Y-m-d') . '.xlsx');
    }

    /**
     * Export Profil Anggota ke Excel
     */
    public function exportProfil(Request $request)
    {
        $filters = $request->only(['status', 'perusahaan_id']);
        return $this->excelDownload(new ProfilExport($filters), 'profil-anggota-' . date('Y-m-d') . '.xlsx');
    }

    /**
     * Export Rekap Anggota ke Excel
     */
    public function exportRekap(Request $request)
    {
        return $this->excelDownload(new \App\Exports\Anggota\RekapAnggotaExport(), 'rekap-anggota-' . date('Y-m-d') . '.xlsx');
    }

    /**
     * Export Anggota Keluar ke Excel
     */
    public function exportKeluar(Request $request)
    {
        $filters = $request->only(['from', 'to']);
        return $this->excelDownload(new \App\Exports\Anggota\KeluarAnggotaExport($filters), 'anggota-keluar-' . date('Y-m-d') . '.xlsx');
    }

    // ─── PDF Reports ─────────────────────────────────────────────────────

    public function pdfProfil(Request $request)
    {
        try {
            $query = Anggota::with('cabang');
            if ($status = $request->input('status')) $query->where('status', $status);
            if ($perusahaanId = $request->input('perusahaan_id')) $query->where('perusahaan_id', $perusahaanId);

            $anggota = $query->orderBy('nama_lengkap')->get();
            $pdf = Pdf::loadView('anggota.pdf_profil', compact('anggota'));
            return $pdf->download('Profil_Anggota_' . date('Y-m-d') . '.pdf');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal generate PDF: ' . $e->getMessage());
        }
    }

    public function pdfKeluar($id)
    {
        try {
            $anggota = Anggota::with(['cabang', 'rekeningSimpanan.produk'])->findOrFail($id);

            if ($anggota->status !== 'keluar') {
                return back()->with('error', 'Hanya dapat mengekspor data untuk anggota yang sudah keluar.');
            }

            $rekeningIds = $anggota->rekeningSimpanan->pluck('id');
            $historyTransaksi = TransaksiSimpanan::whereIn('rekening_id', $rekeningIds)
                ->with(['rekening.produk'])
                ->orderBy('created_at', 'asc')
                ->get();

            $pdf = Pdf::loadView('anggota.pdf_keluar', compact('anggota', 'historyTransaksi'));
            return $pdf->download('Bukti_Penutupan_Keanggotaan_' . $anggota->no_anggota . '.pdf');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal generate PDF: ' . $e->getMessage());
        }
    }

    /**
     * Form Import Anggota dari Excel
     */
    public function importForm()
    {
        return view('anggota.import');
    }

    /**
     * Proses Import Anggota dari Excel
     */
    public function importProcess(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:10240',
        ]);

        try {
            $import = new AnggotaImport();
            Excel::import($import, $request->file('file'));
            $hasil = $import->getHasil();

            $message = "Import selesai. Berhasil: {$hasil['berhasil']}, Gagal: {$hasil['gagal']}.";

            return back()
                ->with('import_hasil', $hasil['hasil'])
                ->with('success', $message);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal import: ' . $e->getMessage());
        }
    }

    /**
     * Download Template Import Anggota
     */
    public function downloadTemplate()
    {
        return $this->excelDownload(
            new \App\Exports\Anggota\TemplateAnggotaExport(),
            'template-import-anggota.xlsx'
        );
    }

    /**
     * Form Import Master Data (Template.xlsx format)
     */
    public function importMasterForm()
    {
        return view('anggota.import_master');
    }

    /**
     * Proses Import Master Data dari Template.xlsx (4 sheets)
     */
    public function importMasterProcess(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:10240',
            'reset_data' => 'nullable|boolean',
        ]);

        $resetData = $request->boolean('reset_data', true);

        try {
            $import = new MasterDataImport($resetData);
            Excel::import($import, $request->file('file'));

            $hasil = $import->getHasil();

            $suksesOST = collect($hasil['OST'] ?? [])->where('status', 'berhasil')->count();
            $gagalOST = collect($hasil['OST'] ?? [])->where('status', 'gagal')->count();
            $suksesSimpanan = collect(array_merge($hasil['SIMPANAN POKOK DAN WAJIB'] ?? [], $hasil['SEMUA SIMPANAN'] ?? []))->where('status', 'berhasil')->count();
            $gagalSimpanan = collect(array_merge($hasil['SIMPANAN POKOK DAN WAJIB'] ?? [], $hasil['SEMUA SIMPANAN'] ?? []))->where('status', 'gagal')->count();

            $message = "Import selesai. Anggota: {$suksesOST} berhasil, {$gagalOST} gagal. Rekening: {$suksesSimpanan} berhasil, {$gagalSimpanan} gagal.";

            return redirect()->route('anggota.import.master')
                ->with('success', $message);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal import master data: ' . $e->getMessage());
        }
    }
}
