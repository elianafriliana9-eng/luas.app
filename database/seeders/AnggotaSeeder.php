<?php

namespace Database\Seeders;

use App\Models\Anggota;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AnggotaSeeder extends Seeder
{
    private array $departemenList = [
        'Produksi', 'Marketing', 'Finance', 'HRD', 'IT', 'Operasional', 'Purchasing', 'Warehouse',
    ];

    private array $jabatanList = [
        'Staff', 'Senior Staff', 'Supervisor', 'Asisten Manager', 'Manager',
        'Koordinator', 'Admin', 'Operator', 'Teknisi', 'Analyst',
    ];

    public function run(): void
    {
        // Demo accounts first (the ones from CLAUDE.md)
        $this->createDemoAccounts();

        // Create additional employees
        for ($i = 3; $i <= 50; $i++) {
            $this->createEmployee($i);
        }
    }

    private function createDemoAccounts(): void
    {
        $cabangIds = \App\Models\Cabang::pluck('id')->toArray();
        $cabangPusat = \App\Models\Cabang::where('kode', 'CBG-JKT')->first();
        $cabangId = $cabangPusat ? $cabangPusat->id : ($cabangIds[0] ?? null);

        // Budi Santoso — Demo account 1
        Anggota::updateOrCreate(
            ['nik' => '3171012304900001'],
            [
                'cabang_id' => $cabangId,
                'no_anggota' => 'ANG-2023-001',
                'nama_lengkap' => 'Budi Santoso',
                'tempat_lahir' => 'Jakarta',
                'tanggal_lahir' => '1990-04-23',
                'jenis_kelamin' => 'L',
                'alamat' => 'Jl. Merdeka No. 45, Jakarta Selatan',
                'no_hp' => '081234567890',
                'email' => 'budi.santoso@kopsaku.com',
                'gaji_pokok' => 8500000,
                'tanggal_gajian' => 25,
                'departemen' => 'Produksi',
                'jabatan' => 'Supervisor',
                'tanggal_mulai_kerja' => '2020-03-15',
                'no_pegawai' => 'EMP-2020-0045',
                'status' => 'aktif',
                'tanggal_masuk' => '2023-01-15',
                'password' => Hash::make('123456'),
            ]
        );

        // Siti Rahayu — Demo account 2
        Anggota::updateOrCreate(
            ['nik' => '3271012405920002'],
            [
                'cabang_id' => $cabangId,
                'no_anggota' => 'ANG-2023-002',
                'nama_lengkap' => 'Siti Rahayu',
                'tempat_lahir' => 'Tangerang',
                'tanggal_lahir' => '1992-05-24',
                'jenis_kelamin' => 'P',
                'alamat' => 'Jl. Sudirman No. 12, Tangerang',
                'no_hp' => '082345678901',
                'email' => 'siti.rahayu@kopsaku.com',
                'gaji_pokok' => 7200000,
                'tanggal_gajian' => 25,
                'departemen' => 'Finance',
                'jabatan' => 'Senior Staff',
                'tanggal_mulai_kerja' => '2021-06-01',
                'no_pegawai' => 'EMP-2021-0112',
                'status' => 'aktif',
                'tanggal_masuk' => '2023-02-01',
                'password' => Hash::make('123456'),
            ]
        );
    }

    private function createEmployee(int $index): void
    {
        $cabangIds = \App\Models\Cabang::pluck('id')->toArray();
        if (empty($cabangIds)) return;

        $namaDepan = $this->getRandomNamaDepan($index);
        $namaBelakang = $this->getRandomNamaBelakang($index);
        $namaLengkap = "{$namaDepan} {$namaBelakang}";
        $jenisKelamin = $this->isFemale($index) ? 'P' : 'L';
        $departemen = $this->departemenList[array_rand($this->departemenList)];
        $jabatan = $this->jabatanList[array_rand($this->jabatanList)];
        $tahunMulai = rand(2018, 2024);
        $bulanMulai = rand(1, 12);
        $tanggalMulai = "{$tahunMulai}-{$bulanMulai}-" . str_pad(rand(1, 28), 2, '0', STR_PAD_LEFT);
        $tahunLahir = now()->year - rand(25, 55);
        $bulanLahir = rand(1, 12);
        $nik = sprintf('3171%02d%02d%02d000%d', rand(1, 12), rand(1, 28), $tahunLahir - 2000, $index);

        $baseSalary = match ($jabatan) {
            'Manager' => rand(12000000, 18000000),
            'Asisten Manager' => rand(9000000, 14000000),
            'Supervisor' => rand(7500000, 11000000),
            'Koordinator' => rand(7000000, 10000000),
            'Senior Staff' => rand(6000000, 9000000),
            'Staff' => rand(5000000, 8000000),
            default => rand(4500000, 7000000),
        };

        $tempatLahirList = ['Jakarta', 'Tangerang', 'Bekasi', 'Depok', 'Bogor', 'Bandung', 'Surabaya'];
        $jalanList = ['Merdeka', 'Sudirman', 'Ahmad Yani', 'Gatot Subroto', 'Diponegoro', 'Imam Bonjol'];

        Anggota::create([
            'cabang_id' => $cabangIds[array_rand($cabangIds)],
            'no_anggota' => sprintf('ANG-%d-%03d', now()->year, $index),
            'nik' => $nik,
            'nama_lengkap' => $namaLengkap,
            'tempat_lahir' => $tempatLahirList[array_rand($tempatLahirList)],
            'tanggal_lahir' => sprintf('%d-%02d-%02d', $tahunLahir, $bulanLahir, rand(1, 28)),
            'jenis_kelamin' => $jenisKelamin,
            'alamat' => 'Jl. ' . $jalanList[array_rand($jalanList)] . ' No. ' . rand(1, 200),
            'no_hp' => '08' . rand(1, 9) . rand(10000000, 99999999),
            'email' => strtolower(str_replace(' ', '.', $namaLengkap)) . '@kopsaku.com',
            'gaji_pokok' => $baseSalary,
            'tanggal_gajian' => 25,
            'departemen' => $departemen,
            'jabatan' => $jabatan,
            'tanggal_mulai_kerja' => $tanggalMulai,
            'no_pegawai' => sprintf('EMP-%d-%04d', $tahunMulai, $index * 7),
            'status' => 'aktif',
            'tanggal_masuk' => $tanggalMulai,
            'password' => Hash::make('123456'),
        ]);
    }

    private function getRandomNamaDepan(int $seed): string
    {
        $pria = ['Ahmad', 'Budi', 'Dedi', 'Eko', 'Fajar', 'Gunawan', 'Hendra', 'Iwan', 'Joko', 'Kurniawan', 'Lukman', 'Muhammad', 'Nugroho', 'Oscar', 'Putra', 'Rudi', 'Stefanus', 'Teguh', 'Umar', 'Wahyu', 'Yusuf', 'Zainal'];
        $wanita = ['Ani', 'Bella', 'Citra', 'Dewi', 'Eka', 'Fitri', 'Gita', 'Hana', 'Indah', 'Julia', 'Kartika', 'Lina', 'Maya', 'Nia', 'Olivia', 'Putri', 'Rina', 'Sari', 'Tina', 'Umi', 'Vina', 'Wulan', 'Yuni', 'Zulaikha'];
        return $this->isFemale($seed)
            ? $wanita[$seed % count($wanita)]
            : $pria[$seed % count($pria)];
    }

    private function getRandomNamaBelakang(int $seed): string
    {
        $names = ['Saputra', 'Wibowo', 'Prasetyo', 'Hidayat', 'Susanto', 'Kurniawan', 'Setiawan', 'Wicaksono', 'Purnomo', 'Hermawan', 'Suryadi', 'Utama', 'Firmansyah', 'Rahardjo', 'Subagyo', 'Wijaya', 'Nugraha', 'Santoso', 'Pambudi', 'Laksono'];
        return $names[$seed % count($names)];
    }

    private function isFemale(int $seed): bool
    {
        return ($seed % 2) === 0;
    }
}
