<?php

namespace App\Exports\Simpanan;

use App\Models\RekeningSimpanan;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class RegistSimpananExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithTitle
{
    public function title(): string
    {
        return 'Registrasi Simpanan';
    }

    public function query()
    {
        return RekeningSimpanan::with(['anggota', 'produk'])->orderBy('tanggal_buka', 'desc');
    }

    public function headings(): array
    {
        return ['No', 'No. Rekening', 'Anggota', 'Produk', 'Tanggal Buka', 'Saldo', 'Status'];
    }

    public function map($rek): array
    {
        static $i = 0;
        $i++;

        return [
            $i,
            $rek->no_rekening,
            $rek->anggota?->nama_lengkap ?? '-',
            $rek->produk?->nama ?? '-',
            $rek->tanggal_buka?->format('d/m/Y') ?? '-',
            (float) $rek->saldo,
            ucfirst($rek->status),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();
        $lastCol = 'G';

        $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A5F']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

        $sheet->getStyle("A2:{$lastCol}{$lastRow}")->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D1D5DB']]],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ]);

        $sheet->getStyle("F2:F{$lastRow}")->getNumberFormat()->setFormatCode('"Rp" #,##0');
        $sheet->getStyle("B2:B{$lastRow}")->getFont()->setName('Consolas');
    }
}
