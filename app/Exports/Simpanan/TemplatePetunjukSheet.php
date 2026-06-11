<?php

namespace App\Exports\Simpanan;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class TemplatePetunjukSheet implements FromArray, WithTitle, WithStyles, ShouldAutoSize
{
    public function title(): string
    {
        return 'Petunjuk';
    }

    public function array(): array
    {
        return [
            ['PETUNJUK PENGISIAN TEMPLATE IMPORT TRANSAKSI'],
            [''],
            ['Kolom A - No. Rekening'],
            ['Isi dengan nomor rekening tujuan. Pastikan rekening sudah terdaftar di sistem.'],
            ['Contoh: REK-SP-ANG-0001'],
            [''],
            ['Kolom B - Jenis Transaksi'],
            ['Pilih salah satu: setoran, penarikan, pinbuk_masuk, pinbuk_keluar'],
            ['setoran       = Setoran / penyetoran uang ke rekening'],
            ['penarikan     = Penarikan / pengambilan uang dari rekening'],
            ['pinbuk_masuk  = Pemindahbukuan masuk (diterima dari rekening lain)'],
            ['pinbuk_keluar = Pemindahbukuan keluar (dikirim ke rekening lain)'],
            [''],
            ['Kolom C - Nominal'],
            ['Isi dengan jumlah nominal dalam Rupiah (angka saja, tanpa titik/koma).'],
            ['Minimal: Rp 1.000'],
            ['Contoh: 500000 (untuk Rp 500.000)'],
            [''],
            ['Kolom D - Keterangan (Opsional)'],
            ['Isi dengan deskripsi transaksi. Boleh dikosongkan.'],
            ['Contoh: Setoran bulan Januari 2026'],
            [''],
            ['KETERANGAN PENTING:'],
            ['1. Baris pertama (header) TIDAK BOLEH diubah/hapus'],
            ['2. Data diisi mulai baris kedua dan seterusnya'],
            ['3. File harus berekstensi .xlsx atau .xls'],
            ['4. Maksimal ukuran file 10MB'],
            ['5. Saldo akan otomatis terupdate setelah import'],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '1E3A5F']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E8EEF4']],
        ]);

        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        $sheet->getStyle('A3:A4')->applyFromArray([
            'font' => ['bold' => true, 'size' => 11],
        ]);

        $sheet->getStyle('A6:A7')->applyFromArray([
            'font' => ['bold' => true, 'size' => 11],
        ]);

        $sheet->getStyle('A14:A16')->applyFromArray([
            'font' => ['bold' => true, 'size' => 11],
        ]);

        $sheet->getStyle('A18:A19')->applyFromArray([
            'font' => ['bold' => true, 'size' => 11],
        ]);

        $sheet->getStyle('A21')->applyFromArray([
            'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => '1E3A5F']],
        ]);

        $sheet->getColumnDimension('A')->setWidth(80);
    }
}
