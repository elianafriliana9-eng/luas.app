<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Kas extends Model
{
    use HasUuid;

    protected $table = 'kas';

    protected $fillable = [
        'cabang_id', 'kode_kas', 'nama_kas', 'akun_id',
        'saldo', 'aktif', 'keterangan',
    ];

    protected $casts = [
        'saldo' => 'decimal:2',
        'aktif' => 'boolean',
    ];

    public function cabang(): BelongsTo
    {
        return $this->belongsTo(Cabang::class);
    }

    public function akun(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'akun_id');
    }
}
