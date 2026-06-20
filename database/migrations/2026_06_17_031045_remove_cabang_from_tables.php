<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tables = [
            'users',
            'anggota',
            'chart_of_accounts',
            'jurnal',
            'periode_tutup',
            'shu_periode',
            'kas',
            'kas_mutasi',
            'setup_laporan'
        ];

        $isSqlite = \Illuminate\Support\Facades\DB::getDriverName() === 'sqlite';

        if (Schema::hasTable('jurnal') && Schema::hasColumn('jurnal', 'cabang_id')) {
            Schema::table('jurnal', function (Blueprint $table) {
                try { $table->dropIndex(['tanggal', 'cabang_id']); } catch (\Exception $e) {}
            });
        }
        
        if (Schema::hasTable('periode_tutup') && Schema::hasColumn('periode_tutup', 'cabang_id')) {
            Schema::table('periode_tutup', function (Blueprint $table) {
                try { $table->dropUnique(['cabang_id', 'tahun', 'bulan']); } catch (\Exception $e) {}
            });
        }

        foreach ($tables as $tableName) {
            if (Schema::hasColumn($tableName, 'cabang_id')) {
                if (!$isSqlite) {
                    try { \Illuminate\Support\Facades\DB::statement("ALTER TABLE {$tableName} DROP FOREIGN KEY {$tableName}_cabang_id_foreign"); } catch (\Exception $e) {}
                }
                
                Schema::table($tableName, function (Blueprint $table) use ($isSqlite) {
                    if ($isSqlite) {
                        try { $table->dropForeign(['cabang_id']); } catch (\Exception $e) {}
                    }
                    $table->dropColumn('cabang_id');
                });
            }
        }

        if (!$isSqlite) {
            try { \Illuminate\Support\Facades\DB::statement('DROP TABLE IF EXISTS cabang'); } catch (\Exception $e) {}
        }
        Schema::dropIfExists('cabang');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Not providing full rollback as data is permanently lost.
    }
};
