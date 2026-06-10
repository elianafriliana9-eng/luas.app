<?php

namespace App\Http\Controllers;

use App\Models\Anggota;
use App\Models\Pembiayaan;
use App\Models\RekeningSimpanan;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Calculate Metrics
        $totalSimpanan = RekeningSimpanan::where('status', 'aktif')->sum('saldo');
        $outstandingPembiayaan = Pembiayaan::whereIn('status', ['aktif', 'lunas'])->sum('saldo_pokok');
        $totalAset = $totalSimpanan + $outstandingPembiayaan; // Simplified asset for demo
        $anggotaAktif = Anggota::where('status', 'aktif')->count();

        // Data array to pass to view
        $data = [
            'totalAset' => $totalAset,
            'anggotaAktif' => $anggotaAktif,
            'outstandingPembiayaan' => $outstandingPembiayaan,
            'totalSimpanan' => $totalSimpanan,
            'nplRatio' => 1.8, // Dummy NPL metric
            'growthAset' => 12, // Dummy Growth %
            'growthAnggota' => 23, // Dummy addition
            'growthSimpanan' => 5.2, // Dummy %
            'currentDate' => Carbon::now()->isoFormat('dddd, D MMMM Y'),
        ];

        return view('dashboard', $data);
    }
}
