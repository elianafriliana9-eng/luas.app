# Update — 11 Juni 2026

## 1. Master Data: Perusahaan (PT) Module
- **Migration**: `create_perusahaan_table` (UUID, kode, nama, alamat, telp, email, aktif)
- **Model**: `Perusahaan` with `HasUuid`, `anggota()` HasMany relation
- **Controller**: `PerusahaanController` full CRUD with auto-generated kode from nama initials
- **Views**: `perusahaan/{index,create,edit}.blade.php`
- **Routes**: 6 routes in `web.php`
- **Sidebar**: Accordion "Master Data" → Perusahaan (PT) + Anggota

## 2. Removed Departemen & Jabatan from Anggota
- **Migration**: `add_perusahaan_id_to_anggota_table` (add `perusahaan_id` FK, drop `departemen`/`jabatan`)
- **Anggota Model**: Removed from `$fillable`, added `perusahaan()` BelongsTo
- **AnggotaController**: Removed validation rules, filters, and queries for departemen/jabatan; added perusahaan filter
- **Views updated**:
  - `anggota/{index,create,edit,show}.blade.php` — replaced with Perusahaan dropdown/column
  - `anggota/laporan/{keluar,profil,rekap}.blade.php` — replaced with Perusahaan
  - `payroll/{index,detail}.blade.php` — replaced with Perusahaan
  - `pembiayaan/registrasi.blade.php` — replaced with Perusahaan
  - `simpanan/{create,rekening_baru}.blade.php` — replaced with Perusahaan
- **Exports**: `AnggotaExport`, `ProfilExport`, `KeluarAnggotaExport`, `RekapAnggotaExport` — replaced columns
- **Imports**: `AnggotaImport` — removed departemen/jabatan fields
- **Templates**: `TemplateAnggotaSheet`, `TemplatePetunjukAnggotaSheet` — removed columns
- **API**: `MemberController` — removed from dashboard & profile responses
- **PayrollController**: Removed `orderBy('departemen')`

## 3. Seeder Updates
- **PerusahaanSeeder**: 3 default PTs (PT Lumbung Artha Sejahtera, PT Karya Mandiri Indonesia, PT Bumi Sejahtera Makmur)
- **AnggotaSeeder**: All 31 entries updated — `departemen`/`jabatan` replaced with `perusahaan_id => null`
- **DatabaseSeeder**: Added `PerusahaanSeeder` before `AnggotaSeeder`

## 4. Bug Fixes (earlier)
- PotonganGaji create: added missing `gaji_diterima`, `gaji_bruto` fields
- RekeningSimpanan create: added missing `tanggal_buka`, fixed duplicate `no_rekening` with kodeMap
- Catch block: `\Exception` → `\Throwable` to catch TypeError
- Carbon `day()` type: cast `$tglGajian` to `(int)`
- Redirect fallback: `back()` → `redirect()->route('anggota.create')`
