<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pinbuk extends Model
{
    use HasUuid, SoftDeletes;

    public $timestamps = false;

    protected $table = 'pinbuk';

    protected $fillable = [
        'rekening_sumber_id', 'rekening_tujuan_id', 'user_id',
        'no_transaksi', 'nominal', 'keterangan',
        'status_approval', 'approved_by', 'approved_at',
    ];

    protected $casts = [
        'nominal' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    public function rekeningSumber(): BelongsTo
    {
        return $this->belongsTo(RekeningSimpanan::class, 'rekening_sumber_id');
    }

    public function rekeningTujuan(): BelongsTo
    {
        return $this->belongsTo(RekeningSimpanan::class, 'rekening_tujuan_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getLabelStatusAttribute(): string
    {
        return match ($this->status_approval) {
            'pending' => 'Menunggu Approval',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            default => '-',
        };
    }
}
