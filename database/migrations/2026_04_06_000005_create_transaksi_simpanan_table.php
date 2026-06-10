<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaksi_simpanan', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('rekening_id')->constrained('rekening_simpanan');
            $table->foreignUuid('user_id')->nullable()->constrained('users');
            $table->string('no_transaksi', 30)->unique();
            $table->enum('jenis', ['setoran', 'penarikan', 'bunga', 'koreksi']);
            $table->decimal('nominal', 15, 2);
            $table->decimal('saldo_sebelum', 15, 2);
            $table->decimal('saldo_sesudah', 15, 2);
            $table->text('keterangan')->nullable();
            $table->enum('channel', ['teller', 'mobile', 'qris', 'virtual_account'])->default('teller');
            $table->string('ref_payment', 100)->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaksi_simpanan');
    }
};
