<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cabang extends Model
{
    use HasUuid;

    protected $table = 'cabang';

    protected $fillable = ['kode', 'nama', 'alamat', 'telp', 'aktif'];

    protected $casts = [
        'aktif' => 'boolean',
    ];

    public function anggota(): HasMany
    {
        return $this->hasMany(Anggota::class, 'cabang_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'cabang_id');
    }
}
