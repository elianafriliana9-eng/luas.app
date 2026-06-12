<?php

namespace Database\Seeders;

use App\Models\RekeningSimpanan;
use App\Models\TransaksiSimpanan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class TransaksiSimpananSeeder extends Seeder
{
    public function run(): void
    {
        $rekenings = RekeningSimpanan::with('produk', 'anggota')->get();
        $user = User::where('role', 'super_admin')->first() ?? User::first();
        if ($rekenings->isEmpty() || !$user) return;

        $sekarang = Carbon::now()->startOfDay();

        foreach ($rekenings as $rekening) {
            $jenis = $rekening->produk->kode;
            $tglBuka = Carbon::parse($rekening->tanggal_buka)->startOfDay();
            $noAnggota = $rekening->anggota->no_anggota;

            // SIMPOK — setoran awal Rp 150.000
            if ($jenis === 'SIMPOK') {
                $saldoAkhir = (float) $rekening->saldo;
                TransaksiSimpanan::updateOrCreate(
                    ['no_transaksi' => 'TRX-POKOK-' . $noAnggota],
                    [
                        'rekening_id' => $rekening->id,
                        'user_id' => $user->id,
                        'jenis' => 'setoran',
                        'nominal' => $saldoAkhir,
                        'saldo_sebelum' => 0,
                        'saldo_sesudah' => $saldoAkhir,
                        'keterangan' => 'Setoran Simpanan Pokok',
                        'channel' => 'admin',
                        'status_approval' => 'approved',
                        'created_at' => $tglBuka,
                    ]
                );
            }

            // SIMWA — Rp 50.000/bulan sejak join
            if ($jenis === 'SIMWA') {
                $lamaBulan = (int) $tglBuka->diffInMonths($sekarang);
                $maxTrx = min($lamaBulan, (int) ((float) $rekening->saldo / 50000));
                if ($maxTrx > 10) $maxTrx = 10;
                $saldoJalan = 0;

                for ($i = 0; $i < $maxTrx; $i++) {
                    $tglSetor = $tglBuka->copy()->addMonths($i);
                    if ($tglSetor->gt($sekarang)) break;

                    $saldoSebelum = $saldoJalan;
                    $saldoJalan += 50000;

                    TransaksiSimpanan::updateOrCreate(
                        ['no_transaksi' => 'TRX-WAJIB-' . $noAnggota . '-' . str_pad($i + 1, 2, '0', STR_PAD_LEFT)],
                        [
                            'rekening_id' => $rekening->id,
                            'user_id' => $user->id,
                            'jenis' => 'setoran',
                            'nominal' => 50000,
                            'saldo_sebelum' => $saldoSebelum,
                            'saldo_sesudah' => $saldoJalan,
                            'keterangan' => 'Setoran Simpanan Wajib bulan ke-' . ($i + 1),
                            'channel' => 'admin',
                            'status_approval' => 'approved',
                            'created_at' => $tglSetor,
                        ]
                    );
                }
            }

            // SIMSUKA — nominal tetap (tidak random)
            if ($jenis === 'SIMSUKA') {
                $saldoAkhir = (float) $rekening->saldo;
                if ($saldoAkhir <= 0) continue;

                // Tentukan jumlah setoran spesifik berdasarkan nominal akhir
                $setoran = $this->getSetoranSukarela($noAnggota, $saldoAkhir);
                $saldoJalan = 0;

                foreach ($setoran as $i => $nominal) {
                    $tglSetor = $tglBuka->copy()->addMonths($i * 2);
                    if ($tglSetor->gt($sekarang)) $tglSetor = $sekarang->copy()->subDays(count($setoran) - $i);

                    $saldoSebelum = $saldoJalan;
                    $saldoJalan += $nominal;

                    TransaksiSimpanan::updateOrCreate(
                        ['no_transaksi' => 'TRX-SUKARELA-' . $noAnggota . '-' . str_pad($i + 1, 2, '0', STR_PAD_LEFT)],
                        [
                            'rekening_id' => $rekening->id,
                            'user_id' => $user->id,
                            'jenis' => 'setoran',
                            'nominal' => $nominal,
                            'saldo_sebelum' => $saldoSebelum,
                            'saldo_sesudah' => $saldoJalan,
                            'keterangan' => 'Setoran Simpanan Sukarela',
                            'channel' => 'admin',
                            'status_approval' => 'approved',
                            'created_at' => $tglSetor,
                        ]
                    );
                }
            }
        }
    }

    private function getSetoranSukarela(string $noAnggota, float $total): array
    {
        $daftar = [
            'ANG-2023-001' => [2000000, 1500000, 1000000, 500000],        // 5jt
            'ANG-2023-002' => [1500000, 1000000, 500000],                  // 3jt
            'ANG-2024-003' => [10000000, 8000000, 5000000, 2000000],       // 25jt
            'ANG-2024-004' => [1000000],                                    // 1jt
            'ANG-2024-005' => [3000000, 2500000, 2000000],                  // 7.5jt
            'ANG-2024-006' => [10000000, 10000000, 5000000, 5000000],      // 30jt
            'ANG-2024-007' => [2000000, 1500000, 1000000, 500000],         // 5jt
            'ANG-2024-008' => [1000000, 1000000],                           // 2jt
            'ANG-2024-010' => [500000],                                     // 500rb
            'ANG-2024-011' => [8000000, 7000000, 5000000],                 // 20jt
            'ANG-2024-012' => [5000000, 3000000, 2000000],                 // 10jt
            'ANG-2024-013' => [3000000, 3000000, 2000000],                 // 8jt
            'ANG-2024-014' => [1000000, 500000],                            // 1.5jt
        ];

        return $daftar[$noAnggota] ?? [$total];
    }
}
