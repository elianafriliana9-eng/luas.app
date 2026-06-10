<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('produk_simpanan', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('kode', 10)->unique();
            $table->string('nama', 100);
            $table->enum('jenis', ['pokok', 'wajib', 'sukarela', 'berjangka']);
            $table->decimal('bunga_pa', 5, 2)->default(0);
            $table->decimal('minimal_saldo', 15, 2)->default(0);
            $table->boolean('auto_bunga')->default(false);
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produk_simpanan');
    }
};
