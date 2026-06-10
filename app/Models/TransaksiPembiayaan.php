<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransaksiPembiayaan extends Model
{
    use HasUuid;

    protected $table = 'transaksi_pembiayaan';
    public $timestamps = false;

    protected $fillable = [
        'pembiayaan_id', 'jadwal_id', 'no_transaksi', 'jenis',
        'nominal_pokok', 'nominal_bunga', 'nominal_denda', 'total',
        'channel', 'ref_payment',
    ];

    protected $casts = [
        'nominal_pokok' => 'decimal:2',
        'nominal_bunga' => 'decimal:2',
        'nominal_denda' => 'decimal:2',
        'total' => 'decimal:2',
        'created_at' => 'datetime',
    ];

    public function pembiayaan(): BelongsTo
    {
        return $this->belongsTo(Pembiayaan::class, 'pembiayaan_id');
    }

    public function jadwal(): BelongsTo
    {
        return $this->belongsTo(JadwalAngsuran::class, 'jadwal_id');
    }
}
