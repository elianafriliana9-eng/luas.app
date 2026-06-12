<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tables = [
        'anggota',
        'rekening_simpanan',
        'transaksi_simpanan',
        'pembiayaan',
        'simpanan_berjangka',
        'pinbuk',
        'potongan_gaji',
        'pay_later',
        'jaminan',
        'pengajuan_pembiayaan',
        'jadwal_angsuran',
        'transaksi_pembiayaan',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            if (!Schema::hasColumn($table, 'deleted_at')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->softDeletes();
                });
            }
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            if (Schema::hasColumn($table, 'deleted_at')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->dropSoftDeletes();
                });
            }
        }
    }
};
