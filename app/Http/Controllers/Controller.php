<?php

namespace App\Http\Controllers;

use Maatwebsite\Excel\Facades\Excel;

abstract class Controller
{
    protected function excelDownload($export, string $fileName)
    {
        $content = Excel::raw($export, \Maatwebsite\Excel\Excel::XLSX);

        return response($content, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            'Content-Length' => strlen($content),
            'Cache-Control' => 'no-cache, must-revalidate',
            'Pragma' => 'no-cache',
        ]);
    }
}
