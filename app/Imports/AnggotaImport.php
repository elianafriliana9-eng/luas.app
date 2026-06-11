<?php

namespace App\Imports;

use App\Models\Anggota;
use App\Models\Cabang;
use App\Models\ProdukSimpanan;
use App\Models\RekeningSimpanan;
use App\Models\PotonganGaji;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\RemembersRowNumber;
use Maatwebsite\Excel\Validators\Failure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AnggotaImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure
{
    use \Maatwebsite\Excel\Concerns\Importable, RemembersRowNumber;

    protected array $hasil = [];

    public function model(array $row)
    {
        $baris = $this->getRowNumber();
        $nik = $row['nik'] ?? '-';
        $nama = $row['nama_lengkap'] ?? '-';
        $cabangKode = $row['kode_cabang'] ?? '';

        $cabang = Cabang::where('kode', $cabangKode)->orWhere('nama', $cabangKode)->first();
        if (!$cabang) {
            $this->hasil[] = ['baris' => $baris, 'nik' => $nik, 'nama' => $nama, 'status' => 'gagal', 'pesan' => "Cabang '{$cabangKode}' tidak ditemukan."];
            return null;
        }

        $exists = Anggota::where('nik', $nik)->exists();
        if ($exists) {
            $this->hasil[] = ['baris' => $baris, 'nik' => $nik, 'nama' => $nama, 'status' => 'gagal', 'pesan' => 'NIK sudah terdaftar.'];
            return null;
        }

        $noAnggota = $row['no_anggota'] ?? 'ANG-' . now()->year . '-' . str_pad(Anggota::max('no_anggota') ? (int) substr(Anggota::max('no_anggota'), -4) + 1 : 1, 4, '0', STR_PAD_LEFT);

        $anggota = null;

        DB::beginTransaction();
        try {
            $anggota = Anggota::create([
                'cabang_id' => $cabang->id,
                'no_anggota' => $noAnggota,
                'nik' => $nik,
                'nama_lengkap' => $nama,
                'tempat_lahir' => $row['tempat_lahir'] ?? '-',
                'tanggal_lahir' => $this->parseDate($row['tanggal_lahir'] ?? now()),
                'jenis_kelamin' => strtoupper($row['jenis_kelamin'] ?? 'L'),
                'alamat' => $row['alamat'] ?? '-',
                'no_hp' => $row['no_hp'] ?? '-',
                'email' => $row['email'] ?? null,
                'gaji_pokok' => (float) ($row['gaji_pokok'] ?? 0),
                'tanggal_gajian' => (int) ($row['tanggal_gajian'] ?? 25),
                'perusahaan_id' => null,
                'tanggal_mulai_kerja' => $this->parseDate($row['tanggal_mulai_kerja'] ?? null),
                'no_pegawai' => $row['no_pegawai'] ?? null,
                'status' => 'aktif',
                'tanggal_masuk' => now(),
                'password' => Hash::make('123456'),
            ]);

            $produks = ProdukSimpanan::where('aktif', true)->get();
            foreach ($produks as $produk) {
                RekeningSimpanan::create([
                    'anggota_id' => $anggota->id,
                    'produk_id' => $produk->id,
                    'no_rekening' => 'REK-' . strtoupper(substr($produk->kode, 0, 3)) . '-' . $anggota->no_anggota,
                    'saldo' => 0,
                    'status' => 'aktif',
                    'tanggal_buka' => now(),
                ]);
            }

            $bulanMulai = now()->startOfMonth();
            for ($i = 1; $i <= 3; $i++) {
                PotonganGaji::create([
                    'anggota_id' => $anggota->id,
                    'jenis_potongan' => 'simpanan',
                    'periode' => $bulanMulai->copy()->addMonths($i - 1)->format('Y-m-d'),
                    'nominal_potongan' => 50000,
                    'gaji_bruto' => $anggota->gaji_pokok ?? 0,
                    'gaji_diterima' => max(0, ($anggota->gaji_pokok ?? 0) - 50000),
                    'status' => 'pending',
                    'keterangan' => 'Cicilan Simpanan Pokok (' . $i . '/3)',
                ]);
            }

            DB::commit();
            $this->hasil[] = ['baris' => $baris, 'nik' => $nik, 'nama' => $nama, 'status' => 'berhasil', 'pesan' => ''];
        } catch (\Throwable $e) {
            DB::rollBack();
            $msg = $e->getMessage();
            $pesan = match (true) {
                str_contains($msg, 'Duplicate') && str_contains($msg, '23000') => 'NIK sudah terdaftar.',
                str_contains($msg, '23000') => 'Data tidak valid (constraint violation).',
                default => $msg,
            };
            $this->hasil[] = ['baris' => $baris, 'nik' => $nik, 'nama' => $nama, 'status' => 'gagal', 'pesan' => $pesan];
        }

        return null;
    }

    public function rules(): array
    {
        return [
            'no_anggota' => 'nullable|string|max:20',
            'nik' => 'required|string|max:20',
            'nama_lengkap' => 'required|string|max:255',
            'kode_cabang' => 'required|string',
            'jenis_kelamin' => 'nullable|in:L,P',
            'gaji_pokok' => 'nullable|numeric|min:0',
            'tanggal_gajian' => 'nullable|integer|min:1|max:31',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'nik.required' => 'Kolom NIK wajib diisi.',
            'nama_lengkap.required' => 'Kolom nama_lengkap wajib diisi.',
            'kode_cabang.required' => 'Kolom kode_cabang wajib diisi.',
        ];
    }

    public function onFailure(Failure ...$failures)
    {
        foreach ($failures as $failure) {
            $vals = $failure->values();
            $this->hasil[] = [
                'baris' => $failure->row(),
                'nik' => $vals['nik'] ?? $vals['NIK'] ?? '-',
                'nama' => $vals['nama_lengkap'] ?? $vals['Nama Lengkap'] ?? '-',
                'status' => 'gagal',
                'pesan' => implode(', ', $failure->errors()),
            ];
        }
    }

    public function getHasil(): array
    {
        $berhasil = count(array_filter($this->hasil, fn($h) => $h['status'] === 'berhasil'));
        $gagal = count(array_filter($this->hasil, fn($h) => $h['status'] === 'gagal'));

        usort($this->hasil, fn($a, $b) => $a['baris'] <=> $b['baris']);

        return [
            'berhasil' => $berhasil,
            'gagal' => $gagal,
            'hasil' => $this->hasil,
        ];
    }

    private function parseDate($value): ?string
    {
        if (!$value) return null;
        try {
            if (is_numeric($value)) {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)->format('Y-m-d');
            }
            return date('Y-m-d', strtotime($value));
        } catch (\Exception $e) {
            return now()->format('Y-m-d');
        }
    }
}
