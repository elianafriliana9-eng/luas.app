<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProdukPembiayaan extends Model
{
    use HasUuid;

    protected $table = 'produk_pembiayaan';

    protected $fillable = [
        'kode', 'nama', 'skema_bunga', 'bunga_pa', 'max_jangka',
        'max_plafon', 'is_chanelling', 'aktif',
    ];

    protected $casts = [
        'bunga_pa' => 'decimal:2',
        'max_plafon' => 'decimal:2',
        'is_chanelling' => 'boolean',
        'aktif' => 'boolean',
    ];

    public function pengajuan(): HasMany
    {
        return $this->hasMany(PengajuanPembiayaan::class, 'produk_id');
    }
}
