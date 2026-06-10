<?php

namespace App\Http\Controllers;

use App\Models\Anggota;
use App\Models\Cabang;
use App\Models\ChartOfAccount;
use App\Models\Jurnal;
use App\Models\JurnalDetail;
use App\Models\Kas;
use App\Models\TransaksiSimpanan;
use App\Models\TransaksiPembiayaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AkuntansiController extends Controller
{
    /**
     * a. Transaksi akuntansi — list semua transaksi (auto + manual)
     */
    public function index(Request $request)
    {
        $query = Jurnal::with(['details.akun', 'pembuat', 'cabang']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('no_jurnal', 'like', "%{$search}%")
                  ->orWhere('keterangan', 'like', "%{$search}%");
            });
        }
        if ($jenis = $request->input('jenis')) {
            $query->where('jenis', $jenis);
        }
        if ($from = $request->input('from')) $query->whereDate('tanggal', '>=', $from);
        if ($to = $request->input('to')) $query->whereDate('tanggal', '<=', $to);
        if ($cabangId = $request->input('cabang_id')) $query->where('cabang_id', $cabangId);

        $jurnals = $query->latest('tanggal')->latest('created_at')->paginate(15)->withQueryString();
        $cabangList = Cabang::where('aktif', true)->get();

        return view('akuntansi.index', compact('jurnals', 'cabangList'));
    }

    /**
     * b. & h. Transaksi jurnal — form input jurnal baru
     */
    public function createJurnal()
    {
        $accounts = ChartOfAccount::where('aktif', true)->where('is_header', false)
            ->orderBy('kode_akun')->get();
        $kasList = Kas::where('aktif', true)->get();
        $cabangList = Cabang::where('aktif', true)->get();

        return view('akuntansi.jurnal_create', compact('accounts', 'kasList', 'cabangList'));
    }

    /**
     * b. Simpan jurnal
     */
    public function storeJurnal(Request $request)
    {
        $validated = $request->validate([
            'cabang_id' => 'required|uuid|exists:cabang,id',
            'tanggal' => 'required|date',
            'jenis' => 'required|in:manual,koreksi',
            'keterangan' => 'required|string|max:500',
            'entries' => 'required|array|min:2',
            'entries.*.akun_id' => 'required|uuid|exists:chart_of_accounts,id',
            'entries.*.debet' => 'nullable|numeric|min:0',
            'entries.*.kredit' => 'nullable|numeric|min:0',
            'entries.*.keterangan' => 'nullable|string|max:255',
        ]);

        // Validate balance
        $totalDebet = 0;
        $totalKredit = 0;
        foreach ($validated['entries'] as $entry) {
            $totalDebet += (float) ($entry['debet'] ?? 0);
            $totalKredit += (float) ($entry['kredit'] ?? 0);
        }

        if (abs($totalDebet - $totalKredit) > 0.01) {
            return back()->withErrors(['entries' => 'Jurnal tidak balance! Debet: Rp ' . number_format($totalDebet, 0, ',', '.') . ' ≠ Kredit: Rp ' . number_format($totalKredit, 0, ',', '.')])->withInput();
        }

        DB::beginTransaction();
        try {
            $jurnal = Jurnal::create([
                'cabang_id' => $validated['cabang_id'],
                'no_jurnal' => 'JRN-' . now()->format('ymd') . '-' . strtoupper(substr(uniqid(), -6)),
                'tanggal' => $validated['tanggal'],
                'keterangan' => $validated['keterangan'],
                'jenis' => $validated['jenis'],
                'dibuat_oleh' => auth()->id(),
            ]);

            foreach ($validated['entries'] as $entry) {
                JurnalDetail::create([
                    'jurnal_id' => $jurnal->id,
                    'akun_id' => $entry['akun_id'],
                    'debet' => $entry['debet'] ?? 0,
                    'kredit' => $entry['kredit'] ?? 0,
                    'keterangan' => $entry['keterangan'] ?? null,
                ]);
            }

            // Update kas saldo if kas account is used
            foreach ($validated['entries'] as $entry) {
                $kas = Kas::where('akun_id', $entry['akun_id'])->first();
                if ($kas) {
                    $kas->saldo += ($entry['debet'] ?? 0) - ($entry['kredit'] ?? 0);
                    $kas->save();
                }
            }

            DB::commit();
            return redirect()->route('akuntansi.jurnal')->with('success', 'Jurnal berhasil disimpan!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * c. Pembatalan jurnal
     */
    public function batalForm($id)
    {
        $jurnal = Jurnal::with(['details.akun', 'pembuat'])->findOrFail($id);

        if ($jurnal->is_cancelled) {
            return back()->with('error', 'Jurnal sudah dibatalkan.');
        }

        return view('akuntansi.batal', compact('jurnal'));
    }

    public function batalSubmit(Request $request, $id)
    {
        $validated = $request->validate([
            'alasan' => 'required|string|max:500',
        ]);

        $jurnal = Jurnal::with('details')->findOrFail($id);

        DB::beginTransaction();
        try {
            // Reverse kas saldo
            foreach ($jurnal->details as $detail) {
                $kas = Kas::where('akun_id', $detail->akun_id)->first();
                if ($kas) {
                    $kas->saldo -= ($detail->debet ?? 0) - ($detail->kredit ?? 0);
                    $kas->save();
                }
            }

            // Create reversal journal
            $reversal = Jurnal::create([
                'cabang_id' => $jurnal->cabang_id,
                'no_jurnal' => 'REV-' . now()->format('ymd') . '-' . strtoupper(substr(uniqid(), -6)),
                'tanggal' => now()->format('Y-m-d'),
                'keterangan' => 'Pembatalan jurnal ' . $jurnal->no_jurnal . ' — ' . $validated['alasan'],
                'jenis' => 'koreksi',
                'ref_id' => $jurnal->id,
                'ref_tabel' => 'jurnal',
                'dibuat_oleh' => auth()->id(),
            ]);

            // Reverse entries
            foreach ($jurnal->details as $detail) {
                JurnalDetail::create([
                    'jurnal_id' => $reversal->id,
                    'akun_id' => $detail->akun_id,
                    'debet' => $detail->kredit,
                    'kredit' => $detail->debet,
                    'keterangan' => 'Reversal: ' . ($detail->keterangan ?? ''),
                ]);
            }

            // Mark original as cancelled
            $jurnal->is_cancelled = true;
            $jurnal->cancelled_by = auth()->id();
            $jurnal->cancelled_at = now();
            $jurnal->alasan_batal = $validated['alasan'];
            $jurnal->save();

            DB::commit();
            return redirect()->route('akuntansi.jurnal')->with('success', 'Jurnal berhasil dibatalkan. Reversal otomatis dibuat.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    /**
     * d. Revisi jurnal
     */
    public function revisiForm($id)
    {
        $jurnal = Jurnal::with(['details.akun'])->findOrFail($id);

        if ($jurnal->is_cancelled) {
            return back()->with('error', 'Jurnal yang sudah dibatalkan tidak bisa direvisi.');
        }

        $accounts = ChartOfAccount::where('aktif', true)->where('is_header', false)
            ->orderBy('kode_akun')->get();

        return view('akuntansi.revisi', compact('jurnal', 'accounts'));
    }

    public function revisiSubmit(Request $request, $id)
    {
        $validated = $request->validate([
            'keterangan' => 'required|string|max:500',
            'entries' => 'required|array|min:2',
            'entries.*.akun_id' => 'required|uuid|exists:chart_of_accounts,id',
            'entries.*.debet' => 'nullable|numeric|min:0',
            'entries.*.kredit' => 'nullable|numeric|min:0',
            'entries.*.keterangan' => 'nullable|string|max:255',
        ]);

        // Validate balance
        $totalDebet = 0;
        $totalKredit = 0;
        foreach ($validated['entries'] as $entry) {
            $totalDebet += (float) ($entry['debet'] ?? 0);
            $totalKredit += (float) ($entry['kredit'] ?? 0);
        }

        if (abs($totalDebet - $totalKredit) > 0.01) {
            return back()->withErrors(['entries' => 'Jurnal tidak balance!'])->withInput();
        }

        DB::beginTransaction();
        try {
            $jurnal = Jurnal::with('details')->findOrFail($id);

            // Reverse original
            foreach ($jurnal->details as $detail) {
                $kas = Kas::where('akun_id', $detail->akun_id)->first();
                if ($kas) {
                    $kas->saldo -= ($detail->debet ?? 0) - ($detail->kredit ?? 0);
                    $kas->save();
                }
            }
            $jurnal->details()->delete();

            // Create new entries
            foreach ($validated['entries'] as $entry) {
                JurnalDetail::create([
                    'jurnal_id' => $jurnal->id,
                    'akun_id' => $entry['akun_id'],
                    'debet' => $entry['debet'] ?? 0,
                    'kredit' => $entry['kredit'] ?? 0,
                    'keterangan' => $entry['keterangan'] ?? null,
                ]);

                $kas = Kas::where('akun_id', $entry['akun_id'])->first();
                if ($kas) {
                    $kas->saldo += ($entry['debet'] ?? 0) - ($entry['kredit'] ?? 0);
                    $kas->save();
                }
            }

            $jurnal->keterangan = $validated['keterangan'];
            $jurnal->save();

            DB::commit();
            return redirect()->route('akuntansi.jurnal')->with('success', 'Jurnal berhasil direvisi!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    /**
     * e. Setup ledger/akun (COA management)
     */
    public function coa(Request $request)
    {
        $query = ChartOfAccount::with('parent');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('kode_akun', 'like', "%{$search}%")
                  ->orWhere('nama_akun', 'like', "%{$search}%");
            });
        }
        if ($kelompok = $request->input('kelompok')) {
            $query->where('kelompok', $kelompok);
        }

        $coa = $query->orderBy('kode_akun')->paginate(20)->withQueryString();
        $parentAccounts = ChartOfAccount::where('is_header', true)->where('aktif', true)
            ->orderBy('kode_akun')->get();

        return view('akuntansi.coa', compact('coa', 'parentAccounts'));
    }

    public function storeCoa(Request $request)
    {
        $validated = $request->validate([
            'kode_akun' => 'required|string|max:10|unique:chart_of_accounts,kode_akun',
            'nama_akun' => 'required|string|max:150',
            'kelompok' => 'required|in:aset,liabilitas,ekuitas,pendapatan,beban',
            'posisi_normal' => 'required|in:debet,kredit',
            'is_header' => 'nullable|boolean',
            'parent_id' => 'nullable|uuid|exists:chart_of_accounts,id',
        ]);

        ChartOfAccount::create($validated);
        return back()->with('success', 'Akun berhasil ditambahkan!');
    }

    public function updateCoa(Request $request, $id)
    {
        $coa = ChartOfAccount::findOrFail($id);

        $validated = $request->validate([
            'nama_akun' => 'required|string|max:150',
            'kelompok' => 'required|in:aset,liabilitas,ekuitas,pendapatan,beban',
            'posisi_normal' => 'required|in:debet,kredit',
            'aktif' => 'nullable|boolean',
        ]);

        $coa->update($validated);
        return back()->with('success', 'Akun berhasil diupdate!');
    }

    /**
     * f. Setup kas
     */
    public function kas(Request $request)
    {
        $query = Kas::with(['akun', 'cabang']);

        if ($cabangId = $request->input('cabang_id')) {
            $query->where('cabang_id', $cabangId);
        }

        $kasList = $query->orderBy('kode_kas')->get();
        $cabangList = Cabang::where('aktif', true)->get();
        $kasAccounts = ChartOfAccount::where('aktif', true)
            ->where('kelompok', 'aset')
            ->where('is_header', false)
            ->orderBy('kode_akun')->get();

        return view('akuntansi.kas', compact('kasList', 'cabangList', 'kasAccounts'));
    }

    public function storeKas(Request $request)
    {
        $validated = $request->validate([
            'cabang_id' => 'required|uuid|exists:cabang,id',
            'kode_kas' => 'required|string|max:20|unique:kas,kode_kas',
            'nama_kas' => 'required|string|max:100',
            'akun_id' => 'required|uuid|exists:chart_of_accounts,id',
            'keterangan' => 'nullable|string|max:500',
        ]);

        Kas::create($validated);
        return back()->with('success', 'Kas berhasil ditambahkan!');
    }

    public function updateKasSaldo(Request $request, $id)
    {
        $validated = $request->validate([
            'saldo' => 'required|numeric|min:0',
        ]);

        $kas = Kas::findOrFail($id);
        $kas->saldo = $validated['saldo'];
        $kas->save();

        return back()->with('success', 'Saldo kas berhasil diupdate!');
    }

    /**
     * i. Laporan kas
     */
    public function laporanKas(Request $request)
    {
        $query = Kas::with(['akun', 'cabang']);

        if ($cabangId = $request->input('cabang_id')) {
            $query->where('cabang_id', $cabangId);
        }

        $kasList = $query->orderBy('kode_kas')->get();
        $totalKas = $kasList->sum('saldo');
        $cabangList = Cabang::where('aktif', true)->get();

        return view('akuntansi.laporan.kas', compact('kasList', 'totalKas', 'cabangList'));
    }

    /**
     * i. Laporan neraca saldo
     */
    public function neracaSaldo(Request $request)
    {
        $accounts = ChartOfAccount::where('aktif', true)->where('is_header', false)
            ->orderBy('kode_akun')->get();

        $periodeBulan = $request->input('bulan', now()->month);
        $periodeTahun = $request->input('tahun', now()->year);

        // Calculate saldo for each account
        foreach ($accounts as $account) {
            $debet = JurnalDetail::where('akun_id', $account->id)
                ->join('jurnal', 'jurnal_detail.jurnal_id', '=', 'jurnal.id')
                ->where('jurnal.is_cancelled', false)
                ->whereMonth('jurnal.tanggal', '<=', $periodeBulan)
                ->whereYear('jurnal.tanggal', '<=', $periodeTahun)
                ->sum('jurnal_detail.debet');

            $kredit = JurnalDetail::where('akun_id', $account->id)
                ->join('jurnal', 'jurnal_detail.jurnal_id', '=', 'jurnal.id')
                ->where('jurnal.is_cancelled', false)
                ->whereMonth('jurnal.tanggal', '<=', $periodeBulan)
                ->whereYear('jurnal.tanggal', '<=', $periodeTahun)
                ->sum('jurnal_detail.kredit');

            $account->saldo = $account->posisi_normal === 'debet'
                ? $debet - $kredit
                : $kredit - $debet;
        }

        $totalDebet = $accounts->sum(function ($a) {
            return $a->posisi_normal === 'debet' ? max(0, $a->saldo) : 0;
        });
        $totalKredit = $accounts->sum(function ($a) {
            return $a->posisi_normal === 'kredit' ? max(0, $a->saldo) : 0;
        });

        return view('akuntansi.laporan.neraca_saldo', compact('accounts', 'periodeBulan', 'periodeTahun', 'totalDebet', 'totalKredit'));
    }

    /**
     * i. Laporan neraca (balance sheet)
     */
    public function neraca(Request $request)
    {
        $accounts = ChartOfAccount::where('aktif', true)->where('is_header', false)
            ->whereIn('kelompok', ['aset', 'liabilitas', 'ekuitas'])
            ->orderBy('kode_akun')->get();

        $aset = $accounts->where('kelompok', 'aset');
        $liabilitas = $accounts->where('kelompok', 'liabilitas');
        $ekuitas = $accounts->where('kelompok', 'ekuitas');

        // Calculate saldo
        foreach ($accounts as $account) {
            $debet = JurnalDetail::where('akun_id', $account->id)
                ->join('jurnal', 'jurnal_detail.jurnal_id', '=', 'jurnal.id')
                ->where('jurnal.is_cancelled', false)
                ->sum('jurnal_detail.debet');

            $kredit = JurnalDetail::where('akun_id', $account->id)
                ->join('jurnal', 'jurnal_detail.jurnal_id', '=', 'jurnal.id')
                ->where('jurnal.is_cancelled', false)
                ->sum('jurnal_detail.kredit');

            $account->saldo = $account->posisi_normal === 'debet'
                ? $debet - $kredit
                : $kredit - $debet;
        }

        $totalAset = $aset->sum('saldo');
        $totalLiabilitas = $liabilitas->sum('saldo');
        $totalEkuitas = $ekuitas->sum('saldo');

        return view('akuntansi.laporan.neraca', compact('aset', 'liabilitas', 'ekuitas', 'totalAset', 'totalLiabilitas', 'totalEkuitas'));
    }

    /**
     * Detail jurnal
     */
    public function detailJurnal($id)
    {
        $jurnal = Jurnal::with(['details.akun', 'pembuat', 'cabang'])->findOrFail($id);
        return view('akuntansi.detail', compact('jurnal'));
    }

    /**
     * List all jurnal (for transaksi view)
     */
    public function jurnal(Request $request)
    {
        return $this->index($request);
    }

    /**
     * Buku Besar
     */
    public function bukuBesar(Request $request)
    {
        $accounts = ChartOfAccount::where('aktif', true)->where('is_header', false)
            ->orderBy('kode_akun')->get();
        $selectedAccountId = $request->input('akun_id');

        $mutasi = collect();
        $saldoAwal = 0;
        $activeAccount = null;

        if ($selectedAccountId) {
            $activeAccount = ChartOfAccount::find($selectedAccountId);

            $query = JurnalDetail::with('jurnal')
                ->where('akun_id', $selectedAccountId)
                ->join('jurnal', 'jurnal_detail.jurnal_id', '=', 'jurnal.id')
                ->where('jurnal.is_cancelled', false)
                ->select('jurnal_detail.*', 'jurnal.tanggal', 'jurnal.no_jurnal', 'jurnal.keterangan as jurnal_keterangan')
                ->orderBy('jurnal.tanggal', 'asc')
                ->orderBy('jurnal.created_at', 'asc');

            $mutasi = $query->paginate(20)->withQueryString();
        }

        return view('akuntansi.buku_besar', compact('accounts', 'activeAccount', 'mutasi', 'selectedAccountId'));
    }
}
