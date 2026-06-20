<?php

namespace App\Traits;

use App\Models\ChartOfAccount;
use App\Models\Jurnal;
use App\Models\JurnalDetail;
use App\Models\Kas;
use App\Models\KonfigurasiCoa;
use App\Models\PeriodeTutup;
use App\Models\TransaksiPembiayaan;

trait PembiayaanJurnal
{
    private function getKodeAkunPembiayaan(string $key): ?string
    {
        $config = KonfigurasiCoa::where('key', $key)->first();
        if ($config) return $config->kode_akun;

        // Fallback hardcoded
        return match ($key) {
            'kas'               => '11010',
            'piutang_anggota'   => '11030',
            'pendapatan_margin' => '41010',
            'pendapatan_denda'  => '41020',
            default             => null,
        };
    }

    private function getAkun(string $key): ?ChartOfAccount
    {
        $kodeAkun = $this->getKodeAkunPembiayaan($key);
        return $kodeAkun ? ChartOfAccount::where('kode_akun', $kodeAkun)->first() : null;
    }

    protected function checkPeriodeTutupPembiayaan(string $cabangId, string $tanggal): void
    {
        $periode = substr($tanggal, 0, 7); // YYYY-MM
        $tutup = PeriodeTutup::where('cabang_id', $cabangId)
            ->where('periode', $periode)
            ->exists();

        if ($tutup) {
            throw new \Exception("Periode akuntansi {$periode} sudah ditutup. Transaksi tidak dapat diproses.");
        }
    }

    protected function updateKasSaldoPembiayaan(string $akunId, float $debet, float $kredit): void
    {
        $kas = Kas::where('coa_id', $akunId)->first();
        if ($kas) {
            $kas->saldo += ($debet - $kredit);
            $kas->save();
        }
    }

    protected function buatJurnalPencairan(TransaksiPembiayaan $transaksi): void
    {
        $pembiayaan = $transaksi->pembiayaan()->with('anggota.cabang', 'pengajuan.produk')->first();
        if (!$pembiayaan || !$pembiayaan->anggota->cabang) return;

        $tglTransaksi = $transaksi->created_at ? $transaksi->created_at->format('Y-m-d') : now()->format('Y-m-d');
        $this->checkPeriodeTutupPembiayaan($pembiayaan->anggota->cabang_id, $tglTransaksi);

        $akunKas = $this->getAkun('kas');
        $akunPiutang = $this->getAkun('piutang_anggota');
        if (!$akunKas || !$akunPiutang) return;

        $nominal = (float) $transaksi->total;

        $jurnal = Jurnal::create([
            'cabang_id'   => $pembiayaan->anggota->cabang_id,
            'no_jurnal'   => 'JU-PMB-' . now()->format('Ymd') . '-' . str_pad(substr($transaksi->id, 0, 8), 5, '0', STR_PAD_LEFT),
            'tanggal'     => $tglTransaksi,
            'keterangan'  => 'Pencairan Pembiayaan a.n. ' . $pembiayaan->anggota->nama_lengkap . ' (' . $pembiayaan->no_pembiayaan . ')',
            'jenis'       => 'otomatis',
            'ref_id'      => $transaksi->id,
            'ref_tabel'   => 'transaksi_pembiayaan',
            'dibuat_oleh' => auth()->id() ?? User::where('role', 'super_admin')->first()->id,
        ]);

        // Pencairan: Debet Piutang Anggota, Kredit Kas
        JurnalDetail::create([
            'jurnal_id'  => $jurnal->id,
            'akun_id'    => $akunPiutang->id,
            'debet'      => $nominal,
            'kredit'     => 0,
            'keterangan' => 'Penambahan Piutang Anggota ' . $pembiayaan->anggota->nama_lengkap,
        ]);

        JurnalDetail::create([
            'jurnal_id'  => $jurnal->id,
            'akun_id'    => $akunKas->id,
            'debet'      => 0,
            'kredit'     => $nominal,
            'keterangan' => 'Pencairan pembiayaan ' . $pembiayaan->no_pembiayaan,
        ]);

        $this->updateKasSaldoPembiayaan($akunKas->id, 0, $nominal);
    }

