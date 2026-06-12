<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAnggotaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array(auth()->user()->role, ['super_admin', 'admin']);
    }

    public function rules(): array
    {
        return [
            'cabang_id' => 'required|uuid|exists:cabang,id',
            'nik' => 'required|string|max:20|unique:anggota,nik',
            'nama_lengkap' => 'required|string|max:255',
            'tempat_lahir' => 'required|string|max:100',
            'tanggal_lahir' => 'required|date|before:today',
            'jenis_kelamin' => 'required|in:L,P',
            'alamat' => 'required|string',
            'no_hp' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'perusahaan_id' => 'nullable|uuid|exists:perusahaan,id',
            'gaji_pokok' => 'nullable|numeric|min:0',
            'tanggal_gajian' => 'nullable|integer|min:1|max:31',
            'tanggal_mulai_kerja' => 'nullable|date',
            'no_pegawai' => 'nullable|string|max:50',
            'departemen' => 'nullable|string|max:100',
            'jabatan' => 'nullable|string|max:100',
            'foto_ktp' => 'nullable|image|max:2048',
            'foto_selfie' => 'nullable|image|max:2048',
        ];
    }

    public function attributes(): array
    {
        return [
            'cabang_id' => 'Cabang',
            'nik' => 'NIK',
            'nama_lengkap' => 'Nama Lengkap',
            'tempat_lahir' => 'Tempat Lahir',
            'tanggal_lahir' => 'Tanggal Lahir',
            'jenis_kelamin' => 'Jenis Kelamin',
            'alamat' => 'Alamat',
            'no_hp' => 'No. HP',
            'perusahaan_id' => 'Perusahaan',
            'gaji_pokok' => 'Gaji Pokok',
            'no_pegawai' => 'No. Pegawai',
            'departemen' => 'Departemen',
            'jabatan' => 'Jabatan',
        ];
    }
}
