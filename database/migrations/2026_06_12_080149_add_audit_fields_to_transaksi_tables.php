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
        if (!Schema::hasColumn('transaksi_simpanan', 'created_by')) {
            Schema::table('transaksi_simpanan', function (Blueprint $table) {
                $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete()->after('user_agent');
                $table->foreignUuid('updated_by')->nullable()->constrained('users')->nullOnDelete()->after('created_by');
            });
        }
    }

    public function down(): void
    {
        Schema::table('transaksi_simpanan', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            $table->dropColumn(['created_by', 'updated_by']);
        });
    }
};
