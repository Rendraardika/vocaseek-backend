<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@vocaseek.com'],
            [
                'nama' => 'Super Admin Vocaseek',
                'password' => 'admin123',
                'role' => 'super_admin',
            ]
        );

        User::firstOrCreate(
            ['email' => 'staff@vocaseek.com'],
            [
                'nama' => 'Staff Verifikasi',
                'password' => 'staff123',
                'role' => 'staff_admin',
            ]
        );
    }
}
