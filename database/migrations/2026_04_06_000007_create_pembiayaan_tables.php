<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Grup 2 - Pembiayaan & Collection

        Schema::create('produk_pembiayaan', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('kode', 10)->unique();
            $table->string('nama', 100);
            $table->enum('skema_bunga', ['flat', 'efektif', 'anuitas']);
            $table->decimal('bunga_pa', 5, 2);
            $table->integer('max_jangka')->nullable();
            $table->decimal('max_plafon', 15, 2)->nullable();
            $table->boolean('is_chanelling')->default(false);
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });

        Schema::create('pengajuan_pembiayaan', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('anggota_id')->constrained('anggota');
            $table->foreignUuid('produk_id')->constrained('produk_pembiayaan');
            $table->string('no_pengajuan', 30)->unique();
            $table->decimal('nominal_diajukan', 15, 2);
            $table->integer('jangka_bulan');
            $table->enum('tujuan', ['modal_kerja', 'konsumtif', 'investasi', 'lainnya'])->default('konsumtif');
            $table->enum('status_approval', ['pending', 'disetujui', 'ditolak', 'dibatalkan'])->default('pending');
            $table->foreignUuid('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('pembiayaan', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('pengajuan_id')->constrained('pengajuan_pembiayaan');
            $table->foreignUuid('anggota_id')->constrained('anggota');
            $table->string('no_pembiayaan', 30)->unique();
            $table->decimal('nominal_disetujui', 15, 2);
            $table->decimal('nominal_cair', 15, 2);
            $table->integer('jangka_bulan');
            $table->decimal('bunga_pa', 5, 2);
            $table->enum('metode_hitung', ['flat', 'efektif', 'anuitas']);
            $table->decimal('angsuran_pokok', 15, 2);
            $table->decimal('angsuran_bunga', 15, 2);
            $table->date('tanggal_akad');
            $table->date('tanggal_cair')->nullable();
            $table->date('tanggal_lunas')->nullable();
            $table->enum('status', ['aktif', 'lunas', 'macet', 'hapus_buku'])->default('aktif');
            $table->decimal('saldo_pokok', 15, 2);
            $table->decimal('saldo_bunga', 15, 2)->default(0);
            $table->integer('hari_tunggak')->default(0);
            $table->smallInteger('kolektibilitas')->default(1)->comment('1=Lancar 2=DPK 3=KL 4=Diragukan 5=Macet');
            $table->boolean('is_chanelling')->default(false);
            $table->string('sumber_dana', 100)->nullable();
            $table->timestamps();

            $table->index('no_pembiayaan');
        });

        Schema::create('jadwal_angsuran', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('pembiayaan_id')->constrained('pembiayaan');
            $table->integer('ke');
            $table->date('tanggal_jatuh_tempo');
            $table->decimal('pokok', 15, 2);
            $table->decimal('bunga', 15, 2);
            $table->decimal('total', 15, 2);
            $table->decimal('saldo_akhir', 15, 2);
            $table->enum('status', ['belum', 'lunas', 'terlambat'])->default('belum');
            $table->date('tanggal_bayar')->nullable();
            $table->timestamps();
        });

        Schema::create('transaksi_pembiayaan', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('pembiayaan_id')->constrained('pembiayaan');
            $table->foreignUuid('jadwal_id')->nullable()->constrained('jadwal_angsuran');
            $table->string('no_transaksi', 30)->unique();
            $table->enum('jenis', ['angsuran', 'realisasi', 'pelunasan', 'denda']);
            $table->decimal('nominal_pokok', 15, 2);
            $table->decimal('nominal_bunga', 15, 2);
            $table->decimal('nominal_denda', 15, 2)->default(0);
            $table->decimal('total', 15, 2);
            $table->enum('channel', ['teller', 'mobile', 'qris', 'virtual_account'])->default('teller');
            $table->string('ref_payment', 100)->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('jaminan', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('pembiayaan_id')->constrained('pembiayaan');
            $table->enum('jenis_jaminan', ['tanah', 'kendaraan', 'bpkb', 'sk_kerja', 'lainnya']);
            $table->text('deskripsi');
            $table->decimal('nilai_taksasi', 15, 2)->nullable();
            $table->string('no_dokumen', 80)->nullable();
            $table->text('foto_dokumen')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jaminan');
        Schema::dropIfExists('transaksi_pembiayaan');
        Schema::dropIfExists('jadwal_angsuran');
        Schema::dropIfExists('pembiayaan');
        Schema::dropIfExists('pengajuan_pembiayaan');
        Schema::dropIfExists('produk_pembiayaan');
    }
};
