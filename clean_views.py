import re
import glob

def clean_blade(filepath):
    with open(filepath, 'r') as f:
        content = f.read()

    # Remove Cabang <select> blocks (usually wrapped in a div)
    content = re.sub(r'<div>\s*<label[^>]*>Cabang[^<]*</label>\s*<select name="cabang_id"[^>]*>.*?</select>\s*</div>', '', content, flags=re.DOTALL)
    content = re.sub(r'<div class="min-w-\[150px\]">\s*<label[^>]*>Cabang[^<]*</label>\s*<select name="cabang_id"[^>]*>.*?</select>\s*</div>', '', content, flags=re.DOTALL)
    content = re.sub(r'<div class="min-w-\[150px\]">\s*<select name="cabang_id"[^>]*>.*?</select>\s*</div>', '', content, flags=re.DOTALL)
    
    # Remove standalone labels and selects for cabang
    content = re.sub(r'<label[^>]*>Cabang[^<]*</label>\s*<select name="cabang_id"[^>]*>.*?</select>', '', content, flags=re.DOTALL)
    
    # Remove @error('cabang_id') blocks
    content = re.sub(r"@error\('cabang_id'\).*?@enderror", '', content, flags=re.DOTALL)

    # Remove TH
    content = re.sub(r'<th[^>]*>Cabang</th>', '', content)
    
    # Remove TD containing cabang
    content = re.sub(r'<td[^>]*>\{\{\s*\$\w+(?:->|\[\')cabang.*?(?:->|\]).*?\}\}\s*</td>', '', content)
    
    # Remove div containing cabang nama
    content = re.sub(r'<div class="text-xs text-gray-500">\{\{\s*\$\w+->cabang\?->nama\s*\}\}</div>', '', content)
    
    # PDF files
    content = re.sub(r'<div><span class="label">Cabang</span>: \{\{\s*\$\w+->cabang\?->nama.*\}\}</div>', '', content)
    content = re.sub(r'<th>Cabang</th>', '', content)
    content = re.sub(r'<td>\{\{\s*\$\w+->cabang->nama.*\}\}</td>', '', content)
    
    # Akuntansi detail
    content = re.sub(r' \| \{\{\s*\$\w+->cabang\?->nama\s*\}\}', '', content)

    # Show blade detail
    content = re.sub(r'<div class="mb-4">\s*<p class="text-sm text-gray-500 mb-1">Cabang</p>\s*<p class="font-medium">\{\{\s*\$\w+->cabang\?->nama.*\}\}</p>\s*</div>', '', content, flags=re.DOTALL)
    
    # Import instructions
    content = re.sub(r',Kode Cabang', '', content)
    content = re.sub(r'<span[^>]*>Kode Cabang \* wajib</span>', '', content)

    # Rekap per cabang block
    content = re.sub(r'<!-- Per Cabang -->.*?<!-- Per Perusahaan -->', '<!-- Per Perusahaan -->', content, flags=re.DOTALL)

    # Fix any remaining stray <div class="col-span-..."> related to cabang
    content = re.sub(r'<div[^>]*>\s*<label[^>]*>Cabang\s*(?:<span[^>]*>\*</span>)?</label>\s*<select name="cabang_id".*?</select>\s*</div>', '', content, flags=re.DOTALL)
    
    with open(filepath, 'w') as f:
        f.write(content)

for filepath in glob.glob('resources/views/**/*.blade.php', recursive=True):
    clean_blade(filepath)

print("Done cleaning views")
