<?php

namespace App\Console\Commands;

use App\Models\ProdukSimpanan;
use App\Models\RekeningSimpanan;
use App\Models\TransaksiSimpanan;
use App\Traits\SimpananJurnal;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ProsesBungaSimpanan extends Command
{
    use SimpananJurnal;

    protected $signature = 'simpanan:bunga {--dry-run : Preview tanpa mengubah data}';

    protected $description = 'Hitung dan kredit bunga untuk semua rekening simpanan dengan auto_bunga aktif';

    public function handle(): int
    {
        $produkList = ProdukSimpanan::where('aktif', true)->where('auto_bunga', true)->get();

        if ($produkList->isEmpty()) {
            $this->warn('Tidak ada produk simpanan dengan auto_bunga aktif.');
            return Command::SUCCESS;
        }

        $periode = now()->format('Y-m');
        $totalBunga = 0;
        $totalRekening = 0;
        $dryRun = $this->option('dry-run');

        foreach ($produkList as $produk) {
            $this->info("Produk: {$produk->nama} ({$produk->bunga_pa}% p.a.)");

            $rekeningList = RekeningSimpanan::where('produk_id', $produk->id)
                ->where('status', 'aktif')
                ->get();

            $bar = $this->output->createProgressBar($rekeningList->count());
            $bar->start();

            foreach ($rekeningList as $rekening) {
                $bungaBulanan = round($rekening->saldo * ($produk->bunga_pa / 12 / 100), 2);

                if ($bungaBulanan <= 0) {
                    $bar->advance();
                    continue;
                }

                $totalBunga += $bungaBulanan;
                $totalRekening++;

                if (!$dryRun) {
                    DB::beginTransaction();
                    try {
                        $saldoSebelum = $rekening->saldo;
                        $saldoSesudah = $saldoSebelum + $bungaBulanan;

                        $rekening->saldo = $saldoSesudah;
                        $rekening->save();

                        $transaksi = TransaksiSimpanan::create([
                            'rekening_id' => $rekening->id,
                            'user_id' => auth()->id() ?? '00000000-0000-0000-0000-000000000000',
                            'no_transaksi' => 'BNG-' . now()->format('ymd') . '-' . strtoupper(substr(uniqid(), -6)),
                            'jenis' => 'bunga',
                            'nominal' => $bungaBulanan,
                            'saldo_sebelum' => $saldoSebelum,
                            'saldo_sesudah' => $saldoSesudah,
                            'keterangan' => "Bunga {$produk->nama} periode {$periode}",
                            'channel' => 'system',
                            'status_approval' => 'approved',
                            'approved_by' => auth()->id() ?? '00000000-0000-0000-0000-000000000000',
                            'approved_at' => now(),
                        ]);

                        $this->buatJurnalSetoran($transaksi);

                        DB::commit();
                    } catch (\Exception $e) {
                        DB::rollBack();
                        $this->error("Gagal proses rekening {$rekening->no_rekening}: {$e->getMessage()}");
                    }
                }

                $bar->advance();
            }

            $bar->finish();
            $this->newLine();
        }

        $this->newLine();
        $mode = $dryRun ? '[DRY-RUN] ' : '';
        $this->info("{$mode}Selesai. {$totalRekening} rekening diproses, total bunga: Rp " . number_format($totalBunga, 0, ',', '.'));

        return Command::SUCCESS;
    }
}
