<?php

namespace Tests\Feature\Auth;

use App\Models\InternProfile;
use App\Models\PendingRegistration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class PendingRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_only_creates_pending_registration_before_email_verification(): void
    {
        Notification::fake();

        $response = $this->postJson('/api/register', [
            'nama' => 'Calon User',
            'email' => 'calon@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'notelp' => '08123456789',
            'role' => 'intern',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('email', 'calon@example.com');

        $this->assertDatabaseHas('pending_registrations', [
            'email' => 'calon@example.com',
            'role' => 'intern',
        ]);
        $this->assertDatabaseMissing('users', [
            'email' => 'calon@example.com',
        ]);
    }

    public function test_pending_verification_link_creates_user_and_profile(): void
    {
        config([
            'app.url' => 'http://192.168.100.166:8000',
            'app.frontend_url' => 'http://frontend.test',
            'app.public_frontend_url' => 'http://frontend.test',
        ]);

        $pendingRegistration = PendingRegistration::create([
            'nama' => 'Calon User',
            'email' => 'calon@example.com',
            'password' => 'password123',
            'role' => 'intern',
            'notelp' => '08123456789',
            'preferred_locale' => 'id',
        ]);

        $verificationPath = URL::temporarySignedRoute(
            'api.pending-registration.verify',
            now()->addMinutes(60),
            [
                'id' => $pendingRegistration->getKey(),
                'hash' => sha1($pendingRegistration->email),
            ],
            absolute: false,
        );

        $verificationUrl = rtrim((string) config('app.url'), '/').'/'.ltrim($verificationPath, '/');

        $response = $this->get($verificationUrl);

        $response->assertRedirect('http://frontend.test/email-verification?status=success&email=calon%40example.com');

        $user = User::where('email', 'calon@example.com')->first();

        $this->assertNotNull($user);
        $this->assertNotNull($user->email_verified_at);
        $this->assertSame('id', $user->preferred_locale);
        $this->assertInstanceOf(InternProfile::class, $user->internProfile);
        $this->assertDatabaseMissing('pending_registrations', [
            'email' => 'calon@example.com',
        ]);
    }

    public function test_login_returns_pending_verification_message_when_account_not_activated_yet(): void
    {
        PendingRegistration::create([
            'nama' => 'Calon User',
            'email' => 'calon@example.com',
            'password' => 'password123',
            'role' => 'intern',
            'notelp' => '08123456789',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'calon@example.com',
            'password' => 'password123',
        ]);

        $response
            ->assertForbidden()
            ->assertJsonPath('code', 'email_pending_verification');
    }
}
