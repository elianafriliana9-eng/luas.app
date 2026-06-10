<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CabangSeeder::class,
            AnggotaSeeder::class,
            ProdukSimpananSeeder::class,
            RekeningSimpananSeeder::class,
            TransaksiSimpananSeeder::class,
            PembiayaanSeeder::class,
            CoaSeeder::class,
            JurnalSeeder::class,
            UserSeeder::class,
        ]);
    }
}
