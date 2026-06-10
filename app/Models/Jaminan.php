<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Jaminan extends Model
{
    use HasUuid;

    protected $table = 'jaminan';

    protected $fillable = [
        'pembiayaan_id', 'jenis_jaminan', 'deskripsi', 'nilai_taksasi',
        'no_dokumen', 'foto_dokumen',
    ];

    protected $casts = [
        'nilai_taksasi' => 'decimal:2',
    ];

    public function pembiayaan(): BelongsTo
    {
        return $this->belongsTo(Pembiayaan::class, 'pembiayaan_id');
    }
}
