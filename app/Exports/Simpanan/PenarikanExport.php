<?php

namespace App\Exports\Simpanan;

use App\Models\TransaksiSimpanan;
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

class PenarikanExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithTitle, WithEvents
{
    protected array $filters;
    protected float $grandTotal = 0;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function title(): string
    {
        return 'Laporan Penarikan';
    }

    public function query()
    {
        $query = TransaksiSimpanan::where('jenis', 'penarikan')
            ->where('dibatalkan', false)
            ->with(['rekening.anggota', 'rekening.produk']);

        if (!empty($this->filters['from'])) {
            $query->whereDate('created_at', '>=', $this->filters['from']);
        }
        if (!empty($this->filters['to'])) {
            $query->whereDate('created_at', '<=', $this->filters['to']);
        }

        return $query->orderByDesc('created_at');
    }

    public function headings(): array
    {
        return ['No', 'Tanggal', 'No. Transaksi', 'Anggota', 'Rekening', 'Nominal', 'Keterangan'];
    }

    public function map($trx): array
    {
        static $i = 0;
        $i++;
        $this->grandTotal += (float) $trx->nominal;

        return [
            $i,
            $trx->created_at->format('d/m/Y H:i'),
            $trx->no_transaksi,
            $trx->rekening?->anggota?->nama_lengkap ?? '-',
            $trx->rekening?->produk?->nama ?? '-',
            (float) $trx->nominal,
            $trx->keterangan ?? '',
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

        $sheet->getStyle("F2:F{$lastRow}")->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle("C2:C{$lastRow}")->getFont()->setName('Consolas');
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
                $sheet->setCellValue("E{$grandRow}", 'GRAND TOTAL');
                $sheet->setCellValue("F{$grandRow}", $this->grandTotal);

                $sheet->getStyle("A{$grandRow}:{$lastCol}{$grandRow}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F3F4F6']],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '1E3A5F']]],
                ]);

                $sheet->getStyle("F{$grandRow}")->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle("E{$grandRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            },
        ];
    }
}
