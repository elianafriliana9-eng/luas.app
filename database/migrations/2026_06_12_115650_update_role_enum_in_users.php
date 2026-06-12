<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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
