<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShuAnggota extends Model
{
    use HasUuid;

    protected $table = 'shu_anggota';

    protected $fillable = [
        'shu_periode_id', 'anggota_id', 'jasa_simpanan', 'jasa_pinjaman',
        'total_shu', 'status_bayar', 'dibayar_at',
    ];

    protected $casts = [
        'jasa_simpanan' => 'decimal:2',
        'jasa_pinjaman' => 'decimal:2',
        'total_shu' => 'decimal:2',
        'dibayar_at' => 'datetime',
    ];

    public function shuPeriode(): BelongsTo
    {
        return $this->belongsTo(ShuPeriode::class, 'shu_periode_id');
    }

    public function anggota(): BelongsTo
    {
        return $this->belongsTo(Anggota::class, 'anggota_id');
    }
}
