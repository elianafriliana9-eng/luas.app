<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE transaksi_pembiayaan MODIFY COLUMN channel ENUM('admin','mobile','qris','virtual_account') DEFAULT 'admin'");
        }
    }

    public function down(): void
    {
        Schema::table('transaksi_pembiayaan', function () {
            //
        });
    }
};
