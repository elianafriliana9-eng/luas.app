<?php

namespace Tests\Feature;

use App\Models\Anggota;
use App\Models\Pinbuk;
use App\Models\ProdukSimpanan;
use App\Models\RekeningSimpanan;
use App\Models\TransaksiSimpanan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SimpananTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $user;
    private ProdukSimpanan $produkPokok;
    private ProdukSimpanan $produkWajib;
    private ProdukSimpanan $produkSukarela;
    private Anggota $anggota;
    private RekeningSimpanan $rekPokok;
    private RekeningSimpanan $rekWajib;
    private RekeningSimpanan $reksukarela;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'super_admin']);
        $this->user = User::factory()->create(['role' => 'user']);

        $this->cabang = Cabang::create([
            'kode' => 'CBG-TST',
            'nama' => 'Cabang Test',
            'alamat' => 'Jl. Test No. 1',
            'telp' => '021-12345678',
            'aktif' => true,
        ]);

        $this->produkPokok = ProdukSimpanan::create([
            'kode' => 'SIMPOK', 'nama' => 'Simpanan Pokok', 'jenis' => 'pokok',
            'bunga_pa' => 0, 'minimal_saldo' => 100000, 'auto_bunga' => false, 'aktif' => true,
        ]);
        $this->produkWajib = ProdukSimpanan::create([
            'kode' => 'SIMWA', 'nama' => 'Simpanan Wajib', 'jenis' => 'wajib',
            'bunga_pa' => 0, 'minimal_saldo' => 50000, 'auto_bunga' => false, 'aktif' => true,
        ]);
        $this->produkSukarela = ProdukSimpanan::create([
            'kode' => 'SIMSUKA', 'nama' => 'Simpanan Sukarela', 'jenis' => 'sukarela',
            'bunga_pa' => 2.5, 'minimal_saldo' => 10000, 'auto_bunga' => true, 'aktif' => true,
        ]);

        $this->anggota = Anggota::create([
            'no_anggota' => 'ANG-TEST-001',
            'nik' => '3171012304900001',
            'nama_lengkap' => 'Test Anggota',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '1990-04-23',
            'jenis_kelamin' => 'L',
            'alamat' => 'Jl. Test',
            'no_hp' => '08123456789',
            'gaji_pokok' => 5000000,
            'tanggal_gajian' => 25,
            'tanggal_mulai_kerja' => '2020-01-15',
            'status' => 'aktif',
            'tanggal_masuk' => '2020-01-15',
        ]);

        $this->rekPokok = RekeningSimpanan::create([
            'anggota_id' => $this->anggota->id,
            'produk_id' => $this->produkPokok->id,
            'no_rekening' => '019900001',
            'saldo' => 150000,
            'status' => 'aktif',
            'tanggal_buka' => now(),
        ]);

        $this->rekWajib = RekeningSimpanan::create([
            'anggota_id' => $this->anggota->id,
            'produk_id' => $this->produkWajib->id,
            'no_rekening' => '029900001',
            'saldo' => 500000,
            'status' => 'aktif',
            'tanggal_buka' => now(),
        ]);

        $this->reksukarela = RekeningSimpanan::create([
            'anggota_id' => $this->anggota->id,
            'produk_id' => $this->produkSukarela->id,
            'no_rekening' => '039900001',
            'saldo' => 10000000,
            'status' => 'aktif',
            'tanggal_buka' => now(),
        ]);
    }

    protected function transaksiPayload(array $overrides = []): array
    {
        return array_merge([
            'anggota_id' => $this->anggota->id,
            'rekening_id' => $this->reksukarela->id,
            'jenis' => 'setoran',
            'nominal' => 100000,
            'keterangan' => 'Test setoran',
        ], $overrides);
    }

    // ───────────────────── INDEX & REKENING LIST ─────────────────────

    public function test_index_page_loads(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('simpanan.index'));
        $response->assertStatus(200);
    }

    public function test_index_filters_by_jenis(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('simpanan.index', ['jenis' => 'setoran']));
        $response->assertStatus(200);
    }

    public function test_index_filters_by_status(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('simpanan.index', ['status' => 'approved']));
        $response->assertStatus(200);
    }

    public function test_rekening_page_loads(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('simpanan.rekening'));
        $response->assertStatus(200);
    }

    public function test_rekening_page_filters_by_jenis_simpanan(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('simpanan.rekening', ['jenis_simpanan' => 'pokok']));
        $response->assertStatus(200);
    }

    // ───────────────────── CREATE & STORE ─────────────────────

    public function test_create_form_loads(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('simpanan.create'));
        $response->assertStatus(200);
    }

    public function test_create_form_loads_with_anggota_search(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('simpanan.create', ['anggota' => 'Test']));
        $response->assertStatus(200);
    }

    public function test_create_form_loads_with_anggota_uuid(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('simpanan.create', [
            'anggota' => $this->anggota->id,
            'jenis' => 'setoran',
        ]));
        $response->assertStatus(200);
    }

    public function test_store_setoran_approved_immediately(): void
    {
        $this->actingAs($this->admin);
        $saldoAwal = $this->reksukarela->fresh()->saldo;

        $response = $this->post(route('simpanan.store'), $this->transaksiPayload());

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $transaksi = TransaksiSimpanan::where('rekening_id', $this->reksukarela->id)
            ->where('jenis', 'setoran')
            ->latest('created_at')
            ->first();
        $this->assertNotNull($transaksi);
        $this->assertEquals('approved', $transaksi->status_approval);
        $this->assertEquals($this->reksukarela->id, $transaksi->rekening_id);
        $this->assertEquals(100000, (float) $transaksi->nominal);
        $this->assertEquals($saldoAwal, (float) $transaksi->saldo_sebelum);
        $this->assertEquals($saldoAwal + 100000, (float) $transaksi->saldo_sesudah);
        $this->assertEquals($saldoAwal + 100000, (float) $this->reksukarela->fresh()->saldo);
    }

    public function test_store_penarikan_kecil_approved_immediately(): void
    {
        $this->actingAs($this->admin);
        $saldoAwal = $this->reksukarela->fresh()->saldo;

        $response = $this->post(route('simpanan.store'), $this->transaksiPayload([
            'jenis' => 'penarikan',
            'nominal' => 500000,
        ]));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $transaksi = TransaksiSimpanan::where('rekening_id', $this->reksukarela->id)
            ->where('jenis', 'penarikan')
            ->latest('created_at')
            ->first();
        $this->assertNotNull($transaksi);
        $this->assertEquals('approved', $transaksi->status_approval);
        $this->assertEquals($saldoAwal - 500000, (float) $this->reksukarela->fresh()->saldo);
    }

    public function test_store_penarikan_besar_pending_approval(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('simpanan.store'), $this->transaksiPayload([
            'jenis' => 'penarikan',
            'nominal' => 2000000,
        ]));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $transaksi = TransaksiSimpanan::where('rekening_id', $this->reksukarela->id)
            ->where('jenis', 'penarikan')
            ->latest('created_at')
            ->first();
        $this->assertNotNull($transaksi);
        $this->assertEquals('pending', $transaksi->status_approval);
    }

    public function test_store_validates_required_fields(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('simpanan.store'), []);

        $response->assertSessionHasErrors(['anggota_id', 'rekening_id', 'jenis', 'nominal']);
    }

    public function test_store_validates_min_nominal(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('simpanan.store'), $this->transaksiPayload(['nominal' => 500]));

        $response->assertSessionHasErrors(['nominal']);
    }

    public function test_store_rejects_inactive_rekening(): void
    {
        $this->actingAs($this->admin);
        $this->reksukarela->update(['status' => 'blokir']);

        $response = $this->post(route('simpanan.store'), $this->transaksiPayload());

        $response->assertSessionHasErrors(['rekening_id']);
    }

    public function test_store_rejects_insufficient_saldo(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('simpanan.store'), $this->transaksiPayload([
            'jenis' => 'penarikan',
            'nominal' => 999999999,
        ]));

        $response->assertSessionHasErrors(['nominal']);
    }

    public function test_store_rejects_penarikan_pokok(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('simpanan.store'), $this->transaksiPayload([
            'rekening_id' => $this->rekPokok->id,
            'jenis' => 'penarikan',
            'nominal' => 10000,
        ]));

        $response->assertSessionHasErrors(['nominal']);
    }

    public function test_store_rejects_penarikan_wajib(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('simpanan.store'), $this->transaksiPayload([
            'rekening_id' => $this->rekWajib->id,
            'jenis' => 'penarikan',
            'nominal' => 10000,
        ]));

        $response->assertSessionHasErrors(['nominal']);
    }

    public function test_store_rejects_penarikan_below_minimal_saldo(): void
    {
        $this->actingAs($this->admin);
        $this->reksukarela->update(['saldo' => 50000]);

        $response = $this->post(route('simpanan.store'), $this->transaksiPayload([
            'jenis' => 'penarikan',
            'nominal' => 49000,
        ]));

        $response->assertSessionHasErrors(['nominal']);
    }

    // ───────────────────── APPROVAL ─────────────────────

    public function test_approval_page_loads_only_for_super_admin(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('simpanan.approval'));
        $response->assertStatus(200);
    }

    public function test_approval_page_blocked_for_user(): void
    {
        $this->actingAs($this->user);
        $response = $this->get(route('simpanan.approval'));
        $response->assertStatus(403);
    }

    public function test_approve_transaction(): void
    {
        $this->actingAs($this->admin);
        $transaksi = TransaksiSimpanan::create([
            'rekening_id' => $this->reksukarela->id,
            'user_id' => $this->admin->id,
            'no_transaksi' => 'TRX-TEST-APPROVE',
            'jenis' => 'penarikan',
            'nominal' => 500000,
            'saldo_sebelum' => $this->reksukarela->saldo,
            'saldo_sesudah' => $this->reksukarela->saldo - 500000,
            'channel' => 'admin',
            'status_approval' => 'pending',
        ]);

        $response = $this->post(route('simpanan.approve', $transaksi->id), ['action' => 'approve']);

        $response->assertSessionHas('success');
        $this->assertEquals('approved', $transaksi->fresh()->status_approval);
        $this->assertEquals($this->admin->id, $transaksi->fresh()->approved_by);
        $this->assertNotNull($transaksi->fresh()->approved_at);
    }

    public function test_reject_transaction_leaves_saldo_unchanged(): void
    {
        $this->actingAs($this->admin);
        $saldoAwal = $this->reksukarela->saldo;
        $nominal = 500000;

        $transaksi = TransaksiSimpanan::create([
            'rekening_id' => $this->reksukarela->id,
            'user_id' => $this->admin->id,
            'no_transaksi' => 'TRX-TEST-REJECT',
            'jenis' => 'penarikan',
            'nominal' => $nominal,
            'saldo_sebelum' => $saldoAwal,
            'saldo_sesudah' => $saldoAwal - $nominal,
            'channel' => 'admin',
            'status_approval' => 'pending',
        ]);

        $rekeningId = $this->reksukarela->id;

        $response = $this->post(route('simpanan.approve', $transaksi->id), ['action' => 'reject']);

        $response->assertSessionHas('success');
        $this->assertEquals('rejected', $transaksi->fresh()->status_approval);

        $rekening = RekeningSimpanan::find($rekeningId);
        $this->assertEquals($saldoAwal, (float) $rekening->saldo);
    }

    public function test_approve_transaction_blocked_for_user(): void
    {
        $this->actingAs($this->user);
        $transaksi = TransaksiSimpanan::create([
            'rekening_id' => $this->reksukarela->id,
            'user_id' => $this->admin->id,
            'no_transaksi' => 'TRX-TEST-403',
            'jenis' => 'penarikan',
            'nominal' => 5000,
            'saldo_sebelum' => 0,
            'saldo_sesudah' => 0,
            'channel' => 'admin',
            'status_approval' => 'pending',
        ]);

        $response = $this->post(route('simpanan.approve', $transaksi->id), ['action' => 'approve']);
        $response->assertStatus(403);
    }

    // ───────────────────── PINBUK ─────────────────────

    public function test_pinbuk_form_loads(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('simpanan.pinbuk'));
        $response->assertStatus(200);
    }

    public function test_pinbuk_store_no_approval(): void
    {
        $this->actingAs($this->admin);
        $saldoSumber = $this->reksukarela->fresh()->saldo;
        $saldoTujuan = $this->rekPokok->fresh()->saldo;

        $response = $this->post(route('simpanan.pinbuk.store'), [
            'rekening_sumber_id' => $this->reksukarela->id,
            'rekening_tujuan_id' => $this->rekPokok->id,
            'nominal' => 500000,
            'keterangan' => 'Test pinbuk',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertEquals($saldoSumber - 500000, (float) $this->reksukarela->fresh()->saldo);
        $this->assertEquals($saldoTujuan + 500000, (float) $this->rekPokok->fresh()->saldo);

        $pinbuk = Pinbuk::where('rekening_sumber_id', $this->reksukarela->id)->first();
        $this->assertNotNull($pinbuk);
        $this->assertEquals('approved', $pinbuk->status_approval);

        $transaksiKeluar = TransaksiSimpanan::where('rekening_id', $this->reksukarela->id)
            ->where('jenis', 'pinbuk_keluar')->first();
        $this->assertNotNull($transaksiKeluar);
        $this->assertEquals('approved', $transaksiKeluar->status_approval);

        $transaksiMasuk = TransaksiSimpanan::where('rekening_id', $this->rekPokok->id)
            ->where('jenis', 'pinbuk_masuk')->first();
        $this->assertNotNull($transaksiMasuk);
        $this->assertEquals('approved', $transaksiMasuk->status_approval);
    }

    public function test_pinbuk_store_needs_approval(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('simpanan.pinbuk.store'), [
            'rekening_sumber_id' => $this->reksukarela->id,
            'rekening_tujuan_id' => $this->rekPokok->id,
            'nominal' => 5000000,
            'keterangan' => 'Test pinbuk besar',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $pinbuk = Pinbuk::where('rekening_sumber_id', $this->reksukarela->id)->first();
        $this->assertNotNull($pinbuk);
        $this->assertEquals('pending', $pinbuk->status_approval);
    }

    public function test_pinbuk_rejects_pokok_wajib_as_sumber(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('simpanan.pinbuk.store'), [
            'rekening_sumber_id' => $this->rekPokok->id,
            'rekening_tujuan_id' => $this->reksukarela->id,
            'nominal' => 50000,
        ]);

        $response->assertSessionHasErrors(['rekening_sumber_id']);
    }

    public function test_pinbuk_rejects_insufficient_saldo(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('simpanan.pinbuk.store'), [
            'rekening_sumber_id' => $this->reksukarela->id,
            'rekening_tujuan_id' => $this->rekPokok->id,
            'nominal' => 999999999,
        ]);

        $response->assertSessionHasErrors(['nominal']);
    }

    public function test_pinbuk_rejects_inactive_rekening(): void
    {
        $this->actingAs($this->admin);
        $this->reksukarela->update(['status' => 'blokir']);

        $response = $this->post(route('simpanan.pinbuk.store'), [
            'rekening_sumber_id' => $this->reksukarela->id,
            'rekening_tujuan_id' => $this->rekPokok->id,
            'nominal' => 50000,
        ]);

        $response->assertSessionHasErrors(['rekening_sumber_id']);
    }

    public function test_pinbuk_validates_required_fields(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('simpanan.pinbuk.store'), []);
        $response->assertSessionHasErrors(['rekening_sumber_id', 'rekening_tujuan_id', 'nominal']);
    }

    public function test_pinbuk_validates_different_rekening(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('simpanan.pinbuk.store'), [
            'rekening_sumber_id' => $this->reksukarela->id,
            'rekening_tujuan_id' => $this->reksukarela->id,
            'nominal' => 50000,
        ]);

        $response->assertSessionHasErrors(['rekening_tujuan_id']);
    }

    // ───────────────────── PINBUK APPROVAL ─────────────────────

    public function test_pinbuk_approval_page_loads(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('simpanan.pinbuk.approval'));
        $response->assertStatus(200);
    }

    public function test_pinbuk_approve(): void
    {
        $this->actingAs($this->admin);
        $saldoSumber = $this->reksukarela->fresh()->saldo;
        $saldoTujuan = $this->rekPokok->fresh()->saldo;

        $response = $this->post(route('simpanan.pinbuk.store'), [
            'rekening_sumber_id' => $this->reksukarela->id,
            'rekening_tujuan_id' => $this->rekPokok->id,
            'nominal' => 5000000,
        ]);

        $pinbuk = Pinbuk::where('rekening_sumber_id', $this->reksukarela->id)->first();

        $response2 = $this->post(route('simpanan.pinbuk.approve', $pinbuk->id));

        $response2->assertSessionHas('success');
        $this->assertEquals('approved', $pinbuk->fresh()->status_approval);
    }

    public function test_pinbuk_reject_reverses_saldo(): void
    {
        $this->actingAs($this->admin);
        $saldoSumber = $this->reksukarela->fresh()->saldo;
        $saldoTujuan = $this->rekPokok->fresh()->saldo;

        $this->post(route('simpanan.pinbuk.store'), [
            'rekening_sumber_id' => $this->reksukarela->id,
            'rekening_tujuan_id' => $this->rekPokok->id,
            'nominal' => 5000000,
        ]);

        $pinbuk = Pinbuk::where('rekening_sumber_id', $this->reksukarela->id)->first();
        $rekSumberId = $this->reksukarela->id;
        $rekTujuanId = $this->rekPokok->id;

        $response = $this->post(route('simpanan.pinbuk.reject', $pinbuk->id));

        $response->assertSessionHas('success');
        $this->assertEquals('rejected', $pinbuk->fresh()->status_approval);
        $this->assertEquals($saldoSumber, (float) RekeningSimpanan::find($rekSumberId)->saldo);
        $this->assertEquals($saldoTujuan, (float) RekeningSimpanan::find($rekTujuanId)->saldo);
    }

    // ───────────────────── CANCEL ─────────────────────

    public function test_cancel_form_loads(): void
    {
        $this->actingAs($this->admin);
        $transaksi = TransaksiSimpanan::create([
            'rekening_id' => $this->reksukarela->id,
            'user_id' => $this->admin->id,
            'no_transaksi' => 'TRX-TEST-CANCEL',
            'jenis' => 'setoran',
            'nominal' => 100000,
            'saldo_sebelum' => 0,
            'saldo_sesudah' => 100000,
            'channel' => 'admin',
            'status_approval' => 'approved',
        ]);

        $response = $this->get(route('simpanan.cancel', $transaksi->id));
        $response->assertStatus(200);
    }

    public function test_cancel_setoran_reverses_saldo(): void
    {
        $this->actingAs($this->admin);
        $saldoAwal = $this->reksukarela->fresh()->saldo;

        $this->post(route('simpanan.store'), $this->transaksiPayload(['nominal' => 500000]));
        $transaksi = TransaksiSimpanan::where('rekening_id', $this->reksukarela->id)
            ->where('jenis', 'setoran')
            ->latest('created_at')
            ->first();

        $saldoSetelahSetor = (float) $this->reksukarela->fresh()->saldo;
        $this->assertEquals($saldoAwal + 500000, $saldoSetelahSetor);

        $response = $this->post(route('simpanan.cancel.submit', $transaksi->id), [
            'alasan' => 'Test cancel',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertEquals($saldoAwal, (float) $this->reksukarela->fresh()->saldo);
        $this->assertTrue((bool) $transaksi->fresh()->dibatalkan);
    }

    public function test_cancel_penarikan_reverses_saldo(): void
    {
        $this->actingAs($this->admin);
        $saldoAwal = $this->reksukarela->fresh()->saldo;

        $this->post(route('simpanan.store'), $this->transaksiPayload([
            'jenis' => 'penarikan',
            'nominal' => 500000,
        ]));

        $transaksi = TransaksiSimpanan::where('rekening_id', $this->reksukarela->id)
            ->where('jenis', 'penarikan')
            ->latest('created_at')
            ->first();

        $saldoSetelahTarik = (float) $this->reksukarela->fresh()->saldo;
        $this->assertEquals($saldoAwal - 500000, $saldoSetelahTarik);

        $response = $this->post(route('simpanan.cancel.submit', $transaksi->id), [
            'alasan' => 'Cancel penarikan',
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals($saldoAwal, (float) $this->reksukarela->fresh()->saldo);
    }

    public function test_cancel_rejects_if_already_cancelled(): void
    {
        $this->actingAs($this->admin);
        $transaksi = TransaksiSimpanan::create([
            'rekening_id' => $this->reksukarela->id,
            'user_id' => $this->admin->id,
            'no_transaksi' => 'TRX-TEST-ALREADY',
            'jenis' => 'setoran',
            'nominal' => 50000,
            'saldo_sebelum' => 0,
            'saldo_sesudah' => 50000,
            'channel' => 'admin',
            'status_approval' => 'approved',
            'dibatalkan' => true,
        ]);

        $response = $this->post(route('simpanan.cancel.submit', $transaksi->id), [
            'alasan' => 'Should fail',
        ]);

        $response->assertSessionHas('error');
    }

    public function test_cancel_validates_alasan(): void
    {
        $this->actingAs($this->admin);
        $transaksi = TransaksiSimpanan::create([
            'rekening_id' => $this->reksukarela->id,
            'user_id' => $this->admin->id,
            'no_transaksi' => 'TRX-TEST-ALASAN',
            'jenis' => 'setoran',
            'nominal' => 50000,
            'saldo_sebelum' => 0,
            'saldo_sesudah' => 50000,
            'channel' => 'admin',
            'status_approval' => 'approved',
        ]);

        $response = $this->post(route('simpanan.cancel.submit', $transaksi->id), []);
        $response->assertSessionHasErrors(['alasan']);
    }

    // ───────────────────── BLOKIR ─────────────────────

    public function test_blokir_form_loads(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('simpanan.blokir', $this->reksukarela->id));
        $response->assertStatus(200);
    }

    public function test_blokir_submit_blocks_rekening(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('simpanan.blokir.submit', $this->reksukarela->id), [
            'alasan' => 'Test blokir',
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals('blokir', $this->reksukarela->fresh()->status);
    }

    public function test_buka_blokir_reactivates(): void
    {
        $this->actingAs($this->admin);
        $this->reksukarela->update(['status' => 'blokir']);

        $response = $this->post(route('simpanan.buka_blokir', $this->reksukarela->id));

        $response->assertSessionHas('success');
        $this->assertEquals('aktif', $this->reksukarela->fresh()->status);
    }

    // ───────────────────── TUTUP REKENING ─────────────────────

    public function test_tutup_form_loads(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('simpanan.tutup', $this->reksukarela->id));
        $response->assertStatus(200);
    }

    public function test_tutup_rejects_if_saldo_positive(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('simpanan.tutup.submit', $this->reksukarela->id), [
            'alasan' => 'Test tutup',
        ]);

        $response->assertSessionHasErrors(['saldo']);
    }

    public function test_tutup_succeeds_with_zero_saldo(): void
    {
        $this->actingAs($this->admin);
        $this->reksukarela->update(['saldo' => 0]);

        $response = $this->post(route('simpanan.tutup.submit', $this->reksukarela->id), [
            'alasan' => 'Test tutup zero saldo',
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals('tutup', $this->reksukarela->fresh()->status);
        $this->assertNotNull($this->reksukarela->fresh()->tanggal_tutup);
    }

    // ───────────────────── REKENING BARU ─────────────────────

    public function test_rekening_baru_form_loads(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('simpanan.rekening_baru'));
        $response->assertStatus(200);
    }

    public function test_rekening_baru_store(): void
    {
        $this->actingAs($this->admin);

        $produkBaru = ProdukSimpanan::create([
            'kode' => 'SIMPANL', 'nama' => 'Simpanan Khusus', 'jenis' => 'sukarela',
            'bunga_pa' => 3, 'minimal_saldo' => 0, 'auto_bunga' => false, 'aktif' => true,
        ]);

        $response = $this->post(route('simpanan.rekening_baru.store'), [
            'anggota_id' => $this->anggota->id,
            'produk_id' => $produkBaru->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $exists = RekeningSimpanan::where('anggota_id', $this->anggota->id)
            ->where('produk_id', $produkBaru->id)
            ->exists();
        $this->assertTrue($exists);
    }

    public function test_rekening_baru_validates(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('simpanan.rekening_baru.store'), []);
        $response->assertSessionHasErrors(['anggota_id', 'produk_id']);
    }

    // ───────────────────── LAPORAN ─────────────────────

    public function test_laporan_rekap_loads(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('simpanan.laporan.rekap'));
        $response->assertStatus(200);
    }

    public function test_laporan_setoran_loads(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('simpanan.laporan.setoran'));
        $response->assertStatus(200);
    }

    public function test_laporan_penarikan_loads(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('simpanan.laporan.penarikan'));
        $response->assertStatus(200);
    }

    public function test_laporan_regist_loads(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('simpanan.laporan.regist'));
        $response->assertStatus(200);
    }

    public function test_laporan_pinbuk_loads(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('simpanan.laporan.pinbuk'));
        $response->assertStatus(200);
    }

    public function test_statement_page_loads(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('simpanan.statement', $this->reksukarela->id));
        $response->assertStatus(200);
    }

    // ───────────────────── EXPORT ─────────────────────

    public function test_export_rekening(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('simpanan.export.rekening'));
        $response->assertStatus(200);
        $this->assertStringContainsString('attachment', $response->headers->get('Content-Disposition') ?? '');
    }

    public function test_export_transaksi(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('simpanan.export.transaksi'));
        $response->assertStatus(200);
    }

    public function test_export_rekap(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('simpanan.export.rekap'));
        $response->assertStatus(200);
    }

    public function test_export_setoran(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('simpanan.export.setoran'));
        $response->assertStatus(200);
    }

    public function test_export_penarikan(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('simpanan.export.penarikan'));
        $response->assertStatus(200);
    }

    public function test_export_regist(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('simpanan.export.regist'));
        $response->assertStatus(200);
    }

    public function test_export_pinbuk(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('simpanan.export.pinbuk'));
        $response->assertStatus(200);
    }

    public function test_export_statement(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('simpanan.export.statement', $this->reksukarela->id));
        $response->assertStatus(200);
    }

    // ───────────────────── IMPORT UPLOAD ─────────────────────

    public function test_upload_form_loads(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('simpanan.upload'));
        $response->assertStatus(200);
    }

    public function test_download_template(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('simpanan.download_template'));
        $response->assertStatus(200);
    }

    // ───────────────────── TRANSACTION LIST ─────────────────────

    public function test_transaksi_redirects_to_index(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('simpanan.transaksi'));
        $response->assertRedirect(route('simpanan.index'));
    }
}
