<?php

namespace App\Http\Requests\Simpanan;

use Illuminate\Foundation\Http\FormRequest;

class RekeningBaruRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'anggota_id' => 'required|uuid|exists:anggota,id',
            'produk_id' => 'required|uuid|exists:produk_simpanan,id',
        ];
    }
}
