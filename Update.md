# KopSaku — Update Progres

> Terakhir diperbarui: 12 Juni 2026

---

## ✅ Selesai (Committed)

### Modul Anggota
- CRUD anggota (create, edit, show, list) dengan filter/search
- Upload KTP + selfie
- Auto-generate no_anggota
- Status workflow: `pending_aktif` → `aktif` → `pengajuan_keluar` → `keluar`
- Approval anggota baru + approval pengajuan keluar
- Auto-rekening + auto-cicilan simpanan pokok saat approve
- Approve keluar → auto-withdraw semua simpanan
- Export Excel (data, saldo, profil, rekap, keluar) — 5 jenis
- Import Excel (single sheet + master 4-sheet)
- Download template Excel
- PDF profil + PDF surat keluar
- 6 laporan (saldo, profil, rekap, keluar, show, history)
- Edit/reject anggota keluar

### Modul Simpanan
- 3 produk: Pokok, Wajib, Sukarela
- Setoran (auto-approve)
- Penarikan (≤ Rp 1jt auto, > Rp 1jt pending approval)
- Pinbuk/transfer antar rekening (≤ Rp 1jt auto, > Rp 1jt pending approval)
- Approval workflow penarikan & pinbuk
- Blokir/buka blokir rekening
- Tutup rekening
- Cancel transaksi (dengan reversal jurnal)
- Buka rekening baru
- Statement per rekening
- Import Excel transaksi + download template
- 9 laporan (rekap, setoran, penarikan, regist, pinbuk, saldo, statement, blokir, tutup)
- Export Excel (rekening, transaksi, rekap, setoran, penarikan, regist, pinbuk, statement) — 8 jenis
- PDF statement + PDF rekap
- Simpanan Berjangka (Deposito) — CRUD, pencairan, ARO cron — **DIHIDE dari sidebar**

### Modul Pembiayaan (Pinjaman)
- Simulasi angsuran (flat + anuitas)
- Pengajuan → Registrasi → Approve → Pencairan
- Auto-generate jadwal angsuran (sesuai tanggal gajian anggota)
- Bayar angsuran manual
- Pelunasan dipercepat
- Pay Later (bayar sebelum gajian) — request → approve → proses
- Cetak SP3 + Cetak Perjanjian (HTML print)
- Kolektibilitas OJK (1–5) auto-update via cron
- Flag auto_potong_gaji + nominal_potongan + bulan_tersisa_potongan
- 4 laporan (pengajuan, registrasi, pencairan, pembiayaan aktif)
- Model Jaminan (collateral) sudah include

### Modul Payroll
- Potongan gaji otomatis via cron (`payroll:proses`) — jalan tiap tanggal gajian (25th)
- Manual trigger dengan opsi `--periode=` dan `--dry-run`
- Pay Later (early payment) — approval & proses
- Halaman dashboard payroll + detail per anggota

### Modul Akuntansi
- Chart of Accounts (COA) — setup akun header/detail
- Jurnal double-entry (create, detail, revisi, batal + auto-reversal)
- Setup Kas + update saldo
- Buku Besar (ledger per akun)
- Laporan: kas, neraca saldo (trial balance), neraca (balance sheet)
- Periode Tutup — cegah jurnal di periode tertutup
- **Auto-jurnal**: semua transaksi simpanan (setoran, penarikan, pinbuk, bunga) auto-generate jurnal via `SimpananJurnal` trait
- Konfigurasi COA via DB (mapping transaksi → kode akun)
- Import transaksi + jurnal otomatis
- COA fallback hardcoded jika konfigurasi DB kosong

### Fitur Lintas Modul
- **Role-based access**: admin, super_admin, teller, bendahara, manajer
- **Soft Deletes**: 12 tabel (Anggota, RekeningSimpanan, TransaksiSimpanan, Pembiayaan, dll)
- **Audit trail**: `created_by`, `updated_by`, `ip_address`, `user_agent` di tabel transaksional
- **Immutable flag**: TransaksiSimpanan tidak bisa di-ubah setelah approve
- **UUID primary keys** — no auto-increment
- **HasUuid + Auditable + SimpananJurnal trait**
- **Form Request**: StoreAnggotaRequest, UpdateAnggotaRequest, StoreTransaksiRequest, StorePinbukRequest, RekeningBaruRequest

### UI/UX
- **Phase 1**: Loading state + disabled button + spinner di 14 form finansial
- **Phase 1b**: confirm() dialog di 12 aksi destruktif + logout
- **Phase 2a**: Inline @error + red border di semua form anggota & simpanan
- **Phase 2b**: Searchable dropdown (Alpine.js filter) di pinbuk & rekening_baru
- **Phase 3a**: Empty states — `@forelse` + `@empty` di semua laporan
- **Phase 3b**: Breadcrumbs di halaman anggota & simpanan
- **Phase 3c**: Sidebar badge query optimization (Cache::remember)
- **Phase 3d**: CSS consistency — emoji → SVG icons

### Testing
- **173 tests —全部 passing** (327 assertions)
- AnggotaTest: 9 tests
- SimpananTest: 65 tests
- SmokeTest: 74 tests (semua halaman bisa diakses)
- Auth + Profile tests: 25 tests
- SQLite in-memory — migration-safe

### Bugfixes
- PembiayaanController: tambah method `pengajuan()` untuk route `pembiayaan.pengajuan`
- RekapAnggotaExport: fix Closure bug `Anggota::sum(function(...))` → `leftJoin()->sum()`
- SmokeTest: tambah `tanggal_buka` di rekening seed, tambah `anggotaKeluar` seed
- SimpananTest: `test_simpanan_transaksi` → assertRedirect (bukan assertOk)

---

## 📋 Belum Dimulai (Backlog)

### Modul SHU (Sisa Hasil Usaha)
- [ ] Perhitungan SHU tahunan
- [ ] Pembagian SHU per anggota (jasa simpanan + jasa pinjaman)
- [ ] Approval & finalisasi SHU
- [ ] Model `ShuPeriode` & `ShuAnggota` sudah siap

### Modul Sembako (Toko)
- [ ] POS (Point of Sale)
- [ ] Manajemen stok
- [ ] Transaksi sembako

### Flutter Mobile App
- [ ] Setup project Flutter
- [ ] API integration (Sanctum)
- [ ] Halaman login (NIK + PIN)
- [ ] Dashboard anggota
- [ ] Pengajuan pembiayaan
- [ ] Pay Later
- [ ] Cek saldo & mutasi simpanan

### Improvement
- [ ] Multi-level approval (admin → super_admin → executive)
- [ ] Notifikasi (in-app atau email)
- [ ] Batas transaksi per user role

---

## Statistik

| Item | Jumlah |
|------|--------|
| Total migrations | ~35 |
| Total models | 26 |
| Total controllers | 12 |
| Total views | ~90 |
| Tests | 173 (327 assertions) |
| Console commands | 5 |
| Export classes | 16 |
| Import classes | 4 |
