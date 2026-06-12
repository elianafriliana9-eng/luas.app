# KopSaku — Update Progres

> Terakhir diperbarui: 12 Juni 2026

---

## ✅ Sudah Selesai (Committed)

### 1. Master Data Perusahaan/PT
- Module CRUD Perusahaan/PT
- Integrasi PT ke semua anggota (setiap anggota terikat ke PT)
- Hapus field `departemen` dan `jabatan` dari tabel anggota

### 2. Fitur Export/Import Anggota
- Export anggota ke Excel
- Import anggota dari Excel
- Template export/download

### 3. Simpanan — Pinbuk (Pemindahbukuan)
- Approval flow pinbuk
- Fitur pemindahbukuan antar rekening simpanan

### 4. UI Improvements
- Perbaikan layout & tampilan modul anggota dan simpanan
- Peningkatan navigasi

### 5. Role-Based Access
- Middleware role (`RoleMiddleware`)
- Enum role user: `admin`, `teller`, `bendahara`, `manajer`

---

## 🚧 Sedang Dikerjakan (Uncommitted)

### 1. Master Import (Legacy Data)
- Import massal data legacy dari Excel (anggota, pembiayaan, simpanan, saldo)
- Command: `php artisan master:import`
- Sheet: OST (anggota + pembiayaan), Simpanan Pokok & Wajib, Semua Simpanan
- Support flag `--reset` untuk hapus data lama, `--file` untuk custom path

### 2. Double-Entry Jurnal untuk Simpanan
- Trait `SimpananJurnal` untuk jurnal otomatis:
  - Setoran → debit Kas, credit rekening simpanan
  - Penarikan → debit rekening simpanan, credit Kas
  - Pinbuk → debit rek sumber, credit rek tujuan
- Mapping produk ke COA: SIMPOK→21010, SIMWA→21020, SIMSUKA→21030

### 3. Form Request Validation
- `RekeningBaruRequest` — validasi buka rekening simpanan
- `StorePinbukRequest` — validasi transfer antar rekening
- `StoreTransaksiRequest` — validasi setoran/penarikan

### 4. Feature Tests
- `AnggotaTest` (204 baris) — test CRUD anggota
- `SimpananTest` (932 baris) — test rekening, transaksi, pinbuk

### 5. UI Components
- `toast-notification` — notifikasi auto-dismiss dengan Alpine.js
- `import_master` — halaman upload import data master

### 6. Refactor Seeder
- Perombakan besar `AnggotaSeeder` (penyesuaian struktur PT)
- Penyesuaian seeder: User, Pembiayaan, PotonganGaji, RekeningSimpanan, TransaksiSimpanan, dll.

---

## 📋 Belum Dimulai (Backlog)

### Modul Pembiayaan & Payroll
- [ ] Pengajuan pembiayaan (member → admin)
- [ ] Approval & pencairan pembiayaan
- [ ] Jadwal angsuran
- [ ] Potong gaji otomatis (cron: `payroll:proses`)
- [ ] Pay Later (bayar sebelum gajian) — approval & proses

### Modul Akuntansi
- [ ] Jurnal umum
- [ ] Buku besar
- [ ] Laporan keuangan (Neraca, Laba/Rugi)
- [ ] SHU (Sisa Hasil Usaha)
- [ ] Period lock

### Modul Sembako (Toko)
- [ ] POS (Point of Sale)
- [ ] Manajemen stok
- [ ] Transaksi sembako

### Flutter Mobile App
- [ ] Setup project Flutter
- [ ] API integration
- [ ] Halaman login (NIK + PIN)
- [ ] Dashboard anggota
- [ ] Pengajuan pembiayaan
- [ ] Pay Later
- [ ] Cek saldo & mutasi simpanan

---

## Statistik

| Item | Jumlah |
|------|--------|
| Total migrations | ~20 |
| Total models | ~25 |
| Total views | ~40 |
| Tests (baris) | ~1,200 |
| Uncommitted files | ~15 new + ~38 modified |
