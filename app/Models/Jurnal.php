<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jurnal extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'jurnal';
    
    // UUID setup
    public $incrementing = false;
    protected $keyType = 'string';

    // The created_at column is used without updated_at
    public const UPDATED_AT = null;

    protected $fillable = [
        'no_jurnal',
        'tanggal',
        'keterangan',
        'jenis',
        'ref_id',
        'ref_tabel',
        'dibuat_oleh',
        'is_eliminasi',
        'is_reversed',
        'reversed_by'
    ];

    public function details()
    {
        return $this->hasMany(JurnalDetail::class, 'jurnal_id');
    }

    public function pembuat()
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }
}
