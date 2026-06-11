<?php

namespace Database\Seeders;

use App\Models\Anggota;
use App\Models\SimpananBerjangka;
use Illuminate\Database\Seeder;

class SimpananBerjangkaSeeder extends Seeder
{
    public function run(): void
    {
        $ahmad = Anggota::where('nik', '3172010505880003')->first();
        $teguh = Anggota::where('nik', '3277010107870019')->first();
        $bambang = Anggota::where('nik', '3283012204860025')->first();

        $deposits = [];

        if ($ahmad) {
            $deposits[] = [
                'anggota_id' => $ahmad->id,
                'no_deposito' => 'DEPO-2025-001',
                'nominal' => 25000000,
                'jangka_bulan' => 6,
                'bunga_pa' => 5.0,
                'tanggal_mulai' => '2025-01-15',
                'tanggal_jatuh_tempo' => '2025-07-15',
                'status' => 'aktif',
                'bunga_akrual' => 0,
                'auto_perpanjang' => true,
            ];
        }

        if ($teguh) {
            $deposits[] = [
                'anggota_id' => $teguh->id,
                'no_deposito' => 'DEPO-2025-002',
                'nominal' => 15000000,
                'jangka_bulan' => 3,
                'bunga_pa' => 4.5,
                'tanggal_mulai' => '2025-04-01',
                'tanggal_jatuh_tempo' => '2025-07-01',
                'status' => 'aktif',
                'bunga_akrual' => 0,
                'auto_perpanjang' => false,
            ];
        }

        if ($bambang) {
            $deposits[] = [
                'anggota_id' => $bambang->id,
                'no_deposito' => 'DEPO-2024-001',
                'nominal' => 50000000,
                'jangka_bulan' => 12,
                'bunga_pa' => 5.5,
                'tanggal_mulai' => '2024-07-01',
                'tanggal_jatuh_tempo' => '2025-07-01',
                'status' => 'aktif',
                'bunga_akrual' => 0,
                'auto_perpanjang' => true,
            ];
        }

        foreach ($deposits as $depo) {
            SimpananBerjangka::updateOrCreate(
                ['no_deposito' => $depo['no_deposito']],
                $depo
            );
        }
    }
}
