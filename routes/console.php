<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Scheduled Jobs for Koperasi Karyawan
Schedule::command('payroll:proses')
    ->dailyAt('01:00') // Run at 1 AM on payday
    ->when(function () {
        // Only run if today is the 25th (or configure per employee)
        return now()->day === 25;
    })
    ->appendOutputTo(storage_path('logs/payroll.log'));

// Kolektibilitas update — daily at 2 AM
Schedule::command('kolektibilitas:update')
    ->dailyAt('02:00')
    ->appendOutputTo(storage_path('logs/kolektibilitas.log'));

// Bunga simpanan — every 1st of month at 3 AM
Schedule::command('simpanan:bunga')
    ->monthlyOn(1, '03:00')
    ->appendOutputTo(storage_path('logs/bunga-simpanan.log'));

// Auto Roll Over deposito — daily at 4 AM
Schedule::command('deposito:aro')
    ->dailyAt('04:00')
    ->appendOutputTo(storage_path('logs/deposito-aro.log'));
