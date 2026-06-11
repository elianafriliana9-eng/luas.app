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
        $produkPokok = ProdukSimpanan::where('kode', 'SIMPOK')->first();
        $produkWajib = ProdukSimpanan::where('kode', 'SIMWA')->first();
        $produkSukarela = ProdukSimpanan::where('kode', 'SIMSUKA')->first();

        if ($anggotas->isEmpty() || !$produkPokok || !$produkWajib) return;

        $sekarang = now()->startOfDay();

        foreach ($anggotas as $anggota) {
            $tglMasuk = $anggota->tanggal_masuk
                ? \Carbon\Carbon::parse($anggota->tanggal_masuk)->startOfDay()
                : $sekarang->copy()->subMonths(6);
            $lamaBulan = (int) $tglMasuk->diffInMonths($sekarang);
            if ($lamaBulan < 1) $lamaBulan = 1;

            // Simpanan Pokok — fixed Rp 100.000
            RekeningSimpanan::updateOrCreate(
                ['no_rekening' => 'REK-POKOK-' . $anggota->no_anggota],
                [
                    'anggota_id' => $anggota->id,
                    'produk_id' => $produkPokok->id,
                    'saldo' => 100000,
                    'status' => 'aktif',
                    'tanggal_buka' => $tglMasuk,
                ]
            );

            // Simpanan Wajib — Rp 50.000/bulan, max Rp 500.000
            // Hanya untuk anggota dengan gaji (karyawan aktif)
            if ($anggota->gaji_pokok !== null) {
                $saldoWajib = min($lamaBulan * 50000, 500000);
                if ($saldoWajib < 50000) $saldoWajib = 50000;

                RekeningSimpanan::updateOrCreate(
                    ['no_rekening' => 'REK-WAJIB-' . $anggota->no_anggota],
                    [
                        'anggota_id' => $anggota->id,
                        'produk_id' => $produkWajib->id,
                        'saldo' => $saldoWajib,
                        'status' => 'aktif',
                        'tanggal_buka' => $tglMasuk,
                    ]
                );
            }

            // Simpanan Sukarela — hanya untuk gaji >= Rp 7.000.000
            $gaji = $anggota->gaji_pokok ?? 0;
            if ($gaji >= 7000000 && $produkSukarela) {
                $saldoSukarela = match (true) {
                    $gaji >= 15000000 => rand(15000000, 30000000),
                    $gaji >= 10000000 => rand(5000000, 20000000),
                    $gaji >= 8000000 => rand(2000000, 10000000),
                    default => rand(1000000, 5000000),
                };

                RekeningSimpanan::updateOrCreate(
                    ['no_rekening' => 'REK-SUKARELA-' . $anggota->no_anggota],
                    [
                        'anggota_id' => $anggota->id,
                        'produk_id' => $produkSukarela->id,
                        'saldo' => $saldoSukarela,
                        'status' => 'aktif',
                        'tanggal_buka' => $tglMasuk,
                    ]
                );
            }
        }
    }
}
