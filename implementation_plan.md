# KopSaku — Demo-Ready Implementation Plan

## Tujuan
Bangun aplikasi **web admin KopSaku** yang fungsional dan visual untuk **demo ke klien**. Prioritas: tampilan profesional + data realistis, bukan fitur lengkap.

## Strategi: Backend-First tapi Demo-Focused

Setup backend (migration + seeder) secepat mungkin, lalu fokus ke **3 modul demo** yang paling impactful:

1. **Dashboard** — Statistik ringkasan (total anggota, saldo simpanan, outstanding pembiayaan, dll)
2. **Modul Anggota** — CRUD lengkap, paling mudah dipahami klien
3. **Modul Simpanan** — Transaksi, saldo, history — "wow factor" karena klien lihat uang bergerak

> [!IMPORTANT]
> Modul Pembiayaan & Akuntansi di-skip untuk demo awal karena kompleksitas tinggi. Bisa ditambahkan setelah klien approve.

## Environment

| Komponen | Status |
|----------|--------|
| PHP | ✅ 8.4.16 |
| Composer | ✅ 2.9.2 |
| Node.js | ✅ v24.13.0 |
| npm | ✅ 11.6.2 |
| MySQL (XAMPP) | ⚠️ Belum running — perlu start manual |
| Laravel | ❌ Belum di-install |

## User Review Required

> [!WARNING]
> **MySQL harus di-start dulu** via XAMPP Control Panel sebelum bisa lanjut. Pastikan MySQL XAMPP sudah running.

> [!IMPORTANT]
> **Scope Demo**: Plan ini hanya mencakup 3 modul (Dashboard, Anggota, Simpanan). Apakah ada modul lain yang klien ingin lihat di demo pertama?

## Proposed Changes

### Phase 1: Project Setup (~15 menit)

#### [NEW] Laravel 12 Project
- Init Laravel 12 via `composer create-project` di folder kopsaku
- Konfigurasi `.env` untuk MySQL XAMPP (root, no password, db: kopsaku)
- Install Laravel Breeze untuk auth scaffolding (login, register, dashboard)

---

### Phase 2: Database Foundation (~30 menit)

#### [NEW] Migrations — 18 tabel (3 grup)

Semua 18 tabel dari guide.md akan dibuat sebagai migration:

**Grup 1 — Anggota & Simpanan:**
- `create_cabang_table`
- `create_anggota_table`
- `create_produk_simpanan_table`
- `create_rekening_simpanan_table`
- `create_transaksi_simpanan_table`
- `create_simpanan_berjangka_table`

**Grup 2 — Pembiayaan & Collection:**
- `create_produk_pembiayaan_table`
- `create_pengajuan_pembiayaan_table`
- `create_pembiayaan_table`
- `create_jadwal_angsuran_table`
- `create_transaksi_pembiayaan_table`
- `create_jaminan_table`

**Grup 3 — Akuntansi & SHU:**
- `create_chart_of_accounts_table`
- `create_jurnal_table`
- `create_jurnal_detail_table`
- `create_periode_tutup_table`
- `create_shu_periode_table`
- `create_shu_anggota_table`

#### [NEW] Models + Relationships
- 18 Eloquent models dengan relasi sesuai ER diagram
- UUID trait untuk semua model

#### [NEW] Seeders — Data Demo Realistis
- `CabangSeeder` — 3 cabang (Pusat, Cabang Tangerang, Cabang Bekasi)
- `AnggotaSeeder` — 50 anggota dengan nama Indonesia realistis
- `ProdukSimpananSeeder` — 4 produk (Simpanan Pokok, Wajib, Sukarela, Berjangka)
- `RekeningSeeder` — Rekening per anggota
- `TransaksiSimpananSeeder` — 200+ transaksi simulasi 3 bulan terakhir
- `UserSeeder` — Admin & staff accounts

---

### Phase 3: Web Admin UI — Demo Modules (~2-3 jam)

#### [NEW] Layout & Design System
- Sidebar navigation (fixed)
- Header dengan user info + notifikasi
- Color scheme: dark sidebar + light content area
- Typography: Inter / Outfit dari Google Fonts
- Warna brand: hijau koperasi (#059669 primary)

#### [NEW] Dashboard Page
- **Stat cards**: Total Anggota, Total Simpanan, Outstanding Pembiayaan, Anggota Baru Bulan Ini
- **Chart**: Grafik pertumbuhan simpanan 6 bulan (Chart.js)
- **Tabel**: Transaksi terbaru (5 terakhir)
- **Pie chart**: Komposisi simpanan per jenis

#### [NEW] Modul Anggota
- **List anggota** — DataTable dengan search, filter per cabang, pagination
- **Detail anggota** — Profil lengkap + rekening + history
- **Form tambah/edit** — Validasi NIK, upload foto KTP/selfie placeholder
- **Status management** — Aktif/Tidak aktif/Keluar

#### [NEW] Modul Simpanan
- **List rekening** — Semua rekening dengan saldo real-time
- **Detail rekening** — Saldo + history transaksi
- **Form setoran/penarikan** — Dengan auto-update saldo
- **Laporan simpanan** — Statement per rekening (bisa export)

---

### Phase 4: Polish & Demo-Ready (~30 menit)

- Responsive check (tablet-friendly minimal)
- Loading states & animations
- Format currency Rupiah (Rp 1.000.000)
- Tanggal format Indonesia (06 April 2026)
- Login page branded "KopSaku"

## Open Questions

> [!IMPORTANT]
> 1. **MySQL XAMPP** sudah running belum? Perlu di-start dulu sebelum lanjut.
> 2. **Scope demo**: Dashboard + Anggota + Simpanan cukup? Atau ada modul lain yang klien pasti tanya?
> 3. **Deadline demo**: Kapan demo ke klien? Ini menentukan seberapa polish yang bisa dicapai.
> 4. **Laravel Breeze vs manual auth**: Pakai Breeze (Blade + Tailwind) untuk auth cepat, atau custom?

## Verification Plan

### Automated Tests
```bash
php artisan migrate:fresh --seed   # Pastikan migration + seeder jalan
php artisan test                   # Run feature tests
```

### Manual Verification
- Login sebagai admin → dashboard tampil statistik benar
- CRUD anggota → tambah, edit, lihat, nonaktifkan
- Transaksi simpanan → setoran/penarikan, saldo update real-time
- Responsive check di tablet view
