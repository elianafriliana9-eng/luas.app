<?php

namespace App\Exports\Anggota;

use App\Models\Anggota;
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

class SaldoExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithTitle, WithEvents
{
    protected array $filters;
    protected float $grandTotal = 0;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function title(): string
    {
        return 'Saldo Anggota';
    }

    public function query()
    {
        $query = Anggota::with('rekeningSimpanan.produk', 'cabang')->where('status', 'aktif');

        if (!empty($this->filters['search'])) {
            $s = $this->filters['search'];
            $query->where(function ($q) use ($s) {
                $q->where('nama_lengkap', 'like', "%{$s}%")
                  ->orWhere('no_anggota', 'like', "%{$s}%");
            });
        }
        if (!empty($this->filters['cabang_id'])) {
            $query->where('cabang_id', $this->filters['cabang_id']);
        }

        return $query->orderBy('nama_lengkap');
    }

    public function headings(): array
    {
        return [
            'No. Anggota',
            'Nama',
            'Cabang',
            'Simpanan Pokok',
            'Simpanan Wajib',
            'Simpanan Sukarela',
            'Total Simpanan',
        ];
    }

    public function map($anggota): array
    {
        $pokok = $anggota->rekeningSimpanan->where('produk.kode', 'SP')->sum('saldo');
        $wajib = $anggota->rekeningSimpanan->where('produk.kode', 'SW')->sum('saldo');
        $sukarela = $anggota->rekeningSimpanan->where('produk.kode', 'SS')->sum('saldo');
        $total = $pokok + $wajib + $sukarela;

        $this->grandTotal += $total;

        return [
            $anggota->no_anggota,
            $anggota->nama_lengkap,
            $anggota->cabang?->nama ?? '-',
            $pokok,
            $wajib,
            $sukarela,
            $total,
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

        $sheet->getStyle("D2:G{$lastRow}")->getNumberFormat()->setFormatCode('"Rp" #,##0');

        $sheet->getStyle("A2:A{$lastRow}")->getFont()->setName('Consolas');
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
                $sheet->setCellValue("C{$grandRow}", 'GRAND TOTAL');
                $sheet->setCellValue("D{$grandRow}", '');
                $sheet->setCellValue("E{$grandRow}", '');
                $sheet->setCellValue("F{$grandRow}", '');
                $sheet->setCellValue("G{$grandRow}", $this->grandTotal);

                $sheet->getStyle("A{$grandRow}:{$lastCol}{$grandRow}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F3F4F6']],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '1E3A5F']]],
                ]);

                $sheet->getStyle("G{$grandRow}")->getNumberFormat()->setFormatCode('"Rp" #,##0');
                $sheet->getStyle("C{$grandRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            },
        ];
    }
}
