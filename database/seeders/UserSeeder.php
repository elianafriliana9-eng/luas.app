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
        $cabangJkt = Cabang::where('kode', 'CBG-JKT')->first();
        $cabangTgr = Cabang::where('kode', 'CBG-TGR')->first();
        $cabangBks = Cabang::where('kode', 'CBG-BKS')->first();

        $users = [
            [
                'name' => 'Super Administrator',
                'email' => 'superadmin@kopsaku.com',
                'password' => Hash::make('password'),
                'role' => 'super_admin',
                'cabang_id' => $cabangJkt?->id,
            ],
            [
                'name' => 'Admin Jakarta',
                'email' => 'admin@kopsaku.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'cabang_id' => $cabangJkt?->id,
            ],
            [
                'name' => 'Admin Tangerang',
                'email' => 'admin.tangerang@kopsaku.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'cabang_id' => $cabangTgr?->id,
            ],
            [
                'name' => 'Admin Bekasi',
                'email' => 'admin.bekasi@kopsaku.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'cabang_id' => $cabangBks?->id,
            ],
            [
                'name' => 'Executive',
                'email' => 'executive@kopsaku.com',
                'password' => Hash::make('password'),
                'role' => 'executive',
                'cabang_id' => $cabangJkt?->id,
            ],
            [
                'name' => 'User',
                'email' => 'user@kopsaku.com',
                'password' => Hash::make('password'),
                'role' => 'user',
                'cabang_id' => $cabangJkt?->id,
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                array_merge($user, [
                    'email_verified_at' => now(),
                    'remember_token' => Str::random(10),
                ])
            );
        }
    }
}
