<?php

namespace Database\Seeders;

use App\Models\Cabang;
use Illuminate\Database\Seeder;

class CabangSeeder extends Seeder
{
    public function run(): void
    {
        $cabangs = [
            [
                'kode' => 'CBG-JKT',
                'nama' => 'Cabang Utama Jakarta',
                'alamat' => 'Jl. Jend. Sudirman No. 1, Jakarta Pusat',
                'telp' => '021-12345678',
                'aktif' => true,
            ],
            [
                'kode' => 'CBG-TGR',
                'nama' => 'Cabang Tangerang',
                'alamat' => 'Jl. Boulevard Gading Serpong, Tangerang',
                'telp' => '021-87654321',
                'aktif' => true,
            ],
            [
                'kode' => 'CBG-BKS',
                'nama' => 'Cabang Bekasi',
                'alamat' => 'Jl. Ahmad Yani No. 10, Bekasi',
                'telp' => '021-11223344',
                'aktif' => true,
            ],
        ];

        foreach ($cabangs as $cabang) {
            Cabang::create($cabang);
        }
    }
}
