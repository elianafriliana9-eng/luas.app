<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add pending_aktif to anggota status enum (MySQL only — SQLite ignores ENUM)
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE anggota MODIFY COLUMN status ENUM('aktif','tidak_aktif','keluar','pengajuan_keluar','meninggal','pending_aktif') NOT NULL DEFAULT 'aktif'");
        }

        // Add back departemen & jabatan (were dropped in previous migration)
        if (!Schema::hasColumn('anggota', 'departemen')) {
            Schema::table('anggota', function (Blueprint $table) {
                $table->string('departemen', 100)->nullable()->after('tanggal_gajian');
                $table->string('jabatan', 100)->nullable()->after('departemen');
            });
        }
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE anggota MODIFY COLUMN status ENUM('aktif','tidak_aktif','keluar','pengajuan_keluar','meninggal') NOT NULL DEFAULT 'aktif'");
        }
        Schema::table('anggota', function (Blueprint $table) {
            $table->dropColumn(['departemen', 'jabatan']);
        });
    }
};
