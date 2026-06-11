<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('anggota', function (Blueprint $table) {
            $table->foreignUuid('perusahaan_id')->nullable()->after('cabang_id')
                ->constrained('perusahaan')->nullOnDelete();
            $table->dropColumn(['departemen', 'jabatan']);
        });
    }

    public function down(): void
    {
        Schema::table('anggota', function (Blueprint $table) {
            $table->dropForeign(['perusahaan_id']);
            $table->dropColumn('perusahaan_id');
            $table->string('departemen', 100)->nullable()->after('tanggal_gajian');
            $table->string('jabatan', 100)->nullable()->after('departemen');
        });
    }
};
