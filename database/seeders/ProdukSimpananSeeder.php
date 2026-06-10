<?php

namespace Database\Seeders;

use App\Models\ProdukSimpanan;
use Illuminate\Database\Seeder;

class ProdukSimpananSeeder extends Seeder
{
    public function run(): void
    {
        $produks = [
            [
                'kode' => 'SIMPOK',
                'nama' => 'Simpanan Pokok',
                'jenis' => 'pokok',
                'bunga_pa' => 0,
                'minimal_saldo' => 100000,
                'auto_bunga' => false,
                'aktif' => true,
            ],
            [
                'kode' => 'SIMWA',
                'nama' => 'Simpanan Wajib',
                'jenis' => 'wajib',
                'bunga_pa' => 0,
                'minimal_saldo' => 50000,
                'auto_bunga' => false,
                'aktif' => true,
            ],
            [
                'kode' => 'SIMSUKA',
                'nama' => 'Simpanan Sukarela',
                'jenis' => 'sukarela',
                'bunga_pa' => 2.5,
                'minimal_saldo' => 10000,
                'auto_bunga' => true,
                'aktif' => true,
            ],
            [
                'kode' => 'DEPO6',
                'nama' => 'Simpanan Berjangka 6 Bulan',
                'jenis' => 'berjangka',
                'bunga_pa' => 5.0,
                'minimal_saldo' => 1000000,
                'auto_bunga' => false,
                'aktif' => true,
            ],
        ];

        foreach ($produks as $produk) {
            ProdukSimpanan::create($produk);
        }
    }
}
