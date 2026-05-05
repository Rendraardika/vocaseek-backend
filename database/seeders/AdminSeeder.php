<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $adminPayload = [
            'nama' => env('SUPER_ADMIN_NAME', 'Super Admin Vocaseek'),
            'password' => env('SUPER_ADMIN_PASSWORD', 'admin123'),
            'role' => 'super_admin',
        ];

        if (Schema::hasColumn('users', 'status')) {
            $adminPayload['status'] = 'active';
        }

        if (Schema::hasColumn('users', 'email_verified_at')) {
            $adminPayload['email_verified_at'] = now();
        }

        User::updateOrCreate(
            ['email' => env('SUPER_ADMIN_EMAIL', 'admin@vocaseek.com')],
            $adminPayload
        );

        $staffPayload = [
            'nama' => env('STAFF_ADMIN_NAME', 'Staff Verifikasi'),
            'password' => env('STAFF_ADMIN_PASSWORD', 'staff123'),
            'role' => 'staff_admin',
        ];

        if (Schema::hasColumn('users', 'status')) {
            $staffPayload['status'] = 'active';
        }

        if (Schema::hasColumn('users', 'email_verified_at')) {
            $staffPayload['email_verified_at'] = now();
        }

        User::updateOrCreate(
            ['email' => env('STAFF_ADMIN_EMAIL', 'staff@vocaseek.com')],
            $staffPayload
        );
    }
}
