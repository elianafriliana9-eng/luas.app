<?php

namespace App\Exports\Anggota;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class TemplatePetunjukAnggotaSheet implements FromArray, WithTitle, WithStyles, ShouldAutoSize
{
    public function title(): string
    {
        return 'Petunjuk';
    }

    public function array(): array
    {
        return [
            ['PETUNJUK PENGISIAN TEMPLATE IMPORT ANGGOTA'],
            [''],
            ['Kolom A - NIK'],
            ['Isi dengan NIK (16 digit). Wajib diisi dan harus unik.'],
            ['Contoh: 3171012304900001'],
            [''],
            ['Kolom B - Nama Lengkap'],
            ['Isi dengan nama lengkap anggota. Wajib diisi.'],
            ['Contoh: Budi Santoso'],
            [''],
            ['Kolom C - Kode Cabang'],
            ['Isi dengan kode cabang. Wajib diisi. Cek daftar cabang di sistem.'],
            ['Contoh: CBG-JKT, CBG-TGR, CBG-BKS'],
            [''],
            ['Kolom D - Tempat Lahir (Opsional)'],
            ['Isi dengan tempat lahir anggota.'],
            ['Contoh: Jakarta'],
            [''],
            ['Kolom E - Tanggal Lahir (Opsional)'],
            ['Isi dengan tanggal lahir. Format: YYYY-MM-DD'],
            ['Contoh: 1990-04-23'],
            [''],
            ['Kolom F - Jenis Kelamin (Opsional)'],
            ['Isi L untuk Laki-laki, P untuk Perempuan. Default: L'],
            [''],
            ['Kolom G - Alamat (Opsional)'],
            ['Isi dengan alamat lengkap.'],
            [''],
            ['Kolom H - No. HP (Opsional)'],
            ['Isi dengan nomor handphone.'],
            [''],
            ['Kolom I - Email (Opsional)'],
            ['Isi dengan alamat email.'],
            [''],
            ['Kolom J - Tanggal Mulai Kerja (Opsional)'],
            ['Format: YYYY-MM-DD'],
            [''],
            ['Kolom K - No. Pegawai (Opsional)'],
            ['Isi dengan nomor pegawai/NIP jika ada.'],
            [''],
            ['KETERANGAN PENTING:'],
            ['1. Baris pertama (header) TIDAK BOLEH diubah/hapus'],
            ['2. Data diisi mulai baris kedua dan seterusnya'],
            ['3. File harus berekstensi .xlsx atau .xls'],
            ['4. Maksimal ukuran file 10MB'],
            ['5. Sistem otomatis membuat: Rekening Simpanan dan Jadwal Potongan Gaji'],
            ['6. Password default: 123456'],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '1E3A5F']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E8EEF4']],
        ]);

        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        $boldSections = ['A3:A4', 'A7:A8', 'A11:A12', 'A14:A15', 'A17:A18', 'A20:A21', 'A23:A24', 'A26:A27', 'A29:A30', 'A32:A33', 'A35:A36'];
        foreach ($boldSections as $range) {
            $sheet->getStyle($range)->applyFromArray([
                'font' => ['bold' => true, 'size' => 11],
            ]);
        }

        $sheet->getStyle('A44')->applyFromArray([
            'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => '1E3A5F']],
        ]);

        $sheet->getColumnDimension('A')->setWidth(80);
    }
}
