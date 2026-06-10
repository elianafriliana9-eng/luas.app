<?php

namespace App\Console\Commands;

use App\Models\Pembiayaan;
use App\Models\PotonganGaji;
use App\Models\TransaksiPembiayaan;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ProsesPotonganGaji extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payroll:proses {--periode=} {--dry-run}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Proses potongan gaji otomatis untuk karyawan yang memiliki pembiayaan aktif';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $periode = $this->option('periode') ?? now()->format('Y-m-01');
        $dryRun = $this->option('dry-run');

        $this->info("Memproses potongan gaji untuk periode: {$periode}");
        if ($dryRun) {
            $this->warn("MODE DRY RUN — tidak ada data yang diubah");
        }

        // Get all active pembiayaan with auto_potong_gaji
        $pembiayaanList = Pembiayaan::where('status', 'aktif')
            ->where('auto_potong_gaji', true)
            ->where('bulan_tersisa_potongan', '>', 0)
            ->with(['anggota', 'jadwalAngsuran'])
            ->get();

        $this->info("Ditemukan {$pembiayaanList->count()} pembiayaan dengan auto_potong_gaji aktif");

        $processedCount = 0;
        $skippedCount = 0;
        $failedCount = 0;

        DB::beginTransaction();
        try {
            foreach ($pembiayaanList as $pembiayaan) {
                $anggota = $pembiayaan->anggota;

                // Check if already processed for this period
                $existing = PotonganGaji::where('anggota_id', $anggota->id)
                    ->where('periode', $periode)
                    ->where('pembiayaan_id', $pembiayaan->id)
                    ->first();

                if ($existing) {
                    $this->warn("  [SKIP] {$anggota->nama_lengkap} - Sudah diproses untuk periode ini");
                    $skippedCount++;
                    continue;
                }

                // Check if today is the payday for this employee
                $tanggalGajian = $anggota->tanggal_gajian ?? 25;
                $today = now()->day;
                if ($today != $tanggalGajian && !$this->option('periode')) {
                    $this->warn("  [SKIP] {$anggota->nama_lengkap} - Belum tanggal gajian ({$tanggalGajian})");
                    $skippedCount++;
                    continue;
                }

                // Find the next unpaid installment
                $nextInstallment = $pembiayaan->jadwalAngsuran()
                    ->where('status', 'belum')
                    ->orderBy('tanggal_jatuh_tempo', 'asc')
                    ->first();

                if (!$nextInstallment) {
                    $this->info("  [LUNAS] {$anggota->nama_lengkap} - Semua angsuran sudah lunas");
                    if (!$dryRun) {
                        $pembiayaan->status = 'lunas';
                        $pembiayaan->tanggal_lunas = now();
                        $pembiayaan->save();
                    }
                    continue;
                }

                $nominalPotongan = $nextInstallment->total;
                $gajiBruto = $anggota->gaji_pokok ?? 0;
                $gajiDiterima = $gajiBruto - $nominalPotongan;

                if ($dryRun) {
                    $this->info("  [DRY RUN] {$anggota->nama_lengkap} - Potongan: Rp " . number_format($nominalPotongan, 0, ',', '.'));
                    $processedCount++;
                    continue;
                }

                // Create potongan record
                $potongan = PotonganGaji::create([
                    'anggota_id' => $anggota->id,
                    'pembiayaan_id' => $pembiayaan->id,
                    'jadwal_angsuran_id' => $nextInstallment->id,
                    'periode' => $periode,
                    'gaji_bruto' => $gajiBruto,
                    'nominal_potongan' => $nominalPotongan,
                    'gaji_diterima' => $gajiDiterima,
                    'jenis_potongan' => 'angsuran_pokok',
                    'status' => 'diproses',
                    'processed_at' => now(),
                ]);

                // Mark installment as paid
                $nextInstallment->status = 'lunas';
                $nextInstallment->tanggal_bayar = now();
                $nextInstallment->save();

                // Update pembiayaan balance
                $pembiayaan->saldo_pokok -= $nextInstallment->angsuran_pokok;
                $pembiayaan->saldo_bunga -= $nextInstallment->angsuran_bunga;
                $pembiayaan->bulan_tersisa_potongan = max(0, ($pembiayaan->bulan_tersisa_potongan ?? 1) - 1);

                if ($pembiayaan->saldo_pokok <= 0) {
                    $pembiayaan->status = 'lunas';
                    $pembiayaan->tanggal_lunas = now();
                }
                $pembiayaan->save();

                // Create transaction record
                TransaksiPembiayaan::create([
                    'pembiayaan_id' => $pembiayaan->id,
                    'jadwal_id' => $nextInstallment->id,
                    'no_transaksi' => 'PAYROLL-' . now()->format('ymd') . '-' . strtoupper(substr(uniqid(), -6)),
                    'jenis' => 'angsuran',
                    'nominal_pokok' => $nextInstallment->angsuran_pokok,
                    'nominal_bunga' => $nextInstallment->angsuran_bunga,
                    'nominal_denda' => 0,
                    'total' => $nextInstallment->total,
                    'channel' => 'potong_gaji',
                    'ref_payment' => $potongan->id,
                ]);

                $this->info("  [OK] {$anggota->nama_lengkap} - Potongan: Rp " . number_format($nominalPotongan, 0, ',', '.') . " | Diterima: Rp " . number_format($gajiDiterima, 0, ',', '.'));
                $processedCount++;
            }

            if (!$dryRun) {
                DB::commit();
            }

            $this->newLine();
            $this->info("=== HASIL ===");
            $this->info("Diproses: {$processedCount}");
            $this->info("Dilewati: {$skippedCount}");
            $this->info("Gagal: {$failedCount}");

            if ($dryRun) {
                DB::rollBack();
                $this->warn("Dry run selesai — tidak ada perubahan data");
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Gagal: " . $e->getMessage());
            $this->error($e->getTraceAsString());
            return Command::FAILURE;
        }
    }
}
