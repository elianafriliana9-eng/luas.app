<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChartOfAccount extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'chart_of_accounts';
    
    // UUID setup
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'kode_akun',
        'nama_akun',
        'kelompok',
        'posisi_normal',
        'is_header',
        'parent_id',
        'aktif'
    ];

    public function parent()
    {
        return $this->belongsTo(ChartOfAccount::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(ChartOfAccount::class, 'parent_id');
    }
}
