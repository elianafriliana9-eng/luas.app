<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SimpananBerjangka extends Model
{
    use HasUuid, SoftDeletes;

    protected $table = 'simpanan_berjangka';

    protected $fillable = [
        'anggota_id', 'no_deposito', 'nominal', 'jangka_bulan', 'bunga_pa',
        'tanggal_mulai', 'tanggal_jatuh_tempo', 'status', 'bunga_akrual',
        'last_accrual_date', 'auto_perpanjang',
    ];

    protected $casts = [
        'nominal' => 'decimal:2',
        'bunga_pa' => 'decimal:2',
        'bunga_akrual' => 'decimal:2',
        'tanggal_mulai' => 'date',
        'tanggal_jatuh_tempo' => 'date',
        'last_accrual_date' => 'date',
        'auto_perpanjang' => 'boolean',
    ];

    public function anggota(): BelongsTo
    {
        return $this->belongsTo(Anggota::class, 'anggota_id');
    }
}
