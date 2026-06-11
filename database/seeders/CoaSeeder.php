<?php

namespace Database\Seeders;

use App\Models\Cabang;
use App\Models\ChartOfAccount;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CoaSeeder extends Seeder
{
    public function run(): void
    {
        $cabang = Cabang::first();
        if (!$cabang) return;

        // --- LEVEL 1 (HEADERS) ---
        $headers = [
            '10000' => ['nama' => 'Aset', 'kelompok' => 'aset', 'posisi' => 'debet'],
            '20000' => ['nama' => 'Liabilitas', 'kelompok' => 'liabilitas', 'posisi' => 'kredit'],
            '30000' => ['nama' => 'Ekuitas', 'kelompok' => 'ekuitas', 'posisi' => 'kredit'],
            '40000' => ['nama' => 'Pendapatan', 'kelompok' => 'pendapatan', 'posisi' => 'kredit'],
            '50000' => ['nama' => 'Beban', 'kelompok' => 'beban', 'posisi' => 'debet'],
        ];

        $headerModels = [];
        foreach ($headers as $kode => $data) {
            $headerModels[$kode] = ChartOfAccount::updateOrCreate(
                ['kode_akun' => $kode],
                [
                    'cabang_id' => $cabang->id,
                    'nama_akun' => $data['nama'],
                    'kelompok' => $data['kelompok'],
                    'posisi_normal' => $data['posisi'],
                    'is_header' => true,
                    'parent_id' => null,
                    'aktif' => true,
                ]
            );
        }

        // --- LEVEL 2 (SUB ACCOUNTS) ---
        $subAccounts = [
            // Aset
            ['parent' => '10000', 'kode' => '11010', 'nama' => 'Kas Teller Cabang', 'kelompok' => 'aset', 'posisi' => 'debet'],
            ['parent' => '10000', 'kode' => '11020', 'nama' => 'Kas Besar / Vault', 'kelompok' => 'aset', 'posisi' => 'debet'],
            ['parent' => '10000', 'kode' => '12010', 'nama' => 'Bank Mandiri', 'kelompok' => 'aset', 'posisi' => 'debet'],
            ['parent' => '10000', 'kode' => '12020', 'nama' => 'Bank BNI', 'kelompok' => 'aset', 'posisi' => 'debet'],
            ['parent' => '10000', 'kode' => '12030', 'nama' => 'Bank BRI', 'kelompok' => 'aset', 'posisi' => 'debet'],
            ['parent' => '10000', 'kode' => '13010', 'nama' => 'Piutang Pembiayaan Pokok', 'kelompok' => 'aset', 'posisi' => 'debet'],
            ['parent' => '10000', 'kode' => '13020', 'nama' => 'Piutang Bunga Pembiayaan', 'kelompok' => 'aset', 'posisi' => 'debet'],
            ['parent' => '10000', 'kode' => '14010', 'nama' => 'Aset Tetap', 'kelompok' => 'aset', 'posisi' => 'debet'],
            ['parent' => '10000', 'kode' => '14020', 'nama' => 'Akumulasi Penyusutan', 'kelompok' => 'aset', 'posisi' => 'kredit'],

            // Liabilitas
            ['parent' => '20000', 'kode' => '21010', 'nama' => 'Simpanan Pokok', 'kelompok' => 'liabilitas', 'posisi' => 'kredit'],
            ['parent' => '20000', 'kode' => '21020', 'nama' => 'Simpanan Wajib', 'kelompok' => 'liabilitas', 'posisi' => 'kredit'],
            ['parent' => '20000', 'kode' => '21030', 'nama' => 'Simpanan Sukarela', 'kelompok' => 'liabilitas', 'posisi' => 'kredit'],

            ['parent' => '20000', 'kode' => '22010', 'nama' => 'Hutang Bank', 'kelompok' => 'liabilitas', 'posisi' => 'kredit'],
            ['parent' => '20000', 'kode' => '23010', 'nama' => 'Hutang Lain-lain', 'kelompok' => 'liabilitas', 'posisi' => 'kredit'],

            // Ekuitas
            ['parent' => '30000', 'kode' => '31010', 'nama' => 'Modal Disetor', 'kelompok' => 'ekuitas', 'posisi' => 'kredit'],
            ['parent' => '30000', 'kode' => '32010', 'nama' => 'SHU Ditahan', 'kelompok' => 'ekuitas', 'posisi' => 'kredit'],
            ['parent' => '30000', 'kode' => '33010', 'nama' => 'SHU Tahun Berjalan', 'kelompok' => 'ekuitas', 'posisi' => 'kredit'],

            // Pendapatan
            ['parent' => '40000', 'kode' => '41010', 'nama' => 'Pendapatan Bunga Pembiayaan', 'kelompok' => 'pendapatan', 'posisi' => 'kredit'],
            ['parent' => '40000', 'kode' => '41020', 'nama' => 'Pendapatan Denda', 'kelompok' => 'pendapatan', 'posisi' => 'kredit'],
            ['parent' => '40000', 'kode' => '42010', 'nama' => 'Pendapatan Provisi / Administrasi', 'kelompok' => 'pendapatan', 'posisi' => 'kredit'],
            ['parent' => '40000', 'kode' => '42020', 'nama' => 'Pendapatan Jasa Simpanan', 'kelompok' => 'pendapatan', 'posisi' => 'kredit'],

            // Beban
            ['parent' => '50000', 'kode' => '51010', 'nama' => 'Beban Jasa Simpanan', 'kelompok' => 'beban', 'posisi' => 'debet'],
            ['parent' => '50000', 'kode' => '52010', 'nama' => 'Beban Operasional Karyawan', 'kelompok' => 'beban', 'posisi' => 'debet'],
            ['parent' => '50000', 'kode' => '52020', 'nama' => 'Beban Listrik, Air & Telepon', 'kelompok' => 'beban', 'posisi' => 'debet'],
            ['parent' => '50000', 'kode' => '52030', 'nama' => 'Beban ATK & Perlengkapan', 'kelompok' => 'beban', 'posisi' => 'debet'],
            ['parent' => '50000', 'kode' => '52040', 'nama' => 'Beban Penyusutan', 'kelompok' => 'beban', 'posisi' => 'debet'],
            ['parent' => '50000', 'kode' => '53010', 'nama' => 'Beban Lain-lain', 'kelompok' => 'beban', 'posisi' => 'debet'],
        ];

        foreach ($subAccounts as $data) {
            ChartOfAccount::updateOrCreate(
                ['kode_akun' => $data['kode']],
                [
                    'cabang_id' => $cabang->id,
                    'nama_akun' => $data['nama'],
                    'kelompok' => $data['kelompok'],
                    'posisi_normal' => $data['posisi'],
                    'is_header' => false,
                    'parent_id' => $headerModels[$data['parent']]->id,
                    'aktif' => true,
                ]
            );
        }
    }
}
