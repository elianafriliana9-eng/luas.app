# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**KopSaku** — Sistem Koperasi Karyawan Digital (Digital Employee Cooperative System) for Koperasi Lumbung Artha Sejahtera. Web + Mobile application for managing employee members, savings, **payroll-deducted loans**, accounting, and grocery store operations.

### Core Concept: Employee Cooperative with Payroll Deduction
This is a **Koperasi Karyawan** (Employee Cooperative) where the **primary loan repayment method** is **automatic salary deduction** (potong gaji). Employees can also choose to **pay before payday** via the **Pay Later** feature.

## Tech Stack

- **Backend + Web Admin**: PHP Laravel 12 with REST API
- **Mobile App**: Flutter (`luas_app/`)
- **Database**: MySQL (XAMPP local development)
- **Server**: XAMPP local development environment at `/Applications/XAMPP/xamppfiles/htdocs/kopsaku`

## Common Commands

```bash
# Laravel development
php artisan serve                    # Start dev server
php artisan migrate                  # Run migrations
php artisan migrate:rollback         # Rollback last migration
php artisan test                     # Run all tests
php artisan make:model ModelName -m  # Create model with migration

# Payroll commands
php artisan payroll:proses           # Process salary deductions for current period
php artisan payroll:proses --periode=2026-04-01  # Specific period
php artisan payroll:proses --dry-run  # Preview without changing data
php artisan kolektibilitas:update    # Update collectibility ratings (OJK compliance)

composer install                     # Install PHP dependencies
```

## Architecture

### Database Structure — Core Modules

**Group 1 — Anggota & Simpanan (Members & Savings)**: `cabang`, `anggota`, `produk_simpanan`, `rekening_simpanan`, `transaksi_simpanan`, `simpanan_berjangka`

**Group 2 — Pembiayaan & Payroll (Loans)**: `produk_pembiayaan`, `pengajuan_pembiayaan`, `pembiayaan`, `jadwal_angsuran`, `transaksi_pembiayaan`, `jaminan`, `potongan_gaji`, `pay_later`

**Group 3 — Akuntansi & SHU (Accounting)**: `chart_of_accounts`, `jurnal`, `jurnal_detail`, `periode_tutup`, `shu_periode`, `shu_anggota`

### Key Employee-Specific Fields

**`anggota` table**:
- `gaji_pokok` — Base salary (DECIMAL 15,2)
- `tanggal_gajian` — Payday each month (1-31)
- `departemen` — Department
- `jabatan` — Position/title
- `tanggal_mulai_kerja` — Start date (for tenure calculation)
- `no_pegawai` — Employee ID number

**`pembiayaan` table**:
- `auto_potong_gaji` — Whether loan is auto-deducted from salary
- `nominal_potongan` — Monthly deduction amount
- `bulan_tersisa_potongan` — Remaining months of deduction
- `sumber_pembayaran` — `potong_gaji`, `bayar_manual`, or `keduanya`

**New tables**:
- `potongan_gaji` — Tracks each salary deduction transaction
- `pay_later` — Member requests to pay before payday

### Key Modules

1. **Anggota** — Employee member data, profiles, salary info, department
2. **Simpanan** — Savings accounts, deposits, withdrawals
3. **Pembiayaan** — Employee loans with **auto payroll deduction** + **Pay Later** (early payment)
4. **Payroll** — Salary deduction processing, approval workflow, history
5. **Akuntansi** — Double-entry journals, ledger, financial reports
6. **Sembako** — Grocery store POS (future)

### Cron Jobs (Scheduled in `routes/console.php`)

- **Daily at 1 AM** (on payday = 25th): `payroll:proses` — Auto-deduct loan installments from salary
- **Daily at 2 AM**: `kolektibilitas:update` — Update OJK collectibility ratings based on overdue days

## Payroll Flow

1. Employee applies for loan via mobile app
2. Admin approves and sets `auto_potong_gaji = true` with `nominal_potongan`
3. Each month on `tanggal_gajian`, cron job (`payroll:proses`) runs:
   - Finds all active employees with `auto_potong_gaji` loans
   - Deducts `nominal_potongan` from `gaji_pokok`
   - Marks installment as paid
   - Creates `potongan_gaji` record
   - Creates `transaksi_pembiayaan` record with `channel = 'potong_gaji'`
   - Decrements `bulan_tersisa_potongan`

### Pay Later (Bayar Sebelum Gajian)
- Employee can choose to pay an installment **before** payday via mobile app
- Creates a `pay_later` record with `status = pending`
- Admin approves via web admin (`/pay-later/pending`)
- Once approved, admin processes payment
- This does NOT affect the `bulan_tersisa_potongan` counter (it's a manual payment)

## Critical Implementation Rules

- **Primary Keys**: UUID v4 — no auto-increment
- **No Hard Deletes**: Use `status` field or `aktif=false`
- **Immutable Transactions**: Transaction tables cannot be UPDATEd. Corrections via reversal entries only
- **Money Fields**: Always `DECIMAL(15,2)` — never FLOAT/DOUBLE
- **Timestamps**: Store in UTC. Convert to WIB (UTC+7) only in presentation
- **Double-Entry Accounting**: Every transaction must auto-generate journal entries
- **Period Lock**: Check `periode_tutup.is_closed` before every journal insert
- **Kolektibilitas**: 1=Lancar, 2=Dalam Perhatian Khusus, 3=Kurang Lancar, 4=Diragukan, 5=Macet

## Demo Accounts

**Mobile App Login**:
- NIK: `3171012304900001` | PIN: `123456` (Budi Santoso — Rp 8.5M salary, auto-deducted loan)
- NIK: `3271012405920002` | PIN: `123456` (Siti Rahayu — Rp 7.2M salary, partial auto-deduct)

## Reference Document

`guide.md` — Full project specification including all module details, database schema, and ER diagrams.
