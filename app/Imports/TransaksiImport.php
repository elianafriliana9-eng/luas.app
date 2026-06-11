<?php

namespace App\Imports;

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
        $rekening = RekeningSimpanan::where('no_rekening', trim($row['no_rekening']))->first();

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

        TransaksiSimpanan::create([
            'rekening_id' => $rekening->id,
            'user_id' => auth()->id(),
            'no_transaksi' => 'XLS-' . now()->format('ymd') . '-' . strtoupper(Str::random(8)),
            'jenis' => $jenis,
            'nominal' => $nominal,
            'saldo_sebelum' => $saldoSebelum,
            'saldo_sesudah' => $saldoSesudah,
            'keterangan' => ($row['keterangan'] ?? 'Import Excel') . ' (Import)',
            'channel' => 'teller',
            'status_approval' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return null;
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
