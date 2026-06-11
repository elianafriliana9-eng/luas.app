<?php

namespace App\Exports\Simpanan;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class TemplateTransaksiExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            'Template' => new TemplateTransaksiSheet(),
            'Petunjuk' => new TemplatePetunjukSheet(),
        ];
    }
}
