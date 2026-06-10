<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Grup 3 - Akuntansi & SHU

        Schema::create('chart_of_accounts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('cabang_id')->nullable()->constrained('cabang')->comment('null = konsolidasi');
            $table->string('kode_akun', 10);
            $table->string('nama_akun', 150);
            $table->enum('kelompok', ['aset', 'liabilitas', 'ekuitas', 'pendapatan', 'beban']);
            $table->enum('posisi_normal', ['debet', 'kredit']);
            $table->boolean('is_header')->default(false);
            $table->foreignUuid('parent_id')->nullable()->constrained('chart_of_accounts');
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });

        Schema::create('jurnal', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('cabang_id')->constrained('cabang');
            $table->string('no_jurnal', 30)->unique();
            $table->date('tanggal');
            $table->text('keterangan');
            $table->enum('jenis', ['otomatis', 'manual', 'eliminasi', 'koreksi']);
            $table->uuid('ref_id')->nullable();
            $table->string('ref_tabel', 80)->nullable();
            $table->foreignUuid('dibuat_oleh')->constrained('users');
            $table->boolean('is_eliminasi')->default(false);
            $table->boolean('is_reversed')->default(false);
            $table->foreignUuid('reversed_by')->nullable()->constrained('jurnal');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['tanggal', 'cabang_id']);
        });

        Schema::create('jurnal_detail', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('jurnal_id')->constrained('jurnal')->cascadeOnDelete();
            $table->foreignUuid('akun_id')->constrained('chart_of_accounts');
            $table->decimal('debet', 15, 2)->default(0);
            $table->decimal('kredit', 15, 2)->default(0);
            $table->text('keterangan')->nullable();
        });

        Schema::create('periode_tutup', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('cabang_id')->constrained('cabang');
            $table->integer('tahun');
            $table->integer('bulan');
            $table->boolean('is_closed')->default(false);
            $table->timestamp('closed_at')->nullable();
            $table->foreignUuid('closed_by')->nullable()->constrained('users');
            $table->timestamps();

            $table->unique(['cabang_id', 'tahun', 'bulan']);
        });

        Schema::create('shu_periode', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('cabang_id')->constrained('cabang');
            $table->integer('tahun');
            $table->decimal('total_shu', 15, 2);
            $table->decimal('shu_cadangan', 15, 2);
            $table->decimal('shu_anggota', 15, 2);
            $table->decimal('shu_pengurus', 15, 2);
            $table->decimal('shu_karyawan', 15, 2);
            $table->enum('status', ['draft', 'finalized'])->default('draft');
            $table->timestamp('finalized_at')->nullable();
            $table->timestamps();
        });

        Schema::create('shu_anggota', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('shu_periode_id')->constrained('shu_periode');
            $table->foreignUuid('anggota_id')->constrained('anggota');
            $table->decimal('jasa_simpanan', 15, 2);
            $table->decimal('jasa_pinjaman', 15, 2);
            $table->decimal('total_shu', 15, 2);
            $table->enum('status_bayar', ['belum', 'sudah', 'dialihkan'])->default('belum');
            $table->timestamp('dibayar_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shu_anggota');
        Schema::dropIfExists('shu_periode');
        Schema::dropIfExists('periode_tutup');
        Schema::dropIfExists('jurnal_detail');
        Schema::dropIfExists('jurnal');
        Schema::dropIfExists('chart_of_accounts');
    }
};
