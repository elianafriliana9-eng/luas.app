<?php

namespace Database\Seeders;

use App\Models\Cabang;
use App\Models\ChartOfAccount;
use App\Models\Jurnal;
use App\Models\JurnalDetail;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Carbon\Carbon;

class JurnalSeeder extends Seeder
{
    public function run(): void
    {
        $cabang = Cabang::first();
        $user = User::first();
        if (!$cabang || !$user) return;

        $akun = function (string $kode) {
            return ChartOfAccount::where('kode_akun', $kode)->first();
        };

        $kas = $akun('11010');
        $bank = $akun('12010');
        $modal = $akun('31010');
        $simpananPokok = $akun('21010');
        $simpananWajib = $akun('21020');
        $simpananSukarela = $akun('21030');
        $piutangPembiayaan = $akun('13010');
        $pendapatanBunga = $akun('41010');
        $pendapatanAdmin = $akun('42010');
        $bebanOperasional = $akun('52010');

        $noJurnal = 0;

        $buatJurnal = function (string $tanggal, string $keterangan, string $jenis, array $details) use ($cabang, $user, &$noJurnal) {
            $noJurnal++;
            $jurnal = Jurnal::create([
                'cabang_id' => $cabang->id,
                'no_jurnal' => 'JU-' . Carbon::parse($tanggal)->format('Ymd') . '-' . str_pad($noJurnal, 3, '0', STR_PAD_LEFT),
                'tanggal' => $tanggal,
                'keterangan' => $keterangan,
                'jenis' => $jenis,
                'dibuat_oleh' => $user->id,
            ]);

            foreach ($details as $detail) {
                JurnalDetail::create([
                    'jurnal_id' => $jurnal->id,
                    'akun_id' => $detail['akun']->id,
                    'debet' => $detail['debet'] ?? 0,
                    'kredit' => $detail['kredit'] ?? 0,
                    'keterangan' => $detail['keterangan'] ?? '',
                ]);
            }

            return $jurnal;
        };

        if ($kas && $bank && $modal) {
            // 1. Setoran modal awal
            $buatJurnal(
                now()->subMonths(6)->format('Y-m-d'),
                'Setoran Modal Awal Koperasi Lumbung Artha Sejahtera',
                'manual',
                [
                    ['akun' => $bank, 'debet' => 500000000, 'kredit' => 0, 'keterangan' => 'Setoran modal ke Bank Mandiri'],
                    ['akun' => $modal, 'debet' => 0, 'kredit' => 500000000, 'keterangan' => 'Modal disetor anggota'],
                ]
            );

            // 2. Tarik tunai untuk kas teller
            $buatJurnal(
                now()->subMonths(6)->addDays(1)->format('Y-m-d'),
                'Tarik Tunai dari Bank Mandiri untuk Kas Teller Cabang Utama',
                'manual',
                [
                    ['akun' => $kas, 'debet' => 100000000, 'kredit' => 0, 'keterangan' => 'Kas teller bertambah'],
                    ['akun' => $bank, 'debet' => 0, 'kredit' => 100000000, 'keterangan' => 'Bank Mandiri berkurang'],
                ]
            );

            // 3. Pencairan pembiayaan Budi Santoso
            $buatJurnal(
                now()->subMonths(4)->format('Y-m-d'),
                'Pencairan Pembiayaan Budi Santoso Rp 10.000.000 (PMB-26040001)',
                'otomatis',
                [
                    ['akun' => $piutangPembiayaan, 'debet' => 10000000, 'kredit' => 0, 'keterangan' => 'Piutang pembiayaan bertambah'],
                    ['akun' => $kas, 'debet' => 0, 'kredit' => 10000000, 'keterangan' => 'Kas teller berkurang (pencairan)'],
                ]
            );

            // 4. Penerimaan angsuran Budi Santoso (bulan 1)
            $buatJurnal(
                now()->subMonths(3)->format('Y-m-d'),
                'Angsuran Pembiayaan Budi Santoso ke-1 (potong gaji)',
                'otomatis',
                [
                    ['akun' => $kas, 'debet' => 916666, 'kredit' => 0, 'keterangan' => 'Penerimaan angsuran'],
                    ['akun' => $piutangPembiayaan, 'debet' => 0, 'kredit' => 833333, 'keterangan' => 'Pengurangan piutang pokok'],
                    ['akun' => $pendapatanBunga, 'debet' => 0, 'kredit' => 83333, 'keterangan' => 'Pendapatan bunga'],
                ]
            );

            // 5. Penerimaan angsuran Budi Santoso (bulan 2)
            $buatJurnal(
                now()->subMonths(2)->format('Y-m-d'),
                'Angsuran Pembiayaan Budi Santoso ke-2 (potong gaji)',
                'otomatis',
                [
                    ['akun' => $kas, 'debet' => 916666, 'kredit' => 0, 'keterangan' => 'Penerimaan angsuran'],
                    ['akun' => $piutangPembiayaan, 'debet' => 0, 'kredit' => 833333, 'keterangan' => 'Pengurangan piutang pokok'],
                    ['akun' => $pendapatanBunga, 'debet' => 0, 'kredit' => 83333, 'keterangan' => 'Pendapatan bunga'],
                ]
            );

            // 6. Penerimaan angsuran Budi Santoso (bulan 3)
            $buatJurnal(
                now()->subMonth()->format('Y-m-d'),
                'Angsuran Pembiayaan Budi Santoso ke-3 (potong gaji)',
                'otomatis',
                [
                    ['akun' => $kas, 'debet' => 916666, 'kredit' => 0, 'keterangan' => 'Penerimaan angsuran'],
                    ['akun' => $piutangPembiayaan, 'debet' => 0, 'kredit' => 833333, 'keterangan' => 'Pengurangan piutang pokok'],
                    ['akun' => $pendapatanBunga, 'debet' => 0, 'kredit' => 83333, 'keterangan' => 'Pendapatan bunga'],
                ]
            );

            // 7. Beban operasional bulan ini
            $buatJurnal(
                now()->format('Y-m-d'),
                'Beban Operasional Bulan ' . now()->format('M Y'),
                'manual',
                [
                    ['akun' => $bebanOperasional, 'debet' => 15000000, 'kredit' => 0, 'keterangan' => 'Gaji karyawan dan operasional'],
                    ['akun' => $kas, 'debet' => 0, 'kredit' => 15000000, 'keterangan' => 'Pembayaran beban operasional'],
                ]
            );
        }
    }
}
