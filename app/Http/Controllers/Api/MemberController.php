<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PayLater;
use App\Models\TransaksiPembiayaan;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    public function dashboard(Request $request)
    {
        /** @var \App\Models\Anggota $anggota */
        $anggota = $request->user();

        // 1. Get Simpanan Wallets
        $rekenings = $anggota->rekeningSimpanan()->with('produk')->get();
        $totalSimpanan = $rekenings->sum('saldo');

        // 2. Get Pembiayaan Active
        $pembiayaan = $anggota->pembiayaan()->where('status', 'aktif')->get();
        $totalTagihan = $pembiayaan->sum('saldo_pokok') + $pembiayaan->sum('saldo_bunga');

        // 3. Get Recent Transactions (Last 5)
        $rekeningIds = $rekenings->pluck('id');
        $recentTransactions = \App\Models\TransaksiSimpanan::whereIn('rekening_id', $rekeningIds)
            ->with(['rekening.produk'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // 4. Get Next Installment
        $nextInstallment = null;
        $activePembiayaanIds = $pembiayaan->pluck('id');
        if ($activePembiayaanIds->isNotEmpty()) {
            $nextInstallment = \App\Models\JadwalAngsuran::whereIn('pembiayaan_id', $activePembiayaanIds)
                ->where('status', 'belum')
                ->orderBy('tanggal_jatuh_tempo', 'asc')
                ->first();
        }

        // 5. Payroll Info
        $hasAutoPotongGaji = $pembiayaan->contains('auto_potong_gaji', true);
        $totalPotonganGaji = $pembiayaan->where('auto_potong_gaji', true)->sum('nominal_potongan');
        $gajiDiterima = ($anggota->gaji_pokok ?? 0) - $totalPotonganGaji;

        return response()->json([
            'status' => 'success',
            'data' => [
                'total_simpanan' => $totalSimpanan,
                'total_tagihan' => $totalTagihan,
                'rekening' => $rekenings->map(function ($rek) {
                    return [
                        'no_rekening' => $rek->no_rekening,
                        'produk' => $rek->produk->nama_produk ?? 'Simpanan',
                        'saldo' => $rek->saldo,
                    ];
                }),
                'pembiayaan_aktif' => $pembiayaan->map(function ($pem) {
                    return [
                        'no_pembiayaan' => $pem->no_pembiayaan,
                        'tujuan' => $pem->tujuan_pembiayaan ?? 'Pembiayaan Karyawan',
                        'sisa_tagihan' => $pem->saldo_pokok + $pem->saldo_bunga,
                        'kolektibilitas' => $pem->kolektibilitas,
                        'auto_potong_gaji' => $pem->auto_potong_gaji,
                        'nominal_potongan' => $pem->nominal_potongan,
                        'sumber_pembayaran' => $pem->sumber_pembayaran,
                    ];
                }),
                'recent_transactions' => $recentTransactions->map(function ($trx) {
                    return [
                        'id' => $trx->id,
                        'no_transaksi' => $trx->no_transaksi,
                        'jenis' => $trx->jenis,
                        'nominal' => $trx->nominal,
                        'keterangan' => $trx->keterangan,
                        'tanggal' => $trx->created_at->format('d M Y'),
                        'is_debit' => in_array($trx->jenis, ['tarik', 'bayar_angsuran']),
                        'produk' => $trx->rekening?->produk?->nama_produk ?? 'Umum',
                    ];
                }),
                'next_installment' => $nextInstallment ? [
                    'tanggal' => Carbon::parse($nextInstallment->tanggal_jatuh_tempo)->format('d M'),
                    'nominal' => $nextInstallment->total,
                    'status' => $this->getInstallmentStatusLabel($nextInstallment->tanggal_jatuh_tempo),
                    'diff_days' => now()->diffInDays($nextInstallment->tanggal_jatuh_tempo, false),
                ] : null,
                // Payroll info
                'payroll_info' => [
                    'gaji_pokok' => $anggota->gaji_pokok ?? 0,
                    'tanggal_gajian' => $anggota->tanggal_gajian ?? 25,
                    'total_potongan' => $totalPotonganGaji,
                    'gaji_diterima' => $gajiDiterima,
                    'auto_potong_aktif' => $hasAutoPotongGaji,
                    'departemen' => $anggota->departemen ?? '-',
                    'jabatan' => $anggota->jabatan ?? '-',
                ],
            ]
        ]);
    }

    public function pembiayaanDetail(Request $request)
    {
        /** @var \App\Models\Anggota $anggota */
        $anggota = $request->user();

        // 1. Get Active Pembiayaan (Detailed)
        $pembiayaan = $anggota->pembiayaan()->where('status', 'aktif')->with(['jadwalAngsuran' => function($query) {
            $query->orderBy('ke', 'asc');
        }])->first();

        if (!$pembiayaan) {
            return response()->json([
                'status' => 'error',
                'message' => 'No active loan found.'
            ], 404);
        }

        $jadwal = $pembiayaan->jadwalAngsuran;
        $totalBulan = $pembiayaan->jangka_bulan;
        $lunasCount = $jadwal->where('status', 'lunas')->count();

        $firstUnpaid = $jadwal->where('status', 'belum')->first();

        return response()->json([
            'status' => 'success',
            'data' => [
                'no_pembiayaan' => $pembiayaan->no_pembiayaan,
                'produk' => 'Pembiayaan Karyawan',
                'plafon' => $pembiayaan->nominal_disetujui,
                'progress' => [
                    'current' => $lunasCount,
                    'total' => $totalBulan,
                    'percent' => $totalBulan > 0 ? round(($lunasCount / $totalBulan) * 100, 2) : 0,
                ],
                'progress_potongan_gaji' => $pembiayaan->progress_potongan,
                'auto_potong_gaji' => $pembiayaan->auto_potong_gaji,
                'nominal_potongan' => $pembiayaan->nominal_potongan,
                'sumber_pembayaran' => $pembiayaan->sumber_pembayaran,
                'label_sumber_pembayaran' => $pembiayaan->label_sumber_pembayaran,
                'installments' => $jadwal->map(function ($item) use ($firstUnpaid) {
                    return [
                        'ke' => $item->ke,
                        'tanggal_jatuh_tempo' => $item->tanggal_jatuh_tempo->format('d M Y'),
                        'nominal' => $item->total,
                        'status' => $item->status,
                        'is_current' => $firstUnpaid && $item->id === $firstUnpaid->id,
                        'via_potong_gaji' => $item->transaksi()->where('channel', 'potong_gaji')->exists(),
                    ];
                }),
            ]
        ]);
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'method' => 'required|in:qris,bri_va,transfer,cashier,potong_gaji',
        ]);

        /** @var \App\Models\Anggota $anggota */
        $anggota = $request->user();
        $pembiayaan = $anggota->pembiayaan()->where('status', 'aktif')->first();

        if (!$pembiayaan) {
            return response()->json(['status' => 'error', 'message' => 'Pinjaman tidak ditemukan'], 404);
        }

        $nextInstallment = $pembiayaan->jadwalAngsuran()->where('status', 'belum')->first();
        if (!$nextInstallment) {
            return response()->json(['status' => 'error', 'message' => 'Semua angsuran lunas'], 400);
        }

        $method = $request->input('method');

        // If paying via salary deduction
        if ($method === 'potong_gaji') {
            if (!$pembiayaan->auto_potong_gaji) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Pembiayaan ini tidak menggunakan potongan gaji. Silakan bayar manual.'
                ], 400);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Pembayaran akan diproses otomatis saat tanggal gajian (' . $anggota->tanggal_gajian . ')',
                'data' => [
                    'no_transaksi' => 'PAYROLL-' . time() . '-' . $anggota->id,
                    'nominal' => $nextInstallment->total,
                    'tanggal_gajian' => $anggota->tanggal_gajian,
                    'method' => 'potong_gaji',
                ]
            ]);
        }

        $responseData = [
            'no_transaksi' => 'TRX-' . time() . '-' . $anggota->id,
            'nominal' => $nextInstallment->total,
            'expired_at' => now()->addMinutes(15)->format('Y-m-d H:i:s'),
            'method' => $method,
        ];

        if ($method === 'qris') {
            $responseData['qris_image'] = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=KOPSAKU-TRX-' . $responseData['no_transaksi'];
        } elseif ($method === 'bri_va') {
            $responseData['va_number'] = '8801 0' . substr($anggota->nik, -10);
            $responseData['bank_name'] = 'BRI';
        } elseif ($method === 'transfer') {
            $responseData['source_accounts'] = $anggota->rekeningSimpanan()->with('produk')->get()->map(function ($rek) {
                return [
                    'id' => $rek->id,
                    'no_rekening' => $rek->no_rekening,
                    'produk' => $rek->produk->nama_produk,
                    'saldo' => $rek->saldo,
                ];
            });
        }

        return response()->json([
            'status' => 'success',
            'data' => $responseData,
        ]);
    }

    public function payInstallment(Request $request)
    {
        $request->validate([
            'method' => 'required|in:qris,bri_va,transfer,cashier',
            'no_transaksi' => 'required',
        ]);

        /** @var \App\Models\Anggota $anggota */
        $anggota = $request->user();

        /** @var \App\Models\Pembiayaan|null $pembiayaan */
        $pembiayaan = $anggota->pembiayaan()->where('status', 'aktif')->first();

        if (!$pembiayaan) {
            return response()->json(['status' => 'error', 'message' => 'Pinjaman tidak ditemukan'], 404);
        }

        /** @var \App\Models\JadwalAngsuran|null $nextInstallment */
        $nextInstallment = $pembiayaan->jadwalAngsuran()->where('status', 'belum')->first();
        if (!$nextInstallment) {
            return response()->json(['status' => 'error', 'message' => 'Semua angsuran lunas'], 400);
        }

        // Simulating balance check for 'transfer'
        if ($request->input('method') === 'transfer') {
            $rekeningId = $request->input('rekening_id');
            $rekeningQuery = $anggota->rekeningSimpanan();

            /** @var \App\Models\RekeningSimpanan|null $rekening */
            $rekening = $rekeningId
                ? $rekeningQuery->where('id', $rekeningId)->first()
                : $rekeningQuery->first();

            if (!$rekening || $rekening->saldo < $nextInstallment->total) {
                return response()->json(['status' => 'error', 'message' => 'Saldo tidak cukup pada rekening yang dipilih'], 400);
            }
            $rekening->saldo -= $nextInstallment->total;
            $rekening->save();
        }

        // Mark as paid
        $nextInstallment->status = 'lunas';
        $nextInstallment->tanggal_bayar = now();
        $nextInstallment->save();

        // Update pembiayaan balance
        $pembiayaan->saldo_pokok -= $nextInstallment->angsuran_pokok;
        $pembiayaan->saldo_bunga -= $nextInstallment->angsuran_bunga;
        if ($pembiayaan->saldo_pokok <= 0) {
            $pembiayaan->status = 'lunas';
            $pembiayaan->tanggal_lunas = now();
        }
        $pembiayaan->save();

        // Create transaction record
        TransaksiPembiayaan::create([
            'pembiayaan_id' => $pembiayaan->id,
            'jadwal_id' => $nextInstallment->id,
            'no_transaksi' => $request->no_transaksi,
            'jenis' => 'angsuran',
            'nominal_pokok' => $nextInstallment->angsuran_pokok,
            'nominal_bunga' => $nextInstallment->angsuran_bunga,
            'nominal_denda' => 0,
            'total' => $nextInstallment->total,
            'channel' => $request->input('method') === 'transfer' ? 'mobile' : $request->input('method'),
            'ref_payment' => $request->no_transaksi,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Pembayaran berhasil dikonfirmasi',
            'data' => [
                'no_transaksi' => $request->no_transaksi,
                'tanggal' => now()->format('d M Y H:i'),
            ]
        ]);
    }

    public function profile(Request $request)
    {
        /** @var \App\Models\Anggota $anggota */
        $anggota = $request->user();

        $anggota->load('cabang');

        /** @var \Illuminate\Support\Carbon|null $joinDate */
        $joinDate = $anggota->tanggal_masuk;
        $durationString = '-';
        if ($joinDate) {
            $diff = $joinDate->diff(now());
            $durationString = "{$diff->y} thn {$diff->m} bln";
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'nama_lengkap' => $anggota->nama_lengkap,
                'no_anggota' => $anggota->no_anggota,
                'nik' => $anggota->nik,
                'no_pegawai' => $anggota->no_pegawai ?? '-',
                'status' => $anggota->status == 'aktif' ? 'Anggota Aktif' : 'Tidak Aktif',
                'cabang' => $anggota->cabang?->nama ?? 'Pusat',
                'departemen' => $anggota->departemen ?? '-',
                'jabatan' => $anggota->jabatan ?? '-',
                'tanggal_masuk' => $joinDate ? $joinDate->format('d M Y') : '-',
                'tanggal_mulai_kerja' => $anggota->tanggal_mulai_kerja ? $anggota->tanggal_mulai_kerja->format('d M Y') : '-',
                'masa_kerja' => $anggota->masa_kerja,
                'durasi' => $durationString,
                'no_hp' => $this->maskString($anggota->no_hp, 4, 4),
                'email' => $this->maskEmail($anggota->email),
                'inisial' => collect(explode(' ', $anggota->nama_lengkap))->map(fn($n) => substr($n, 0, 1))->take(2)->join(''),
                // Payroll info
                'gaji_pokok' => $anggota->gaji_pokok ?? 0,
                'tanggal_gajian' => $anggota->tanggal_gajian ?? 25,
                'total_potongan_per_bulan' => $anggota->total_potongan_per_bulan,
                'gaji_diterima' => $anggota->gaji_diterima,
            ]
        ]);
    }

    /**
     * Submit pay-later request — bayar sebelum gajian.
     */
    public function submitPayLater(Request $request)
    {
        $request->validate([
            'pembiayaan_id' => 'required|uuid|exists:pembiayaan,id',
            'jadwal_angsuran_id' => 'nullable|uuid|exists:jadwal_angsuran,id',
            'nominal' => 'required|numeric|min:0',
            'jenis' => 'required|in:angsuran,pelunasan',
            'keterangan' => 'nullable|string',
        ]);

        /** @var \App\Models\Anggota $anggota */
        $anggota = $request->user();

        $pembiayaan = $anggota->pembiayaan()->where('id', $request->pembiayaan_id)->first();
        if (!$pembiayaan) {
            return response()->json(['status' => 'error', 'message' => 'Pembiayaan tidak ditemukan'], 404);
        }

        $payLater = PayLater::create([
            'anggota_id' => $anggota->id,
            'pembiayaan_id' => $request->pembiayaan_id,
            'jadwal_angsuran_id' => $request->jadwal_angsuran_id,
            'nominal' => $request->nominal,
            'jenis' => $request->jenis,
            'status' => 'pending',
            'no_transaksi' => 'PL-' . now()->format('ymd') . '-' . strtoupper(substr(uniqid(), -6)),
            'keterangan' => $request->keterangan,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Permintaan pembayaran berhasil diajukan. Menunggu approval admin.',
            'data' => $payLater,
        ], 201);
    }

    /**
     * Get pay-later history for the member.
     */
    public function payLaterHistory(Request $request)
    {
        /** @var \App\Models\Anggota $anggota */
        $anggota = $request->user();

        $history = $anggota->payLater()->with(['pembiayaan', 'jadwalAngsuran'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $history->map(function ($pl) {
                return [
                    'id' => $pl->id,
                    'no_transaksi' => $pl->no_transaksi,
                    'nominal' => $pl->nominal,
                    'jenis' => $pl->label_jenis,
                    'status' => $pl->label_status,
                    'keterangan' => $pl->keterangan,
                    'tanggal' => $pl->created_at->format('d M Y'),
                    'no_pembiayaan' => $pl->pembiayaan?->no_pembiayaan,
                    'angsuran_ke' => $pl->jadwalAngsuran?->ke,
                ];
            }),
        ]);
    }

    private function getInstallmentStatusLabel(string $tanggalJatuhTempo): string
    {
        $diff = now()->diffInDays(Carbon::parse($tanggalJatuhTempo), false);
        if ($diff < 0) {
            return 'TERLAMBAT';
        }
        if ($diff <= 3) {
            return "{$diff} HARI LAGI";
        }
        return 'BELUM JATUH TEMPO';
    }

    private function maskString($str, $prefixLen, $maskLen) {
        if (!$str) return '-';
        if (strlen($str) <= ($prefixLen + $maskLen)) return $str;
        return substr($str, 0, $prefixLen) . str_repeat('*', $maskLen) . substr($str, $prefixLen + $maskLen);
    }

    private function maskEmail($email) {
        if (!$email) return '-';
        $parts = explode('@', $email);
        if (count($parts) < 2) return $email;
        $name = $parts[0];
        $domain = $parts[1];
        $len = strlen($name);
        if ($len <= 2) return $email;
        return substr($name, 0, 1) . str_repeat('*', $len - 2) . substr($name, -1) . '@' . $domain;
    }
}
