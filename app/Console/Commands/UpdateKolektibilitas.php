<?php

namespace App\Console\Commands;

use App\Models\Pembiayaan;
use Illuminate\Console\Command;

class UpdateKolektibilitas extends Command
{
    protected $signature = 'kolektibilitas:update';
    protected $description = 'Update kolektibilitas rating (1-5) berdasarkan hari tunggakan (OJK compliance)';

    public function handle()
    {
        $this->info('Memulai update kolektibilitas...');

        $pembiayaanList = Pembiayaan::where('status', 'aktif')->get();
        $updatedCount = 0;

        foreach ($pembiayaanList as $pembiayaan) {
            $nextInstallment = $pembiayaan->jadwalAngsuran()
                ->where('status', 'belum')
                ->orderBy('tanggal_jatuh_tempo', 'asc')
                ->first();

            if (!$nextInstallment) {
                continue;
            }

            $jatuhTempo = \Carbon\Carbon::parse($nextInstallment->tanggal_jatuh_tempo);
            $hariTunggak = max(0, $jatuhTempo->diffInDays(now(), false));

            $kolektibilitas = match (true) {
                $hariTunggak <= 30 => 1,  // Lancar
                $hariTunggak <= 90 => 2,  // Dalam Perhatian Khusus
                $hariTunggak <= 120 => 3, // Kurang Lancar
                $hariTunggak <= 180 => 4, // Diragukan
                default => 5,             // Macet
            };

            $changed = false;
            if ($pembiayaan->hari_tunggak != $hariTunggak) {
                $pembiayaan->hari_tunggak = $hariTunggak;
                $changed = true;
            }
            if ($pembiayaan->kolektibilitas != $kolektibilitas) {
                $pembiayaan->kolektibilitas = $kolektibilitas;
                $changed = true;
            }

            if ($changed) {
                $pembiayaan->save();
                $updatedCount++;
                $this->info("  [UPDATE] {$pembiayaan->no_pembiayaan} - Hari: {$hariTunggak}, Kolektibilitas: {$kolektibilitas}");
            }
        }

        $this->info("Selesai. {$updatedCount} pembiayaan diupdate.");
        return Command::SUCCESS;
    }
}
