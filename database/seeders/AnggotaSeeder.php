<?php

namespace Database\Seeders;

use App\Models\Anggota;
use App\Models\Cabang;
use App\Models\Perusahaan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AnggotaSeeder extends Seeder
{
    public function run(): void
    {
        $cabangJkt = Cabang::where('kode', 'CBG-JKT')->first()->id;
        $cabangTgr = Cabang::where('kode', 'CBG-TGR')->first()->id;
        $cabangBks = Cabang::where('kode', 'CBG-BKS')->first()->id;

        $ptLas = Perusahaan::where('kode', 'PT-LAS')->first()->id;
        $ptKmi = Perusahaan::where('kode', 'PT-KMI')->first()->id;
        $ptBsm = Perusahaan::where('kode', 'PT-BSM')->first()->id;

        $data = [
            // ========== PT Lumbung Artha Sejahtera — Jakarta ==========
            [
                'cabang_id' => $cabangJkt, 'no_anggota' => 'ANG-2023-001',
                'nik' => '3171012304900001', 'nama_lengkap' => 'Budi Santoso',
                'tempat_lahir' => 'Jakarta', 'tanggal_lahir' => '1990-04-23',
                'jenis_kelamin' => 'L',
                'alamat' => 'Jl. Merdeka No. 45, RT 05 RW 03, Kel. Menteng, Jakarta Pusat',
                'no_hp' => '081234567890', 'email' => 'budi.santoso@lumbungartha.co.id',
                'gaji_pokok' => 8500000, 'tanggal_gajian' => 25,
                'perusahaan_id' => $ptLas,
                'tanggal_mulai_kerja' => '2018-03-15', 'no_pegawai' => 'LAS-2018-0045',
                'status' => 'aktif', 'tanggal_masuk' => '2023-01-15',
                'password' => Hash::make('123456'),
            ],
            [
                'cabang_id' => $cabangJkt, 'no_anggota' => 'ANG-2023-002',
                'nik' => '3271012405920002', 'nama_lengkap' => 'Siti Rahayu',
                'tempat_lahir' => 'Tangerang', 'tanggal_lahir' => '1992-05-24',
                'jenis_kelamin' => 'P',
                'alamat' => 'Jl. Sudirman No. 12, RT 02 RW 01, Kel. Ciputat, Tangerang Selatan',
                'no_hp' => '082345678901', 'email' => 'siti.rahayu@lumbungartha.co.id',
                'gaji_pokok' => 7200000, 'tanggal_gajian' => 25,
                'perusahaan_id' => $ptLas,
                'tanggal_mulai_kerja' => '2019-06-01', 'no_pegawai' => 'LAS-2019-0112',

                'status' => 'aktif', 'tanggal_masuk' => '2023-02-01',
                'password' => Hash::make('123456'),
            ],
            [
                'cabang_id' => $cabangJkt, 'no_anggota' => 'ANG-2024-003',
                'nik' => '3172020505880003', 'nama_lengkap' => 'Ahmad Fauzi',
                'tempat_lahir' => 'Jakarta', 'tanggal_lahir' => '1988-05-05',
                'jenis_kelamin' => 'L',
                'alamat' => 'Jl. Rasuna Said Kav. 8, RT 03 RW 02, Kel. Kuningan, Jakarta Selatan',
                'no_hp' => '085611223344', 'email' => 'ahmad.fauzi@lumbungartha.co.id',
                'gaji_pokok' => 15000000, 'tanggal_gajian' => 25,
                'perusahaan_id' => $ptLas,
                'tanggal_mulai_kerja' => '2016-01-10', 'no_pegawai' => 'LAS-2016-0012',

                'status' => 'aktif', 'tanggal_masuk' => '2024-01-10',
                'password' => Hash::make('123456'),
            ],
            [
                'cabang_id' => $cabangJkt, 'no_anggota' => 'ANG-2024-004',
                'nik' => '3273021507920004', 'nama_lengkap' => 'Dewi Sartika',
                'tempat_lahir' => 'Bandung', 'tanggal_lahir' => '1992-07-15',
                'jenis_kelamin' => 'P',
                'alamat' => 'Jl. Gatot Subroto No. 27, RT 08 RW 04, Kel. Karet Semanggi, Jakarta Selatan',
                'no_hp' => '087812345678', 'email' => 'dewi.sartika@lumbungartha.co.id',
                'gaji_pokok' => 5500000, 'tanggal_gajian' => 25,
                'perusahaan_id' => $ptLas,
                'tanggal_mulai_kerja' => '2021-08-20', 'no_pegawai' => 'LAS-2021-0256',

                'status' => 'aktif', 'tanggal_masuk' => '2024-02-15',
                'password' => Hash::make('123456'),
            ],
            [
                'cabang_id' => $cabangJkt, 'no_anggota' => 'ANG-2024-005',
                'nik' => '3175041206930005', 'nama_lengkap' => 'Rudi Hermawan',
                'tempat_lahir' => 'Surabaya', 'tanggal_lahir' => '1993-06-12',
                'jenis_kelamin' => 'L',
                'alamat' => 'Jl. TB Simatupang No. 15, RT 01 RW 06, Kel. Cilandak, Jakarta Selatan',
                'no_hp' => '081998877665', 'email' => 'rudi.hermawan@lumbungartha.co.id',
                'gaji_pokok' => 9000000, 'tanggal_gajian' => 25,
                'perusahaan_id' => $ptLas,
                'tanggal_mulai_kerja' => '2019-11-01', 'no_pegawai' => 'LAS-2019-0089',
                'status' => 'aktif', 'tanggal_masuk' => '2024-03-01',
                'password' => Hash::make('123456'),
            ],

            // ========== PT Karya Mandiri Indonesia — Tangerang ==========
            [
                'cabang_id' => $cabangTgr, 'no_anggota' => 'ANG-2024-006',
                'nik' => '3671021807910006', 'nama_lengkap' => 'Kurniawan Saputra',
                'tempat_lahir' => 'Tangerang', 'tanggal_lahir' => '1991-07-18',
                'jenis_kelamin' => 'L',
                'alamat' => 'Jl. BSD Raya No. 21, BSD City, Kec. Serpong, Tangerang Selatan',
                'no_hp' => '081298765432', 'email' => 'kurniawan.saputra@karyamandiri.co.id',
                'gaji_pokok' => 16000000, 'tanggal_gajian' => 25,
                'perusahaan_id' => $ptKmi,
                'tanggal_mulai_kerja' => '2017-02-20', 'no_pegawai' => 'KMI-2017-0023',
                'status' => 'aktif', 'tanggal_masuk' => '2024-06-01',
                'password' => Hash::make('123456'),
            ],
            [
                'cabang_id' => $cabangTgr, 'no_anggota' => 'ANG-2024-007',
                'nik' => '3672041508950007', 'nama_lengkap' => 'Linda Kusuma',
                'tempat_lahir' => 'Tangerang', 'tanggal_lahir' => '1995-08-15',
                'jenis_kelamin' => 'P',
                'alamat' => 'Jl. Gading Serpong No. 8, Kec. Kelapa Dua, Tangerang',
                'no_hp' => '085712345680', 'email' => 'linda.kusuma@karyamandiri.co.id',
                'gaji_pokok' => 7500000, 'tanggal_gajian' => 25,
                'perusahaan_id' => $ptKmi,
                'tanggal_mulai_kerja' => '2020-03-01', 'no_pegawai' => 'KMI-2020-0088',
                'status' => 'aktif', 'tanggal_masuk' => '2024-06-15',
                'password' => Hash::make('123456'),
            ],
            [
                'cabang_id' => $cabangTgr, 'no_anggota' => 'ANG-2024-008',
                'nik' => '3673011406900008', 'nama_lengkap' => 'Hendra Gunawan',
                'tempat_lahir' => 'Tangerang', 'tanggal_lahir' => '1990-06-14',
                'jenis_kelamin' => 'L',
                'alamat' => 'Jl. Alam Sutera No. 17, Kec. Pinang, Tangerang',
                'no_hp' => '087898765432', 'email' => 'hendra.gunawan@karyamandiri.co.id',
                'gaji_pokok' => 6500000, 'tanggal_gajian' => 25,
                'perusahaan_id' => $ptKmi,
                'tanggal_mulai_kerja' => '2021-01-15', 'no_pegawai' => 'KMI-2021-0145',
                'status' => 'aktif', 'tanggal_masuk' => '2024-07-01',
                'password' => Hash::make('123456'),
            ],
            [
                'cabang_id' => $cabangTgr, 'no_anggota' => 'ANG-2024-009',
                'nik' => '3674050305970009', 'nama_lengkap' => 'Nina Kurnia',
                'tempat_lahir' => 'Bogor', 'tanggal_lahir' => '1997-05-03',
                'jenis_kelamin' => 'P',
                'alamat' => 'Jl. Villa Melati Mas No. 5, Kec. Cikupa, Tangerang',
                'no_hp' => '081234567892', 'email' => 'nina.kurnia@karyamandiri.co.id',
                'gaji_pokok' => 5300000, 'tanggal_gajian' => 25,
                'perusahaan_id' => $ptKmi,
                'tanggal_mulai_kerja' => '2022-12-01', 'no_pegawai' => 'KMI-2022-0311',

                'status' => 'aktif', 'tanggal_masuk' => '2024-07-15',
                'password' => Hash::make('123456'),
            ],
            [
                'cabang_id' => $cabangTgr, 'no_anggota' => 'ANG-2024-010',
                'nik' => '3675011911920010', 'nama_lengkap' => 'Agus Supriyadi',
                'tempat_lahir' => 'Tangerang', 'tanggal_lahir' => '1992-11-19',
                'jenis_kelamin' => 'L',
                'alamat' => 'Jl. Raya Serpong No. 25, Kec. Serpong Utara, Tangerang Selatan',
                'no_hp' => '081223344556', 'email' => 'agus.supriyadi@karyamandiri.co.id',
                'gaji_pokok' => 4800000, 'tanggal_gajian' => 25,
                'perusahaan_id' => $ptKmi,
                'tanggal_mulai_kerja' => '2023-02-01', 'no_pegawai' => 'KMI-2023-0367',
                'status' => 'aktif', 'tanggal_masuk' => '2024-08-01',
                'password' => Hash::make('123456'),
            ],

            // ========== PT Bumi Sejahtera Makmur — Bekasi ==========
            [
                'cabang_id' => $cabangBks, 'no_anggota' => 'ANG-2024-011',
                'nik' => '3275010107870011', 'nama_lengkap' => 'Teguh Setiawan',
                'tempat_lahir' => 'Bekasi', 'tanggal_lahir' => '1987-01-01',
                'jenis_kelamin' => 'L',
                'alamat' => 'Jl. Pejuang No. 10, Kec. Medan Satria, Bekasi',
                'no_hp' => '085612345679', 'email' => 'teguh.setiawan@bumisejahtera.co.id',
                'gaji_pokok' => 14000000, 'tanggal_gajian' => 25,
                'perusahaan_id' => $ptBsm,
                'tanggal_mulai_kerja' => '2017-07-15', 'no_pegawai' => 'BSM-2017-0034',
                'status' => 'aktif', 'tanggal_masuk' => '2024-10-01',
                'password' => Hash::make('123456'),
            ],
            [
                'cabang_id' => $cabangBks, 'no_anggota' => 'ANG-2024-012',
                'nik' => '3276021806880012', 'nama_lengkap' => 'Zainal Arifin',
                'tempat_lahir' => 'Bekasi', 'tanggal_lahir' => '1988-06-18',
                'jenis_kelamin' => 'L',
                'alamat' => 'Jl. Raya Hankam No. 30, Kec. Pondok Gede, Bekasi',
                'no_hp' => '081234567894', 'email' => 'zainal.arifin@bumisejahtera.co.id',
                'gaji_pokok' => 10000000, 'tanggal_gajian' => 25,
                'perusahaan_id' => $ptBsm,
                'tanggal_mulai_kerja' => '2019-03-01', 'no_pegawai' => 'BSM-2019-0156',
                'status' => 'aktif', 'tanggal_masuk' => '2024-12-01',
                'password' => Hash::make('123456'),
            ],
            [
                'cabang_id' => $cabangBks, 'no_anggota' => 'ANG-2024-013',
                'nik' => '3277011004890013', 'nama_lengkap' => 'Indra Lesmana',
                'tempat_lahir' => 'Jakarta', 'tanggal_lahir' => '1989-04-10',
                'jenis_kelamin' => 'L',
                'alamat' => 'Jl. Warung Buncit No. 19, RT 02 RW 01, Kel. Kalibata, Jakarta Selatan',
                'no_hp' => '081234567891', 'email' => 'indra.lesmana@bumisejahtera.co.id',
                'gaji_pokok' => 9500000, 'tanggal_gajian' => 25,
                'perusahaan_id' => $ptBsm,
                'tanggal_mulai_kerja' => '2020-08-15', 'no_pegawai' => 'BSM-2020-0134',
                'status' => 'aktif', 'tanggal_masuk' => '2024-05-01',
                'password' => Hash::make('123456'),
            ],
            [
                'cabang_id' => $cabangBks, 'no_anggota' => 'ANG-2024-014',
                'nik' => '3278010709960014', 'nama_lengkap' => 'Maya Anggraini',
                'tempat_lahir' => 'Bekasi', 'tanggal_lahir' => '1996-09-07',
                'jenis_kelamin' => 'P',
                'alamat' => 'Jl. Bumi Satria Kencana No. 3, Kec. Bekasi Timur, Bekasi',
                'no_hp' => '082134567892', 'email' => 'maya.anggraini@bumisejahtera.co.id',
                'gaji_pokok' => 5800000, 'tanggal_gajian' => 25,
                'perusahaan_id' => $ptBsm,
                'tanggal_mulai_kerja' => '2022-09-01', 'no_pegawai' => 'BSM-2022-0299',

                'status' => 'aktif', 'tanggal_masuk' => '2024-12-15',
                'password' => Hash::make('123456'),
            ],
            [
                'cabang_id' => $cabangBks, 'no_anggota' => 'ANG-2025-015',
                'nik' => '3279012204860015', 'nama_lengkap' => 'Bambang Sudrajat',
                'tempat_lahir' => 'Tasikmalaya', 'tanggal_lahir' => '1986-04-22',
                'jenis_kelamin' => 'L',
                'alamat' => 'Jl. Satria Raya No. 1, Kec. Bekasi Utara, Bekasi',
                'no_hp' => '085611223355', 'email' => 'bambang.sudrajat@bumisejahtera.co.id',
                'gaji_pokok' => 4500000, 'tanggal_gajian' => 25,
                'perusahaan_id' => $ptBsm,
                'tanggal_mulai_kerja' => '2016-04-01', 'no_pegawai' => 'BSM-2016-0011',
                'status' => 'aktif', 'tanggal_masuk' => '2025-01-01',
                'password' => Hash::make('123456'),
            ],

            // ========== Doni Prasetyo — join 2 bulan lalu, sudah potong 50rb ==========
            [
                'cabang_id' => $cabangJkt, 'no_anggota' => 'ANG-2026-016',
                'nik' => '3171031507010016', 'nama_lengkap' => 'Doni Prasetyo',
                'tempat_lahir' => 'Jakarta', 'tanggal_lahir' => '2001-07-15',
                'jenis_kelamin' => 'L',
                'alamat' => 'Jl. Kebon Jeruk No. 7, RT 01 RW 02, Kel. Kebon Jeruk, Jakarta Barat',
                'no_hp' => '081377889900', 'email' => 'doni.prasetyo@lumbungartha.co.id',
                'gaji_pokok' => 6000000, 'tanggal_gajian' => 25,
                'perusahaan_id' => $ptLas,
                'tanggal_mulai_kerja' => '2026-04-01', 'no_pegawai' => 'LAS-2026-0391',
                'status' => 'aktif', 'tanggal_masuk' => '2026-04-01',
                'password' => Hash::make('123456'),
            ],
        ];

        foreach ($data as $anggota) {
            Anggota::updateOrCreate(['nik' => $anggota['nik']], $anggota);
        }
    }
}
