<?php

namespace Database\Seeders;

use App\Models\Perusahaan;
use Illuminate\Database\Seeder;

class PerusahaanSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['kode' => 'PT-LAS', 'nama' => 'PT Lumbung Artha Sejahtera', 'alamat' => 'Jl. Merdeka No. 45, Jakarta Pusat', 'telp' => '021-12345678', 'email' => 'info@lumbungartha.co.id'],
            ['kode' => 'PT-KMI', 'nama' => 'PT Karya Mandiri Indonesia', 'alamat' => 'Jl. Sudirman No. 12, Tangerang Selatan', 'telp' => '021-87654321', 'email' => 'hrd@karyamandiri.co.id'],
            ['kode' => 'PT-BSM', 'nama' => 'PT Bumi Sejahtera Makmur', 'alamat' => 'Jl. Raya Bekasi No. 88, Bekasi', 'telp' => '021-11223344', 'email' => 'corsec@bumisejahtera.co.id'],
        ];

        foreach ($data as $item) {
            Perusahaan::updateOrCreate(['kode' => $item['kode']], $item);
        }
    }
}
