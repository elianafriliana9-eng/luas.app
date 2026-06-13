<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'sqlite') {
            // 1. Temporarily change column to VARCHAR to bypass ENUM strictness
            DB::statement("ALTER TABLE transaksi_pembiayaan MODIFY COLUMN channel VARCHAR(255)");
            
            // 2. Safely update 'teller' to 'admin'
            DB::table('transaksi_pembiayaan')->where('channel', 'teller')->update(['channel' => 'admin']);
            
            // 3. Apply the final new ENUM
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
