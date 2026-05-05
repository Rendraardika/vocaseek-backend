<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminPasswordChangeAliasTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_password_change_accepts_post_and_alias_fields(): void
    {
        $admin = User::create([
            'nama' => 'Super Admin',
            'email' => 'admin@vocaseek.com',
            'password' => 'admin123',
            'role' => 'super_admin',
            'notelp' => '08123456789',
        ]);

        Sanctum::actingAs($admin);

        $response = $this->postJson('/api/admin/profile/change-password', [
            'old_password' => 'admin123',
            'new_password' => 'PasswordBaru123!',
            'confirm_password' => 'PasswordBaru123!',
        ]);

        $response
            ->assertOk()
            ->assertJson([
                'message' => 'Kata sandi berhasil diperbarui!',
            ]);

        $this->assertTrue(Hash::check('PasswordBaru123!', $admin->fresh()->password));
        $this->assertFalse(Hash::check('admin123', $admin->fresh()->password));
    }
}
