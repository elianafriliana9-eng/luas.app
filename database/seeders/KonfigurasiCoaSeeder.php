<?php

namespace Database\Seeders;

use App\Models\KonfigurasiCoa;
use Illuminate\Database\Seeder;

class KonfigurasiCoaSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['key' => 'simpanan_pokok',       'label' => 'Simpanan Pokok',       'kode_akun' => '21010', 'jenis' => 'simpanan', 'keterangan' => 'Akun untuk simpanan pokok'],
            ['key' => 'simpanan_wajib',        'label' => 'Simpanan Wajib',       'kode_akun' => '21020', 'jenis' => 'simpanan', 'keterangan' => 'Akun untuk simpanan wajib'],
            ['key' => 'simpanan_sukarela',     'label' => 'Simpanan Sukarela',    'kode_akun' => '21030', 'jenis' => 'simpanan', 'keterangan' => 'Akun untuk simpanan sukarela'],
            ['key' => 'kas',                   'label' => 'Kas',                  'kode_akun' => '11010', 'jenis' => 'umum',    'keterangan' => 'Akun kas utama'],
            ['key' => 'simpanan_berjangka',    'label' => 'Simpanan Berjangka',   'kode_akun' => '21040', 'jenis' => 'simpanan', 'keterangan' => 'Akun untuk deposito/simpanan berjangka'],
            ['key' => 'bunga_simpanan',        'label' => 'Beban Bunga Simpanan', 'kode_akun' => '51010', 'jenis' => 'beban',   'keterangan' => 'Beban bunga untuk simpanan'],
            ['key' => 'pendapatan_bunga',      'label' => 'Pendapatan Bunga',     'kode_akun' => '41010', 'jenis' => 'pendapatan', 'keterangan' => 'Pendapatan bunga pembiayaan'],
        ];

        foreach ($data as $item) {
            KonfigurasiCoa::create($item);
        }
    }
}
