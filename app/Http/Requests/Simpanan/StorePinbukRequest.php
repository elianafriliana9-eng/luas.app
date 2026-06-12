<?php

namespace App\Http\Requests\Simpanan;

use Illuminate\Foundation\Http\FormRequest;

class StorePinbukRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rekening_sumber_id' => 'required|uuid|exists:rekening_simpanan,id',
            'rekening_tujuan_id' => 'required|uuid|exists:rekening_simpanan,id|different:rekening_sumber_id',
            'nominal' => 'required|numeric|min:1000',
            'keterangan' => 'nullable|string|max:500',
        ];
    }
}
