<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pembiayaan extends Model
{
    use HasUuid, SoftDeletes;

    protected $table = 'pembiayaan';

    protected $fillable = [
        'pengajuan_id', 'anggota_id', 'no_pembiayaan', 'nominal_disetujui',
        'nominal_cair', 'jangka_bulan', 'bunga_pa', 'metode_hitung',
        'angsuran_pokok', 'angsuran_bunga', 'tanggal_akad', 'tanggal_cair',
        'tanggal_lunas', 'status', 'saldo_pokok', 'saldo_bunga',
        'hari_tunggak', 'kolektibilitas', 'is_chanelling', 'sumber_dana',
        'auto_potong_gaji', 'nominal_potongan', 'bulan_tersisa_potongan',
        'sumber_pembayaran',
    ];

    protected $casts = [
        'nominal_disetujui' => 'decimal:2',
        'nominal_cair' => 'decimal:2',
        'bunga_pa' => 'decimal:2',
        'angsuran_pokok' => 'decimal:2',
        'angsuran_bunga' => 'decimal:2',
        'saldo_pokok' => 'decimal:2',
        'saldo_bunga' => 'decimal:2',
        'tanggal_akad' => 'date',
        'tanggal_cair' => 'date',
        'tanggal_lunas' => 'date',
        'is_chanelling' => 'boolean',
        'auto_potong_gaji' => 'boolean',
        'nominal_potongan' => 'decimal:2',
    ];

    public function pengajuan(): BelongsTo
    {
        return $this->belongsTo(PengajuanPembiayaan::class, 'pengajuan_id');
    }

    public function anggota(): BelongsTo
    {
        return $this->belongsTo(Anggota::class, 'anggota_id');
    }

    public function jadwalAngsuran(): HasMany
    {
        return $this->hasMany(JadwalAngsuran::class, 'pembiayaan_id');
    }

    public function transaksi(): HasMany
    {
        return $this->hasMany(TransaksiPembiayaan::class, 'pembiayaan_id');
    }

    public function jaminan(): HasMany
    {
        return $this->hasMany(Jaminan::class, 'pembiayaan_id');
    }

    public function potonganGaji(): HasMany
    {
        return $this->hasMany(PotonganGaji::class, 'pembiayaan_id');
    }

    public function payLater(): HasMany
    {
        return $this->hasMany(PayLater::class, 'pembiayaan_id');
    }

    public function getLabelKolektibilitasAttribute(): string
    {
        return match ($this->kolektibilitas) {
            1 => 'Lancar',
            2 => 'Dalam Perhatian Khusus',
            3 => 'Kurang Lancar',
            4 => 'Diragukan',
            5 => 'Macet',
            default => '-',
        };
    }

    // Label sumber pembayaran
    public function getLabelSumberPembayaranAttribute(): string
    {
        return match ($this->sumber_pembayaran) {
            'potong_gaji' => 'Potong Gaji',
            'bayar_manual' => 'Bayar Manual',
            'keduanya' => 'Potong Gaji + Manual',
            default => '-',
        };
    }

    // Check apakah masih ada angsuran yang belum dipotong
    public function hasRemainingPotongan(): bool
    {
        return ($this->bulan_tersisa_potongan ?? 0) > 0;
    }

    // Progress potongan gaji
    public function getProgressPotonganAttribute(): string
    {
        $total = $this->jangka_bulan;
        $remaining = $this->bulan_tersisa_potongan ?? $total;
        $paid = $total - $remaining;
        return "{$paid}/{$total} bulan";
    }
}
