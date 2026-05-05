<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminPasswordChangeTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_password_change_updates_login_credentials(): void
    {
        $admin = User::create([
            'nama' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'password' => 'password-lama',
            'role' => 'super_admin',
            'notelp' => '08123456789',
        ]);

        Sanctum::actingAs($admin);

        $response = $this->putJson('/api/admin/profile/change-password', [
            'current_password' => 'password-lama',
            'password' => 'PasswordBaru123!',
            'password_confirmation' => 'PasswordBaru123!',
        ]);

        $response
            ->assertOk()
            ->assertJson([
                'message' => 'Kata sandi berhasil diperbarui!',
            ]);

        $this->assertTrue(Hash::check('PasswordBaru123!', $admin->fresh()->password));

        $this->assertFalse(Hash::check('password-lama', $admin->fresh()->password));
        $this->assertTrue(Hash::check('PasswordBaru123!', $admin->fresh()->password));
    }
}
