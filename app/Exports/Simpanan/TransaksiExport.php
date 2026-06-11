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

class TransaksiExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithTitle, WithEvents
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function title(): string
    {
        return 'Transaksi Simpanan';
    }

    public function query()
    {
        $query = TransaksiSimpanan::with(['rekening.anggota', 'rekening.produk']);

        if (!empty($this->filters['jenis_simpanan'])) {
            $query->whereHas('rekening.produk', fn($q) => $q->where('jenis', $this->filters['jenis_simpanan']));
        }
        if (!empty($this->filters['jenis'])) {
            $query->where('jenis', $this->filters['jenis']);
        }
        if (!empty($this->filters['status'])) {
            if ($this->filters['status'] === 'dibatalkan') {
                $query->where('dibatalkan', true);
            } else {
                $query->where('dibatalkan', false)->where('status_approval', $this->filters['status']);
            }
        }
        if (!empty($this->filters['search'])) {
            $s = $this->filters['search'];
            $query->where(function ($q) use ($s) {
                $q->where('no_transaksi', 'like', "%{$s}%")
                  ->orWhereHas('rekening.anggota', fn($q2) => $q2->where('nama_lengkap', 'like', "%{$s}%"));
            });
        }
        if (!empty($this->filters['from'])) {
            $query->whereDate('created_at', '>=', $this->filters['from']);
        }
        if (!empty($this->filters['to'])) {
            $query->whereDate('created_at', '<=', $this->filters['to']);
        }

        return $query->latest('created_at');
    }

    public function headings(): array
    {
        return [
            'No. Transaksi',
            'Tanggal',
            'Anggota',
            'No. Anggota',
            'Rekening',
            'Jenis',
            'Nominal',
            'Saldo Sebelum',
            'Saldo Sesudah',
            'Keterangan',
            'Status',
        ];
    }

    public function map($trx): array
    {
        $jenisMap = [
            'setoran' => 'Setoran',
            'penarikan' => 'Penarikan',
            'pinbuk_masuk' => 'Pinbuk Masuk',
            'pinbuk_keluar' => 'Pinbuk Keluar',
            'bunga' => 'Bunga',
            'koreksi' => 'Koreksi',
        ];

        $status = $trx->dibatalkan ? 'Dibatalkan' : match ($trx->status_approval) {
            'pending' => 'Pending',
            'rejected' => 'Ditolak',
            default => 'Disetujui',
        };

        return [
            $trx->no_transaksi,
            $trx->created_at->format('d/m/Y H:i'),
            $trx->rekening?->anggota?->nama_lengkap ?? '-',
            $trx->rekening?->anggota?->no_anggota ?? '-',
            $trx->rekening?->no_rekening ?? '-',
            $jenisMap[$trx->jenis] ?? $trx->jenis,
            (float) $trx->nominal,
            (float) $trx->saldo_sebelum,
            (float) $trx->saldo_sesudah,
            $trx->keterangan ?? '-',
            $status,
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

        $sheet->getStyle("G2:H{$lastRow}")->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle("G2:H{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle("I2:I{$lastRow}")->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle("I2:I{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        $sheet->getStyle("A2:A{$lastRow}")->getFont()->setName('Consolas');
        $sheet->getStyle("D2:D{$lastRow}")->getFont()->setName('Consolas');
        $sheet->getStyle("E2:E{$lastRow}")->getFont()->setName('Consolas');
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();
                $lastCol = 'K';

                $sheet->freezePane('A2');
                $sheet->setAutoFilter("A1:{$lastCol}{$lastRow}");
            },
        ];
    }
}
