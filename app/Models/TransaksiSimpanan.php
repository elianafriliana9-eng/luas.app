<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransaksiSimpanan extends Model
{
    use HasUuid, \App\Traits\Auditable, SoftDeletes;

    protected static function boot(): void
    {
        parent::boot();

        static::updating(function ($transaksi) {
            // Immutable: prevent changes to core financial fields once finalized
            if ($transaksi->getOriginal('dibatalkan') || $transaksi->getOriginal('status_approval') === 'rejected') {
                return;
            }
            if ($transaksi->getOriginal('status_approval') === 'approved' && !$transaksi->dibatalkan) {
                $immutableFields = ['nominal', 'rekening_id', 'jenis', 'saldo_sebelum', 'saldo_sesudah', 'no_transaksi'];
                foreach ($immutableFields as $field) {
                    if ($transaksi->isDirty($field)) {
                        throw new \LogicException("Field '{$field}' tidak dapat diubah setelah transaksi disetujui. Gunakan transaksi koreksi.");
                    }
                }
            }
        });
    }

    protected $table = 'transaksi_simpanan';

    public $timestamps = false;

    protected $fillable = [
        'rekening_id', 'user_id', 'no_transaksi', 'jenis', 'nominal',
        'saldo_sebelum', 'saldo_sesudah', 'keterangan', 'channel', 'ref_payment',
        'status_approval', 'approved_by', 'approved_at',
        'dibatalkan', 'dibatalkan_by', 'dibatalkan_at', 'jenis_pembatalan',
        'ip_address', 'user_agent',
        'created_by', 'updated_by',
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
