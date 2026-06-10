<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShuPeriode extends Model
{
    use HasUuid;

    protected $table = 'shu_periode';

    protected $fillable = [
        'cabang_id', 'tahun', 'total_shu', 'shu_cadangan', 'shu_anggota',
        'shu_pengurus', 'shu_karyawan', 'status', 'finalized_at',
    ];

    protected $casts = [
        'total_shu' => 'decimal:2',
        'shu_cadangan' => 'decimal:2',
        'shu_anggota' => 'decimal:2',
        'shu_pengurus' => 'decimal:2',
        'shu_karyawan' => 'decimal:2',
        'finalized_at' => 'datetime',
    ];

    public function cabang(): BelongsTo
    {
        return $this->belongsTo(Cabang::class, 'cabang_id');
    }

    public function rincianAnggota(): HasMany
    {
        return $this->hasMany(ShuAnggota::class, 'shu_periode_id');
    }
}
