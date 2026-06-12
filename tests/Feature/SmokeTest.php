<?php

namespace Tests\Feature;

use App\Models\Anggota;
use App\Models\Cabang;
use App\Models\ChartOfAccount;
use App\Models\Jurnal;
use App\Models\JurnalDetail;
use App\Models\Kas;
use App\Models\KonfigurasiCoa;
use App\Models\Pembiayaan;
use App\Models\PengajuanPembiayaan;
use App\Models\PeriodeTutup;
use App\Models\Pinbuk;
use App\Models\PotonganGaji;
use App\Models\ProdukPembiayaan;
use App\Models\ProdukSimpanan;
use App\Models\RekeningSimpanan;
use App\Models\SimpananBerjangka;
use App\Models\TransaksiSimpanan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SmokeTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $teller;
    private Cabang $cabang;
    private Anggota $anggota;
    private Anggota $anggotaKeluar;
    private RekeningSimpanan $rekPokok;
    private RekeningSimpanan $reksukarela;
    private ProdukSimpanan $produkPokok;
    private ProdukSimpanan $produkWajib;
    private ProdukSimpanan $produkSukarela;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'super_admin']);
        $this->teller = User::factory()->create(['role' => 'teller']);

        $this->cabang = Cabang::create([
            'kode' => 'CBG-SMK', 'nama' => 'Cabang Smoke Test',
            'alamat' => 'Jl. Test No. 1', 'telp' => '021-12345678', 'aktif' => true,
        ]);

        $this->produkPokok = ProdukSimpanan::create([
            'kode' => 'SIMPOK', 'nama' => 'Simpanan Pokok', 'jenis' => 'pokok',
            'bunga_pa' => 0, 'minimal_saldo' => 100000, 'auto_bunga' => false, 'aktif' => true,
        ]);
        $this->produkWajib = ProdukSimpanan::create([
            'kode' => 'SIMWA', 'nama' => 'Simpanan Wajib', 'jenis' => 'wajib',
            'bunga_pa' => 3, 'minimal_saldo' => 50000, 'auto_bunga' => true, 'aktif' => true,
        ]);
        $this->produkSukarela = ProdukSimpanan::create([
            'kode' => 'SIMSUKA', 'nama' => 'Simpanan Sukarela', 'jenis' => 'sukarela',
            'bunga_pa' => 2, 'minimal_saldo' => 0, 'auto_bunga' => true, 'aktif' => true,
        ]);

        $this->anggota = Anggota::create([
            'cabang_id' => $this->cabang->id,
            'no_anggota' => 'SMK-001',
            'nama_lengkap' => 'Anggota Smoke Test',
            'nik' => '9999999999999999',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '1990-01-01',
            'jenis_kelamin' => 'L',
            'alamat' => 'Jl. Test',
            'no_hp' => '08123456789',
            'status' => 'aktif',
            'tanggal_masuk' => '2024-01-01',
            'gaji_pokok' => 5000000,
            'tanggal_gajian' => 25,
        ]);

        $this->anggotaKeluar = Anggota::create([
            'cabang_id' => $this->cabang->id,
            'no_anggota' => 'SMK-002',
            'nama_lengkap' => 'Anggota Keluar',
            'nik' => '8888888888888888',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '1990-01-01',
            'jenis_kelamin' => 'L',
            'alamat' => 'Jl. Test',
            'no_hp' => '08123456788',
            'status' => 'keluar',
            'tanggal_masuk' => '2020-01-01',
            'tanggal_keluar' => '2024-06-01',
            'alasan_keluar' => 'Mengundurkan diri',
            'gaji_pokok' => 5000000,
            'tanggal_gajian' => 25,
        ]);

        $this->rekPokok = RekeningSimpanan::create([
            'anggota_id' => $this->anggota->id,
            'produk_id' => $this->produkPokok->id,
            'no_rekening' => 'SMK-001-POKOK',
            'saldo' => 100000,
            'aktif' => true,
            'tanggal_buka' => '2024-01-01',
        ]);
        $this->reksukarela = RekeningSimpanan::create([
            'anggota_id' => $this->anggota->id,
            'produk_id' => $this->produkSukarela->id,
            'no_rekening' => 'SMK-001-SUKARELA',
            'saldo' => 10000000,
            'aktif' => true,
            'tanggal_buka' => '2024-01-01',
        ]);
    }

    public function test_dashboard_page(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('dashboard'));
        $response->assertOk();
    }

    public function test_login_page(): void
    {
        $response = $this->get(route('login'));
        $response->assertOk();
    }

    // ================ ANGGOTA ================

    public function test_anggota_index(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('anggota.index'));
        $response->assertOk();
    }

    public function test_anggota_create(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('anggota.create'));
        $response->assertOk();
    }

    public function test_anggota_show(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('anggota.show', $this->anggota->id));
        $response->assertOk();
    }

    public function test_anggota_edit(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('anggota.edit', $this->anggota->id));
        $response->assertOk();
    }

    public function test_anggota_saldo(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('anggota.saldo'));
        $response->assertOk();
    }

    public function test_anggota_history(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('anggota.history', $this->anggota->id));
        $response->assertOk();
    }

    public function test_anggota_keluar_form(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('anggota.keluar', $this->anggota->id));
        $response->assertOk();
    }

    public function test_anggota_approval_keluar(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('anggota.approval_keluar'));
        $response->assertOk();
    }

    public function test_anggota_pending_approval(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('anggota.pending_approval'));
        $response->assertOk();
    }

    public function test_anggota_import_form(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('anggota.import'));
        $response->assertOk();
    }

    public function test_anggota_import_master_form(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('anggota.import.master'));
        $response->assertOk();
    }

    public function test_anggota_download_template(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('anggota.download_template'));
        $response->assertOk();
    }

    // ================ LAPORAN ANGGOTA ================

    public function test_anggota_laporan_saldo(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('anggota.laporan.saldo'));
        $response->assertOk();
    }

    public function test_anggota_laporan_profil(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('anggota.laporan.profil'));
        $response->assertOk();
    }

    public function test_anggota_laporan_rekap(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('anggota.laporan.rekap'));
        $response->assertOk();
    }

    public function test_anggota_laporan_keluar(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('anggota.laporan.keluar'));
        $response->assertOk();
    }

    // ================ EXPORT ANGGOTA ================

    public function test_anggota_export_data(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('anggota.export.data'));
        $response->assertOk();
    }

    public function test_anggota_export_saldo(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('anggota.export.saldo'));
        $response->assertOk();
    }

    public function test_anggota_export_profil(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('anggota.export.profil'));
        $response->assertOk();
    }

    public function test_anggota_export_rekap(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('anggota.export.rekap'));
        $response->assertOk();
    }

    public function test_anggota_export_keluar(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('anggota.export.keluar'));
        $response->assertOk();
    }

    // ================ PDF ANGGOTA ================

    public function test_anggota_pdf_keluar(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('anggota.pdf_keluar', $this->anggotaKeluar->id));
        $response->assertOk();
    }

    public function test_anggota_pdf_profil(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('anggota.pdf.profil'));
        $response->assertOk();
    }

    // ================ SIMPANAN ================

    public function test_simpanan_index(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('simpanan.index'));
        $response->assertOk();
    }

    public function test_simpanan_create(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('simpanan.create'));
        $response->assertOk();
    }

    public function test_simpanan_rekening(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('simpanan.rekening'));
        $response->assertOk();
    }

    public function test_simpanan_transaksi(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('simpanan.transaksi'));
        $response->assertRedirect(route('simpanan.index'));
    }

    public function test_simpanan_approval(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('simpanan.approval'));
        $response->assertOk();
    }

    public function test_simpanan_pinbuk_form(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('simpanan.pinbuk'));
        $response->assertOk();
    }

    public function test_simpanan_pinbuk_approval(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('simpanan.pinbuk.approval'));
        $response->assertOk();
    }

    public function test_simpanan_statement(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('simpanan.statement', $this->reksukarela->id));
        $response->assertOk();
    }

    public function test_simpanan_rekening_baru_form(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('simpanan.rekening_baru'));
        $response->assertOk();
    }

    public function test_simpanan_upload_form(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('simpanan.upload'));
        $response->assertOk();
    }

    public function test_simpanan_download_template(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('simpanan.download_template'));
        $response->assertOk();
    }

    // ================ LAPORAN SIMPANAN ================

    public function test_simpanan_laporan_rekap(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('simpanan.laporan.rekap'));
        $response->assertOk();
    }

    public function test_simpanan_laporan_setoran(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('simpanan.laporan.setoran'));
        $response->assertOk();
    }

    public function test_simpanan_laporan_penarikan(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('simpanan.laporan.penarikan'));
        $response->assertOk();
    }

    public function test_simpanan_laporan_regist(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('simpanan.laporan.regist'));
        $response->assertOk();
    }

    public function test_simpanan_laporan_pinbuk(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('simpanan.laporan.pinbuk'));
        $response->assertOk();
    }

    public function test_simpanan_laporan_saldo(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('simpanan.laporan.saldo'));
        $response->assertOk();
    }

    public function test_simpanan_laporan_statement(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('simpanan.laporan.statement'));
        $response->assertOk();
    }

    public function test_simpanan_laporan_blokir(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('simpanan.laporan.blokir'));
        $response->assertOk();
    }

    public function test_simpanan_laporan_tutup(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('simpanan.laporan.tutup'));
        $response->assertOk();
    }

    // ================ PDF SIMPANAN ================

    public function test_simpanan_pdf_rekap(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('simpanan.pdf.rekap'));
        $response->assertOk();
    }

    // ================ SIMPANAN BERJANGKA ================

    public function test_simpanan_berjangka_index(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('simpanan-berjangka.index'));
        $response->assertOk();
    }

    public function test_simpanan_berjangka_create(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('simpanan-berjangka.create'));
        $response->assertOk();
    }

    // ================ KONFIGURASI COA ================

    public function test_konfigurasi_coa_index(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('konfigurasi-coa.index'));
        $response->assertOk();
    }

    // ================ PEMBIAYAAN ================

    public function test_pembiayaan_index(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('pembiayaan.index'));
        $response->assertOk();
    }

    public function test_pembiayaan_pengajuan(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('pembiayaan.pengajuan'));
        $response->assertOk();
    }

    public function test_pembiayaan_create_pengajuan(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('pembiayaan.pengajuan.create'));
        $response->assertOk();
    }

    public function test_pembiayaan_registrasi(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('pembiayaan.registrasi'));
        $response->assertOk();
    }

    public function test_pembiayaan_transaksi(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('pembiayaan.transaksi'));
        $response->assertOk();
    }

    public function test_pembiayaan_simulasi(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('pembiayaan.simulasi'));
        $response->assertOk();
    }

    // ================ LAPORAN PEMBIAYAAN ================

    public function test_pembiayaan_laporan_pengajuan(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('pembiayaan.laporan.pengajuan'));
        $response->assertOk();
    }

    public function test_pembiayaan_laporan_registrasi(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('pembiayaan.laporan.registrasi'));
        $response->assertOk();
    }

    public function test_pembiayaan_laporan_pencairan(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('pembiayaan.laporan.pencairan'));
        $response->assertOk();
    }

    public function test_pembiayaan_laporan_pembiayaan(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('pembiayaan.laporan.pembiayaan'));
        $response->assertOk();
    }

    // ================ PAYROLL ================

    public function test_payroll_index(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('payroll.index'));
        $response->assertOk();
    }

    public function test_payroll_detail(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('payroll.detail', $this->anggota->id));
        $response->assertOk();
    }

    public function test_payroll_pay_later_pending(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('payroll.pay_later_pending'));
        $response->assertOk();
    }

    // ================ AKUNTANSI ================

    public function test_akuntansi_index(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('akuntansi.index'));
        $response->assertOk();
    }

    public function test_akuntansi_coa(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('akuntansi.coa'));
        $response->assertOk();
    }

    public function test_akuntansi_jurnal(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('akuntansi.jurnal'));
        $response->assertOk();
    }

    public function test_akuntansi_create_jurnal(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('akuntansi.jurnal.create'));
        $response->assertOk();
    }

    public function test_akuntansi_kas(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('akuntansi.kas'));
        $response->assertOk();
    }

    public function test_akuntansi_buku_besar(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('akuntansi.buku_besar'));
        $response->assertOk();
    }

    public function test_akuntansi_neraca(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('akuntansi.laporan.neraca'));
        $response->assertOk();
    }

    public function test_akuntansi_neraca_saldo(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('akuntansi.laporan.neraca_saldo'));
        $response->assertOk();
    }

    public function test_akuntansi_laporan_kas(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('akuntansi.laporan.kas'));
        $response->assertOk();
    }

    // ================ PERUSAHAAN ================

    public function test_perusahaan_index(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('perusahaan.index'));
        $response->assertOk();
    }

    public function test_perusahaan_create(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('perusahaan.create'));
        $response->assertOk();
    }

    // ================ PROFILE ================

    public function test_profile_edit(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('profile.edit'));
        $response->assertOk();
    }
}
