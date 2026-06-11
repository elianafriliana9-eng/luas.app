<?php

namespace App\Exports\Anggota;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

class TemplateAnggotaSheet implements FromArray, WithHeadings, WithStyles, ShouldAutoSize, WithEvents
{
    public function headings(): array
    {
        return [
            'NIK',
            'Nama Lengkap',
            'Kode Cabang',
            'Tempat Lahir',
            'Tanggal Lahir',
            'Jenis Kelamin',
            'Alamat',
            'No. HP',
            'Email',
            'Departemen',
            'Jabatan',
            'Tanggal Mulai Kerja',
            'No. Pegawai',
        ];
    }

    public function array(): array
    {
        return [
            ['3171012304900001', 'Budi Santoso', 'CBG-JKT', 'Jakarta', '1990-04-23', 'L', 'Jl. Merdeka No. 10, Jakarta Pusat', '081234567890', 'budi@email.com', 'Keuangan', 'Staff', '2020-06-01', 'PEG-001'],
            ['3271012405920002', 'Siti Rahayu', 'CBG-TGR', 'Tangerang', '1992-05-24', 'P', 'Jl. Gatot Subroto No. 5, Tangerang', '081234567891', 'siti@email.com', 'HRD', 'Staff', '2021-03-15', 'PEG-002'],
            ['3171012506930003', 'Ahmad Fauzi', 'CBG-BKS', 'Bekasi', '1993-06-25', 'L', 'Perumahan Grand Wisata Blok A1, Bekasi', '081234567892', 'ahmad@email.com', 'IT', 'Supervisor', '2019-01-10', 'PEG-003'],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $lastCol = 'M';
        $lastRow = 4;

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
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->freezePane('A2');

                $lastRow = $sheet->getHighestRow();
                for ($i = 2; $i <= $lastRow; $i++) {
                    $val = $sheet->getCell("A{$i}")->getValue();
                    if (is_numeric($val)) {
                        $sheet->setCellValueExplicit("A{$i}", (string) $val, DataType::TYPE_STRING);
                    }
                }
            },
        ];
    }
}
