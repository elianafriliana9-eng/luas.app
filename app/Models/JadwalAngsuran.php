<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JadwalAngsuran extends Model
{
    use HasUuid;

    protected $table = 'jadwal_angsuran';

    protected $fillable = [
        'pembiayaan_id', 'ke', 'tanggal_jatuh_tempo', 'pokok', 'bunga',
        'total', 'saldo_akhir', 'status', 'tanggal_bayar',
    ];

    protected $casts = [
        'pokok' => 'decimal:2',
        'bunga' => 'decimal:2',
        'total' => 'decimal:2',
        'saldo_akhir' => 'decimal:2',
        'tanggal_jatuh_tempo' => 'date',
        'tanggal_bayar' => 'date',
    ];

    public function pembiayaan(): BelongsTo
    {
        return $this->belongsTo(Pembiayaan::class, 'pembiayaan_id');
    }
}
