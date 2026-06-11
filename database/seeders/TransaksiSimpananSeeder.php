<?php

namespace Database\Seeders;

use App\Models\RekeningSimpanan;
use App\Models\TransaksiSimpanan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TransaksiSimpananSeeder extends Seeder
{
    public function run(): void
    {
        $rekenings = RekeningSimpanan::with('produk', 'anggota')->get();
        $user = User::where('role', 'teller')->first() ?? User::first();
        if ($rekenings->isEmpty() || !$user) return;

        $sekarang = Carbon::now()->startOfDay();

        foreach ($rekenings as $rekening) {
            $jenis = $rekening->produk->jenis;
            $tglBuka = Carbon::parse($rekening->tanggal_buka)->startOfDay();

            if ($jenis === 'pokok') {
                TransaksiSimpanan::updateOrCreate(
                    ['no_transaksi' => 'TRX-S-POKOK-' . $rekening->anggota->no_anggota],
                    [
                        'rekening_id' => $rekening->id,
                        'user_id' => $user->id,
                        'jenis' => 'setoran',
                        'nominal' => 100000,
                        'saldo_sebelum' => 0,
                        'saldo_sesudah' => 100000,
                        'keterangan' => 'Setoran Simpanan Pokok',
                        'channel' => 'teller',
                        'status_approval' => 'approved',
                        'created_at' => $tglBuka,
                    ]
                );
            }

            if ($jenis === 'wajib') {
                $lamaBulan = (int) $tglBuka->diffInMonths($sekarang);
                $maxTrx = min($lamaBulan, 10); // max 10 bulan transaksi
                $saldoJalan = 0;

                for ($i = 0; $i < $maxTrx; $i++) {
                    $tglSetor = $tglBuka->copy()->addMonths($i);
                    if ($tglSetor->gt($sekarang)) break;

                    $saldoSebelum = $saldoJalan;
                    $saldoJalan += 50000;

                    TransaksiSimpanan::updateOrCreate(
                        [
                            'no_transaksi' => 'TRX-S-WAJIB-' . $rekening->anggota->no_anggota . '-' . ($i + 1),
                        ],
                        [
                            'rekening_id' => $rekening->id,
                            'user_id' => $user->id,
                            'jenis' => 'setoran',
                            'nominal' => 50000,
                            'saldo_sebelum' => $saldoSebelum,
                            'saldo_sesudah' => $saldoJalan,
                            'keterangan' => 'Setoran Simpanan Wajib bulan ke-' . ($i + 1),
                            'channel' => 'teller',
                            'status_approval' => 'approved',
                            'created_at' => $tglSetor,
                        ]
                    );
                }
            }

            if ($jenis === 'sukarela') {
                $saldoSukarela = (float) $rekening->saldo;
                if ($saldoSukarela <= 0) continue;

                // Create 3-5 setoran transactions with varying amounts
                $jumlahTrx = rand(3, 5);
                $sisaSaldo = $saldoSukarela;
                $saldoJalan = 0;

                for ($i = 0; $i < $jumlahTrx; $i++) {
                    // Distribute saldo across transactions
                    if ($i === $jumlahTrx - 1) {
                        $nominal = $sisaSaldo;
                    } else {
                        $maxBagian = (int) ($saldoSukarela / $jumlahTrx) * 2;
                        $minBagian = (int) ($saldoSukarela / $jumlahTrx) / 2;
                        $nominal = rand((int) $minBagian, (int) $maxBagian);
                        if ($nominal > $sisaSaldo) $nominal = $sisaSaldo;
                        if ($nominal < 100000) $nominal = $sisaSaldo / ($jumlahTrx - $i);
                    }
                    $nominal = (int) round($nominal / 1000) * 1000;

                    $tglSetor = $sekarang->copy()->subMonths($jumlahTrx - $i)->addDays(rand(1, 20));
                    if ($tglSetor->lt($tglBuka)) $tglSetor = $tglBuka->copy()->addDays(rand(1, 5));

                    $saldoSebelum = $saldoJalan;
                    $saldoJalan += $nominal;
                    $sisaSaldo -= $nominal;

                    TransaksiSimpanan::updateOrCreate(
                        [
                            'no_transaksi' => 'TRX-S-SUKARELA-' . $rekening->anggota->no_anggota . '-' . ($i + 1),
                        ],
                        [
                            'rekening_id' => $rekening->id,
                            'user_id' => $user->id,
                            'jenis' => 'setoran',
                            'nominal' => $nominal,
                            'saldo_sebelum' => $saldoSebelum,
                            'saldo_sesudah' => $saldoJalan,
                            'keterangan' => 'Setoran Simpanan Sukarela',
                            'channel' => 'teller',
                            'status_approval' => 'approved',
                            'created_at' => $tglSetor,
                        ]
                    );
                }
            }
        }
    }
}
