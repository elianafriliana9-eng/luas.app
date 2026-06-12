<?php

namespace App\Traits;

use App\Models\ChartOfAccount;
use App\Models\Jurnal;
use App\Models\JurnalDetail;
use App\Models\Kas;
use App\Models\KonfigurasiCoa;
use App\Models\PeriodeTutup;
use App\Models\RekeningSimpanan;
use App\Models\TransaksiSimpanan;
use Illuminate\Validation\ValidationException;

trait SimpananJurnal
{
    private function getKodeAkun(string $key): ?string
    {
        $config = KonfigurasiCoa::where('key', $key)->first();
        if ($config) return $config->kode_akun;

        // Fallback hardcoded
        return match ($key) {
            'simpanan_pokok'    => '21010',
            'simpanan_wajib'    => '21020',
            'simpanan_sukarela' => '21030',
            'kas'               => '11010',
            default             => null,
        };
    }

    private function getAkunSimpanan(string $produkKode): ?ChartOfAccount
    {
        $map = [
            'SIMPOK'  => 'simpanan_pokok',
            'SIMWA'   => 'simpanan_wajib',
            'SIMSUKA' => 'simpanan_sukarela',
        ];

        $key = $map[$produkKode] ?? null;
        if (!$key) return null;

        $kodeAkun = $this->getKodeAkun($key);
        if (!$kodeAkun) return null;

        return ChartOfAccount::where('kode_akun', $kodeAkun)->first();
    }

    private function getAkunKas(): ?ChartOfAccount
    {
        $kodeAkun = $this->getKodeAkun('kas');
        return $kodeAkun ? ChartOfAccount::where('kode_akun', $kodeAkun)->first() : null;
    }

    protected function buatJurnalSetoran(TransaksiSimpanan $transaksi): void
    {
        $rekening = RekeningSimpanan::with('produk', 'anggota.cabang')->find($transaksi->rekening_id);
        if (!$rekening || !$rekening->produk || !$rekening->anggota->cabang) return;

        $tglTransaksi = $transaksi->created_at ? $transaksi->created_at->format('Y-m-d') : now()->format('Y-m-d');
        $this->checkPeriodeTutup($rekening->anggota->cabang_id, $tglTransaksi);

        $akunSimpanan = $this->getAkunSimpanan($rekening->produk->kode);
        $akunKas = $this->getAkunKas();
        if (!$akunSimpanan || !$akunKas) return;

        $nominal = (float) $transaksi->nominal;

        $jurnal = Jurnal::create([
            'cabang_id'   => $rekening->anggota->cabang_id,
            'no_jurnal'   => 'JU-SIMP-' . now()->format('Ymd') . '-' . str_pad(substr($transaksi->id, 0, 8), 5, '0', STR_PAD_LEFT),
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

        $tglTransaksi = $transaksi->created_at ? $transaksi->created_at->format('Y-m-d') : now()->format('Y-m-d');
        $this->checkPeriodeTutup($rekening->anggota->cabang_id, $tglTransaksi);

        $akunSimpanan = $this->getAkunSimpanan($rekening->produk->kode);
        $akunKas = $this->getAkunKas();
        if (!$akunSimpanan || !$akunKas) return;

        $nominal = (float) $transaksi->nominal;

        $jurnal = Jurnal::create([
            'cabang_id'   => $rekening->anggota->cabang_id,
            'no_jurnal'   => 'JU-SIMP-' . now()->format('Ymd') . '-' . str_pad(substr($transaksi->id, 0, 8), 5, '0', STR_PAD_LEFT),
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

        $this->checkPeriodeTutup($sumber->anggota->cabang_id, now()->format('Y-m-d'));

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

    protected function buatJurnalReversal(TransaksiSimpanan $transaksi): void
    {
        $rekening = RekeningSimpanan::with('produk', 'anggota.cabang')->find($transaksi->rekening_id);
        if (!$rekening || !$rekening->produk || !$rekening->anggota->cabang) return;

        $this->checkPeriodeTutup($rekening->anggota->cabang_id, now()->format('Y-m-d'));

        $akunSimpanan = $this->getAkunSimpanan($rekening->produk->kode);
        $akunKas = $this->getAkunKas();
        if (!$akunSimpanan || !$akunKas) return;

        $nominal = (float) $transaksi->nominal;
        $isDebetKeRekening = in_array($transaksi->jenis_pembatalan, ['setoran', 'pinbuk_masuk']);

        $jurnal = Jurnal::create([
            'cabang_id'   => $rekening->anggota->cabang_id,
            'no_jurnal'   => 'JU-RVL-' . now()->format('Ymd') . '-' . str_pad(substr($transaksi->id, 0, 8), 5, '0', STR_PAD_LEFT),
            'tanggal'     => now()->format('Y-m-d'),
            'keterangan'  => 'Pembatalan ' . $transaksi->label_jenis . ' #' . $transaksi->no_transaksi . ' (' . $rekening->anggota->nama_lengkap . ')',
            'jenis'       => 'reversal',
            'ref_id'      => $transaksi->id,
            'ref_tabel'   => 'transaksi_simpanan',
            'dibuat_oleh' => auth()->id(),
            'is_reversed' => true,
        ]);

        // Reversal: kebalikan dari jurnal asli
        if ($isDebetKeRekening) {
            // Awalnya: Debet Kas, Kredit Simpanan → Reversal: Debet Simpanan, Kredit Kas
            JurnalDetail::create([
                'jurnal_id'  => $jurnal->id,
                'akun_id'    => $akunSimpanan->id,
                'debet'      => $nominal,
                'kredit'     => 0,
                'keterangan' => 'Reversal: ' . $transaksi->label_jenis . ' ' . $rekening->anggota->nama_lengkap,
            ]);
            JurnalDetail::create([
                'jurnal_id'  => $jurnal->id,
                'akun_id'    => $akunKas->id,
                'debet'      => 0,
                'kredit'     => $nominal,
                'keterangan' => 'Reversal: ' . $transaksi->label_jenis,
            ]);
            $this->updateKasSaldo($akunKas->id, 0, $nominal);
        } else {
            // Awalnya: Debet Simpanan, Kredit Kas → Reversal: Debet Kas, Kredit Simpanan
            JurnalDetail::create([
                'jurnal_id'  => $jurnal->id,
                'akun_id'    => $akunKas->id,
                'debet'      => $nominal,
                'kredit'     => 0,
                'keterangan' => 'Reversal: ' . $transaksi->label_jenis . ' ' . $rekening->anggota->nama_lengkap,
            ]);
            JurnalDetail::create([
                'jurnal_id'  => $jurnal->id,
                'akun_id'    => $akunSimpanan->id,
                'debet'      => 0,
                'kredit'     => $nominal,
                'keterangan' => 'Reversal: ' . $transaksi->label_jenis,
            ]);
            $this->updateKasSaldo($akunKas->id, $nominal, 0);
        }
    }

    private function checkPeriodeTutup(string $cabangId, string $tanggal): void
    {
        $tahun = date('Y', strtotime($tanggal));
        $bulan = date('m', strtotime($tanggal));

        $periode = PeriodeTutup::where('cabang_id', $cabangId)
            ->where('tahun', $tahun)
            ->where('bulan', $bulan)
            ->where('is_closed', true)
            ->first();

        if ($periode) {
            throw ValidationException::withMessages([
                'periode' => 'Periode ' . $bulan . '/' . $tahun . ' sudah ditutup. Tidak dapat membuat jurnal baru.',
            ]);
        }
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
