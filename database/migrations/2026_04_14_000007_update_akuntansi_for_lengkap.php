<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add is_cancelled to jurnal
        Schema::table('jurnal', function (Blueprint $table) {
            $table->boolean('is_cancelled')->default(false)->after('is_reversed');
            $table->foreignUuid('cancelled_by')->nullable()->after('is_cancelled')->constrained('users');
            $table->timestamp('cancelled_at')->nullable()->after('cancelled_by');
            $table->string('alasan_batal', 500)->nullable()->after('cancelled_at');
        });

        // Setup Kas table
        Schema::create('kas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('cabang_id')->constrained('cabang');
            $table->string('kode_kas', 20)->unique();
            $table->string('nama_kas', 100);
            $table->foreignUuid('akun_id')->constrained('chart_of_accounts');
            $table->decimal('saldo', 15, 2)->default(0);
            $table->boolean('aktif')->default(true);
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        // Laporan config
        Schema::create('setup_laporan', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('cabang_id')->constrained('cabang');
            $table->enum('jenis', ['kas', 'neraca_saldo', 'neraca', 'laba_rugi', 'arus_kas']);
            $table->string('nama_laporan', 100);
            $table->json('konfigurasi')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('setup_laporan');
        Schema::dropIfExists('kas');

        Schema::table('jurnal', function (Blueprint $table) {
            $table->dropForeign(['cancelled_by']);
            $table->dropColumn(['is_cancelled', 'cancelled_by', 'cancelled_at', 'alasan_batal']);
        });
    }
};
