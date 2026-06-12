<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PotonganGaji extends Model
{
    use HasUuid, SoftDeletes;

    protected $table = 'potongan_gaji';
    public $timestamps = false;

    protected $fillable = [
        'anggota_id', 'pembiayaan_id', 'jadwal_angsuran_id',
        'periode', 'gaji_bruto', 'nominal_potongan', 'gaji_diterima',
        'jenis_potongan', 'status', 'keterangan', 'processed_at',
    ];

    protected $casts = [
        'gaji_bruto' => 'decimal:2',
        'nominal_potongan' => 'decimal:2',
        'gaji_diterima' => 'decimal:2',
        'periode' => 'date',
        'processed_at' => 'datetime',
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

    public function getLabelStatusAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Menunggu Proses',
            'diproses' => 'Sudah Diproses',
            'gagal' => 'Gagal',
            default => '-',
        };
    }

    public function getLabelJenisPotonganAttribute(): string
    {
        return match ($this->jenis_potongan) {
            'angsuran_pokok' => 'Angsuran Pokok',
            'angsuran_bunga' => 'Angsuran Bunga',
            'simpanan' => 'Simpanan',
            'lainnya' => 'Lainnya',
            default => '-',
        };
    }
}
