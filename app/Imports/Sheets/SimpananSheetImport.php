<?php

namespace App\Imports\Sheets;

use App\Models\Anggota;
use App\Models\ProdukSimpanan;
use App\Models\RekeningSimpanan;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class SimpananSheetImport implements ToCollection, WithStartRow, SkipsEmptyRows
{
    private int $startRow;
    private array $hasil = [];
    private static array $produkCache = [];

    public function __construct(int $startRow = 3)
    {
        $this->startRow = $startRow;
    }

    public function startRow(): int
    {
        return $this->startRow;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $baris = $row[1] ?? null;
            $idAnggota = $row[4] ? (string) $row[4] : '';
            $namaProduk = strtolower(trim($row[6] ?? ''));
            $saldo = (float) str_replace(',', '', $row[8] ?? 0);

            if (empty($idAnggota) || empty($namaProduk)) continue;

            if (!ctype_digit($idAnggota) && !preg_match('/^\d{3}$/', $idAnggota)) continue;

            try {
                $anggota = Anggota::where('no_anggota', $idAnggota)->first();
                if (!$anggota) {
                    $this->hasil[] = ['baris' => $baris, 'nik' => $idAnggota, 'nama' => $idAnggota, 'status' => 'gagal', 'pesan' => "Anggota dengan ID '{$idAnggota}' tidak ditemukan."];
                    continue;
                }

                $produk = $this->findProduk($namaProduk);
                if (!$produk) {
                    continue;
                }

                $rekening = RekeningSimpanan::where('anggota_id', $anggota->id)
                    ->where('produk_id', $produk->id)
                    ->first();

                if ($rekening) {
                    $rekening->update([
                        'saldo' => $saldo,
                        'status' => 'aktif',
                    ]);
                } else {
                    RekeningSimpanan::create([
                        'anggota_id' => $anggota->id,
                        'produk_id' => $produk->id,
                        'no_rekening' => RekeningSimpanan::generateNoRekening($produk, $anggota->cabang),
                        'saldo' => $saldo,
                        'status' => 'aktif',
                        'tanggal_buka' => now()->format('Y-m-d'),
                    ]);
                }

                $this->hasil[] = ['baris' => $baris, 'nik' => $anggota->nik, 'nama' => $anggota->nama_lengkap, 'status' => 'berhasil', 'pesan' => "Saldo: Rp " . number_format($saldo, 0, ',', '.')];
            } catch (\Throwable $e) {
                $this->hasil[] = ['baris' => $baris, 'nik' => $idAnggota, 'nama' => $idAnggota, 'status' => 'gagal', 'pesan' => $e->getMessage()];
            }
        }
    }

    public function getHasil(): array
    {
        return $this->hasil;
    }

    private function findProduk(string $nama): ?ProdukSimpanan
    {
        $key = $nama;

        if (!isset(self::$produkCache[$key])) {
            $produk = ProdukSimpanan::where('nama', 'like', "%{$nama}%")->where('aktif', true)->first();

            if (!$produk) {
                $map = [
                    'simpanan pokok' => 'SIMPOK',
                    'pokok' => 'SIMPOK',
                    'simpanan wajib' => 'SIMWA',
                    'wajib' => 'SIMWA',
                    'simpanan sukarela' => 'SIMSUKA',
                    'sukarela' => 'SIMSUKA',
                ];
                $kode = $map[$key] ?? null;
                if ($kode) {
                    $produk = ProdukSimpanan::where('kode', $kode)->where('aktif', true)->first();
                }
            }

            self::$produkCache[$key] = $produk;
        }

        return self::$produkCache[$key];
    }
}
