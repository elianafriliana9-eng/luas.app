<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProdukSimpanan extends Model
{
    use HasUuid;

    protected $table = 'produk_simpanan';

    protected $fillable = ['kode', 'nama', 'jenis', 'bunga_pa', 'minimal_saldo', 'auto_bunga', 'aktif'];

    protected $casts = [
        'bunga_pa' => 'decimal:2',
        'minimal_saldo' => 'decimal:2',
        'auto_bunga' => 'boolean',
        'aktif' => 'boolean',
    ];

    public function rekening(): HasMany
    {
        return $this->hasMany(RekeningSimpanan::class, 'produk_id');
    }
}
