<?php

namespace Database\Seeders;

use App\Models\Anggota;
use App\Models\ProdukSimpanan;
use App\Models\RekeningSimpanan;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class RekeningSimpananSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        $anggotas = Anggota::all();
        $produks = ProdukSimpanan::whereIn('jenis', ['pokok', 'wajib', 'sukarela'])->get();

        if ($anggotas->isEmpty() || $produks->isEmpty()) {
            return;
        }

        foreach ($anggotas as $anggota) {
            foreach ($produks as $produk) {
                // Not all have 'sukarela', but all have 'pokok' and 'wajib'
                if ($produk->jenis === 'sukarela' && $faker->boolean(30)) {
                    continue; // 30% chance they don't have sukarela yet
                }

                $saldo = 0;
                if ($produk->jenis === 'pokok') {
                    $saldo = 100000;
                } elseif ($produk->jenis === 'wajib') {
                    $saldo = $faker->numberBetween(1, 10) * 50000;
                } else {
                    $saldo = $faker->numberBetween(10, 100) * 10000;
                }

                RekeningSimpanan::create([
                    'cabang_id' => $anggota->cabang_id,
                    'anggota_id' => $anggota->id,
                    'produk_id' => $produk->id,
                    'no_rekening' => 'REK-' . $produk->jenis . '-' . $anggota->no_anggota,
                    'saldo' => $saldo,
                    'status' => 'aktif',
                    'tanggal_buka' => $anggota->tanggal_masuk,
                ]);
            }
        }
    }
}
