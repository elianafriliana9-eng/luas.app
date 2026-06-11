<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== CABANG ===\n";
$cabang = DB::table('cabang')->select('kode', 'nama')->get();
foreach ($cabang as $c) echo "  {$c->kode} - {$c->nama}\n";

echo "\n=== REKENING (5 sample) ===\n";
$rek = DB::table('rekening_simpanan')
    ->join('anggota', 'anggota.id', '=', 'rekening_simpanan.anggota_id')
    ->join('produk_simpanan', 'produk_simpanan.id', '=', 'rekening_simpanan.produk_id')
    ->select('rekening_simpanan.no_rekening', 'anggota.nama_lengkap', 'produk_simpanan.jenis', 'rekening_simpanan.saldo')
    ->limit(5)
    ->get();
foreach ($rek as $r) echo "  {$r->no_rekening} | {$r->nama_lengkap} | {$r->jenis} | Rp " . number_format($r->saldo, 0, ',', '.') . "\n";
