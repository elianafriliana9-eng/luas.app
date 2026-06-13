<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Update old roles to their new equivalents to prevent "Data truncated" error
        DB::table('users')->whereIn('role', ['teller', 'akuntan', 'account_officer'])->update(['role' => 'admin']);
        DB::table('users')->where('role', 'kepala_cabang')->update(['role' => 'super_admin']);

        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('super_admin','admin','executive','user') DEFAULT 'user'");
        }
    }

    public function down(): void
    {
        Schema::table('users', function () {
            //
        });
    }
};