    protected function buatJurnalPelunasanAtauAngsuran(TransaksiPembiayaan $transaksi): void
    {
        $pembiayaan = $transaksi->pembiayaan()->with('anggota.cabang')->first();
        if (!$pembiayaan || !$pembiayaan->anggota->cabang) return;

        $tglTransaksi = $transaksi->created_at ? $transaksi->created_at->format('Y-m-d') : now()->format('Y-m-d');
        $this->checkPeriodeTutupPembiayaan($pembiayaan->anggota->cabang_id, $tglTransaksi);

        $akunKas = $this->getAkun('kas');
        $akunPiutang = $this->getAkun('piutang_anggota');
        $akunMargin = $this->getAkun('pendapatan_margin');
        $akunDenda = $this->getAkun('pendapatan_denda');
        
        if (!$akunKas || !$akunPiutang || !$akunMargin) return;

        $nominalPokok = (float) $transaksi->nominal_pokok;
        $nominalBunga = (float) $transaksi->nominal_bunga;
        $nominalDenda = (float) $transaksi->nominal_denda;
        $totalBayar   = (float) $transaksi->total;

        $jurnal = Jurnal::create([
            'cabang_id'   => $pembiayaan->anggota->cabang_id,
            'no_jurnal'   => 'JU-PMB-' . now()->format('Ymd') . '-' . str_pad(substr($transaksi->id, 0, 8), 5, '0', STR_PAD_LEFT),
            'tanggal'     => $tglTransaksi,
            'keterangan'  => 'Pembayaran Angsuran/Pelunasan a.n. ' . $pembiayaan->anggota->nama_lengkap . ' (' . $pembiayaan->no_pembiayaan . ')',
            'jenis'       => 'otomatis',
            'ref_id'      => $transaksi->id,
            'ref_tabel'   => 'transaksi_pembiayaan',
            'dibuat_oleh' => auth()->id() ?? User::where('role', 'super_admin')->first()->id,
        ]);

        // Debet Kas
        JurnalDetail::create([
            'jurnal_id'  => $jurnal->id,
            'akun_id'    => $akunKas->id,
            'debet'      => $totalBayar,
            'kredit'     => 0,
            'keterangan' => 'Penerimaan pembayaran pembiayaan ' . $pembiayaan->no_pembiayaan,
        ]);

        // Kredit Piutang Anggota
        if ($nominalPokok > 0) {
            JurnalDetail::create([
                'jurnal_id'  => $jurnal->id,
                'akun_id'    => $akunPiutang->id,
                'debet'      => 0,
                'kredit'     => $nominalPokok,
                'keterangan' => 'Pengurangan Piutang Anggota ' . $pembiayaan->anggota->nama_lengkap,
            ]);
        }

        // Kredit Pendapatan Margin
        if ($nominalBunga > 0) {
            JurnalDetail::create([
                'jurnal_id'  => $jurnal->id,
                'akun_id'    => $akunMargin->id,
                'debet'      => 0,
                'kredit'     => $nominalBunga,
                'keterangan' => 'Pendapatan Margin ' . $pembiayaan->no_pembiayaan,
            ]);
        }

        // Kredit Pendapatan Denda
        if ($nominalDenda > 0 && $akunDenda) {
            JurnalDetail::create([
                'jurnal_id'  => $jurnal->id,
                'akun_id'    => $akunDenda->id,
                'debet'      => 0,
                'kredit'     => $nominalDenda,
                'keterangan' => 'Pendapatan Denda Keterlambatan ' . $pembiayaan->no_pembiayaan,
            ]);
        }

        $this->updateKasSaldoPembiayaan($akunKas->id, $totalBayar, 0);
    }
}
