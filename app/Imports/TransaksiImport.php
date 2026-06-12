<?php

namespace App\Imports;

use App\Models\ChartOfAccount;
use App\Models\Jurnal;
use App\Models\JurnalDetail;
use App\Models\Kas;
use App\Models\KonfigurasiCoa;
use App\Models\RekeningSimpanan;
use App\Models\TransaksiSimpanan;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Validators\Failure;
use Illuminate\Support\Str;

class TransaksiImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure
{
    use Importable;

    protected int $berhasil = 0;
    protected int $gagal = 0;
    protected array $errors = [];

    public function model(array $row)
    {
        $rekening = RekeningSimpanan::with('produk', 'anggota.cabang')->where('no_rekening', trim($row['no_rekening']))->first();

        if (!$rekening || $rekening->status !== 'aktif') {
            $this->gagal++;
            $this->errors[] = "Rekening {$row['no_rekening']} tidak ditemukan atau tidak aktif.";
            return null;
        }

        $jenis = strtolower(trim($row['jenis']));
        $nominal = (float) $row['nominal'];
        $isPenambahan = in_array($jenis, ['setoran', 'pinbuk_masuk']);

        $saldoSebelum = $rekening->saldo;
        $saldoSesudah = $isPenambahan ? $saldoSebelum + $nominal : $saldoSebelum - $nominal;

        if ($saldoSesudah < 0) {
            $this->gagal++;
            $this->errors[] = "Saldo tidak cukup untuk {$row['no_rekening']} ({$jenis} Rp " . number_format($nominal, 0, ',', '.') . ')';
            return null;
        }

        $rekening->saldo = $saldoSesudah;
        $rekening->save();

        $this->berhasil++;

        $transaksi = TransaksiSimpanan::create([
            'rekening_id' => $rekening->id,
            'user_id' => auth()->id(),
            'no_transaksi' => 'XLS-' . now()->format('ymd') . '-' . strtoupper(Str::random(8)),
            'jenis' => $jenis,
            'nominal' => $nominal,
            'saldo_sebelum' => $saldoSebelum,
            'saldo_sesudah' => $saldoSesudah,
            'keterangan' => ($row['keterangan'] ?? 'Import Excel') . ' (Import)',
            'channel' => 'admin',
            'status_approval' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        $this->buatJurnalImport($transaksi, $rekening, $jenis);

        return null;
    }

    private function buatJurnalImport(TransaksiSimpanan $transaksi, RekeningSimpanan $rekening, string $jenis): void
    {
        $produk = $rekening->produk;
        $cabang = $rekening->anggota->cabang;
        if (!$produk || !$cabang) return;

        $nominal = (float) $transaksi->nominal;
        $akunKas = $this->getAkunKas();
        $akunSimpanan = $this->getAkunSimpanan($produk->kode);
        if (!$akunKas || !$akunSimpanan) return;

        $isPenambahan = in_array($jenis, ['setoran', 'pinbuk_masuk']);

        $jurnal = Jurnal::create([
            'cabang_id'   => $cabang->id,
            'no_jurnal'   => 'JU-IMP-' . $transaksi->created_at->format('Ymd') . '-' . str_pad(substr($transaksi->id, 0, 8), 5, '0', STR_PAD_LEFT),
            'tanggal'     => $transaksi->created_at->format('Y-m-d'),
            'keterangan'  => 'Import: ' . $jenis . ' ' . $produk->nama . ' a.n. ' . $rekening->anggota->nama_lengkap . ' (' . $rekening->no_rekening . ')',
            'jenis'       => 'otomatis',
            'ref_id'      => $transaksi->id,
            'ref_tabel'   => 'transaksi_simpanan',
            'dibuat_oleh' => auth()->id(),
        ]);

        if ($isPenambahan) {
            JurnalDetail::create(['jurnal_id' => $jurnal->id, 'akun_id' => $akunKas->id, 'debet' => $nominal, 'kredit' => 0, 'keterangan' => 'Penerimaan ' . $jenis]);
            JurnalDetail::create(['jurnal_id' => $jurnal->id, 'akun_id' => $akunSimpanan->id, 'debet' => 0, 'kredit' => $nominal, 'keterangan' => 'Penambahan ' . $produk->nama]);
            $this->updateKasSaldo($akunKas->id, $nominal, 0);
        } else {
            JurnalDetail::create(['jurnal_id' => $jurnal->id, 'akun_id' => $akunSimpanan->id, 'debet' => $nominal, 'kredit' => 0, 'keterangan' => 'Pengurangan ' . $produk->nama]);
            JurnalDetail::create(['jurnal_id' => $jurnal->id, 'akun_id' => $akunKas->id, 'debet' => 0, 'kredit' => $nominal, 'keterangan' => 'Pembayaran ' . $jenis]);
            $this->updateKasSaldo($akunKas->id, 0, $nominal);
        }
    }

    private function getKodeAkun(string $key): ?string
    {
        $config = KonfigurasiCoa::where('key', $key)->first();
        if ($config) return $config->kode_akun;
        return match ($key) {
            'simpanan_pokok' => '21010', 'simpanan_wajib' => '21020',
            'simpanan_sukarela' => '21030', 'kas' => '11010',
            default => null,
        };
    }

    private function getAkunSimpanan(string $produkKode): ?ChartOfAccount
    {
        $map = ['SIMPOK' => 'simpanan_pokok', 'SIMWA' => 'simpanan_wajib', 'SIMSUKA' => 'simpanan_sukarela'];
        $key = $map[$produkKode] ?? null;
        if (!$key) return null;
        $kodeAkun = $this->getKodeAkun($key);
        return $kodeAkun ? ChartOfAccount::where('kode_akun', $kodeAkun)->first() : null;
    }

    private function getAkunKas(): ?ChartOfAccount
    {
        $kodeAkun = $this->getKodeAkun('kas');
        return $kodeAkun ? ChartOfAccount::where('kode_akun', $kodeAkun)->first() : null;
    }

    private function updateKasSaldo(string $akunId, float $debet, float $kredit): void
    {
        $kas = Kas::where('akun_id', $akunId)->first();
        if ($kas) {
            $kas->saldo += $debet - $kredit;
            $kas->save();
        }
    }

    public function rules(): array
    {
        return [
            'no_rekening' => 'required|string',
            'jenis' => 'required|in:setoran,penarikan,pinbuk_masuk,pinbuk_keluar',
            'nominal' => 'required|numeric|min:1000',
            'keterangan' => 'nullable|string|max:500',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'no_rekening.required' => 'Kolom no_rekening wajib diisi.',
            'jenis.required' => 'Kolom jenis wajib diisi (setoran/penarikan/pinbuk_masuk/pinbuk_keluar).',
            'jenis.in' => 'Jenis transaksi tidak valid.',
            'nominal.required' => 'Kolom nominal wajib diisi.',
            'nominal.min' => 'Nominal minimal Rp 1.000.',
        ];
    }

    public function onFailure(Failure ...$failures)
    {
        foreach ($failures as $failure) {
            $this->gagal++;
            $this->errors[] = "Baris {$failure->row()}: " . implode(', ', $failure->errors());
        }
    }

    public function getHasil(): array
    {
        return [
            'berhasil' => $this->berhasil,
            'gagal' => $this->gagal,
            'errors' => $this->errors,
        ];
    }
}
