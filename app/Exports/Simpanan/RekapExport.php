<?php

namespace App\Exports\Simpanan;

use App\Models\RekeningSimpanan;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class RekapExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithTitle, WithEvents
{
    protected array $filters;
    protected float $grandTotal = 0;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function title(): string
    {
        return 'Rekap Simpanan';
    }

    public function query()
    {
        $query = RekeningSimpanan::with(['anggota', 'produk'])->where('status', 'aktif');

        if (!empty($this->filters['produk_id'])) {
            $query->where('produk_id', $this->filters['produk_id']);
        }
        return $query->orderBy('no_rekening');
    }

    public function headings(): array
    {
        return [
            'No',
            'No. Rekening',
            'Anggota',
            'No. Anggota',
            'Produk',
            'Saldo',
            'Status',
        ];
    }

    public function map($rekening): array
    {
        $this->grandTotal += (float) $rekening->saldo;

        return [
            null,
            $rekening->no_rekening,
            $rekening->anggota?->nama_lengkap ?? '-',
            $rekening->anggota?->no_anggota ?? '-',
            $rekening->produk?->nama ?? '-',
            (float) $rekening->saldo,
            ucfirst($rekening->status),
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
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D1D5DB']],
            ],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ]);

        $sheet->getStyle("F2:F{$lastRow}")->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle("F2:F{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        $sheet->getStyle("B2:B{$lastRow}")->getFont()->setName('Consolas');
        $sheet->getStyle("D2:D{$lastRow}")->getFont()->setName('Consolas');
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();
                $grandRow = $lastRow + 1;
                $lastCol = 'G';

                $sheet->setCellValue("A{$grandRow}", '');
                $sheet->setCellValue("B{$grandRow}", '');
                $sheet->setCellValue("C{$grandRow}", '');
                $sheet->setCellValue("D{$grandRow}", '');
                $sheet->setCellValue("E{$grandRow}", 'GRAND TOTAL');
                $sheet->setCellValue("F{$grandRow}", $this->grandTotal);
                $sheet->setCellValue("G{$grandRow}", '');

                $sheet->getStyle("A{$grandRow}:{$lastCol}{$grandRow}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F3F4F6']],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '1E3A5F']]],
                ]);

                $sheet->getStyle("F{$grandRow}")->getNumberFormat()->setFormatCode('"Rp" #,##0');
                $sheet->getStyle("E{$grandRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                $sheet->freezePane('A2');
                $sheet->setAutoFilter("A1:{$lastCol}{$lastRow}");
            },
        ];
    }
}
