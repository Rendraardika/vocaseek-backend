<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_login_returns_token_without_session_store(): void
    {
        User::create([
            'nama' => 'Super Admin',
            'email' => 'admin@vocaseek.com',
            'password' => 'admin123',
            'role' => 'super_admin',
            'notelp' => '08123456789',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'admin@vocaseek.com',
            'password' => 'admin123',
        ]);

        $response
            ->assertOk()
            ->assertJsonStructure([
                'status',
                'token',
                'role',
                'user',
                'user_data' => ['user_id', 'nama', 'email', 'role', 'notelp'],
            ]);
    }
}
