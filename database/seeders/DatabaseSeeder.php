<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $adminEmail = (string) env('DEFAULT_ADMIN_EMAIL', 'adminkc@gmail.com');
        $adminPassword = (string) env('DEFAULT_ADMIN_PASSWORD', 'catur1');
        $adminName = (string) env('DEFAULT_ADMIN_NAME', 'Admin Kelas Catur');
        $adminRole = UserRole::tryFrom((string) env('DEFAULT_ADMIN_ROLE', UserRole::Admin->value)) ?? UserRole::Admin;

        $admin = User::query()
            ->where('email', $adminEmail)
            ->orWhere('email', 'admin@kelascatur.test')
            ->orWhere('nama', $adminName)
            ->first() ?? new User();

        $admin->forceFill([
            'nama' => $adminName,
            'email' => $adminEmail,
            'peran' => $adminRole,
            'email_verified_at' => now(),
            'kata_sandi' => Hash::make($adminPassword),
        ])->save();
    }
}
