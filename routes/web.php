<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\DashboardController;

use App\Http\Controllers\AnggotaController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/anggota', [AnggotaController::class, 'index'])->name('anggota.index');
    Route::get('/anggota/create', [AnggotaController::class, 'create'])->name('anggota.create');
    Route::post('/anggota', [AnggotaController::class, 'store'])->name('anggota.store');
    Route::get('/anggota/{id}', [AnggotaController::class, 'show'])->name('anggota.show');
    Route::get('/anggota/{id}/edit', [AnggotaController::class, 'edit'])->name('anggota.edit');
    Route::put('/anggota/{id}', [AnggotaController::class, 'update'])->name('anggota.update');

    // Anggota keluar
    Route::get('/anggota/{id}/keluar', [AnggotaController::class, 'keluarForm'])->name('anggota.keluar');
    Route::post('/anggota/{id}/keluar', [AnggotaController::class, 'keluarSubmit'])->name('anggota.keluar.submit');
    Route::get('/anggota/approval-keluar', [AnggotaController::class, 'approvalKeluar'])->name('anggota.approval_keluar');
    Route::post('/anggota/{id}/approve-keluar', [AnggotaController::class, 'approveKeluar'])->name('anggota.approve_keluar');
    Route::post('/anggota/{id}/reject-keluar', [AnggotaController::class, 'rejectKeluar'])->name('anggota.reject_keluar');

    // Saldo & History
    Route::get('/anggota/saldo', [AnggotaController::class, 'saldo'])->name('anggota.saldo');
    Route::get('/anggota/{id}/history', [AnggotaController::class, 'historyTransaksi'])->name('anggota.history');

    // Laporan
    Route::get('/anggota/laporan/saldo', [AnggotaController::class, 'laporanSaldo'])->name('anggota.laporan.saldo');
    Route::get('/anggota/laporan/profil', [AnggotaController::class, 'laporanProfil'])->name('anggota.laporan.profil');
    Route::get('/anggota/laporan/rekap', [AnggotaController::class, 'laporanRekap'])->name('anggota.laporan.rekap');
    Route::get('/anggota/laporan/keluar', [AnggotaController::class, 'laporanKeluar'])->name('anggota.laporan.keluar');
    Route::get('/anggota/{id}/export-keluar', [AnggotaController::class, 'exportDataKeluar'])->name('anggota.export_keluar');

    // Simpanan Routes
    Route::get('/simpanan', [\App\Http\Controllers\SimpananController::class, 'index'])->name('simpanan.index');
    Route::get('/simpanan/rekening', [\App\Http\Controllers\SimpananController::class, 'rekening'])->name('simpanan.rekening');
    Route::get('/simpanan/transaksi', [\App\Http\Controllers\SimpananController::class, 'transaksi'])->name('simpanan.transaksi');

    // Input & Transaksi
    Route::get('/simpanan/create', [\App\Http\Controllers\SimpananController::class, 'create'])->name('simpanan.create');
    Route::post('/simpanan', [\App\Http\Controllers\SimpananController::class, 'store'])->name('simpanan.store');

    // Approval
    Route::get('/simpanan/approval', [\App\Http\Controllers\SimpananController::class, 'approval'])->name('simpanan.approval');
    Route::post('/simpanan/approve/{id}', [\App\Http\Controllers\SimpananController::class, 'approveTransaksi'])->name('simpanan.approve');

    // Pinbuk
    Route::get('/simpanan/pinbuk', [\App\Http\Controllers\SimpananController::class, 'pinbukForm'])->name('simpanan.pinbuk');
    Route::post('/simpanan/pinbuk', [\App\Http\Controllers\SimpananController::class, 'pinbukStore'])->name('simpanan.pinbuk.store');

    // Cancel
    Route::get('/simpanan/cancel/{id}', [\App\Http\Controllers\SimpananController::class, 'cancelForm'])->name('simpanan.cancel');
    Route::post('/simpanan/cancel/{id}', [\App\Http\Controllers\SimpananController::class, 'cancelSubmit'])->name('simpanan.cancel.submit');

    // Upload
    Route::get('/simpanan/upload', [\App\Http\Controllers\SimpananController::class, 'uploadForm'])->name('simpanan.upload');
    Route::post('/simpanan/upload', [\App\Http\Controllers\SimpananController::class, 'uploadProcess'])->name('simpanan.upload.process');

    // Blokir & Tutup
    Route::get('/simpanan/blokir/{id}', [\App\Http\Controllers\SimpananController::class, 'blokirForm'])->name('simpanan.blokir');
    Route::post('/simpanan/blokir/{id}', [\App\Http\Controllers\SimpananController::class, 'blokirSubmit'])->name('simpanan.blokir.submit');
    Route::post('/simpanan/blokir/{id}/buka', [\App\Http\Controllers\SimpananController::class, 'bukaBlokir'])->name('simpanan.buka_blokir');
    Route::get('/simpanan/tutup/{id}', [\App\Http\Controllers\SimpananController::class, 'tutupForm'])->name('simpanan.tutup');
    Route::post('/simpanan/tutup/{id}', [\App\Http\Controllers\SimpananController::class, 'tutupSubmit'])->name('simpanan.tutup.submit');

    // Statement
    Route::get('/simpanan/statement/{id}', [\App\Http\Controllers\SimpananController::class, 'statement'])->name('simpanan.statement');

    // Laporan
    Route::get('/simpanan/laporan/regist', [\App\Http\Controllers\SimpananController::class, 'laporanRegist'])->name('simpanan.laporan.regist');
    Route::get('/simpanan/laporan/penarikan', [\App\Http\Controllers\SimpananController::class, 'laporanPenarikan'])->name('simpanan.laporan.penarikan');
    Route::get('/simpanan/laporan/setoran', [\App\Http\Controllers\SimpananController::class, 'laporanSetoran'])->name('simpanan.laporan.setoran');
    Route::get('/simpanan/laporan/rekap', [\App\Http\Controllers\SimpananController::class, 'laporanRekap'])->name('simpanan.laporan.rekap');
    Route::get('/simpanan/laporan/pinbuk', [\App\Http\Controllers\SimpananController::class, 'laporanPinbuk'])->name('simpanan.laporan.pinbuk');

    // Pembiayaan Routes
    Route::get('/pembiayaan', [\App\Http\Controllers\PembiayaanController::class, 'index'])->name('pembiayaan.index');
    Route::get('/pembiayaan/pengajuan', [\App\Http\Controllers\PembiayaanController::class, 'pengajuan'])->name('pembiayaan.pengajuan');
    Route::get('/pembiayaan/transaksi', [\App\Http\Controllers\PembiayaanController::class, 'transaksi'])->name('pembiayaan.transaksi');

    // Simulasi
    Route::get('/pembiayaan/simulasi', [\App\Http\Controllers\PembiayaanController::class, 'simulasi'])->name('pembiayaan.simulasi');
    Route::post('/pembiayaan/simulasi/hitung', [\App\Http\Controllers\PembiayaanController::class, 'simulasiHitung'])->name('pembiayaan.simulasi.hitung');

    // Pengajuan
    Route::get('/pembiayaan/pengajuan/create', [\App\Http\Controllers\PembiayaanController::class, 'createPengajuan'])->name('pembiayaan.pengajuan.create');
    Route::post('/pembiayaan/pengajuan', [\App\Http\Controllers\PembiayaanController::class, 'storePengajuan'])->name('pembiayaan.pengajuan.store');

    // Registrasi
    Route::get('/pembiayaan/registrasi', [\App\Http\Controllers\PembiayaanController::class, 'registrasi'])->name('pembiayaan.registrasi');
    Route::post('/pembiayaan/registrasi/{id}/approve', [\App\Http\Controllers\PembiayaanController::class, 'approvePengajuan'])->name('pembiayaan.registrasi.approve');

    // Pencairan
    Route::get('/pembiayaan/pencairan/{id}', [\App\Http\Controllers\PembiayaanController::class, 'pencairanForm'])->name('pembiayaan.pencairan');
    Route::post('/pembiayaan/pencairan/{id}', [\App\Http\Controllers\PembiayaanController::class, 'pencairanSubmit'])->name('pembiayaan.pencairan.submit');

    // Pelunasan
    Route::get('/pembiayaan/pelunasan/{id}', [\App\Http\Controllers\PembiayaanController::class, 'pelunasanForm'])->name('pembiayaan.pelunasan');
    Route::post('/pembiayaan/pelunasan/{id}', [\App\Http\Controllers\PembiayaanController::class, 'pelunasanSubmit'])->name('pembiayaan.pelunasan.submit');

    // Bayar Angsuran
    Route::post('/pembiayaan/angsuran/{jadwalId}/bayar', [\App\Http\Controllers\PembiayaanController::class, 'bayarAngsuran'])->name('pembiayaan.angsuran.bayar');

    // Cetak
    Route::get('/pembiayaan/cetak/sp3/{id}', [\App\Http\Controllers\PembiayaanController::class, 'cetakSP3'])->name('pembiayaan.cetak.sp3');
    Route::get('/pembiayaan/cetak/perjanjian/{id}', [\App\Http\Controllers\PembiayaanController::class, 'cetakPerjanjian'])->name('pembiayaan.cetak.perjanjian');

    // Generate Jadwal
    Route::post('/pembiayaan/{id}/generate-jadwal', [\App\Http\Controllers\PembiayaanController::class, 'generateJadwal'])->name('pembiayaan.generate_jadwal');

    // Laporan
    Route::get('/pembiayaan/laporan/pengajuan', [\App\Http\Controllers\PembiayaanController::class, 'laporanPengajuan'])->name('pembiayaan.laporan.pengajuan');
    Route::get('/pembiayaan/laporan/registrasi', [\App\Http\Controllers\PembiayaanController::class, 'laporanRegistrasi'])->name('pembiayaan.laporan.registrasi');
    Route::get('/pembiayaan/laporan/pembiayaan', [\App\Http\Controllers\PembiayaanController::class, 'laporanPembiayaan'])->name('pembiayaan.laporan.pembiayaan');
    Route::get('/pembiayaan/laporan/pencairan', [\App\Http\Controllers\PembiayaanController::class, 'laporanPencairan'])->name('pembiayaan.laporan.pencairan');

    // Akuntansi Routes
    Route::get('/akuntansi', [\App\Http\Controllers\AkuntansiController::class, 'index'])->name('akuntansi.index');
    Route::get('/akuntansi/coa', [\App\Http\Controllers\AkuntansiController::class, 'coa'])->name('akuntansi.coa');
    Route::post('/akuntansi/coa', [\App\Http\Controllers\AkuntansiController::class, 'storeCoa'])->name('akuntansi.coa.store');
    Route::put('/akuntansi/coa/{id}', [\App\Http\Controllers\AkuntansiController::class, 'updateCoa'])->name('akuntansi.coa.update');
    Route::get('/akuntansi/kas', [\App\Http\Controllers\AkuntansiController::class, 'kas'])->name('akuntansi.kas');
    Route::post('/akuntansi/kas', [\App\Http\Controllers\AkuntansiController::class, 'storeKas'])->name('akuntansi.kas.store');
    Route::post('/akuntansi/kas/{id}/saldo', [\App\Http\Controllers\AkuntansiController::class, 'updateKasSaldo'])->name('akuntansi.kas.saldo');

    // Transaksi Jurnal
    Route::get('/akuntansi/jurnal', [\App\Http\Controllers\AkuntansiController::class, 'jurnal'])->name('akuntansi.jurnal');
    Route::get('/akuntansi/jurnal/create', [\App\Http\Controllers\AkuntansiController::class, 'createJurnal'])->name('akuntansi.jurnal.create');
    Route::post('/akuntansi/jurnal', [\App\Http\Controllers\AkuntansiController::class, 'storeJurnal'])->name('akuntansi.jurnal.store');
    Route::get('/akuntansi/jurnal/{id}', [\App\Http\Controllers\AkuntansiController::class, 'detailJurnal'])->name('akuntansi.jurnal.detail');

    // Pembatalan & Revisi
    Route::get('/akuntansi/jurnal/{id}/batal', [\App\Http\Controllers\AkuntansiController::class, 'batalForm'])->name('akuntansi.jurnal.batal');
    Route::post('/akuntansi/jurnal/{id}/batal', [\App\Http\Controllers\AkuntansiController::class, 'batalSubmit'])->name('akuntansi.jurnal.batal.submit');
    Route::get('/akuntansi/jurnal/{id}/revisi', [\App\Http\Controllers\AkuntansiController::class, 'revisiForm'])->name('akuntansi.jurnal.revisi');
    Route::post('/akuntansi/jurnal/{id}/revisi', [\App\Http\Controllers\AkuntansiController::class, 'revisiSubmit'])->name('akuntansi.jurnal.revisi.submit');

    // Buku Besar
    Route::get('/akuntansi/buku-besar', [\App\Http\Controllers\AkuntansiController::class, 'bukuBesar'])->name('akuntansi.buku_besar');

    // Laporan
    Route::get('/akuntansi/laporan/kas', [\App\Http\Controllers\AkuntansiController::class, 'laporanKas'])->name('akuntansi.laporan.kas');
    Route::get('/akuntansi/laporan/neraca-saldo', [\App\Http\Controllers\AkuntansiController::class, 'neracaSaldo'])->name('akuntansi.laporan.neraca_saldo');
    Route::get('/akuntansi/laporan/neraca', [\App\Http\Controllers\AkuntansiController::class, 'neraca'])->name('akuntansi.laporan.neraca');

    // Payroll Routes
    Route::get('/payroll', [\App\Http\Controllers\PayrollController::class, 'index'])->name('payroll.index');
    Route::get('/payroll/{anggotaId}', [\App\Http\Controllers\PayrollController::class, 'detail'])->name('payroll.detail');
    Route::post('/payroll/proses', [\App\Http\Controllers\PayrollController::class, 'prosesPotongan'])->name('payroll.proses');
    Route::get('/pay-later/pending', [\App\Http\Controllers\PayrollController::class, 'payLaterPending'])->name('payroll.pay_later_pending');
    Route::post('/pay-later/{id}/approve', [\App\Http\Controllers\PayrollController::class, 'approvePayLater'])->name('payroll.approve_pay_later');
    Route::post('/pay-later/{id}/reject', [\App\Http\Controllers\PayrollController::class, 'rejectPayLater'])->name('payroll.reject_pay_later');
    Route::post('/pay-later/{id}/process', [\App\Http\Controllers\PayrollController::class, 'processPayLater'])->name('payroll.process_pay_later');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
