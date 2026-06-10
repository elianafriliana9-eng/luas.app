<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add alasan_keluar column
        Schema::table('anggota', function (Blueprint $table) {
            $table->text('alasan_keluar')->nullable()->after('tanggal_keluar');
        });

        // Update status enum to include 'pengajuan_keluar'
        DB::statement("ALTER TABLE anggota MODIFY COLUMN status ENUM('aktif', 'tidak_aktif', 'keluar', 'pengajuan_keluar', 'meninggal') DEFAULT 'aktif'");
    }

    public function down(): void
    {
        Schema::table('anggota', function (Blueprint $table) {
            $table->dropColumn('alasan_keluar');
        });
        DB::statement("ALTER TABLE anggota MODIFY COLUMN status ENUM('aktif', 'tidak_aktif', 'keluar', 'meninggal') DEFAULT 'aktif'");
    }
};
