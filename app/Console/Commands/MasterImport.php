<?php

namespace App\Console\Commands;

use App\Imports\MasterDataImport;
use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;

class MasterImport extends Command
{
    protected $signature = 'master:import
        {--reset : Hapus data lama sebelum import}
        {--file= : Path ke file Template.xlsx (default: Template.xlsx di root project)}';

    protected $description = 'Import master data anggota, simpanan, dan saldo dari Template.xlsx';

    public function handle(): int
    {
        $filePath = $this->option('file') ?: base_path('Template.xlsx');
        $reset = $this->option('reset');

        if (!file_exists($filePath)) {
            $this->error("File tidak ditemukan: {$filePath}");
            return Command::FAILURE;
        }

        $this->info('Memulai import master data...');
        if ($reset) {
            $this->warn('Mode reset: data lama akan dihapus sebelum import.');
        }

        try {
            $import = new MasterDataImport($reset);
            Excel::import($import, $filePath);

            $hasil = $import->getHasil();

            $suksesOST = collect($hasil['OST'] ?? [])->where('status', 'berhasil')->count();
            $gagalOST = collect($hasil['OST'] ?? [])->where('status', 'gagal')->count();
            $suksesSimpanan = collect(array_merge(
                $hasil['SIMPANAN POKOK DAN WAJIB'] ?? [],
                $hasil['SEMUA SIMPANAN'] ?? []
            ))->where('status', 'berhasil')->count();
            $gagalSimpanan = collect(array_merge(
                $hasil['SIMPANAN POKOK DAN WAJIB'] ?? [],
                $hasil['SEMUA SIMPANAN'] ?? []
            ))->where('status', 'gagal')->count();

            $this->info("Import selesai.");
            $this->table(
                ['Sheet', 'Berhasil', 'Gagal'],
                [
                    ['OST', $suksesOST, $gagalOST],
                    ['SIMPANAN POKOK DAN WAJIB', $suksesSimpanan, $gagalSimpanan],
                    ['SEMUA SIMPANAN', $suksesSimpanan, $gagalSimpanan],
                ]
            );

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Gagal import master data: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
