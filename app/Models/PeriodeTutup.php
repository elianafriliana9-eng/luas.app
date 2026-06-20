<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PeriodeTutup extends Model
{
    use HasUuid;

    protected $table = 'periode_tutup';

    protected $fillable = [
        'tahun', 'bulan', 'is_closed', 'closed_at', 'closed_by',
    ];

    protected $casts = [
        'is_closed' => 'boolean',
        'closed_at' => 'datetime',
    ];

    public function closedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }
}
