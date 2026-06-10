<?php

namespace Database\Seeders;

use App\Models\Cabang;
use App\Models\ChartOfAccount;
use App\Models\Jurnal;
use App\Models\JurnalDetail;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Carbon\Carbon;

class JurnalSeeder extends Seeder
{
    public function run(): void
    {
        $cabang = Cabang::first();
        $user = User::first();
        if (!$cabang || !$user) return;

        // Get commonly used accounts
        $kas = ChartOfAccount::where('kode_akun', '11010')->first();
        $simpananPokok = ChartOfAccount::where('kode_akun', '21010')->first();
        $pendapatanAdmin = ChartOfAccount::where('kode_akun', '42010')->first();
        
        $bank = ChartOfAccount::where('kode_akun', '12010')->first();
        $piutangPembiayaan = ChartOfAccount::where('kode_akun', '13010')->first();

        // 1. Transaction: Modal Awal Kas Teller (from Bank)
        if ($kas && $bank) {
            $jurnal1 = Jurnal::create([
                'id' => Str::uuid(),
                'cabang_id' => $cabang->id,
                'no_jurnal' => 'JU-' . date('Ymd') . '-001',
                'tanggal' => Carbon::now()->subDays(5)->format('Y-m-d'),
                'keterangan' => 'Tarik Tunai dari Bank Mandiri untuk Kas Teller',
                'jenis' => 'manual',
                'dibuat_oleh' => $user->id,
            ]);

            JurnalDetail::create([
                'id' => Str::uuid(),
                'jurnal_id' => $jurnal1->id,
                'akun_id' => $kas->id,
                'debet' => 100000000, // 100jt
                'kredit' => 0,
                'keterangan' => 'Kas bertambah'
            ]);

            JurnalDetail::create([
                'id' => Str::uuid(),
                'jurnal_id' => $jurnal1->id,
                'akun_id' => $bank->id,
                'debet' => 0,
                'kredit' => 100000000, 
                'keterangan' => 'Bank berkurang'
            ]);
        }

        // 2. Transaction: Setoran Simpanan Anggota Baru
        if ($kas && $simpananPokok && $pendapatanAdmin) {
            $jurnal2 = Jurnal::create([
                'id' => Str::uuid(),
                'cabang_id' => $cabang->id,
                'no_jurnal' => 'JU-' . date('Ymd') . '-002',
                'tanggal' => Carbon::now()->subDays(2)->format('Y-m-d'),
                'keterangan' => 'Setoran Simpanan Pokok Anggota Baru Budi',
                'jenis' => 'otomatis',
                'dibuat_oleh' => $user->id,
            ]);

            // Total uang masuk ke kas: 1.050.000 (1jt POKOK + 50k Admin)
            JurnalDetail::create([
                'id' => Str::uuid(),
                'jurnal_id' => $jurnal2->id,
                'akun_id' => $kas->id,
                'debet' => 1050000,
                'kredit' => 0,
                'keterangan' => 'Penerimaan tunai'
            ]);

            JurnalDetail::create([
                'id' => Str::uuid(),
                'jurnal_id' => $jurnal2->id,
                'akun_id' => $simpananPokok->id,
                'debet' => 0,
                'kredit' => 1000000,
                'keterangan' => 'Kewajiban simpanan bertambah'
            ]);

            JurnalDetail::create([
                'id' => Str::uuid(),
                'jurnal_id' => $jurnal2->id,
                'akun_id' => $pendapatanAdmin->id,
                'debet' => 0,
                'kredit' => 50000,
                'keterangan' => 'Biaya administrasi anggota baru'
            ]);
        }
    }
}
