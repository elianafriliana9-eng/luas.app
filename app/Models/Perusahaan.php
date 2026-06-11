<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Perusahaan extends Model
{
    use HasUuid;

    protected $table = 'perusahaan';

    protected $fillable = ['kode', 'nama', 'alamat', 'telp', 'email', 'aktif'];

    protected $casts = [
        'aktif' => 'boolean',
    ];

    public function anggota(): HasMany
    {
        return $this->hasMany(Anggota::class, 'perusahaan_id');
    }
}
