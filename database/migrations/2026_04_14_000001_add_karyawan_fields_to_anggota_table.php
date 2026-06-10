<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah kolom untuk karyawan: gaji, departemen, jabatan, tanggal gajian.
     */
    public function up(): void
    {
        Schema::table('anggota', function (Blueprint $table) {
            $table->decimal('gaji_pokok', 15, 2)->nullable()->after('email')
                  ->comment('Gaji pokok karyawan');
            $table->tinyInteger('tanggal_gajian')->nullable()->after('gaji_pokok')
                  ->comment('Tanggal gajian setiap bulan (1-31)');
            $table->string('departemen', 100)->nullable()->after('tanggal_gajian')
                  ->comment('Departemen/divisi tempat bekerja');
            $table->string('jabatan', 100)->nullable()->after('departemen')
                  ->comment('Jabatan/posisi karyawan');
            $table->date('tanggal_mulai_kerja')->nullable()->after('jabatan')
                  ->comment('Tanggal mulai bekerja (untuk masa kerja)');
            $table->string('no_pegawai', 50)->nullable()->after('tanggal_mulai_kerja')
                  ->comment('Nomor induk pegawai (jika ada)');
        });
    }

    public function down(): void
    {
        Schema::table('anggota', function (Blueprint $table) {
            $table->dropColumn([
                'gaji_pokok',
                'tanggal_gajian',
                'departemen',
                'jabatan',
                'tanggal_mulai_kerja',
                'no_pegawai',
            ]);
        });
    }
};
