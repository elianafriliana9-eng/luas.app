<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KonfigurasiCoa extends Model
{
    protected $table = 'konfigurasi_coa';

    protected $fillable = [
        'key', 'label', 'kode_akun', 'keterangan', 'jenis',
    ];
}
