<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RekeningSimpanan extends Model
{
    use HasUuid;

    protected $table = 'rekening_simpanan';

    protected $fillable = [
        'anggota_id', 'produk_id', 'no_rekening', 'saldo', 'status',
        'tanggal_buka', 'tanggal_tutup',
    ];

    protected $casts = [
        'saldo' => 'decimal:2',
        'tanggal_buka' => 'date',
        'tanggal_tutup' => 'date',
    ];

    public function anggota(): BelongsTo
    {
        return $this->belongsTo(Anggota::class, 'anggota_id');
    }

    public function produk(): BelongsTo
    {
        return $this->belongsTo(ProdukSimpanan::class, 'produk_id');
    }

    public function transaksi(): HasMany
    {
        return $this->hasMany(TransaksiSimpanan::class, 'rekening_id');
    }
}
