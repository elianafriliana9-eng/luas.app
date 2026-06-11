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
        $rekenings = RekeningSimpanan::all();
        $user = User::where('role', 'teller')->first();

        if ($rekenings->isEmpty()) {
            return;
        }

        foreach ($rekenings as $rekening) {
            $numTransactions = fake()->numberBetween(1, 5);

            for ($i = 0; $i < $numTransactions; $i++) {
                $isSetoran = fake()->boolean(80);
                $nominal = fake()->numberBetween(1, 50) * 10000;

                $saldoSesudah = $isSetoran ? ($rekening->saldo + $nominal) : ($rekening->saldo - $nominal);
                if ($saldoSesudah < 0) continue;

                $tanggalTransaksi = Carbon::now()->subDays(fake()->numberBetween(1, 60));

                TransaksiSimpanan::create([
                    'rekening_id' => $rekening->id,
                    'user_id' => $user->id ?? null,
                    'no_transaksi' => 'TRX-S-' . $tanggalTransaksi->format('ymdHi') . '-' . strtoupper(Str::random(6)),
                    'jenis' => $isSetoran ? 'setoran' : 'penarikan',
                    'nominal' => $nominal,
                    'saldo_sebelum' => $rekening->saldo,
                    'saldo_sesudah' => $saldoSesudah,
                    'keterangan' => $isSetoran ? 'Setoran tunai via teller' : 'Penarikan tunai',
                    'channel' => 'teller',
                    'created_at' => $tanggalTransaksi,
                ]);

                $rekening->update(['saldo' => $saldoSesudah]);
            }
        }
    }
}
