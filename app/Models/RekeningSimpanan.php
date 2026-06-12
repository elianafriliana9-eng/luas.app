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

    public static function generateNoRekening(\App\Models\ProdukSimpanan $produk, \App\Models\Cabang $cabang): string
    {
        $produkMap = [
            'SIMPOK' => '01',
            'SIMWA' => '02',
            'SIMSUKA' => '03',
        ];

        $cabangMap = [
            'CBG-JKT' => '01',
            'CBG-TGR' => '02',
            'CBG-BKS' => '03',
        ];

        $pp = $produkMap[$produk->kode] ?? '99';
        $bb = $cabangMap[$cabang->kode] ?? '99';

        $prefix = $pp . $bb;

        $lastNo = static::where('no_rekening', 'like', $prefix . '%')
            ->orderBy('no_rekening', 'desc')
            ->value('no_rekening');

        $seq = $lastNo ? ((int) substr($lastNo, -5)) + 1 : 1;

        return $prefix . str_pad($seq, 5, '0', STR_PAD_LEFT);
    }
}
