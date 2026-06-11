<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransaksiSimpanan extends Model
{
    use HasUuid;

    protected $table = 'transaksi_simpanan';

    public $timestamps = false;

    protected $fillable = [
        'rekening_id', 'user_id', 'no_transaksi', 'jenis', 'nominal',
        'saldo_sebelum', 'saldo_sesudah', 'keterangan', 'channel', 'ref_payment',
        'status_approval', 'approved_by', 'approved_at',
        'dibatalkan', 'dibatalkan_by', 'dibatalkan_at', 'jenis_pembatalan',
        'created_at',
    ];

    protected $casts = [
        'nominal' => 'decimal:2',
        'saldo_sebelum' => 'decimal:2',
        'saldo_sesudah' => 'decimal:2',
        'created_at' => 'datetime',
        'approved_at' => 'datetime',
        'dibatalkan' => 'boolean',
        'dibatalkan_at' => 'datetime',
    ];

    public function rekening(): BelongsTo
    {
        return $this->belongsTo(RekeningSimpanan::class, 'rekening_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function dibatalkanBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibatalkan_by');
    }

    public function getLabelJenisAttribute(): string
    {
        return match ($this->jenis) {
            'setoran' => 'Setoran',
            'penarikan' => 'Penarikan',
            'pinbuk_masuk' => 'Pinbuk Masuk',
            'pinbuk_keluar' => 'Pinbuk Keluar',
            'bunga' => 'Bunga',
            'koreksi' => 'Koreksi',
            default => '-',
        };
    }

    public function getLabelStatusAttribute(): string
    {
        if ($this->dibatalkan) return 'Dibatalkan';
        return match ($this->status_approval) {
            'pending' => 'Pending',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            default => '-',
        };
    }
}
