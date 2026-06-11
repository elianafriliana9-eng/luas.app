<?php

namespace Database\Seeders;

use App\Models\Anggota;
use App\Models\Pembiayaan;
use App\Models\JadwalAngsuran;
use App\Models\PengajuanPembiayaan;
use App\Models\ProdukPembiayaan;
use App\Models\TransaksiPembiayaan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PembiayaanSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('role', 'super_admin')->first() ?? User::first();
        if (!$user) return;

        $produk = ProdukPembiayaan::firstOrCreate(
            ['kode' => 'KAR'],
            [
                'nama' => 'Pembiayaan Karyawan',
                'skema_bunga' => 'flat',
                'bunga_pa' => 10,
                'max_jangka' => 36,
                'max_plafon' => 50000000,
                'aktif' => true,
            ]
        );

        $budi = Anggota::where('nik', '3171012304900001')->first();
        $siti = Anggota::where('nik', '3271012405920002')->first();
        $kurniawan = Anggota::where('nik', '3671011807910011')->first();
        $zainal = Anggota::where('nik', '3281011806880023')->first();
        $indral = Anggota::where('nik', '3178011004890009')->first();

        // Helper untuk create loan
        $createLoan = function (Anggota $anggota, string $noPengajuan, string $noPembiayaan,
                                float $nominal, int $jangka, int $bulanLalu,
                                string $sumberPembayaran = 'potong_gaji') use ($produk, $user) {

            $pokokPerBulan = (int)round($nominal / $jangka);
            $bungaPerBulan = (int)round(($nominal * 10 / 100) / $jangka);
            $totalAngsuran = $pokokPerBulan + $bungaPerBulan;

            $pengajuan = PengajuanPembiayaan::updateOrCreate(
                ['no_pengajuan' => $noPengajuan],
                [
                    'anggota_id' => $anggota->id,
                    'produk_id' => $produk->id,
                    'nominal_diajukan' => $nominal,
                    'jangka_bulan' => $jangka,
                    'tujuan' => 'konsumtif',
                    'status_approval' => 'disetujui',
                    'approved_by' => $user->id,
                    'approved_at' => now()->subMonths($bulanLalu),
                ]
            );

            $saldoPokok = $nominal - ($pokokPerBulan * $bulanLalu);
            $saldoBunga = ($bungaPerBulan * ($jangka - $bulanLalu));

            $pembiayaan = Pembiayaan::updateOrCreate(
                ['no_pembiayaan' => $noPembiayaan],
                [
                    'pengajuan_id' => $pengajuan->id,
                    'anggota_id' => $anggota->id,
                    'nominal_disetujui' => $nominal,
                    'nominal_cair' => $nominal,
                    'jangka_bulan' => $jangka,
                    'bunga_pa' => 10,
                    'metode_hitung' => 'flat',
                    'angsuran_pokok' => $pokokPerBulan,
                    'angsuran_bunga' => $bungaPerBulan,
                    'tanggal_akad' => now()->subMonths($bulanLalu),
                    'tanggal_cair' => now()->subMonths($bulanLalu),
                    'status' => 'aktif',
                    'saldo_pokok' => $saldoPokok,
                    'saldo_bunga' => $saldoBunga,
                    'kolektibilitas' => 1,
                    'auto_potong_gaji' => $sumberPembayaran !== 'bayar_manual',
                    'nominal_potongan' => $totalAngsuran,
                    'bulan_tersisa_potongan' => $jangka - $bulanLalu,
                    'sumber_pembayaran' => $sumberPembayaran,
                ]
            );

            $pembiayaan->jadwalAngsuran()->delete();
            for ($i = 1; $i <= $jangka; $i++) {
                $isPaid = $i <= $bulanLalu;
                $tglJatuhTempo = now()->subMonths($bulanLalu)->addMonths($i)->setDay($anggota->tanggal_gajian ?? 25);

                JadwalAngsuran::create([
                    'pembiayaan_id' => $pembiayaan->id,
                    'ke' => $i,
                    'tanggal_jatuh_tempo' => $tglJatuhTempo,
                    'pokok' => $pokokPerBulan,
                    'bunga' => $bungaPerBulan,
                    'total' => $totalAngsuran,
                    'saldo_akhir' => $nominal - ($i * $pokokPerBulan),
                    'status' => $isPaid ? 'lunas' : 'belum',
                    'tanggal_bayar' => $isPaid ? $tglJatuhTempo->copy() : null,
                ]);
            }

            // Create transaksi for paid installments
            if ($sumberPembayaran !== 'bayar_manual') {
                for ($i = 1; $i <= $bulanLalu; $i++) {
                    $jadwal = $pembiayaan->jadwalAngsuran()->where('ke', $i)->first();
                    if (!$jadwal) continue;

                    TransaksiPembiayaan::updateOrCreate(
                        ['no_transaksi' => 'TRX-PMB-' . $noPembiayaan . '-' . str_pad($i, 2, '0', STR_PAD_LEFT)],
                        [
                            'pembiayaan_id' => $pembiayaan->id,
                            'jadwal_id' => $jadwal->id,
                            'jenis' => 'angsuran',
                            'nominal_pokok' => $pokokPerBulan,
                            'nominal_bunga' => $bungaPerBulan,
                            'nominal_denda' => 0,
                            'total' => $totalAngsuran,
                            'channel' => 'teller',
                            'created_at' => $jadwal->tanggal_jatuh_tempo->copy()->subDays(1),
                        ]
                    );
                }
            }

            return $pembiayaan;
        };

        // 1. Budi Santoso — Rp 10.000.000, 12 bulan, 4 bulan berjalan (sudah 4x bayar)
        if ($budi) {
            $createLoan($budi, 'PJN-26040001', 'PMB-26040001', 10000000, 12, 4, 'potong_gaji');
        }

        // 2. Siti Rahayu — Rp 5.000.000, 6 bulan, 2 bulan berjalan
        if ($siti) {
            $createLoan($siti, 'PJN-26040002', 'PMB-26040002', 5000000, 6, 2, 'keduanya');
        }

        // 3. Kurniawan Saputra — Rp 25.000.000, 24 bulan, 6 bulan berjalan
        if ($kurniawan) {
            $pembiayaan = $createLoan($kurniawan, 'PJN-26050001', 'PMB-26050001', 25000000, 24, 6, 'potong_gaji');

            // Add collateral for larger loan
            $pembiayaan->jaminan()->updateOrCreate(
                ['no_dokumen' => 'SHM-0012345'],
                [
                    'jenis_jaminan' => 'tanah',
                    'deskripsi' => 'Sertifikat Hak Milik tanah seluas 150 m2 atas nama Kurniawan Saputra, berlokasi di BSD City, Tangerang Selatan',
                    'nilai_taksasi' => 35000000,
                ]
            );
        }

        // 4. Zainal Arifin — Rp 15.000.000, 12 bulan, 3 bulan berjalan
        if ($zainal) {
            $createLoan($zainal, 'PJN-26050002', 'PMB-26050002', 15000000, 12, 3, 'potong_gaji');
        }

        // 5. Indra Lesmana — pengajuan baru (pending)
        if ($indral) {
            PengajuanPembiayaan::updateOrCreate(
                ['no_pengajuan' => 'PJN-26060001'],
                [
                    'anggota_id' => $indral->id,
                    'produk_id' => $produk->id,
                    'nominal_diajukan' => 8000000,
                    'jangka_bulan' => 10,
                    'tujuan' => 'konsumtif',
                    'status_approval' => 'pending',
                ]
            );
        }
    }
}
