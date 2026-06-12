<?php

namespace App\Imports\Sheets;

use App\Models\Anggota;
use App\Models\Cabang;
use App\Models\Pembiayaan;
use App\Models\PengajuanPembiayaan;
use App\Models\Perusahaan;
use App\Models\ProdukPembiayaan;
use App\Models\JadwalAngsuran;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class OstSheetImport implements ToCollection, WithStartRow, SkipsEmptyRows
{
    private bool $resetDone = false;
    private bool $resetBeforeImport;
    private array $hasil = [];
    private static ?Cabang $defaultCabang = null;
    private static array $perusahaanCache = [];

    public function __construct(bool $resetBeforeImport = true)
    {
        $this->resetBeforeImport = $resetBeforeImport;
    }

    public function startRow(): int
    {
        return 3;
    }

    public function collection(Collection $rows)
    {
        if ($this->resetBeforeImport && !$this->resetDone) {
            $this->resetData();
            $this->resetDone = true;
        }

        $this->ensureDefaultCabang();

        foreach ($rows as $row) {
            $baris = $row[1] ?? null;
            $nik = $row[7] ? (string) $row[7] : '-';
            $nama = $row[4] ?? '-';

            if (empty($baris) || empty($nik)) continue;

            try {
                DB::beginTransaction();

                $ptKode = $row[2] ? trim($row[2]) : null;
                $perusahaan = $this->findOrCreatePerusahaan($ptKode);

                $anggota = $this->findOrCreateAnggota($row, $perusahaan);

                $pokok = (float) str_replace(',', '', $row[19] ?? 0);
                if ($pokok > 0) {
                    $this->createPembiayaan($row, $anggota, $perusahaan);
                }

                DB::commit();
                $this->hasil[] = ['baris' => $baris, 'nik' => $nik, 'nama' => $nama, 'status' => 'berhasil', 'pesan' => ''];
            } catch (\Throwable $e) {
                DB::rollBack();
                $this->hasil[] = ['baris' => $baris, 'nik' => $nik, 'nama' => $nama, 'status' => 'gagal', 'pesan' => $e->getMessage()];
            }
        }
    }

    public function getHasil(): array
    {
        return $this->hasil;
    }

    private function resetData(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('potongan_gaji')->delete();
        DB::table('transaksi_pembiayaan')->delete();
        DB::table('jadwal_angsuran')->delete();
        DB::table('pembiayaan')->delete();
        DB::table('pengajuan_pembiayaan')->delete();
        DB::table('jaminan')->delete();
        DB::table('pay_later')->delete();
        DB::table('transaksi_simpanan')->delete();
        DB::table('rekening_simpanan')->delete();
        DB::table('simpanan_berjangka')->delete();
        DB::table('shu_anggota')->delete();
        DB::table('shu_periode')->delete();
        DB::table('jurnal_detail')->delete();
        DB::table('jurnal')->delete();
        Anggota::query()->delete();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    private function ensureDefaultCabang(): void
    {
        if (self::$defaultCabang === null) {
            self::$defaultCabang = Cabang::first();
            if (!self::$defaultCabang) {
                self::$defaultCabang = Cabang::create([
                    'kode' => 'CBG-DFL',
                    'nama' => 'Cabang Default',
                    'alamat' => '-',
                    'aktif' => true,
                ]);
            }
        }
    }

    private function getDefaultProdukId(): string
    {
        $produk = ProdukPembiayaan::where('kode', 'REG')->first();
        if (!$produk) {
            $produk = ProdukPembiayaan::create([
                'kode' => 'REG',
                'nama' => 'Pembiayaan Reguler',
                'skema_bunga' => 'flat',
                'bunga_pa' => 0,
                'aktif' => true,
            ]);
        }
        return $produk->id;
    }

    private function findOrCreatePerusahaan(?string $kode): Perusahaan
    {
        if (!$kode) $kode = 'UMUM';
        $kode = strtoupper(trim($kode));

        if (!isset(self::$perusahaanCache[$kode])) {
            $perusahaan = Perusahaan::where('kode', $kode)->first();
            if (!$perusahaan) {
                $perusahaan = Perusahaan::create([
                    'kode' => $kode,
                    'nama' => 'Perusahaan ' . $kode,
                    'alamat' => '-',
                    'aktif' => true,
                ]);
            }
            self::$perusahaanCache[$kode] = $perusahaan;
        }

        return self::$perusahaanCache[$kode];
    }

    private function findOrCreateAnggota(Collection $row, Perusahaan $perusahaan): Anggota
    {
        $nik = $row[7] ? (string) $row[7] : '-';
        $noAnggota = $row[6] ? (string) $row[6] : 'ANG-' . uniqid();

        $anggota = Anggota::where('nik', $nik)->orWhere('no_anggota', $noAnggota)->first();

        if (!$anggota) {
            $alamatParts = array_filter([
                $row[11] ?? null,
                $row[12] ? 'RT/RW: ' . $row[12] : null,
                $row[13] ? 'Desa: ' . $row[13] : null,
                $row[14] ? 'Kec: ' . $row[14] : null,
                $row[15] ? 'Kota: ' . $row[15] : null,
                $row[16] ? 'Kodepos: ' . $row[16] : null,
            ]);
            $alamat = implode(', ', $alamatParts);
            if (empty($alamat)) $alamat = '-';

            $tanggalLahir = $this->parseDate($row[10] ?? null) ?? '2000-01-01';

            $anggota = Anggota::create([
                'cabang_id' => self::$defaultCabang->id,
                'no_anggota' => $noAnggota,
                'nik' => $nik,
                'nama_lengkap' => $row[4] ?? '-',
                'tempat_lahir' => $row[9] ?? '-',
                'tanggal_lahir' => $tanggalLahir,
                'jenis_kelamin' => 'L',
                'alamat' => $alamat,
                'no_hp' => $row[8] ?? '-',
                'email' => null,
                'perusahaan_id' => $perusahaan->id,
                'gaji_pokok' => 0,
                'tanggal_gajian' => 25,
                'tanggal_mulai_kerja' => null,
                'no_pegawai' => $noAnggota,
                'status' => 'aktif',
                'tanggal_masuk' => now()->format('Y-m-d'),
                'password' => Hash::make('123456'),
            ]);
        }

        return $anggota;
    }

    private function createPembiayaan(Collection $row, Anggota $anggota, Perusahaan $perusahaan): void
    {
        $nominalPokok = (float) str_replace(',', '', $row[19] ?? 0);
        $saldoPokok = (float) str_replace(',', '', $row[25] ?? 0);
        $angsuranTotal = (float) str_replace(',', '', $row[27] ?? 0);
        $bungaValue = (float) str_replace(',', '', $row[20] ?? 0);
        $saldoBunga = (float) str_replace(',', '', $row[26] ?? 0);
        $pembiayaanKe = (int) ($row[24] ?? 1);
        $bayar = (int) ($row[23] ?? 0);

        $noPembiayaan = 'PEM-' . $anggota->no_anggota . '-' . str_pad($pembiayaanKe, 2, '0', STR_PAD_LEFT);

        $existing = Pembiayaan::where('no_pembiayaan', $noPembiayaan)->first();
        if ($existing) return;

        $tanggalCair = $this->parseDate($row[18] ?? null);
        $jatuhTempo = $this->parseDate($row[22] ?? null);

        $jangkaBulan = max(1, $this->hitungJangkaBulan($tanggalCair, $jatuhTempo, $bayar));

        $peruntukan = $row[17] ?? '-';

        $pengajuan = PengajuanPembiayaan::create([
            'anggota_id' => $anggota->id,
            'produk_id' => $this->getDefaultProdukId(),
            'no_pengajuan' => 'PENGAJUAN-' . $noPembiayaan,
            'nominal_diajukan' => $nominalPokok,
            'jangka_bulan' => $jangkaBulan,
            'tujuan' => 'konsumtif',
            'status_approval' => 'disetujui',
            'approved_by' => null,
            'approved_at' => $tanggalCair ? $tanggalCair . ' 00:00:00' : now(),
            'catatan' => 'Import dari master data OST. Peruntukan: ' . $peruntukan,
        ]);

        $sisaAngsuran = max(1, $jangkaBulan - $bayar);

        $pembiayaan = Pembiayaan::create([
            'pengajuan_id' => $pengajuan->id,
            'anggota_id' => $anggota->id,
            'no_pembiayaan' => $noPembiayaan,
            'nominal_disetujui' => $nominalPokok,
            'nominal_cair' => $nominalPokok,
            'jangka_bulan' => $jangkaBulan,
            'bunga_pa' => $nominalPokok > 0 ? round(($bungaValue / $nominalPokok) * 12 * 100, 2) : 0,
            'metode_hitung' => 'flat',
            'angsuran_pokok' => $angsuranTotal > 0 ? round($angsuranTotal - ($saldoBunga / max(1, $sisaAngsuran)), 2) : 0,
            'angsuran_bunga' => $sisaAngsuran > 0 ? round($saldoBunga / $sisaAngsuran, 2) : 0,
            'tanggal_akad' => $tanggalCair ?? now()->format('Y-m-d'),
            'tanggal_cair' => $tanggalCair ?? now()->format('Y-m-d'),
            'tanggal_lunas' => null,
            'status' => $saldoPokok <= 0 ? 'lunas' : 'aktif',
            'saldo_pokok' => $saldoPokok,
            'saldo_bunga' => $saldoBunga,
            'hari_tunggak' => 0,
            'kolektibilitas' => 1,
            'is_chanelling' => false,
            'sumber_dana' => null,
            'auto_potong_gaji' => true,
            'nominal_potongan' => $angsuranTotal > 0 ? $angsuranTotal : ($nominalPokok / $jangkaBulan),
            'bulan_tersisa_potongan' => $sisaAngsuran,
            'sumber_pembayaran' => 'potong_gaji',
        ]);

        $this->createJadwalAngsuran($pembiayaan, $sisaAngsuran, $saldoPokok, $saldoBunga, $jatuhTempo);
    }

    private function createJadwalAngsuran(Pembiayaan $pembiayaan, int $sisaBulan, float $saldoPokok, float $saldoBunga, ?string $jatuhTempo): void
    {
        if ($sisaBulan <= 0) return;

        $angsuranPokok = round($saldoPokok / $sisaBulan, 2);
        $angsuranBunga = round($saldoBunga / $sisaBulan, 2);

        $tanggalMulai = $jatuhTempo
            ? \Carbon\Carbon::parse($jatuhTempo)
            : now()->startOfMonth();

        $sisaPokok = $saldoPokok;

        for ($i = 1; $i <= $sisaBulan; $i++) {
            $jatuhTempoAngsuran = $tanggalMulai->copy()->addMonths($i);

            $pokokAngsuran = $i === $sisaBulan ? round($sisaPokok, 2) : $angsuranPokok;
            $bungaAngsuran = $i === $sisaBulan ? round($saldoBunga - ($angsuranBunga * ($i - 1)), 2) : $angsuranBunga;
            $totalAngsuran = $pokokAngsuran + max(0, $bungaAngsuran);
            $sisaPokok -= $pokokAngsuran;

            JadwalAngsuran::create([
                'pembiayaan_id' => $pembiayaan->id,
                'ke' => $i,
                'tanggal_jatuh_tempo' => $jatuhTempoAngsuran->format('Y-m-d'),
                'pokok' => $pokokAngsuran,
                'bunga' => max(0, $bungaAngsuran),
                'total' => $totalAngsuran,
                'saldo_akhir' => round(max(0, $sisaPokok), 2),
                'status' => 'belum',
                'tanggal_bayar' => null,
            ]);
        }
    }

    private function hitungJangkaBulan(?string $tanggalCair, ?string $jatuhTempo, int $bayar): int
    {
        if ($tanggalCair && $jatuhTempo) {
            $diff = \Carbon\Carbon::parse($tanggalCair)->diffInMonths(\Carbon\Carbon::parse($jatuhTempo));
            return max(1, abs($diff) + $bayar);
        }
        return max(1, $bayar + 12);
    }

    private function parseDate($value): ?string
    {
        if ($value === null || $value === '') return null;
        if (is_numeric($value)) {
            try {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((int) $value)->format('Y-m-d');
            } catch (\Exception $e) {
                return null;
            }
        }
        $value = trim($value);
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) return $value;
        try {
            $date = date('Y-m-d', strtotime($value));
            return $date !== '1970-01-01' && $date !== '-0001-11-30' ? $date : null;
        } catch (\Exception $e) {
            return null;
        }
    }
}
