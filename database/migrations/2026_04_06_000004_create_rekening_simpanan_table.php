<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rekening_simpanan', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('anggota_id')->constrained('anggota')->cascadeOnDelete();
            $table->foreignUuid('produk_id')->constrained('produk_simpanan');
            $table->string('no_rekening', 30)->unique();
            $table->decimal('saldo', 15, 2)->default(0);
            $table->enum('status', ['aktif', 'blokir', 'tutup'])->default('aktif');
            $table->date('tanggal_buka');
            $table->date('tanggal_tutup')->nullable();
            $table->timestamps();

            $table->index('no_rekening');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rekening_simpanan');
    }
};
