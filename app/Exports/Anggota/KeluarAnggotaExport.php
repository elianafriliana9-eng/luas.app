<?php

namespace App\Exports\Anggota;

use App\Models\Anggota;
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

class KeluarAnggotaExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithTitle
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function title(): string
    {
        return 'Anggota Keluar';
    }

    public function query()
    {
        $query = Anggota::with('cabang')
            ->where('status', 'keluar')
            ->orderBy('tanggal_keluar', 'desc');

        if (!empty($this->filters['from'])) {
            $query->whereDate('tanggal_keluar', '>=', $this->filters['from']);
        }
        if (!empty($this->filters['to'])) {
            $query->whereDate('tanggal_keluar', '<=', $this->filters['to']);
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'No',
            'No. Anggota',
            'Nama Lengkap',
            'Perusahaan',
            'Tanggal Keluar',
            'Alasan',
        ];
    }

    public function map($anggota): array
    {
        static $i = 0;
        $i++;

        return [
            $i,
            $anggota->no_anggota,
            $anggota->nama_lengkap,
            $anggota->perusahaan?->nama ?? '-',
            $anggota->tanggal_keluar?->format('d/m/Y') ?? '-',
            $anggota->alasan_keluar ?? '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();
        $lastCol = 'F';

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

        $sheet->getStyle("B2:B{$lastRow}")->getFont()->setName('Consolas');
    }
}
