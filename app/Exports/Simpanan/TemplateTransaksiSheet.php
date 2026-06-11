<?php

namespace App\Exports\Simpanan;

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
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;

class TemplateTransaksiSheet implements FromArray, WithHeadings, WithStyles, ShouldAutoSize, WithEvents
{
    public function headings(): array
    {
        return [
            'No. Rekening',
            'Jenis',
            'Nominal',
            'Keterangan',
        ];
    }

    public function array(): array
    {
        return [
            ['REK-SP-ANG-0001', 'setoran', 500000, 'Setoran bulan Januari'],
            ['REK-SS-ANG-0002', 'setoran', 250000, 'Setoran sukarela'],
            ['REK-SW-ANG-0003', 'penarikan', 100000, 'Penarikan tunai'],
            ['REK-SP-ANG-0004', 'pinbuk_masuk', 300000, 'Pinbuk dari rekening SS'],
            ['REK-SS-ANG-0005', 'pinbuk_keluar', 150000, 'Pinbuk ke rekening SP'],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $lastCol = 'D';

        $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A5F']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

        $sheet->getStyle("A2:D6")->applyFromArray([
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D1D5DB']],
            ],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ]);

        $sheet->getStyle("C2:C6")->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle("C2:C6")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        $sheet->getStyle("A2:A6")->getFont()->setName('Consolas');
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->freezePane('A2');

                $validation = $sheet->getCell('B2')->getDataValidation();
                $validation->setType(DataValidation::TYPE_LIST);
                $validation->setFormula1('"setoran,penarikan,pinbuk_masuk,pinbuk_keluar"');
                $validation->setShowDropDown(true);
                $validation->setShowInputMessage(true);
                $validation->setPromptTitle('Pilih Jenis');
                $validation->setPrompt('Pilih jenis transaksi: setoran, penarikan, pinbuk_masuk, atau pinbuk_keluar');

                $sheet->getStyle("A2:A6")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

                $sheet->getColumnDimension('A')->setWidth(28);
                $sheet->getColumnDimension('B')->setWidth(18);
                $sheet->getColumnDimension('C')->setWidth(18);
                $sheet->getColumnDimension('D')->setWidth(35);
            },
        ];
    }
}
