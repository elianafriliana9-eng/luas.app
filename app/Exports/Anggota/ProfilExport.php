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

class ProfilExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithTitle, WithEvents
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function title(): string
    {
        return 'Profil Anggota';
    }

    public function query()
    {
        $query = Anggota::with('cabang');

        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }
        if (!empty($this->filters['departemen'])) {
            $query->where('departemen', $this->filters['departemen']);
        }

        return $query->orderBy('nama_lengkap');
    }

    public function headings(): array
    {
        return [
            'No',
            'No. Anggota',
            'Nama Lengkap',
            'NIK',
            'No. HP',
            'Email',
            'Cabang',
            'Departemen',
            'Jabatan',
            'Tanggal Masuk',
            'Status',
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
            $anggota->nik,
            $anggota->no_hp ?? '-',
            $anggota->email ?? '-',
            $anggota->cabang?->nama ?? '-',
            $anggota->departemen ?? '-',
            $anggota->jabatan ?? '-',
            $anggota->tanggal_masuk?->format('d/m/Y') ?? '-',
            ucfirst(str_replace('_', ' ', $anggota->status)),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();
        $lastCol = 'K';

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
        $sheet->getStyle("D2:D{$lastRow}")->getFont()->setName('Consolas');
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();
                for ($i = 2; $i <= $lastRow; $i++) {
                    $val = $sheet->getCell("D{$i}")->getValue();
                    if (is_numeric($val)) {
                        $sheet->setCellValueExplicit("D{$i}", (string) $val, DataType::TYPE_STRING);
                    }
                }
            },
        ];
    }
}
