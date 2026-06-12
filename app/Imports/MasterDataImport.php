<?php

namespace App\Imports;

use App\Imports\Sheets\OstSheetImport;
use App\Imports\Sheets\SimpananSheetImport;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class MasterDataImport implements WithMultipleSheets
{
    private bool $resetBeforeImport;
    private OstSheetImport $ostSheet;
    private SimpananSheetImport $simpananSheet1;
    private SimpananSheetImport $simpananSheet2;

    public function __construct(bool $resetBeforeImport = true)
    {
        $this->resetBeforeImport = $resetBeforeImport;
    }

    public function sheets(): array
    {
        $this->ostSheet = new OstSheetImport($this->resetBeforeImport);
        $this->simpananSheet1 = new SimpananSheetImport(4);
        $this->simpananSheet2 = new SimpananSheetImport(3);

        return [
            'OST' => $this->ostSheet,
            'SIMPANAN POKOK DAN WAJIB' => $this->simpananSheet1,
            'SEMUA SIMPANAN' => $this->simpananSheet2,
        ];
    }

    public function getHasil(): array
    {
        $hasil = [];
        if (isset($this->ostSheet)) {
            $hasil['OST'] = $this->ostSheet->getHasil();
        }
        if (isset($this->simpananSheet1)) {
            $hasil['SIMPANAN POKOK DAN WAJIB'] = $this->simpananSheet1->getHasil();
        }
        if (isset($this->simpananSheet2)) {
            $hasil['SEMUA SIMPANAN'] = $this->simpananSheet2->getHasil();
        }
        return $hasil;
    }
}
