<?php

namespace App\Traits;

use App\Models\ChartOfAccount;
use App\Models\Jurnal;
use App\Models\JurnalDetail;
use App\Models\Kas;
use App\Models\RekeningSimpanan;
use App\Models\TransaksiSimpanan;

trait SimpananJurnal
{
    private function getAkunSimpanan(string $produkKode): ?ChartOfAccount
    {
        $map = [
            'SIMPOK'  => '21010',
            'SIMWA'   => '21020',
            'SIMSUKA' => '21030',
        ];

        $kodeAkun = $map[$produkKode] ?? null;
        if (!$kodeAkun) return null;

        return ChartOfAccount::where('kode_akun', $kodeAkun)->first();
    }

    private function getAkunKas(): ?ChartOfAccount
    {
        return ChartOfAccount::where('kode_akun', '11010')->first();
    }

    protected function buatJurnalSetoran(TransaksiSimpanan $transaksi): void
    {
        $rekening = RekeningSimpanan::with('produk', 'anggota.cabang')->find($transaksi->rekening_id);
        if (!$rekening || !$rekening->produk || !$rekening->anggota->cabang) return;

        $akunSimpanan = $this->getAkunSimpanan($rekening->produk->kode);
        $akunKas = $this->getAkunKas();
        if (!$akunSimpanan || !$akunKas) return;

        $nominal = (float) $transaksi->nominal;

        $jurnal = Jurnal::create([
            'cabang_id'   => $rekening->anggota->cabang_id,
            'no_jurnal'   => 'JU-SIMP-' . $transaksi->created_at->format('Ymd') . '-' . str_pad(substr($transaksi->id, 0, 8), 5, '0', STR_PAD_LEFT),
            'tanggal'     => $transaksi->created_at->format('Y-m-d'),
            'keterangan'  => 'Setoran ' . $rekening->produk->nama . ' a.n. ' . $rekening->anggota->nama_lengkap . ' (' . $rekening->no_rekening . ')',
            'jenis'       => 'otomatis',
            'ref_id'      => $transaksi->id,
            'ref_tabel'   => 'transaksi_simpanan',
            'dibuat_oleh' => $transaksi->user_id,
        ]);

        JurnalDetail::create([
            'jurnal_id'  => $jurnal->id,
            'akun_id'    => $akunKas->id,
            'debet'      => $nominal,
            'kredit'     => 0,
            'keterangan' => 'Penerimaan setoran ' . $rekening->produk->nama,
        ]);

        JurnalDetail::create([
            'jurnal_id'  => $jurnal->id,
            'akun_id'    => $akunSimpanan->id,
            'debet'      => 0,
            'kredit'     => $nominal,
            'keterangan' => 'Penambahan ' . $rekening->produk->nama . ' ' . $rekening->anggota->nama_lengkap,
        ]);

        $this->updateKasSaldo($akunKas->id, $nominal, 0);
    }

    protected function buatJurnalPenarikan(TransaksiSimpanan $transaksi): void
    {
        $rekening = RekeningSimpanan::with('produk', 'anggota.cabang')->find($transaksi->rekening_id);
        if (!$rekening || !$rekening->produk || !$rekening->anggota->cabang) return;

        $akunSimpanan = $this->getAkunSimpanan($rekening->produk->kode);
        $akunKas = $this->getAkunKas();
        if (!$akunSimpanan || !$akunKas) return;

        $nominal = (float) $transaksi->nominal;

        $jurnal = Jurnal::create([
            'cabang_id'   => $rekening->anggota->cabang_id,
            'no_jurnal'   => 'JU-SIMP-' . $transaksi->created_at->format('Ymd') . '-' . str_pad(substr($transaksi->id, 0, 8), 5, '0', STR_PAD_LEFT),
            'tanggal'     => $transaksi->created_at->format('Y-m-d'),
            'keterangan'  => 'Penarikan ' . $rekening->produk->nama . ' a.n. ' . $rekening->anggota->nama_lengkap . ' (' . $rekening->no_rekening . ')',
            'jenis'       => 'otomatis',
            'ref_id'      => $transaksi->id,
            'ref_tabel'   => 'transaksi_simpanan',
            'dibuat_oleh' => $transaksi->user_id,
        ]);

        JurnalDetail::create([
            'jurnal_id'  => $jurnal->id,
            'akun_id'    => $akunSimpanan->id,
            'debet'      => $nominal,
            'kredit'     => 0,
            'keterangan' => 'Pengurangan ' . $rekening->produk->nama . ' ' . $rekening->anggota->nama_lengkap,
        ]);

        JurnalDetail::create([
            'jurnal_id'  => $jurnal->id,
            'akun_id'    => $akunKas->id,
            'debet'      => 0,
            'kredit'     => $nominal,
            'keterangan' => 'Pembayaran penarikan ' . $rekening->produk->nama,
        ]);

        $this->updateKasSaldo($akunKas->id, 0, $nominal);
    }

    protected function buatJurnalPinbuk(string $rekSumberId, string $rekTujuanId, float $nominal, string $userId, string $noTransaksi): void
    {
        $sumber = RekeningSimpanan::with('produk', 'anggota.cabang')->find($rekSumberId);
        $tujuan = RekeningSimpanan::with('produk')->find($rekTujuanId);
        if (!$sumber || !$tujuan || !$sumber->produk || !$tujuan->produk || !$sumber->anggota->cabang) return;

        $akunSumber = $this->getAkunSimpanan($sumber->produk->kode);
        $akunTujuan = $this->getAkunSimpanan($tujuan->produk->kode);
        if (!$akunSumber || !$akunTujuan) return;

        $jurnal = Jurnal::create([
            'cabang_id'   => $sumber->anggota->cabang_id,
            'no_jurnal'   => 'JU-PMB-' . now()->format('Ymd') . '-' . str_pad(substr(md5($noTransaksi), 0, 5), 5, '0', STR_PAD_LEFT),
            'tanggal'     => now()->format('Y-m-d'),
            'keterangan'  => 'Pinbuk dari ' . $sumber->produk->nama . ' (' . $sumber->no_rekening . ') ke ' . $tujuan->produk->nama . ' (' . $tujuan->no_rekening . ')',
            'jenis'       => 'otomatis',
            'ref_id'      => $noTransaksi,
            'ref_tabel'   => 'pinbuk',
            'dibuat_oleh' => $userId,
        ]);

        JurnalDetail::create([
            'jurnal_id'  => $jurnal->id,
            'akun_id'    => $akunSumber->id,
            'debet'      => $nominal,
            'kredit'     => 0,
            'keterangan' => 'Pinbuk keluar dari ' . $sumber->produk->nama . ' ' . $sumber->anggota->nama_lengkap,
        ]);

        JurnalDetail::create([
            'jurnal_id'  => $jurnal->id,
            'akun_id'    => $akunTujuan->id,
            'debet'      => 0,
            'kredit'     => $nominal,
            'keterangan' => 'Pinbuk masuk ke ' . $tujuan->produk->nama,
        ]);
    }

    private function updateKasSaldo(string $akunId, float $debet, float $kredit): void
    {
        $kas = Kas::where('akun_id', $akunId)->first();
        if ($kas) {
            $kas->saldo += $debet - $kredit;
            $kas->save();
        }
    }
}
