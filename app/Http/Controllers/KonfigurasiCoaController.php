<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use App\Models\KonfigurasiCoa;
use Illuminate\Http\Request;

class KonfigurasiCoaController extends Controller
{
    public function index()
    {
        $configs = KonfigurasiCoa::orderBy('jenis')->orderBy('key')->get();
        $coaList = ChartOfAccount::where('aktif', true)->orderBy('kode_akun')->get();
        return view('konfigurasi_coa.index', compact('configs', 'coaList'));
    }

    public function update(Request $request, $id)
    {
        $config = KonfigurasiCoa::findOrFail($id);

        $validated = $request->validate([
            'kode_akun' => 'required|string|max:20|exists:chart_of_accounts,kode_akun',
            'label' => 'required|string|max:100',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $config->update($validated);

        return redirect()->route('konfigurasi-coa.index')
            ->with('success', 'Konfigurasi COA berhasil diupdate.');
    }
}
