import re
import glob

def clean_file(filepath):
    with open(filepath, 'r') as f:
        content = f.read()

    # DatabaseSeeder
    if 'DatabaseSeeder.php' in filepath:
        content = re.sub(r'CabangSeeder::class,\s*', '', content)
    
    # Exports
    content = re.sub(r"with\(\s*'\w*(?:cabang)\w*'\s*(?:,\s*)?", "with(", content)
    content = re.sub(r"with\(\s*\[\s*'\w*(?:cabang)\w*'\s*(?:,\s*)?", "with([", content)
    content = re.sub(r"with\(\s*\[([^]]*?)(?:,\s*)?'\w*(?:cabang)\w*'\s*(?:,\s*)?([^]]*)\]\s*\)", r"with([\1\2])", content)
    content = re.sub(r"with\(\[\s*\]\)", "query()", content)
    content = re.sub(r"with\(\s*''\s*\)", "query()", content)
    content = re.sub(r"with\(\s*'\s*,\s*", "with('", content)

    # Specific fixes
    content = content.replace("with('cabang')", "query()")
    content = content.replace("with(['rekeningSimpanan.produk', 'cabang'])", "with(['rekeningSimpanan.produk'])")
    
    # Remove filter cabang_id blocks
    content = re.sub(r"if\s*\(!empty\(\$this->filters\['cabang_id'\]\)\)\s*\{\s*\$query->where\('cabang_id',\s*\$this->filters\['cabang_id'\]\);\s*\}\s*", "", content)
    content = re.sub(r"if\s*\(!empty\(\$this->filters\['cabang_id'\]\)\)\s*\{\s*\$query->whereHas\('anggota',\s*fn\(\$q\)\s*=>\s*\$q->where\('cabang_id',\s*\$this->filters\['cabang_id'\]\)\);\s*\}\s*", "", content)

    # Remove Cabang header
    content = re.sub(r"\s*'Cabang',", "", content)
    
    # Remove $anggota->cabang?->nama
    content = re.sub(r"\s*\$anggota->cabang\?->nama\s*\?\?\s*'-',", "", content)

    # TemplatePetunjukAnggotaSheet
    content = re.sub(r"\s*\['Kolom C - Kode Cabang'\],", "", content)
    content = re.sub(r"\s*\['Isi dengan kode cabang\. Wajib diisi\. Cek daftar cabang di sistem\.'\],", "", content)

    # RekapAnggotaExport specific
    content = re.sub(r"\$perCabang\s*=\s*Anggota::selectRaw.*?;.*?;.*?;", "", content, flags=re.DOTALL)
    content = re.sub(r"// Per Cabang.*?foreach.*?\{.*?\}\s*", "", content, flags=re.DOTALL)

    with open(filepath, 'w') as f:
        f.write(content)

for filepath in glob.glob('app/Exports/**/*.php', recursive=True):
    clean_file(filepath)
clean_file('database/seeders/DatabaseSeeder.php')

import os
if os.path.exists('database/seeders/CabangSeeder.php'):
    os.remove('database/seeders/CabangSeeder.php')

print("Done cleaning exports and seeders")
