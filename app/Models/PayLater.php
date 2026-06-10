<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayLater extends Model
{
    use HasUuid;

    protected $table = 'pay_later';

    protected $fillable = [
        'anggota_id', 'pembiayaan_id', 'jadwal_angsuran_id',
        'nominal', 'jenis', 'status', 'no_transaksi',
        'keterangan', 'approved_by', 'approved_at', 'lunas_at',
    ];

    protected $casts = [
        'nominal' => 'decimal:2',
        'approved_at' => 'datetime',
        'lunas_at' => 'datetime',
    ];

    public function anggota(): BelongsTo
    {
        return $this->belongsTo(Anggota::class, 'anggota_id');
    }

    public function pembiayaan(): BelongsTo
    {
        return $this->belongsTo(Pembiayaan::class, 'pembiayaan_id');
    }

    public function jadwalAngsuran(): BelongsTo
    {
        return $this->belongsTo(JadwalAngsuran::class, 'jadwal_angsuran_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getLabelStatusAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Menunggu Approval',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            'lunas' => 'Lunas',
            default => '-',
        };
    }

    public function getLabelJenisAttribute(): string
    {
        return match ($this->jenis) {
            'angsuran' => 'Angsuran',
            'pelunasan' => 'Pelunasan',
            default => '-',
        };
    }
}
