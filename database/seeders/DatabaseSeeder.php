<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CabangSeeder::class,
            PerusahaanSeeder::class,
            AnggotaSeeder::class,
            ProdukSimpananSeeder::class,
            UserSeeder::class,
            RekeningSimpananSeeder::class,
            TransaksiSimpananSeeder::class,
            SimpananBerjangkaSeeder::class,
            CoaSeeder::class,
            JurnalSeeder::class,
            PembiayaanSeeder::class,
            PotonganGajiSeeder::class,
        ]);
    }
}
