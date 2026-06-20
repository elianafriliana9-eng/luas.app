import re
import glob

def clean_file(filepath):
    with open(filepath, 'r') as f:
        content = f.read()

    # Remove $cabangs = Cabang::where('aktif', true)->get();
    content = re.sub(r'\$cabangs\s*=\s*Cabang::where\([^)]+\)->get\(\);\s*', '', content)
    # Remove use App\Models\Cabang;
    content = re.sub(r'use App\\Models\\Cabang;\s*', '', content)
    
    # Replace compact('anggota', 'cabangs', 'perusahaans') -> compact('anggota', 'perusahaans')
    content = re.sub(r"compact\(\s*'anggota'\s*,\s*'cabangs'\s*,\s*'perusahaans'\s*\)", "compact('anggota', 'perusahaans')", content)
    content = re.sub(r"compact\(\s*'anggota'\s*,\s*'cabangs'\s*\)", "compact('anggota')", content)
    
    # Replace with('cabang') -> with() or remove it
    content = re.sub(r"->with\('cabang'\)", "", content)
    content = re.sub(r"::with\('cabang'\)", "::query()", content)
    content = re.sub(r"::with\(\['cabang'\]\)", "::query()", content)
    content = re.sub(r"with\(\['cabang',\s*", "with(['", content)
    content = re.sub(r"with\(\[\s*'cabang'\s*,\s*", "with(['", content)
    content = re.sub(r",\s*'cabang'\]\)", "])", content)
    
    # Remove filter blocks
    content = re.sub(r"// Filter cabang\s*if\s*\(\$cabangId\s*=\s*\$request->input\('cabang_id'\)\)\s*\{\s*\$query->where\('cabang_id',\s*\$cabangId\);\s*\}\s*", "", content)
    content = re.sub(r"if\s*\(\$cabangId\s*=\s*\$request->input\('cabang_id'\)\)\s*\{\s*\$query->where\('cabang_id',\s*\$cabangId\);\s*\}\s*", "", content)
    content = re.sub(r"if\s*\(\$cabangId\s*=\s*\$request->input\('cabang_id'\)\)\s*\$query->where\('cabang_id',\s*\$cabangId\);\s*", "", content)

    # Remove cabang_id from $request->only
    content = re.sub(r"'cabang_id',\s*", "", content)
    
    # RekeningSimpanan::generateNoRekening($produk, $anggota->cabang) -> RekeningSimpanan::generateNoRekening($produk)
    content = re.sub(r"RekeningSimpanan::generateNoRekening\(\$produk,\s*\$anggota->cabang\)", "RekeningSimpanan::generateNoRekening($produk)", content)

    # Remove perCabang block in Rekap
    content = re.sub(r"\$perCabang\s*=\s*Anggota::selectRaw\([^;]+;\s*", "", content)
    content = re.sub(r"'perCabang',\s*", "", content)

    # AkuntansiController specific
    content = re.sub(r"\$cabangList\s*=\s*Cabang::where\([^)]+\)->get\(\);\s*", "", content)
    content = re.sub(r"compact\(\s*'jurnals'\s*,\s*'cabangList'\s*\)", "compact('jurnals')", content)
    content = re.sub(r"compact\(\s*'accounts'\s*,\s*'kasList'\s*,\s*'cabangList'\s*\)", "compact('accounts', 'kasList')", content)
    content = re.sub(r"compact\(\s*'kasList'\s*,\s*'cabangList'\s*,\s*'kasAccounts'\s*\)", "compact('kasList', 'kasAccounts')", content)
    content = re.sub(r"compact\(\s*'kasList'\s*,\s*'totalKas'\s*,\s*'cabangList'\s*\)", "compact('kasList', 'totalKas')", content)
    content = re.sub(r"'cabang_id'\s*=>\s*'required\|uuid\|exists:cabang,id',\s*", "", content)
    content = re.sub(r"'cabang_id'\s*=>\s*\$validated\['cabang_id'\],\s*", "", content)
    content = re.sub(r"'cabang_id'\s*=>\s*\$jurnal->cabang_id,\s*", "", content)

    with open(filepath, 'w') as f:
        f.write(content)

for f in ['app/Http/Controllers/AnggotaController.php', 'app/Http/Controllers/AkuntansiController.php']:
    clean_file(f)

print("Done cleaning controllers")
