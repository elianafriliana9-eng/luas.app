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
                'alamat' => 'Jl. Jend. Sudirman Kav. 52-53, SCBD, Jakarta Selatan 12190',
                'telp' => '021-52961234',
                'aktif' => true,
            ],
            [
                'kode' => 'CBG-TGR',
                'nama' => 'Cabang Tangerang',
                'alamat' => 'Jl. MH. Thamrin No. 8, CBD BSD, Tangerang Selatan 15321',
                'telp' => '021-53156789',
                'aktif' => true,
            ],
            [
                'kode' => 'CBG-BKS',
                'nama' => 'Cabang Bekasi',
                'alamat' => 'Jl. KH. Noer Alie No. 99, Grand Galaxy City, Bekasi 17147',
                'telp' => '021-82674567',
                'aktif' => true,
            ],
        ];

        foreach ($cabangs as $cabang) {
            Cabang::updateOrCreate(['kode' => $cabang['kode']], $cabang);
        }
    }
}
