<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Update transaksi_simpanan untuk fitur approval, cancel, pinbuk
        Schema::table('transaksi_simpanan', function (Blueprint $table) {
            $table->enum('status_approval', ['approved', 'pending', 'rejected'])->default('approved')->after('jenis');
            $table->foreignUuid('approved_by')->nullable()->after('status_approval')->constrained('users');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->boolean('dibatalkan')->default(false)->after('approved_at');
            $table->foreignUuid('dibatalkan_by')->nullable()->after('dibatalkan')->constrained('users');
            $table->timestamp('dibatalkan_at')->nullable()->after('dibatalkan_by');
            $table->enum('jenis_pembatalan', ['setoran', 'penarikan', 'pinbuk_masuk', 'pinbuk_keluar', 'bunga', 'koreksi'])->nullable()->after('dibatalkan_at');
        });

        // Update enum jenis to include pinbuk
        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE transaksi_simpanan MODIFY COLUMN jenis ENUM('setoran', 'penarikan', 'pinbuk_masuk', 'pinbuk_keluar', 'bunga', 'koreksi') DEFAULT 'setoran'");
        }

        // Tabel pinbuk (pemindahbukuan)
        Schema::create('pinbuk', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('rekening_sumber_id')->constrained('rekening_simpanan');
            $table->foreignUuid('rekening_tujuan_id')->constrained('rekening_simpanan');
            $table->foreignUuid('user_id')->nullable()->constrained('users');
            $table->string('no_transaksi', 30)->unique();
            $table->decimal('nominal', 15, 2);
            $table->text('keterangan')->nullable();
            $table->enum('status_approval', ['approved', 'pending', 'rejected'])->default('approved');
            $table->foreignUuid('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pinbuk');

        Schema::table('transaksi_simpanan', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropForeign(['dibatalkan_by']);
            $table->dropColumn([
                'status_approval', 'approved_by', 'approved_at',
                'dibatalkan', 'dibatalkan_by', 'dibatalkan_at', 'jenis_pembatalan',
            ]);
        });

        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE transaksi_simpanan MODIFY COLUMN jenis ENUM('setoran', 'penarikan', 'bunga', 'koreksi') DEFAULT 'setoran'");
        }
    }
};
