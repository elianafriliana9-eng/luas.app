<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\DashboardController;

use App\Http\Controllers\AnggotaController;
use App\Http\Controllers\PerusahaanController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Master Data - Perusahaan
    Route::get('/perusahaan', [PerusahaanController::class, 'index'])->name('perusahaan.index');
    Route::get('/perusahaan/create', [PerusahaanController::class, 'create'])->name('perusahaan.create');
    Route::post('/perusahaan', [PerusahaanController::class, 'store'])->name('perusahaan.store');
    Route::get('/perusahaan/{id}/edit', [PerusahaanController::class, 'edit'])->name('perusahaan.edit');
    Route::put('/perusahaan/{id}', [PerusahaanController::class, 'update'])->name('perusahaan.update');
    Route::delete('/perusahaan/{id}', [PerusahaanController::class, 'destroy'])->name('perusahaan.destroy')->middleware('role:super_admin');

    // Konfigurasi COA (Accounting)
    Route::get('/konfigurasi-coa', [\App\Http\Controllers\KonfigurasiCoaController::class, 'index'])->name('konfigurasi-coa.index')->middleware('role:super_admin');
    Route::put('/konfigurasi-coa/{id}', [\App\Http\Controllers\KonfigurasiCoaController::class, 'update'])->name('konfigurasi-coa.update')->middleware('role:super_admin');

    Route::get('/anggota', [AnggotaController::class, 'index'])->name('anggota.index');
    Route::get('/anggota/create', [AnggotaController::class, 'create'])->name('anggota.create')->middleware('role:super_admin,admin');
    Route::post('/anggota', [AnggotaController::class, 'store'])->name('anggota.store')->middleware('role:super_admin,admin');

    // Static routes (No ID)
    Route::get('/anggota/pending-approval', [AnggotaController::class, 'pendingApproval'])->name('anggota.pending_approval')->middleware('role:super_admin');
    Route::post('/anggota/{id}/approve-anggota', [AnggotaController::class, 'approveAnggota'])->name('anggota.approve_anggota')->middleware('role:super_admin');
    Route::post('/anggota/{id}/reject-anggota', [AnggotaController::class, 'rejectAnggota'])->name('anggota.reject_anggota')->middleware('role:super_admin');
    Route::get('/anggota/approval-keluar', [AnggotaController::class, 'approvalKeluar'])->name('anggota.approval_keluar')->middleware('role:super_admin');
    Route::get('/anggota/saldo', [AnggotaController::class, 'saldo'])->name('anggota.saldo');
    Route::get('/anggota/laporan/masuk', [AnggotaController::class, 'laporanMasuk'])->name('anggota.laporan.masuk');
    Route::get('/anggota/laporan/saldo', fn() => redirect()->route('anggota.saldo'))->name('anggota.laporan.saldo');
    Route::get('/anggota/laporan/profil', [AnggotaController::class, 'laporanProfil'])->name('anggota.laporan.profil');
    Route::get('/anggota/laporan/rekap', [AnggotaController::class, 'laporanRekap'])->name('anggota.laporan.rekap');
    Route::get('/anggota/laporan/keluar', [AnggotaController::class, 'laporanKeluar'])->name('anggota.laporan.keluar');

    // Anggota Export & Import (must be before {id} routes)
    Route::get('/anggota/export/data', [AnggotaController::class, 'exportAnggota'])->name('anggota.export.data');
    Route::get('/anggota/export/saldo', [AnggotaController::class, 'exportSaldo'])->name('anggota.export.saldo');
    Route::get('/anggota/export/profil', [AnggotaController::class, 'exportProfil'])->name('anggota.export.profil');
    Route::get('/anggota/export/rekap', [AnggotaController::class, 'exportRekap'])->name('anggota.export.rekap');
    Route::get('/anggota/export/keluar', [AnggotaController::class, 'exportKeluar'])->name('anggota.export.keluar');
    Route::get('/anggota/import', [AnggotaController::class, 'importForm'])->name('anggota.import')->middleware('role:super_admin,admin');
    Route::post('/anggota/import', [AnggotaController::class, 'importProcess'])->name('anggota.import.process')->middleware('role:super_admin,admin');
    Route::get('/anggota/import-master', [AnggotaController::class, 'importMasterForm'])->name('anggota.import.master')->middleware('role:super_admin');
    Route::post('/anggota/import-master', [AnggotaController::class, 'importMasterProcess'])->name('anggota.import.master.process')->middleware('role:super_admin');
    Route::get('/anggota/download-template', [AnggotaController::class, 'downloadTemplate'])->name('anggota.download_template');

    // PDF Reports
    Route::get('/anggota/pdf/profil', [AnggotaController::class, 'pdfProfil'])->name('anggota.pdf.profil');

    // Routes with {id} parameter (MUST BE AT THE BOTTOM)
    Route::get('/anggota/{id}', [AnggotaController::class, 'show'])->name('anggota.show');
    Route::get('/anggota/{id}/edit', [AnggotaController::class, 'edit'])->name('anggota.edit')->middleware('role:super_admin,admin');
    Route::put('/anggota/{id}', [AnggotaController::class, 'update'])->name('anggota.update')->middleware('role:super_admin,admin');
    Route::get('/anggota/{id}/keluar', [AnggotaController::class, 'keluarForm'])->name('anggota.keluar')->middleware('role:super_admin,admin');
    Route::post('/anggota/{id}/keluar', [AnggotaController::class, 'keluarSubmit'])->name('anggota.keluar.submit')->middleware('role:super_admin,admin');
    Route::post('/anggota/{id}/approve-keluar', [AnggotaController::class, 'approveKeluar'])->name('anggota.approve_keluar')->middleware('role:super_admin');
    Route::post('/anggota/{id}/reject-keluar', [AnggotaController::class, 'rejectKeluar'])->name('anggota.reject_keluar')->middleware('role:super_admin');
    Route::get('/anggota/{id}/history', [AnggotaController::class, 'historyTransaksi'])->name('anggota.history');
    Route::get('/anggota/{id}/export-keluar', [AnggotaController::class, 'pdfKeluar'])->name('anggota.pdf_keluar');

    // Simpanan Routes
    Route::get('/simpanan', [\App\Http\Controllers\SimpananController::class, 'index'])->name('simpanan.index');
    Route::get('/simpanan/rekening', [\App\Http\Controllers\SimpananController::class, 'rekening'])->name('simpanan.rekening');
    Route::get('/simpanan/transaksi', [\App\Http\Controllers\SimpananController::class, 'transaksi'])->name('simpanan.transaksi');

    // Input & Transaksi
    Route::get('/simpanan/create', [\App\Http\Controllers\SimpananController::class, 'create'])->name('simpanan.create')->middleware('role:super_admin,admin');
    Route::post('/simpanan', [\App\Http\Controllers\SimpananController::class, 'store'])->name('simpanan.store')->middleware('role:super_admin,admin');
    Route::get('/simpanan/rekening-baru', [\App\Http\Controllers\SimpananController::class, 'rekeningBaruForm'])->name('simpanan.rekening_baru')->middleware('role:super_admin,admin');
    Route::post('/simpanan/rekening-baru', [\App\Http\Controllers\SimpananController::class, 'rekeningBaruStore'])->name('simpanan.rekening_baru.store')->middleware('role:super_admin,admin');

    // Approval (super_admin only)
    Route::get('/simpanan/approval', [\App\Http\Controllers\SimpananController::class, 'approval'])->name('simpanan.approval')->middleware('role:super_admin');
    Route::post('/simpanan/approve/{id}', [\App\Http\Controllers\SimpananController::class, 'approveTransaksi'])->name('simpanan.approve')->middleware('role:super_admin');

    // Pinbuk
    Route::get('/simpanan/pinbuk', [\App\Http\Controllers\SimpananController::class, 'pinbukForm'])->name('simpanan.pinbuk')->middleware('role:super_admin,admin');
    Route::post('/simpanan/pinbuk', [\App\Http\Controllers\SimpananController::class, 'pinbukStore'])->name('simpanan.pinbuk.store')->middleware('role:super_admin,admin');
    // Pinbuk Approval (super_admin only)
    Route::get('/simpanan/pinbuk/approval', [\App\Http\Controllers\SimpananController::class, 'pinbukApproval'])->name('simpanan.pinbuk.approval')->middleware('role:super_admin');
    Route::post('/simpanan/pinbuk/approval/{id}/approve', [\App\Http\Controllers\SimpananController::class, 'pinbukApprove'])->name('simpanan.pinbuk.approve')->middleware('role:super_admin');
    Route::post('/simpanan/pinbuk/approval/{id}/reject', [\App\Http\Controllers\SimpananController::class, 'pinbukReject'])->name('simpanan.pinbuk.reject')->middleware('role:super_admin');

    // Cancel (admin+)
    Route::get('/simpanan/cancel/{id}', [\App\Http\Controllers\SimpananController::class, 'cancelForm'])->name('simpanan.cancel')->middleware('role:super_admin');
    Route::post('/simpanan/cancel/{id}', [\App\Http\Controllers\SimpananController::class, 'cancelSubmit'])->name('simpanan.cancel.submit')->middleware('role:super_admin');

    // Upload (admin+)
    Route::get('/simpanan/upload', [\App\Http\Controllers\SimpananController::class, 'uploadForm'])->name('simpanan.upload')->middleware('role:super_admin,admin');
    Route::post('/simpanan/upload', [\App\Http\Controllers\SimpananController::class, 'uploadProcess'])->name('simpanan.upload.process')->middleware('role:super_admin,admin');
    Route::get('/simpanan/export/rekening', [\App\Http\Controllers\SimpananController::class, 'exportRekening'])->name('simpanan.export.rekening');
    Route::get('/simpanan/export/transaksi', [\App\Http\Controllers\SimpananController::class, 'exportTransaksi'])->name('simpanan.export.transaksi');
    Route::get('/simpanan/export/rekap', [\App\Http\Controllers\SimpananController::class, 'exportRekap'])->name('simpanan.export.rekap');
    Route::get('/simpanan/export/setoran', [\App\Http\Controllers\SimpananController::class, 'exportSetoran'])->name('simpanan.export.setoran');
    Route::get('/simpanan/export/penarikan', [\App\Http\Controllers\SimpananController::class, 'exportPenarikan'])->name('simpanan.export.penarikan');
    Route::get('/simpanan/export/regist', [\App\Http\Controllers\SimpananController::class, 'exportRegist'])->name('simpanan.export.regist');
    Route::get('/simpanan/export/pinbuk', [\App\Http\Controllers\SimpananController::class, 'exportPinbuk'])->name('simpanan.export.pinbuk');
    Route::get('/simpanan/export/statement/{id}', [\App\Http\Controllers\SimpananController::class, 'exportStatement'])->name('simpanan.export.statement');
    Route::get('/simpanan/download-template', [\App\Http\Controllers\SimpananController::class, 'downloadTemplate'])->name('simpanan.download_template');

    // PDF Reports
    Route::get('/simpanan/pdf/statement/{id}', [\App\Http\Controllers\SimpananController::class, 'pdfStatement'])->name('simpanan.pdf.statement');
    Route::get('/simpanan/pdf/rekap', [\App\Http\Controllers\SimpananController::class, 'pdfRekap'])->name('simpanan.pdf.rekap');

    // Blokir & Tutup (admin+)
    Route::get('/simpanan/blokir/{id}', [\App\Http\Controllers\SimpananController::class, 'blokirForm'])->name('simpanan.blokir')->middleware('role:super_admin');
    Route::post('/simpanan/blokir/{id}', [\App\Http\Controllers\SimpananController::class, 'blokirSubmit'])->name('simpanan.blokir.submit')->middleware('role:super_admin');
    Route::post('/simpanan/blokir/{id}/buka', [\App\Http\Controllers\SimpananController::class, 'bukaBlokir'])->name('simpanan.buka_blokir')->middleware('role:super_admin');
    Route::get('/simpanan/tutup/{id}', [\App\Http\Controllers\SimpananController::class, 'tutupForm'])->name('simpanan.tutup')->middleware('role:super_admin');
    Route::post('/simpanan/tutup/{id}', [\App\Http\Controllers\SimpananController::class, 'tutupSubmit'])->name('simpanan.tutup.submit')->middleware('role:super_admin');

    // Statement
    Route::get('/simpanan/statement/{id}', [\App\Http\Controllers\SimpananController::class, 'statement'])->name('simpanan.statement');

    // Laporan
    Route::get('/simpanan/laporan/regist', [\App\Http\Controllers\SimpananController::class, 'laporanRegist'])->name('simpanan.laporan.regist');
    Route::get('/simpanan/laporan/penarikan', [\App\Http\Controllers\SimpananController::class, 'laporanPenarikan'])->name('simpanan.laporan.penarikan');
    Route::get('/simpanan/laporan/setoran', [\App\Http\Controllers\SimpananController::class, 'laporanSetoran'])->name('simpanan.laporan.setoran');
    Route::get('/simpanan/laporan/rekap', [\App\Http\Controllers\SimpananController::class, 'laporanRekap'])->name('simpanan.laporan.rekap');
    Route::get('/simpanan/laporan/pinbuk', [\App\Http\Controllers\SimpananController::class, 'laporanPinbuk'])->name('simpanan.laporan.pinbuk');
    Route::get('/simpanan/laporan/saldo', [\App\Http\Controllers\SimpananController::class, 'laporanSaldo'])->name('simpanan.laporan.saldo');
    Route::get('/simpanan/laporan/statement', [\App\Http\Controllers\SimpananController::class, 'laporanStatement'])->name('simpanan.laporan.statement');
    Route::get('/simpanan/laporan/blokir', [\App\Http\Controllers\SimpananController::class, 'laporanBlokir'])->name('simpanan.laporan.blokir');
    Route::get('/simpanan/laporan/tutup', [\App\Http\Controllers\SimpananController::class, 'laporanTutup'])->name('simpanan.laporan.tutup');

    // Simpanan Berjangka (Deposito)
    Route::get('/simpanan-berjangka', [\App\Http\Controllers\SimpananBerjangkaController::class, 'index'])->name('simpanan-berjangka.index');
    Route::get('/simpanan-berjangka/create', [\App\Http\Controllers\SimpananBerjangkaController::class, 'create'])->name('simpanan-berjangka.create');
    Route::post('/simpanan-berjangka', [\App\Http\Controllers\SimpananBerjangkaController::class, 'store'])->name('simpanan-berjangka.store');
    Route::get('/simpanan-berjangka/{id}', [\App\Http\Controllers\SimpananBerjangkaController::class, 'show'])->name('simpanan-berjangka.show');
    Route::get('/simpanan-berjangka/{id}/cair', [\App\Http\Controllers\SimpananBerjangkaController::class, 'cairForm'])->name('simpanan-berjangka.cair')->middleware('role:super_admin');
    Route::post('/simpanan-berjangka/{id}/cair', [\App\Http\Controllers\SimpananBerjangkaController::class, 'cairSubmit'])->name('simpanan-berjangka.cair.submit')->middleware('role:super_admin');

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
    Route::get('/pembiayaan/registrasi', [\App\Http\Controllers\PembiayaanController::class, 'registrasi'])->name('pembiayaan.registrasi')->middleware('role:super_admin');
    Route::post('/pembiayaan/registrasi/{id}/approve', [\App\Http\Controllers\PembiayaanController::class, 'approvePengajuan'])->name('pembiayaan.registrasi.approve')->middleware('role:super_admin');

    // Pencairan
    Route::get('/pembiayaan/pencairan/{id}', [\App\Http\Controllers\PembiayaanController::class, 'pencairanForm'])->name('pembiayaan.pencairan')->middleware('role:super_admin');
    Route::post('/pembiayaan/pencairan/{id}', [\App\Http\Controllers\PembiayaanController::class, 'pencairanSubmit'])->name('pembiayaan.pencairan.submit')->middleware('role:super_admin');

    // Pelunasan
    Route::get('/pembiayaan/pelunasan/{id}', [\App\Http\Controllers\PembiayaanController::class, 'pelunasanForm'])->name('pembiayaan.pelunasan')->middleware('role:super_admin');
    Route::post('/pembiayaan/pelunasan/{id}', [\App\Http\Controllers\PembiayaanController::class, 'pelunasanSubmit'])->name('pembiayaan.pelunasan.submit')->middleware('role:super_admin');

    // Bayar Angsuran
    Route::post('/pembiayaan/angsuran/{jadwalId}/bayar', [\App\Http\Controllers\PembiayaanController::class, 'bayarAngsuran'])->name('pembiayaan.angsuran.bayar')->middleware('role:super_admin');

    // Cetak
    Route::get('/pembiayaan/cetak/sp3/{id}', [\App\Http\Controllers\PembiayaanController::class, 'cetakSP3'])->name('pembiayaan.cetak.sp3');
    Route::get('/pembiayaan/cetak/perjanjian/{id}', [\App\Http\Controllers\PembiayaanController::class, 'cetakPerjanjian'])->name('pembiayaan.cetak.perjanjian');

    // Generate Jadwal
    Route::post('/pembiayaan/{id}/generate-jadwal', [\App\Http\Controllers\PembiayaanController::class, 'generateJadwal'])->name('pembiayaan.generate_jadwal')->middleware('role:super_admin');

    // Laporan
    Route::get('/pembiayaan/laporan/pengajuan', [\App\Http\Controllers\PembiayaanController::class, 'laporanPengajuan'])->name('pembiayaan.laporan.pengajuan');
    Route::get('/pembiayaan/laporan/registrasi', [\App\Http\Controllers\PembiayaanController::class, 'laporanRegistrasi'])->name('pembiayaan.laporan.registrasi');
    Route::get('/pembiayaan/laporan/pembiayaan', [\App\Http\Controllers\PembiayaanController::class, 'laporanPembiayaan'])->name('pembiayaan.laporan.pembiayaan');
    Route::get('/pembiayaan/laporan/pencairan', [\App\Http\Controllers\PembiayaanController::class, 'laporanPencairan'])->name('pembiayaan.laporan.pencairan');

    // Export Excel Laporan
    Route::get('/pembiayaan/export/pengajuan', [\App\Http\Controllers\PembiayaanController::class, 'exportPengajuan'])->name('pembiayaan.export.pengajuan');
    Route::get('/pembiayaan/export/registrasi', [\App\Http\Controllers\PembiayaanController::class, 'exportRegistrasi'])->name('pembiayaan.export.registrasi');
    Route::get('/pembiayaan/export/pembiayaan', [\App\Http\Controllers\PembiayaanController::class, 'exportPembiayaan'])->name('pembiayaan.export.pembiayaan');
    Route::get('/pembiayaan/export/pencairan', [\App\Http\Controllers\PembiayaanController::class, 'exportPencairan'])->name('pembiayaan.export.pencairan');

    // Akuntansi Routes
    Route::get('/akuntansi', [\App\Http\Controllers\AkuntansiController::class, 'index'])->name('akuntansi.index');
    Route::get('/akuntansi/coa', [\App\Http\Controllers\AkuntansiController::class, 'coa'])->name('akuntansi.coa');
    Route::post('/akuntansi/coa', [\App\Http\Controllers\AkuntansiController::class, 'storeCoa'])->name('akuntansi.coa.store')->middleware('role:super_admin');
    Route::put('/akuntansi/coa/{id}', [\App\Http\Controllers\AkuntansiController::class, 'updateCoa'])->name('akuntansi.coa.update')->middleware('role:super_admin');
    Route::get('/akuntansi/kas', [\App\Http\Controllers\AkuntansiController::class, 'kas'])->name('akuntansi.kas');
    Route::post('/akuntansi/kas', [\App\Http\Controllers\AkuntansiController::class, 'storeKas'])->name('akuntansi.kas.store')->middleware('role:super_admin');
    Route::post('/akuntansi/kas/{id}/saldo', [\App\Http\Controllers\AkuntansiController::class, 'updateKasSaldo'])->name('akuntansi.kas.saldo')->middleware('role:super_admin');

    // Transaksi Jurnal
    Route::get('/akuntansi/jurnal', [\App\Http\Controllers\AkuntansiController::class, 'jurnal'])->name('akuntansi.jurnal');
    Route::get('/akuntansi/jurnal/create', [\App\Http\Controllers\AkuntansiController::class, 'createJurnal'])->name('akuntansi.jurnal.create');
    Route::post('/akuntansi/jurnal', [\App\Http\Controllers\AkuntansiController::class, 'storeJurnal'])->name('akuntansi.jurnal.store');
    Route::get('/akuntansi/jurnal/{id}', [\App\Http\Controllers\AkuntansiController::class, 'detailJurnal'])->name('akuntansi.jurnal.detail');

    // Pembatalan & Revisi
    Route::get('/akuntansi/jurnal/{id}/batal', [\App\Http\Controllers\AkuntansiController::class, 'batalForm'])->name('akuntansi.jurnal.batal')->middleware('role:super_admin');
    Route::post('/akuntansi/jurnal/{id}/batal', [\App\Http\Controllers\AkuntansiController::class, 'batalSubmit'])->name('akuntansi.jurnal.batal.submit')->middleware('role:super_admin');
    Route::get('/akuntansi/jurnal/{id}/revisi', [\App\Http\Controllers\AkuntansiController::class, 'revisiForm'])->name('akuntansi.jurnal.revisi')->middleware('role:super_admin');
    Route::post('/akuntansi/jurnal/{id}/revisi', [\App\Http\Controllers\AkuntansiController::class, 'revisiSubmit'])->name('akuntansi.jurnal.revisi.submit')->middleware('role:super_admin');

    // Buku Besar
    Route::get('/akuntansi/buku-besar', [\App\Http\Controllers\AkuntansiController::class, 'bukuBesar'])->name('akuntansi.buku_besar');

    // Laporan
    Route::get('/akuntansi/laporan/kas', [\App\Http\Controllers\AkuntansiController::class, 'laporanKas'])->name('akuntansi.laporan.kas');
    Route::get('/akuntansi/laporan/neraca-saldo', [\App\Http\Controllers\AkuntansiController::class, 'neracaSaldo'])->name('akuntansi.laporan.neraca_saldo');
    Route::get('/akuntansi/laporan/neraca', [\App\Http\Controllers\AkuntansiController::class, 'neraca'])->name('akuntansi.laporan.neraca');

    // Payroll Routes
    Route::get('/payroll', [\App\Http\Controllers\PayrollController::class, 'index'])->name('payroll.index');
    Route::get('/payroll/{anggotaId}', [\App\Http\Controllers\PayrollController::class, 'detail'])->name('payroll.detail');
    Route::post('/payroll/proses', [\App\Http\Controllers\PayrollController::class, 'prosesPotongan'])->name('payroll.proses')->middleware('role:super_admin');
    Route::get('/pay-later/pending', [\App\Http\Controllers\PayrollController::class, 'payLaterPending'])->name('payroll.pay_later_pending')->middleware('role:super_admin');
    Route::post('/pay-later/{id}/approve', [\App\Http\Controllers\PayrollController::class, 'approvePayLater'])->name('payroll.approve_pay_later')->middleware('role:super_admin');
    Route::post('/pay-later/{id}/reject', [\App\Http\Controllers\PayrollController::class, 'rejectPayLater'])->name('payroll.reject_pay_later')->middleware('role:super_admin');
    Route::post('/pay-later/{id}/process', [\App\Http\Controllers\PayrollController::class, 'processPayLater'])->name('payroll.process_pay_later')->middleware('role:super_admin');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
