<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel untuk tracking pemotongan gaji karyawan.
     * Setiap kali gaji dipotong, record dibuat di sini.
     */
    public function up(): void
    {
        Schema::create('potongan_gaji', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('anggota_id')->constrained('anggota');
            $table->foreignUuid('pembiayaan_id')->nullable()->constrained('pembiayaan')
                  ->comment('Pembiayaan yang menjadi tujuan potongan');
            $table->foreignUuid('jadwal_angsuran_id')->nullable()->constrained('jadwal_angsuran')
                  ->comment('Jadwal angsuran yang dibayar via potongan');
            $table->date('periode'); // Periode gaji (YYYY-MM-01)
            $table->decimal('gaji_bruto', 15, 2)->default(0)
                  ->comment('Gaji sebelum potongan');
            $table->decimal('nominal_potongan', 15, 2)
                  ->comment('Nominal yang dipotong');
            $table->decimal('gaji_diterima', 15, 2)
                  ->comment('Gaji yang diterima setelah potongan');
            $table->enum('jenis_potongan', ['angsuran_pokok', 'angsuran_bunga', 'simpanan', 'lainnya'])
                  ->default('angsuran_pokok');
            $table->enum('status', ['pending', 'diproses', 'gagal'])->default('pending')
                  ->comment('Status pemotongan');
            $table->text('keterangan')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('processed_at')->nullable();

            $table->index(['anggota_id', 'periode']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('potongan_gaji');
    }
};
