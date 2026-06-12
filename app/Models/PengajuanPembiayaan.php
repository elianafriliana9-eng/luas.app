<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class PengajuanPembiayaan extends Model
{
    use HasUuid, SoftDeletes;

    protected $table = 'pengajuan_pembiayaan';
    public $timestamps = false;

    protected $fillable = [
        'anggota_id', 'produk_id', 'no_pengajuan', 'nominal_diajukan',
        'jangka_bulan', 'tujuan', 'status_approval', 'approved_by',
        'approved_at', 'catatan',
    ];

    protected $casts = [
        'nominal_diajukan' => 'decimal:2',
        'approved_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    public function anggota(): BelongsTo
    {
        return $this->belongsTo(Anggota::class, 'anggota_id');
    }

    public function produk(): BelongsTo
    {
        return $this->belongsTo(ProdukPembiayaan::class, 'produk_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function pembiayaan(): HasOne
    {
        return $this->hasOne(Pembiayaan::class, 'pengajuan_id');
    }
}
