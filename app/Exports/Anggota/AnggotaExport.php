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
use PhpOffice\PhpSpreadsheet\Cell\DataType;

class AnggotaExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithTitle, WithEvents
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function title(): string
    {
        return 'Data Anggota';
    }

    public function query()
    {
        $query = Anggota::with('cabang');

        if (!empty($this->filters['search'])) {
            $s = $this->filters['search'];
            $query->where(function ($q) use ($s) {
                $q->where('nama_lengkap', 'like', "%{$s}%")
                  ->orWhere('no_anggota', 'like', "%{$s}%")
                  ->orWhere('nik', 'like', "%{$s}%");
            });
        }
        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }
        if (!empty($this->filters['cabang_id'])) {
            $query->where('cabang_id', $this->filters['cabang_id']);
        }
        if (!empty($this->filters['departemen'])) {
            $query->where('departemen', $this->filters['departemen']);
        }

        return $query->orderBy('tanggal_masuk', 'desc');
    }

    public function headings(): array
    {
        return [
            'No. Anggota',
            'NIK',
            'Nama Lengkap',
            'No. Pegawai',
            'Cabang',
            'Departemen',
            'Jabatan',
            'Gaji Pokok',
            'Tanggal Masuk',
            'Status',
        ];
    }

    public function map($anggota): array
    {
        return [
            $anggota->no_anggota,
            $anggota->nik,
            $anggota->nama_lengkap,
            $anggota->no_pegawai ?? '-',
            $anggota->cabang?->nama ?? '-',
            $anggota->departemen ?? '-',
            $anggota->jabatan ?? '-',
            $anggota->gaji_pokok,
            $anggota->tanggal_masuk?->format('d/m/Y') ?? '-',
            ucfirst(str_replace('_', ' ', $anggota->status)),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();
        $lastCol = 'J';

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

        $sheet->getStyle("H2:H{$lastRow}")->getNumberFormat()->setFormatCode('"Rp" #,##0');

        $sheet->getStyle("A2:A{$lastRow}")->getFont()->setName('Consolas');
        $sheet->getStyle("B2:B{$lastRow}")->getFont()->setName('Consolas');
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();
                for ($i = 2; $i <= $lastRow; $i++) {
                    $val = $sheet->getCell("B{$i}")->getValue();
                    if (is_numeric($val)) {
                        $sheet->setCellValueExplicit("B{$i}", (string) $val, DataType::TYPE_STRING);
                    }
                }
            },
        ];
    }
}
