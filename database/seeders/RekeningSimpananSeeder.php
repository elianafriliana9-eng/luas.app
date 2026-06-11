<?php

namespace Database\Seeders;

use App\Models\Anggota;
use App\Models\ProdukSimpanan;
use App\Models\RekeningSimpanan;
use Illuminate\Database\Seeder;

class RekeningSimpananSeeder extends Seeder
{
    public function run(): void
    {
        $anggotas = Anggota::all();
        $produks = ProdukSimpanan::whereIn('jenis', ['pokok', 'wajib', 'sukarela'])->get();

        if ($anggotas->isEmpty() || $produks->isEmpty()) {
            return;
        }

        foreach ($anggotas as $anggota) {
            foreach ($produks as $produk) {
                if ($produk->jenis === 'sukarela' && fake()->boolean(30)) {
                    continue;
                }

                $saldo = match ($produk->jenis) {
                    'pokok' => 100000,
                    'wajib' => fake()->numberBetween(1, 10) * 50000,
                    default => fake()->numberBetween(10, 100) * 10000,
                };

                RekeningSimpanan::create([
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
