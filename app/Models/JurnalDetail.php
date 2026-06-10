<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JurnalDetail extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'jurnal_detail';
    
    // UUID setup
    public $incrementing = false;
    protected $keyType = 'string';

    // Disable timestamps as they are not in the migration
    public $timestamps = false;

    protected $fillable = [
        'jurnal_id',
        'akun_id',
        'debet',
        'kredit',
        'keterangan'
    ];

    public function jurnal()
    {
        return $this->belongsTo(Jurnal::class, 'jurnal_id');
    }

    public function akun()
    {
        return $this->belongsTo(ChartOfAccount::class, 'akun_id');
    }
}
