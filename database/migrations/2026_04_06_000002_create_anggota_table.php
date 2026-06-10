<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('anggota', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('cabang_id')->constrained('cabang')->cascadeOnDelete();
            $table->string('no_anggota', 20)->unique();
            $table->char('nik', 16)->unique();
            $table->string('nama_lengkap', 150);
            $table->string('tempat_lahir', 80)->nullable();
            $table->date('tanggal_lahir');
            $table->char('jenis_kelamin', 1)->comment('L or P');
            $table->text('alamat');
            $table->string('no_hp', 20);
            $table->string('email', 150)->nullable();
            $table->text('foto_ktp')->nullable();
            $table->text('foto_selfie')->nullable();
            $table->enum('status', ['aktif', 'tidak_aktif', 'keluar', 'meninggal'])->default('aktif');
            $table->date('tanggal_masuk');
            $table->date('tanggal_keluar')->nullable();
            $table->timestamps();

            $table->index('no_anggota');
            $table->index('nik');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anggota');
    }
};
