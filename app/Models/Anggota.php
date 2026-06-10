<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Anggota extends Authenticatable
{
    use HasFactory, HasUuid, HasApiTokens, Notifiable;

    protected $table = 'anggota';

    protected $fillable = [
        'cabang_id', 'no_anggota', 'nik', 'nama_lengkap', 'tempat_lahir',
        'tanggal_lahir', 'jenis_kelamin', 'alamat', 'no_hp', 'email',
        'gaji_pokok', 'tanggal_gajian', 'departemen', 'jabatan',
        'tanggal_mulai_kerja', 'no_pegawai',
        'foto_ktp',
        'foto_selfie',
        'status',
        'tanggal_masuk',
        'tanggal_keluar',
        'alasan_keluar',
        'password'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'tanggal_masuk' => 'date',
        'tanggal_keluar' => 'date',
        'tanggal_mulai_kerja' => 'date',
        'gaji_pokok' => 'decimal:2',
        'auto_potong_gaji' => 'boolean',
    ];

    public function cabang(): BelongsTo
    {
        return $this->belongsTo(Cabang::class, 'cabang_id');
    }

    public function rekeningSimpanan(): HasMany
    {
        return $this->hasMany(RekeningSimpanan::class, 'anggota_id');
    }

    public function simpananBerjangka(): HasMany
    {
        return $this->hasMany(SimpananBerjangka::class, 'anggota_id');
    }

    public function pengajuanPembiayaan(): HasMany
    {
        return $this->hasMany(PengajuanPembiayaan::class, 'anggota_id');
    }

    public function pembiayaan(): HasMany
    {
        return $this->hasMany(Pembiayaan::class, 'anggota_id');
    }

    public function potonganGaji(): HasMany
    {
        return $this->hasMany(PotonganGaji::class, 'anggota_id');
    }

    public function payLater(): HasMany
    {
        return $this->hasMany(PayLater::class, 'anggota_id');
    }

    public function shuAnggota(): HasMany
    {
        return $this->hasMany(ShuAnggota::class, 'anggota_id');
    }

    // Accessor: nama singkat
    public function getNamaSingkatAttribute(): string
    {
        $parts = explode(' ', $this->nama_lengkap);
        return count($parts) > 1 ? $parts[0] . ' ' . $parts[1] : $parts[0];
    }

    // Total saldo simpanan
    public function getTotalSimpananAttribute(): float
    {
        return $this->rekeningSimpanan()->where('status', 'aktif')->sum('saldo');
    }

    // Accessor: masa kerja
    public function getMasaKerjaAttribute(): string
    {
        if (!$this->tanggal_mulai_kerja) {
            return '-';
        }
        $diff = $this->tanggal_mulai_kerja->diff(now());
        if ($diff->y > 0) {
            return "{$diff->y} tahun {$diff->m} bulan";
        }
        return "{$diff->m} bulan";
    }

    // Check apakah karyawan memiliki potongan gaji aktif
    public function hasPotonganGajiAktif(): bool
    {
        return $this->pembiayaan()
            ->where('auto_potong_gaji', true)
            ->where('status', 'aktif')
            ->exists();
    }

    // Total potongan per bulan
    public function getTotalPotonganPerBulanAttribute(): float
    {
        return $this->pembiayaan()
            ->where('auto_potong_gaji', true)
            ->where('status', 'aktif')
            ->sum('nominal_potongan');
    }

    // Gaji yang diterima setelah potongan
    public function getGajiDiterimaAttribute(): float
    {
        return ($this->gaji_pokok ?? 0) - $this->total_potongan_per_bulan;
    }
}
