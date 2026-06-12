<?php

namespace App\Console\Commands;

use App\Models\SimpananBerjangka;
use Illuminate\Console\Command;

class ProsesARODeposito extends Command
{
    protected $signature = 'deposito:aro {--dry-run : Preview tanpa mengubah data}';

    protected $description = 'Proses Auto Roll Over (ARO) untuk deposito yang jatuh tempo dengan auto_perpanjang aktif';

    public function handle(): int
    {
        $depositoList = SimpananBerjangka::where('status', 'aktif')
            ->where('auto_perpanjang', true)
            ->whereDate('tanggal_jatuh_tempo', '<=', now())
            ->get();

        if ($depositoList->isEmpty()) {
            $this->info('Tidak ada deposito yang perlu diperpanjang.');
            return Command::SUCCESS;
        }

        $dryRun = $this->option('dry-run');
        $count = 0;

        foreach ($depositoList as $deposito) {
            $this->line("{$deposito->no_deposito} — {$deposito->anggota?->nama_lengkap}");

            if ($dryRun) {
                $this->warn("  [DRY-RUN] Akan diperpanjang {$deposito->jangka_bulan} bulan");
                $count++;
                continue;
            }

            $deposito->status = 'perpanjang';
            $deposito->save();

            SimpananBerjangka::create([
                'anggota_id' => $deposito->anggota_id,
                'no_deposito' => $deposito->no_deposito . '-R' . rand(10, 99),
                'nominal' => $deposito->nominal,
                'jangka_bulan' => $deposito->jangka_bulan,
                'bunga_pa' => $deposito->bunga_pa,
                'tanggal_mulai' => now(),
                'tanggal_jatuh_tempo' => now()->addMonths($deposito->jangka_bulan),
                'status' => 'aktif',
                'auto_perpanjang' => $deposito->auto_perpanjang,
            ]);

            $this->info("  ✓ Diperpanjang");
            $count++;
        }

        $mode = $dryRun ? '[DRY-RUN] ' : '';
        $this->info("{$mode}{$count} deposito diproses.");

        return Command::SUCCESS;
    }
}
