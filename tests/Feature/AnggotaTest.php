<?php

namespace Tests\Feature;

use App\Models\Anggota;
use App\Models\Cabang;
use App\Models\PotonganGaji;
use App\Models\ProdukSimpanan;
use App\Models\RekeningSimpanan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnggotaTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Cabang $cabang;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'super_admin',
        ]);

        $this->cabang = Cabang::create([
            'kode' => 'CBG-TST',
            'nama' => 'Cabang Test',
            'alamat' => 'Jl. Test No. 1',
            'telp' => '021-12345678',
            'aktif' => true,
        ]);

        collect([
            ['kode' => 'SIMPOK', 'nama' => 'Simpanan Pokok', 'jenis' => 'pokok', 'bunga_pa' => 0, 'minimal_saldo' => 100000, 'auto_bunga' => false, 'aktif' => true],
            ['kode' => 'SIMWA', 'nama' => 'Simpanan Wajib', 'jenis' => 'wajib', 'bunga_pa' => 0, 'minimal_saldo' => 50000, 'auto_bunga' => false, 'aktif' => true],
            ['kode' => 'SIMSUKA', 'nama' => 'Simpanan Sukarela', 'jenis' => 'sukarela', 'bunga_pa' => 2.5, 'minimal_saldo' => 10000, 'auto_bunga' => true, 'aktif' => true],
        ])->each(fn($p) => ProdukSimpanan::create($p));
    }

    private function anggotaPayload(array $overrides = []): array
    {
        return array_merge([
            'cabang_id' => $this->cabang->id,
            'nik' => '3171012304900001',
            'nama_lengkap' => 'Test Anggota Baru',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '1990-04-23',
            'jenis_kelamin' => 'L',
            'alamat' => 'Jl. Test No. 123, Jakarta Selatan',
            'no_hp' => '08123456789',
            'email' => 'test@example.com',
            'gaji_pokok' => 5000000,
            'tanggal_gajian' => 25,
            'tanggal_mulai_kerja' => '2020-01-15',
            'no_pegawai' => 'PEG-001',
        ], $overrides);
    }

    public function test_store_anggota_creates_anggota_rekening_and_potongan(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('anggota.store'), $this->anggotaPayload());

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $anggota = Anggota::where('nik', '3171012304900001')->first();
        $this->assertNotNull($anggota);
        $this->assertEquals('aktif', $anggota->status);
        $this->assertEquals(5000000, (float) $anggota->gaji_pokok);
        $this->assertStringStartsWith('ANG-', $anggota->no_anggota);

        $rekenings = RekeningSimpanan::where('anggota_id', $anggota->id)->get();
        $this->assertCount(3, $rekenings);
        $this->assertEquals(0, (float) $rekenings->first()->saldo);
        $this->assertEquals('aktif', $rekenings->first()->status);

        $r = fn($prefix) => $rekenings->first(fn($r) => str_starts_with($r->no_rekening, $prefix));
        $this->assertNotNull($r('01'));
        $this->assertNotNull($r('02'));
        $this->assertNotNull($r('03'));

        $potongans = PotonganGaji::where('anggota_id', $anggota->id)
            ->where('jenis_potongan', 'simpanan')
            ->get();
        $this->assertCount(3, $potongans);

        foreach ($potongans as $p) {
            $this->assertEquals(50000, (float) $p->nominal_potongan);
            $this->assertEquals('pending', $p->status);
            $this->assertEquals(5000000, (float) $p->gaji_bruto);
            $this->assertEquals(4950000, (float) $p->gaji_diterima);
        }

        $periodeDates = $potongans->pluck('periode')->map(fn ($d) => $d->format('Y-m'));
        $this->assertEquals(3, $periodeDates->unique()->count());
    }

    public function test_store_anggota_validates_required_fields(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('anggota.store'), []);

        $response->assertSessionHasErrors(['nik', 'nama_lengkap', 'tempat_lahir', 'tanggal_lahir', 'jenis_kelamin', 'alamat', 'no_hp', 'cabang_id']);
    }

    public function test_store_anggota_rejects_duplicate_nik(): void
    {
        $this->actingAs($this->admin);

        $this->post(route('anggota.store'), $this->anggotaPayload());

        $response = $this->post(route('anggota.store'), $this->anggotaPayload());

        $response->assertSessionHasErrors(['nik']);
    }

    public function test_store_anggota_without_gaji_still_creates_potongan(): void
    {
        $this->actingAs($this->admin);

        $payload = $this->anggotaPayload(['gaji_pokok' => null]);
        $this->post(route('anggota.store'), $payload);

        $anggota = Anggota::where('nik', '3171012304900001')->first();
        $this->assertNotNull($anggota);

        $potongans = PotonganGaji::where('anggota_id', $anggota->id)->get();
        $this->assertCount(3, $potongans);
        $this->assertEquals(0, (float) $potongans->first()->gaji_bruto);
        $this->assertEquals(0, (float) $potongans->first()->gaji_diterima);
    }

    public function test_store_anggota_creates_rekening_with_zero_saldo(): void
    {
        $this->actingAs($this->admin);

        $this->post(route('anggota.store'), $this->anggotaPayload());

        $anggota = Anggota::where('nik', '3171012304900001')->first();
        $rekenings = RekeningSimpanan::where('anggota_id', $anggota->id)->get();

        foreach ($rekenings as $rekening) {
            $this->assertEquals(0, (float) $rekening->saldo);
            $this->assertEquals('aktif', $rekening->status);
        }
    }

    public function test_index_anggota_page_loads(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('anggota.index'));

        $response->assertStatus(200);
    }

    public function test_store_anggota_creates_no_anggota_auto(): void
    {
        $this->actingAs($this->admin);

        $this->post(route('anggota.store'), $this->anggotaPayload(['nik' => '3171012304900002']));
        $this->post(route('anggota.store'), $this->anggotaPayload(['nik' => '3171012304900003']));

        $anggota1 = Anggota::where('nik', '3171012304900002')->first();
        $anggota2 = Anggota::where('nik', '3171012304900003')->first();

        $this->assertNotEquals($anggota1->no_anggota, $anggota2->no_anggota);
        $this->assertStringStartsWith('ANG-' . now()->year . '-', $anggota1->no_anggota);
        $this->assertStringStartsWith('ANG-' . now()->year . '-', $anggota2->no_anggota);
    }

    public function test_role_middleware_blocks_non_admin(): void
    {
        $user = User::factory()->create(['role' => 'teller']);
        $this->actingAs($user);

        $response = $this->get(route('anggota.approval_keluar'));

        $response->assertStatus(403);
    }

    public function test_potongan_gaji_periode_mulai_bulan_depan(): void
    {
        $this->actingAs($this->admin);

        $this->post(route('anggota.store'), $this->anggotaPayload(['tanggal_gajian' => 25]));

        $anggota = Anggota::where('nik', '3171012304900001')->first();
        $potongans = PotonganGaji::where('anggota_id', $anggota->id)->get();

        $expectedBulanMulai = now()->startOfMonth()->addMonth();
        foreach ($potongans as $i => $p) {
            $expectedPeriode = $expectedBulanMulai->copy()->addMonths($i)->day(25);
            $this->assertEquals($expectedPeriode->format('Y-m-d'), $p->periode->format('Y-m-d'));
        }
    }
}
