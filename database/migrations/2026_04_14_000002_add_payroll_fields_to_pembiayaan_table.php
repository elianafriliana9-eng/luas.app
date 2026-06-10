<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah kolom untuk fitur potong gaji pada pembiayaan.
     */
    public function up(): void
    {
        Schema::table('pembiayaan', function (Blueprint $table) {
            $table->boolean('auto_potong_gaji')->default(false)->after('kolektibilitas')
                  ->comment('Apakah angsuran dipotong otomatis dari gaji');
            $table->decimal('nominal_potongan', 15, 2)->nullable()->after('auto_potong_gaji')
                  ->comment('Nominal yang dipotong per bulan dari gaji');
            $table->tinyInteger('bulan_tersisa_potongan')->nullable()->after('nominal_potongan')
                  ->comment('Berapa bulan lagi potongan gaji berlaku');
            $table->enum('sumber_pembayaran', ['potong_gaji', 'bayar_manual', 'keduanya'])->default('bayar_manual')
                  ->after('bulan_tersisa_potongan')
                  ->comment('Sumber pembayaran angsuran');
        });
    }

    public function down(): void
    {
        Schema::table('pembiayaan', function (Blueprint $table) {
            $table->dropColumn([
                'auto_potong_gaji',
                'nominal_potongan',
                'bulan_tersisa_potongan',
                'sumber_pembayaran',
            ]);
        });
    }
};
