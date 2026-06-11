<?php

namespace App\Exports\Anggota;

use App\Models\Anggota;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class RekapAnggotaExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize, WithTitle
{
    public function title(): string
    {
        return 'Rekap Anggota';
    }

    public function headings(): array
    {
        return [
            'Kategori',
            'Item',
            'Jumlah',
        ];
    }

    public function array(): array
    {
        $totalAnggota = Anggota::count();
        $anggotaAktif = Anggota::where('status', 'aktif')->count();
        $anggotaKeluar = Anggota::where('status', 'keluar')->count();
        $totalSimpanan = Anggota::sum(function ($a) {
            return $a->rekeningSimpanan->sum('saldo');
        });

        $perCabang = Anggota::selectRaw('cabang_id, COUNT(*) as total, SUM(CASE WHEN status = "aktif" THEN 1 ELSE 0 END) as aktif')
            ->groupBy('cabang_id')
            ->with('cabang')
            ->get();

        $perDepartemen = Anggota::selectRaw('departemen, COUNT(*) as total')
            ->whereNotNull('departemen')
            ->groupBy('departemen')
            ->orderByDesc('total')
            ->get();

        $rows = [];
        $rows[] = ['', '', ''];

        // Ringkasan
        $rows[] = ['RINGKASAN', '', ''];
        $rows[] = ['Total Anggota', '', (string) $totalAnggota];
        $rows[] = ['Anggota Aktif', '', (string) $anggotaAktif];
        $rows[] = ['Anggota Keluar', '', (string) $anggotaKeluar];
        $rows[] = ['Total Simpanan', '', $totalSimpanan];

        $rows[] = ['', '', ''];

        // Per Cabang
        $rows[] = ['REKAP PER CABANG', '', ''];
        foreach ($perCabang as $c) {
            $rows[] = [$c->cabang?->nama ?? '-', 'Total: ' . $c->total, 'Aktif: ' . $c->aktif];
        }

        $rows[] = ['', '', ''];

        // Per Departemen
        $rows[] = ['REKAP PER DEPARTEMEN', '', ''];
        foreach ($perDepartemen as $d) {
            $rows[] = [$d->departemen ?? '-', '', (string) $d->total];
        }

        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();

        $sheet->getStyle("A1:C1")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A5F']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

        $sheet->getStyle("A2:C{$lastRow}")->applyFromArray([
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D1D5DB']],
            ],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ]);

        $sheet->getStyle("C2:C{$lastRow}")->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle("A2:A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
    }
}
