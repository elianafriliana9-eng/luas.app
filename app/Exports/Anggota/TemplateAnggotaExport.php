<?php

namespace App\Exports\Anggota;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class TemplateAnggotaExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            'Template' => new TemplateAnggotaSheet(),
            'Petunjuk' => new TemplatePetunjukAnggotaSheet(),
        ];
    }
}
