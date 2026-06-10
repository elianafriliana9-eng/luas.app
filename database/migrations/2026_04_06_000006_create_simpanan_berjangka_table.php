<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('simpanan_berjangka', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('anggota_id')->constrained('anggota')->cascadeOnDelete();
            $table->string('no_deposito', 30)->unique();
            $table->decimal('nominal', 15, 2);
            $table->integer('jangka_bulan');
            $table->decimal('bunga_pa', 5, 2);
            $table->date('tanggal_mulai');
            $table->date('tanggal_jatuh_tempo');
            $table->enum('status', ['aktif', 'jatuh_tempo', 'cair', 'perpanjang'])->default('aktif');
            $table->decimal('bunga_akrual', 15, 2)->default(0);
            $table->date('last_accrual_date')->nullable();
            $table->boolean('auto_perpanjang')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('simpanan_berjangka');
    }
};
