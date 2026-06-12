<?php

namespace App\Exports\Anggota;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class TemplateAnggotaExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            'OST' => new TemplateAnggotaSheet(),
            'SIMPANAN POKOK DAN WAJIB' => new TemplateSimpananSheet(),
            'SEMUA SIMPANAN' => new TemplateSimpananSheet(),
            'LIST SALDO ANGGOTA' => new TemplateSaldoSheet(),
        ];
    }
}
