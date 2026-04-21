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
            'nama' => 'Super Admin Vocaseek',
            'password' => 'admin123',
            'role' => 'super_admin',
        ];

        if (Schema::hasColumn('users', 'status')) {
            $adminPayload['status'] = 'active';
        }

        User::updateOrCreate(
            ['email' => 'admin@vocaseek.com'],
            $adminPayload
        );

        $staffPayload = [
            'nama' => 'Staff Verifikasi',
            'password' => 'staff123',
            'role' => 'staff_admin',
        ];

        if (Schema::hasColumn('users', 'status')) {
            $staffPayload['status'] = 'active';
        }

        User::updateOrCreate(
            ['email' => 'staff@vocaseek.com'],
            $staffPayload
        );
    }
}
