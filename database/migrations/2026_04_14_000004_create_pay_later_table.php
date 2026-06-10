<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel untuk pembayaran di luar potong gaji (pay later / bayar sebelum gajian).
     */
    public function up(): void
    {
        Schema::create('pay_later', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('anggota_id')->constrained('anggota');
            $table->foreignUuid('pembiayaan_id')->constrained('pembiayaan');
            $table->foreignUuid('jadwal_angsuran_id')->nullable()->constrained('jadwal_angsuran')
                  ->comment('Angsuran yang ingin dibayar lebih awal');
            $table->decimal('nominal', 15, 2);
            $table->enum('jenis', ['angsuran', 'pelunasan'])->default('angsuran');
            $table->enum('status', ['pending', 'approved', 'rejected', 'lunas'])->default('pending');
            $table->string('no_transaksi', 50)->nullable()->unique();
            $table->text('keterangan')->nullable();
            $table->foreignUuid('approved_by')->nullable()->constrained('users')
                  ->comment('User admin yang approve');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('lunas_at')->nullable();

            $table->index(['anggota_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pay_later');
    }
};
