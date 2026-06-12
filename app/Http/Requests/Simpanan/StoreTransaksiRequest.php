<?php

namespace App\Http\Requests\Simpanan;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransaksiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'anggota_id' => 'required|uuid|exists:anggota,id',
            'rekening_id' => 'required|uuid|exists:rekening_simpanan,id',
            'jenis' => 'required|in:setoran,penarikan',
            'nominal' => 'required|numeric|min:1000',
            'keterangan' => 'nullable|string|max:500',
        ];
    }
}
