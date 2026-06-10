<?php

namespace Database\Seeders;

use App\Models\Cabang;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $cabangUtama = Cabang::where('kode', 'CBG-JKT')->first();

        // 1. Super Admin
        User::create([
            'name' => 'Super Administrator',
            'email' => 'superadmin@kopsaku.com',
            'password' => Hash::make('password'),
            'role' => 'super_admin',
            'cabang_id' => $cabangUtama?->id,
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
        ]);

        // 2. Admin
        User::create([
            'name' => 'Admin Cabang JKT',
            'email' => 'admin@kopsaku.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'cabang_id' => $cabangUtama?->id,
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
        ]);
    }
}
