import re
import glob

def clean_file(filepath):
    with open(filepath, 'r') as f:
        content = f.read()

    # Remove Cabang model import
    content = re.sub(r'use App\\Models\\Cabang;\n', '', content)
    
    # Remove private Cabang $cabang;
    content = re.sub(r'private Cabang \$cabang;\n\s*', '', content)
    
    # Remove $this->cabang creation block
    content = re.sub(r'\$this->cabang = Cabang::create\(\[\s*\'kode\' => \'CBG-SMK\', \'nama\' => \'Cabang Smoke Test\',\s*\'alamat\' => \'Jl\. Test No\. 1\', \'telp\' => \'021-12345678\', \'aktif\' => true,\s*\]\);\n\s*', '', content)
    content = re.sub(r'\$this->cabang = Cabang::create\(\[\s*\'nama\' => \'Cabang Test\',\s*\'kode\' => \'CBG-TEST\',\s*\'alamat\' => \'Jl\. Test\',\s*\'telp\' => \'021-12345678\',\s*\]\);\n\s*', '', content)
    
    # Remove 'cabang_id' => $this->cabang->id,
    content = re.sub(r"'cabang_id' => \$this->cabang->id,\n\s*", '', content)
    content = re.sub(r"'cabang_id' => \$this->cabang->id,\s*", '', content)
    
    # Remove 'cabang_id' from validation rules testing
    content = re.sub(r", 'cabang_id'", '', content)

    with open(filepath, 'w') as f:
        f.write(content)

for filepath in glob.glob('tests/Feature/*.php'):
    clean_file(filepath)

print("Done cleaning tests")
