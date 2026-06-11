<?php

namespace App\Http\Controllers;

use App\Models\Perusahaan;
use Illuminate\Http\Request;

class PerusahaanController extends Controller
{
    public function index()
    {
        $perusahaan = Perusahaan::orderBy('kode')->paginate(20);
        return view('perusahaan.index', compact('perusahaan'));
    }

    public function create()
    {
        return view('perusahaan.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'alamat' => 'nullable|string',
            'telp' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'aktif' => 'boolean',
        ]);

        $validated['aktif'] = $request->boolean('aktif');
        $validated['kode'] = $this->generateKode($validated['nama']);

        Perusahaan::create($validated);

        return redirect()->route('perusahaan.index')
            ->with('success', 'Perusahaan berhasil ditambahkan!');
    }

    private function generateKode(string $nama): string
    {
        $words = preg_split('/\s+/', trim($nama));
        $kode = '';
        foreach ($words as $word) {
            if (!empty($word)) {
                $kode .= strtoupper(mb_substr($word, 0, 1));
            }
        }
        $kode = strtoupper(substr($kode, 0, 5));

        $original = $kode;
        $i = 1;
        while (\App\Models\Perusahaan::where('kode', $kode)->exists()) {
            $kode = $original . $i;
            $i++;
        }

        return $kode;
    }

    public function edit($id)
    {
        $perusahaan = Perusahaan::findOrFail($id);
        return view('perusahaan.edit', compact('perusahaan'));
    }

    public function update(Request $request, $id)
    {
        $perusahaan = Perusahaan::findOrFail($id);

        $validated = $request->validate([
            'kode' => 'required|string|max:20|unique:perusahaan,kode,' . $id,
            'nama' => 'required|string|max:255',
            'alamat' => 'nullable|string',
            'telp' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'aktif' => 'boolean',
        ]);

        $validated['aktif'] = $request->boolean('aktif');

        $perusahaan->update($validated);

        return redirect()->route('perusahaan.index')
            ->with('success', 'Perusahaan berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $perusahaan = Perusahaan::findOrFail($id);

        if ($perusahaan->anggota()->exists()) {
            return back()->withErrors(['error' => 'Tidak dapat menghapus perusahaan yang masih memiliki anggota.']);
        }

        $perusahaan->delete();

        return redirect()->route('perusahaan.index')
            ->with('success', 'Perusahaan berhasil dihapus!');
    }
}
