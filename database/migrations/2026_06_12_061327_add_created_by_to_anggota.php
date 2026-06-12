<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('anggota', 'created_by')) {
            Schema::table('anggota', function (Blueprint $table) {
                $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete()->after('status');
                $table->foreignUuid('updated_by')->nullable()->constrained('users')->nullOnDelete()->after('created_by');
                $table->string('ip_address', 45)->nullable()->after('updated_by');
                $table->string('user_agent')->nullable()->after('ip_address');
            });
        }

        if (!Schema::hasColumn('transaksi_simpanan', 'ip_address')) {
            Schema::table('transaksi_simpanan', function (Blueprint $table) {
                $table->string('ip_address', 45)->nullable()->after('channel');
                $table->string('user_agent')->nullable()->after('ip_address');
            });
        }

        if (!Schema::hasColumn('pinbuk', 'ip_address')) {
            Schema::table('pinbuk', function (Blueprint $table) {
                $table->string('ip_address', 45)->nullable()->after('keterangan');
                $table->string('user_agent')->nullable()->after('ip_address');
            });
        }
    }

    public function down(): void
    {
        Schema::table('anggota', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            $table->dropColumn(['created_by', 'updated_by', 'ip_address', 'user_agent']);
        });
        Schema::table('transaksi_simpanan', function (Blueprint $table) {
            $table->dropColumn(['ip_address', 'user_agent']);
        });
        Schema::table('pinbuk', function (Blueprint $table) {
            $table->dropColumn(['ip_address', 'user_agent']);
        });
    }
};
