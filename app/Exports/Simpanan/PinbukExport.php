<?php

namespace App\Exports\Simpanan;

use App\Models\Pinbuk;
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

class PinbukExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithTitle, WithEvents
{
    protected array $filters;
    protected float $grandTotal = 0;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function title(): string
    {
        return 'Laporan Pinbuk';
    }

    public function query()
    {
        $query = Pinbuk::with(['rekeningSumber.anggota', 'rekeningTujuan.anggota', 'approvedBy']);

        if (!empty($this->filters['status'])) {
            $query->where('status_approval', $this->filters['status']);
        }
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
        return ['No', 'No. Transaksi', 'Sumber', 'Tujuan', 'Nominal', 'Status', 'Tanggal'];
    }

    public function map($pb): array
    {
        static $i = 0;
        $i++;
        $this->grandTotal += (float) $pb->nominal;

        $statusLabel = match ($pb->status_approval) {
            'approved' => 'Disetujui',
            'pending' => 'Pending',
            'rejected' => 'Ditolak',
            default => $pb->status_approval,
        };

        return [
            $i,
            $pb->no_transaksi,
            ($pb->rekeningSumber?->anggota?->nama_lengkap ?? '-') . ' (' . ($pb->rekeningSumber?->no_rekening ?? '-') . ')',
            ($pb->rekeningTujuan?->anggota?->nama_lengkap ?? '-') . ' (' . ($pb->rekeningTujuan?->no_rekening ?? '-') . ')',
            (float) $pb->nominal,
            $statusLabel,
            $pb->created_at->format('d/m/Y'),
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

        $sheet->getStyle("E2:E{$lastRow}")->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle("B2:B{$lastRow}")->getFont()->setName('Consolas');
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
                $sheet->setCellValue("D{$grandRow}", 'GRAND TOTAL');
                $sheet->setCellValue("E{$grandRow}", $this->grandTotal);

                $sheet->getStyle("A{$grandRow}:{$lastCol}{$grandRow}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F3F4F6']],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '1E3A5F']]],
                ]);

                $sheet->getStyle("E{$grandRow}")->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle("D{$grandRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            },
        ];
    }
}
