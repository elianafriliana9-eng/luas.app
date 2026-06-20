# KopSaku — AGENTS.md

> Progress tracker for Claude Code sessions.
> Updated: 12 Juni 2026

## Project Overview

**KopSaku** — Sistem Koperasi Karyawan Digital (Digital Employee Cooperative System).
Backend: Laravel 13 + MySQL (XAMPP). Mobile: Flutter (belum).
Repo: `C:\laragon\www\kopsaku`

## Common Commands

```bash
php artisan serve
php artisan migrate
php artisan migrate:rollback
php artisan test
php artisan payroll:proses
php artisan payroll:proses --periode=2026-04-01 --dry-run
php artisan kolektibilitas:update
composer install
```

## Tests

```bash
php artisan test            # 173 tests, 327 assertions
```

- SimpananTest: 65 tests
- SmokeTest: 74 tests (semua halaman)
- AnggotaTest: 9 tests
- Auth + Profile: 25 tests
- SQLite in-memory, migration-safe

## Architecture Notes

- UUID primary keys (`$incrementing = false`, `$keyType = 'string'`)
- No hard deletes (use `status` or `aktif` flag)
- Immutable transactions (corrections via reversal only)
- Money: `DECIMAL(15,2)` — never float
- Double-entry: `SimpananJurnal` trait auto-generates journals
- Period lock: check `periode_tutup` before journal insert
- Jurnal model: `public const UPDATED_AT = null`
- Roles: `super_admin`, `admin`, `executive`, `user`
- Alpine.js for UI interactivity

## Completed Features

### Modul Anggota
- CRUD + upload KTP/selfie + auto no_anggota
- Status workflow: pending_aktif → aktif → pengajuan_keluar → keluar
- Approval anggota baru + approval keluar
- Auto-rekening + auto-cicilan simpanan pokok
- Export Excel (5 jenis), Import Excel (single + master 4-sheet)
- Download template, PDF profil + PDF surat keluar
- 6 laporan, edit/reject anggota keluar

### Modul Simpanan
- 3 produk: Pokok, Wajib, Sukarela
- Setoran (auto-approve), Penarikan (≤1jt auto, >1jt pending)
- Pinbuk (≤1jt auto, >1jt pending)
- Approval workflow + cancel + reversal jurnal
- Blokir/buka blokir, tutup rekening, buka rekening baru
- Statement per rekening, import Excel, download template
- 9 laporan + 8 export Excel + PDF statement/rekap
- Simpanan Berjangka (Deposito) — CRUD, pencairan, ARO cron (hidden from sidebar)

### Modul Pembiayaan
- Simulasi angsuran (flat + anuitas)
- Pengajuan → Registrasi → Approve → Pencairan
- Auto-jadwal angsuran sesuai tanggal gajian
- Bayar angsuran manual, pelunasan dipercepat
- Pay Later (request → approve → proses)
- Cetak SP3 + Perjanjian (HTML print)
- Kolektibilitas OJK (1-5) auto-update via cron
- auto_potong_gaji + nominal_potongan + bulan_tersisa_potongan
- 4 laporan, model Jaminan

### Modul Payroll
- Potongan gaji otomatis via cron (payroll:proses)
- Manual trigger dengan --periode= dan --dry-run
- Pay Later approval & proses
- Dashboard + detail per anggota

### Modul Akuntansi
- Chart of Accounts (COA) header/detail
- Jurnal double-entry (create, view, revisi, batal + auto-reversal)
- Setup Kas + update saldo
- Buku Besar, Laporan Kas, Neraca Saldo, Neraca
- Periode Tutup, Auto-jurnal via SimpananJurnal trait
- COA configurable via DB + hardcoded fallback
- Import transaksi + jurnal otomatis

### UI/UX
- Loading states + disabled button + spinner (14 forms)
- confirm() dialog (12 destructive actions)
- Inline @error + red border (all forms)
- Searchable dropdown (Alpine.js filter)
- Empty states (@forelse + @empty)
- Breadcrumbs (anggota & simpanan)
- Sidebar badge optimization (Cache::remember)
- SVG icons (replaced emoji)
- no-print CSS for print-friendly pages

### Cross-Cutting
- Role-based access: super_admin (full + approval), admin (data entry), executive/user (mobile only)
- Role restructure: teller/kepala_cabang/account_officer/akuntan removed
- Channel values: teller → admin
- Soft deletes (12 tables), Audit trail (created_by, updated_by, ip_address, user_agent)
- Immutable flag on transaksi
- UUID, HasUuid, Auditable, SimpananJurnal traits
- Form Requests (StoreAnggota, UpdateAnggota, StoreTransaksi, StorePinbuk, RekeningBaru)

## Bugfixes
- PembiayaanController: tambah method pengajuan()
- RekapAnggotaExport: Closure bug fix
- SmokeTest: seed fixes
- **PDF/Cetak kosong**: storage/fonts dibuat (DomPDF butuh font cache)
- **Error handling PDF**: try/catch di semua method PDF
- **Route name fix**: route('anggota.export_keluar') → route('anggota.pdf_keluar')
- **Channel teller→admin**: 9 files updated

## Done 12 Juni 2026
- PDF cetak fix (storage/fonts, error handling, route name)
- Role restructure + sidebar gates + approval gates
- Channel value cleanup (teller→admin)
- All 173 tests pass
- Committed + pushed (e9a732b)

## Next (Backlog)
- Modul SHU (perhitungan, pembagian, approval)
- Modul Sembako (POS, stok, transaksi)
- Flutter Mobile App (setup, API integration, login, dashboard, pengajuan, Pay Later, mutasi)
- Multi-level approval
- Notifikasi (in-app / email)
- Batas transaksi per user role

## ⚠️ JANGAN DISENTUH
Modul berikut sudah ada kodenya tapi BELUM dipakai oleh perusahaan. JANGAN dikembangkan, diubah, atau diperbaiki.
- **Modul Payroll** — jangan sentuh

## Fokus Pengembangan
Ke depan fokus pengembangan mencakup **Modul Anggota**, **Modul Simpanan**, dan **Modul Pembiayaan** (Tahap MVP Lanjutan).

## Rencana Restruktur Menu Anggota (13 Juni 2026)

### Struktur Menu Baru
```
Master Data > Anggota
├── Daftar Anggota                  (admin + super_admin)
├── Approval Anggota Baru           (super_admin only)
├── Approval Keluar                 (super_admin only)
├── Import Anggota                  (admin + super_admin)
├── Import Master Data              (super_admin only)
├── Konfigurasi COA                 (super_admin only)
└── Laporan                         (admin + super_admin)
    ├── Saldo Anggota               ← merged (saldo + laporan saldo)
    ├── Anggota Masuk               ← baru: pending + < 30 hari
    ├── Profil Anggota              ← existing
    ├── Rekap Anggota               ← existing
    └── Anggota Keluar              ← existing
```

### Perubahan
1. Sidebar: "Saldo Anggota" dipindahkan ke dalam submenu Laporan
2. Halaman Saldo Anggota digabung dengan Laporan Saldo → 1 halaman dengan filter status (Semua/Aktif/Keluar), pagination, search, export excel, tombol detail
3. Halaman baru "Anggota Masuk": menampilkan pending_aktif + aktif (tanggal_masuk >= 30 hari)
4. Route `anggota.laporan.saldo` dihapus/diredirect ke `anggota.saldo`
