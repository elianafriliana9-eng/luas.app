<?php

namespace Database\Seeders;

use App\Models\Anggota;
use App\Models\Cabang;
use App\Models\ProdukSimpanan;
use App\Models\RekeningSimpanan;
use Illuminate\Database\Seeder;

class RekeningSimpananSeeder extends Seeder
{
    public function run(): void
    {
        $produk = ProdukSimpanan::pluck('id', 'kode');
        $cabang = Cabang::pluck('id', 'kode');

        // Format: PPBBSSSSS — 9 digit numeric
        // PP=produk (01=POKOK,02=WAJIB,03=SUKARELA), BB=cabang, SSSSS=urutan
        $noRekening = [
            // ===== Jakarta (CBG-JKT) =====
            // POKOK
            'ANG-2023-001' => ['pokok' => '010100001', 'wajib' => '020100001', 'sukarela' => '030100001'],
            'ANG-2023-002' => ['pokok' => '010100002', 'wajib' => '020100002', 'sukarela' => '030100002'],
            'ANG-2024-003' => ['pokok' => '010100003', 'wajib' => '020100003', 'sukarela' => '030100003'],
            'ANG-2024-004' => ['pokok' => '010100004', 'wajib' => '020100004', 'sukarela' => '030100004'],
            'ANG-2024-005' => ['pokok' => '010100005', 'wajib' => '020100005', 'sukarela' => '030100005'],
            // ===== Tangerang (CBG-TGR) =====
            'ANG-2024-006' => ['pokok' => '010200001', 'wajib' => '020200001', 'sukarela' => '030200001'],
            'ANG-2024-007' => ['pokok' => '010200002', 'wajib' => '020200002', 'sukarela' => '030200002'],
            'ANG-2024-008' => ['pokok' => '010200003', 'wajib' => '020200003', 'sukarela' => '030200003'],
            'ANG-2024-009' => ['pokok' => '010200004', 'wajib' => '020200004', 'sukarela' => null],
            'ANG-2024-010' => ['pokok' => '010200005', 'wajib' => '020200005', 'sukarela' => '030200004'],
            // ===== Bekasi (CBG-BKS) =====
            'ANG-2024-011' => ['pokok' => '010300001', 'wajib' => '020300001', 'sukarela' => '030300001'],
            'ANG-2024-012' => ['pokok' => '010300002', 'wajib' => '020300002', 'sukarela' => '030300002'],
            'ANG-2024-013' => ['pokok' => '010300003', 'wajib' => '020300003', 'sukarela' => '030300003'],
            'ANG-2024-014' => ['pokok' => '010300004', 'wajib' => '020300004', 'sukarela' => '030300004'],
            'ANG-2025-015' => ['pokok' => '010300005', 'wajib' => '020300005', 'sukarela' => null],
            // ===== Doni — Jakarta, join Apr 2026 =====
            'ANG-2026-016' => ['pokok' => '010100006', 'wajib' => '020100006', 'sukarela' => null],
        ];

        $saldoSukarela = [
            'ANG-2023-001' => 5000000,   // Budi Santoso
            'ANG-2023-002' => 3000000,   // Siti Rahayu
            'ANG-2024-003' => 25000000,  // Ahmad Fauzi
            'ANG-2024-004' => 1000000,   // Dewi Sartika
            'ANG-2024-005' => 7500000,   // Rudi Hermawan
            'ANG-2024-006' => 30000000,  // Kurniawan Saputra
            'ANG-2024-007' => 5000000,   // Linda Kusuma
            'ANG-2024-008' => 2000000,   // Hendra Gunawan
            'ANG-2024-010' => 500000,    // Agus Supriyadi
            'ANG-2024-011' => 20000000,  // Teguh Setiawan
            'ANG-2024-012' => 10000000,  // Zainal Arifin
            'ANG-2024-013' => 8000000,   // Indra Lesmana
            'ANG-2024-014' => 1500000,   // Maya Anggraini
        ];

        foreach ($noRekening as $noAnggota => $rek) {
            $anggota = Anggota::where('no_anggota', $noAnggota)->first();
            if (!$anggota) continue;

            $tglMasuk = $anggota->tanggal_masuk
                ? \Carbon\Carbon::parse($anggota->tanggal_masuk)->startOfDay()
                : now()->startOfDay()->subMonths(6);

            RekeningSimpanan::updateOrCreate(
                ['no_rekening' => $rek['pokok']],
                [
                    'anggota_id' => $anggota->id,
                    'produk_id' => $produk['SIMPOK'],
                    'saldo' => 150000,
                    'status' => 'aktif',
                    'tanggal_buka' => $tglMasuk,
                ]
            );

            if ($anggota->gaji_pokok !== null && $rek['wajib']) {
                $lamaBulan = max(1, (int) $tglMasuk->diffInMonths(now()->startOfDay()));
                $saldoWajib = min($lamaBulan * 50000, 500000);

                RekeningSimpanan::updateOrCreate(
                    ['no_rekening' => $rek['wajib']],
                    [
                        'anggota_id' => $anggota->id,
                        'produk_id' => $produk['SIMWA'],
                        'saldo' => $saldoWajib,
                        'status' => 'aktif',
                        'tanggal_buka' => $tglMasuk,
                    ]
                );
            }

            if ($rek['sukarela'] && isset($saldoSukarela[$noAnggota])) {
                RekeningSimpanan::updateOrCreate(
                    ['no_rekening' => $rek['sukarela']],
                    [
                        'anggota_id' => $anggota->id,
                        'produk_id' => $produk['SIMSUKA'],
                        'saldo' => $saldoSukarela[$noAnggota],
                        'status' => 'aktif',
                        'tanggal_buka' => $tglMasuk,
                    ]
                );
            }
        }
    }
}
