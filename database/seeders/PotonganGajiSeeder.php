<?php

namespace Database\Seeders;

use App\Models\Anggota;
use App\Models\Pembiayaan;
use App\Models\PotonganGaji;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class PotonganGajiSeeder extends Seeder
{
    public function run(): void
    {
        $budi = Anggota::where('nik', '3171012304900001')->first();
        $siti = Anggota::where('nik', '3271012405920002')->first();
        $kurniawan = Anggota::where('nik', '3671011807910011')->first();
        $zainal = Anggota::where('nik', '3281011806880023')->first();

        $budiLoan = $budi ? Pembiayaan::where('anggota_id', $budi->id)->first() : null;
        $sitiLoan = $siti ? Pembiayaan::where('anggota_id', $siti->id)->first() : null;
        $kurniawanLoan = $kurniawan ? Pembiayaan::where('anggota_id', $kurniawan->id)->first() : null;
        $zainalLoan = $zainal ? Pembiayaan::where('anggota_id', $zainal->id)->first() : null;

        $data = [];

        // Budi — 4 bulan potongan gaji
        if ($budi && $budiLoan) {
            for ($i = 1; $i <= 4; $i++) {
                $jadwal = $budiLoan->jadwalAngsuran()->where('ke', $i)->first();
                $periode = Carbon::now()->subMonths(4 - $i + 1)->setDay(25);
                $data[] = [
                    'anggota_id' => $budi->id,
                    'pembiayaan_id' => $budiLoan->id,
                    'jadwal_angsuran_id' => $jadwal?->id,
                    'periode' => $periode->format('Y-m-d'),
                    'gaji_bruto' => 8500000,
                    'nominal_potongan' => 916666,
                    'gaji_diterima' => 7583334,
                    'jenis_potongan' => 'angsuran_pokok',
                    'status' => 'diproses',
                    'keterangan' => 'Potong gaji angsuran ke-' . $i . ' (PMB-26040001)',
                    'processed_at' => $periode->copy()->subDays(2),
                ];
            }
        }

        // Siti — 2 bulan potongan
        if ($siti && $sitiLoan) {
            for ($i = 1; $i <= 2; $i++) {
                $jadwal = $sitiLoan->jadwalAngsuran()->where('ke', $i)->first();
                $periode = Carbon::now()->subMonths(2 - $i + 1)->setDay(25);
                $data[] = [
                    'anggota_id' => $siti->id,
                    'pembiayaan_id' => $sitiLoan->id,
                    'jadwal_angsuran_id' => $jadwal?->id,
                    'periode' => $periode->format('Y-m-d'),
                    'gaji_bruto' => 7200000,
                    'nominal_potongan' => 875000,
                    'gaji_diterima' => 6325000,
                    'jenis_potongan' => 'angsuran_pokok',
                    'status' => 'diproses',
                    'keterangan' => 'Potong gaji angsuran ke-' . $i . ' (PMB-26040002)',
                    'processed_at' => $periode->copy()->subDays(1),
                ];
            }
        }

        // Kurniawan — 6 bulan potongan
        if ($kurniawan && $kurniawanLoan) {
            for ($i = 1; $i <= 6; $i++) {
                $jadwal = $kurniawanLoan->jadwalAngsuran()->where('ke', $i)->first();
                $periode = Carbon::now()->subMonths(6 - $i + 1)->setDay(25);
                $data[] = [
                    'anggota_id' => $kurniawan->id,
                    'pembiayaan_id' => $kurniawanLoan->id,
                    'jadwal_angsuran_id' => $jadwal?->id,
                    'periode' => $periode->format('Y-m-d'),
                    'gaji_bruto' => 16000000,
                    'nominal_potongan' => 1145833,
                    'gaji_diterima' => 14854167,
                    'jenis_potongan' => 'angsuran_pokok',
                    'status' => 'diproses',
                    'keterangan' => 'Potong gaji angsuran ke-' . $i . ' (PMB-26050001)',
                    'processed_at' => $periode->copy()->subDays(3),
                ];
            }
        }

        // Zainal — 3 bulan potongan
        if ($zainal && $zainalLoan) {
            for ($i = 1; $i <= 3; $i++) {
                $jadwal = $zainalLoan->jadwalAngsuran()->where('ke', $i)->first();
                $periode = Carbon::now()->subMonths(3 - $i + 1)->setDay(25);
                $data[] = [
                    'anggota_id' => $zainal->id,
                    'pembiayaan_id' => $zainalLoan->id,
                    'jadwal_angsuran_id' => $jadwal?->id,
                    'periode' => $periode->format('Y-m-d'),
                    'gaji_bruto' => 10000000,
                    'nominal_potongan' => 1375000,
                    'gaji_diterima' => 8625000,
                    'jenis_potongan' => 'angsuran_pokok',
                    'status' => 'diproses',
                    'keterangan' => 'Potong gaji angsuran ke-' . $i . ' (PMB-26050002)',
                    'processed_at' => $periode->copy()->subDays(2),
                ];
            }
        }

        foreach ($data as $item) {
            PotonganGaji::create($item);
        }
    }
}
