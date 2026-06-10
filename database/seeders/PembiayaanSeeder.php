<?php

namespace Database\Seeders;

use App\Models\Anggota;
use App\Models\Pembiayaan;
use App\Models\JadwalAngsuran;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PembiayaanSeeder extends Seeder
{
    public function run(): void
    {
        $budi = Anggota::where('nik', '3171012304900001')->first();
        if (!$budi) return;

        // 1. Create a Product — Karyawan Loan
        $produk = \App\Models\ProdukPembiayaan::firstOrCreate(
            ['kode' => 'KAR'],
            [
                'nama' => 'Pembiayaan Karyawan',
                'skema_bunga' => 'flat',
                'bunga_pa' => 10,
                'max_jangka' => 36,
                'max_plafon' => 50000000,
                'aktif' => true,
            ]
        );

        // 2. Create Pengajuan
        $pengajuan = \App\Models\PengajuanPembiayaan::updateOrCreate(
            ['no_pengajuan' => 'PJN-26040001'],
            [
                'anggota_id' => $budi->id,
                'produk_id' => $produk->id,
                'nominal_diajukan' => 10000000,
                'jangka_bulan' => 12,
                'tujuan' => 'konsumtif',
                'status_approval' => 'disetujui',
                'approved_at' => now()->subMonths(4),
            ]
        );

        // 3. Create a loan for Budi — dengan potong gaji
        $angsuranPokok = 833333; // 10M / 12
        $angsuranBunga = 83333;  // 10M * 10% / 12
        $totalAngsuran = $angsuranPokok + $angsuranBunga;

        $pembiayaan = Pembiayaan::updateOrCreate(
            ['no_pembiayaan' => 'PMB-26040001'],
            [
                'pengajuan_id' => $pengajuan->id,
                'anggota_id' => $budi->id,
                'nominal_disetujui' => 10000000,
                'nominal_cair' => 10000000,
                'jangka_bulan' => 12,
                'bunga_pa' => 10,
                'metode_hitung' => 'flat',
                'angsuran_pokok' => $angsuranPokok,
                'angsuran_bunga' => $angsuranBunga,
                'tanggal_akad' => now()->subMonths(4),
                'tanggal_cair' => now()->subMonths(4),
                'status' => 'aktif',
                'saldo_pokok' => 6666668, // Paid 4 months
                'saldo_bunga' => 666666,
                'kolektibilitas' => 1,
                // Payroll deduction fields
                'auto_potong_gaji' => true,
                'nominal_potongan' => $totalAngsuran,
                'bulan_tersisa_potongan' => 8, // 12 - 4 months paid
                'sumber_pembayaran' => 'potong_gaji',
            ]
        );

        // 4. Create Jadwal Angsuran (12 months)
        $pembiayaan->jadwalAngsuran()->delete();
        for ($i = 1; $i <= 12; $i++) {
            $isPaid = $i <= 4;
            JadwalAngsuran::create([
                'pembiayaan_id' => $pembiayaan->id,
                'ke' => $i,
                'tanggal_jatuh_tempo' => now()->subMonths(4)->addMonths($i)->setDay($budi->tanggal_gajian ?? 25),
                'pokok' => $angsuranPokok,
                'bunga' => $angsuranBunga,
                'total' => $totalAngsuran,
                'saldo_akhir' => 10000000 - ($i * $angsuranPokok),
                'status' => $isPaid ? 'lunas' : 'belum',
                'tanggal_bayar' => $isPaid ? now()->subMonths(4)->addMonths($i)->subDays(2)->setDay(25) : null,
            ]);
        }

        // 5. Create Siti's loan (second demo account)
        $siti = Anggota::where('nik', '3271012405920002')->first();
        if ($siti) {
            $pengajuanSiti = \App\Models\PengajuanPembiayaan::updateOrCreate(
                ['no_pengajuan' => 'PJN-26040002'],
                [
                    'anggota_id' => $siti->id,
                    'produk_id' => $produk->id,
                    'nominal_diajukan' => 5000000,
                    'jangka_bulan' => 6,
                    'tujuan' => 'konsumtif',
                    'status_approval' => 'disetujui',
                    'approved_at' => now()->subMonths(2),
                ]
            );

            $angsuranPokokSiti = 833333;
            $angsuranBungaSiti = 41667;
            $totalAngsuranSiti = $angsuranPokokSiti + $angsuranBungaSiti;

            $pembiayaanSiti = Pembiayaan::updateOrCreate(
                ['no_pembiayaan' => 'PMB-26040002'],
                [
                    'pengajuan_id' => $pengajuanSiti->id,
                    'anggota_id' => $siti->id,
                    'nominal_disetujui' => 5000000,
                    'nominal_cair' => 5000000,
                    'jangka_bulan' => 6,
                    'bunga_pa' => 10,
                    'metode_hitung' => 'flat',
                    'angsuran_pokok' => $angsuranPokokSiti,
                    'angsuran_bunga' => $angsuranBungaSiti,
                    'tanggal_akad' => now()->subMonths(2),
                    'tanggal_cair' => now()->subMonths(2),
                    'status' => 'aktif',
                    'saldo_pokok' => 3333334,
                    'saldo_bunga' => 250002,
                    'kolektibilitas' => 1,
                    'auto_potong_gaji' => true,
                    'nominal_potongan' => $totalAngsuranSiti,
                    'bulan_tersisa_potongan' => 4,
                    'sumber_pembayaran' => 'keduanya', // Bisa bayar manual juga
                ]
            );

            $pembiayaanSiti->jadwalAngsuran()->delete();
            for ($i = 1; $i <= 6; $i++) {
                $isPaid = $i <= 2;
                JadwalAngsuran::create([
                    'pembiayaan_id' => $pembiayaanSiti->id,
                    'ke' => $i,
                    'tanggal_jatuh_tempo' => now()->subMonths(2)->addMonths($i)->setDay($siti->tanggal_gajian ?? 25),
                    'pokok' => $angsuranPokokSiti,
                    'bunga' => $angsuranBungaSiti,
                    'total' => $totalAngsuranSiti,
                    'saldo_akhir' => 5000000 - ($i * $angsuranPokokSiti),
                    'status' => $isPaid ? 'lunas' : 'belum',
                    'tanggal_bayar' => $isPaid ? now()->subMonths(2)->addMonths($i)->subDays(3)->setDay(25) : null,
                ]);
            }
        }
    }
}
