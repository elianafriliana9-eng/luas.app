<?php

namespace Database\Seeders;

use App\Models\RekeningSimpanan;
use App\Models\TransaksiSimpanan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Carbon\Carbon;

class TransaksiSimpananSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        $rekenings = RekeningSimpanan::all();
        $user = User::where('role', 'teller')->first();

        if ($rekenings->isEmpty()) {
            return;
        }

        foreach ($rekenings as $rekening) {
            // Give 1-5 transactions per account
            $numTransactions = $faker->numberBetween(1, 5);

            for ($i = 0; $i < $numTransactions; $i++) {
                $isSetoran = $faker->boolean(80); // 80% are deposits
                $nominal = $faker->numberBetween(1, 50) * 10000;

                // Adjust balance for history consistency logic if needed
                // We will just do dummy historical balance without strict validation for demo seed
                
                $saldoSesudah = $isSetoran ? ($rekening->saldo + $nominal) : ($rekening->saldo - $nominal);
                if ($saldoSesudah < 0) continue; // Skip invalid withdrawals

                $tanggalTransaksi = Carbon::now()->subDays($faker->numberBetween(1, 60));

                TransaksiSimpanan::create([
                    'rekening_id' => $rekening->id,
                    'user_id' => $user->id ?? null,
                    'no_transaksi' => 'TRX-S-' . $tanggalTransaksi->format('ymdHi') . '-' . strtoupper(\Illuminate\Support\Str::random(6)),
                    'jenis' => $isSetoran ? 'setoran' : 'penarikan',
                    'nominal' => $nominal,
                    'saldo_sebelum' => $rekening->saldo,
                    'saldo_sesudah' => $saldoSesudah,
                    'keterangan' => $isSetoran ? 'Setoran tunai via teller' : 'Penarikan tunai',
                    'channel' => 'teller',
                    'created_at' => $tanggalTransaksi,
                ]);

                // Update reckoning balance
                $rekening->update(['saldo' => $saldoSesudah]);
            }
        }
    }
}
