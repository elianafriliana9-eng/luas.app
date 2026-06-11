<?php

namespace App\Exports\Simpanan;

use App\Models\TransaksiSimpanan;
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

class StatementExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithTitle
{
    protected string $rekeningId;
    protected array $filters;

    public function __construct(string $rekeningId, array $filters = [])
    {
        $this->rekeningId = $rekeningId;
        $this->filters = $filters;
    }

    public function title(): string
    {
        return 'Statement';
    }

    public function query()
    {
        $query = TransaksiSimpanan::where('rekening_id', $this->rekeningId)
            ->orderBy('created_at');

        if (!empty($this->filters['from'])) {
            $query->whereDate('created_at', '>=', $this->filters['from']);
        }
        if (!empty($this->filters['to'])) {
            $query->whereDate('created_at', '<=', $this->filters['to']);
        }

        return $query;
    }

    public function headings(): array
    {
        return ['Tanggal', 'No. Transaksi', 'Jenis', 'Keterangan', 'Debet (+)', 'Kredit (-)', 'Saldo'];
    }

    public function map($trx): array
    {
        $isDebit = in_array($trx->jenis, ['setoran', 'pinbuk_masuk', 'bunga']);

        return [
            $trx->created_at->format('d/m/Y H:i'),
            $trx->no_transaksi,
            $trx->label_jenis,
            $trx->keterangan ?? '',
            $isDebit ? (float) $trx->nominal : '',
            !$isDebit ? (float) $trx->nominal : '',
            (float) $trx->saldo_sesudah,
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

        $sheet->getStyle("E2:G{$lastRow}")->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle("B2:B{$lastRow}")->getFont()->setName('Consolas');
    }
}
