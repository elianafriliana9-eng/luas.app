<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // 1. Master data (no dependencies)
            CabangSeeder::class,
            PerusahaanSeeder::class,
            AnggotaSeeder::class,
            ProdukSimpananSeeder::class,

            // 2. Users (needed by transaction seeders)
            UserSeeder::class,

            // 3. Rekening & Transaksi (needs users)
            RekeningSimpananSeeder::class,
            TransaksiSimpananSeeder::class,

            // 4. Accounting (needs users + cabang)
            CoaSeeder::class,
            JurnalSeeder::class,

            // 5. Pembiayaan (needs users + anggota + produk)
            PembiayaanSeeder::class,
            PotonganGajiSeeder::class,
        ]);
    }
}
