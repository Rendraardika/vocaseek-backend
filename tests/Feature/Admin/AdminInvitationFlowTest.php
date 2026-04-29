<?php

namespace Tests\Feature\Admin;

use App\Mail\AdminInvitationMail;
use App\Models\AdminInvitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminInvitationFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_invite_success(): void
    {
        Mail::fake();
        $superAdmin = $this->createSuperAdmin();

        Sanctum::actingAs($superAdmin);

        $response = $this->postJson('/api/admin/users/invite', [
            'nama' => 'Staff Operasional',
            'email' => 'staff@vocaseek.com',
            'notelp' => '08123456789',
            'role' => 'staff_admin',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.email', 'staff@vocaseek.com');

        $this->assertDatabaseMissing('users', [
            'email' => 'staff@vocaseek.com',
        ]);

        $invitation = AdminInvitation::where('email', 'staff@vocaseek.com')->first();
        $this->assertNotNull($invitation);

        Mail::assertQueued(AdminInvitationMail::class, 1);
    }

    public function test_duplicate_email_is_rejected(): void
    {
        $superAdmin = $this->createSuperAdmin();
        Sanctum::actingAs($superAdmin);

        User::create([
            'nama' => 'Existing Staff',
            'email' => 'duplicate@vocaseek.com',
            'password' => 'password123',
            'role' => 'staff_admin',
            'status' => 'active',
        ]);

        $response = $this->postJson('/api/admin/users/invite', [
            'nama' => 'Staff Baru',
            'email' => 'duplicate@vocaseek.com',
            'notelp' => '081200000000',
            'role' => 'staff_admin',
        ]);

        $response->assertUnprocessable();
    }

    public function test_verify_valid_invitation(): void
    {
        $plainToken = 'valid-token-1234567890valid-token-1234567890';
        $this->createPendingInvitation($plainToken);

        $response = $this->getJson('/api/admin/invitations/verify?token='.$plainToken);

        $response
            ->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.email', 'staff@vocaseek.com');
    }

    public function test_verify_expired_invitation(): void
    {
        $plainToken = 'expired-token-1234567890expired-token-1234567890';
        $this->createPendingInvitation($plainToken, ['expires_at' => now()->subHour()]);

        $response = $this->getJson('/api/admin/invitations/verify?token='.$plainToken);

        $response
            ->assertStatus(410)
            ->assertJsonPath('code', 'invitation_expired');
    }

    public function test_accept_invitation_success(): void
    {
        $plainToken = 'accept-token-1234567890accept-token-1234567890';
        $invitation = $this->createPendingInvitation($plainToken);

        $response = $this->postJson('/api/admin/invitations/accept', [
            'token' => $plainToken,
            'password' => 'PasswordBaru123!',
            'password_confirmation' => 'PasswordBaru123!',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('status', 'success');

        $user = User::where('email', 'staff@vocaseek.com')->first();

        $this->assertNotNull($user);
        $this->assertSame('active', $user->status);
        $this->assertTrue(Hash::check('PasswordBaru123!', $user->password));
        $this->assertDatabaseHas('admin_invitations', [
            'user_id' => $user->user_id,
        ]);
        $this->assertNotNull(AdminInvitation::where('user_id', $user->user_id)->first()?->used_at);
    }

    public function test_accept_invitation_with_invalid_token(): void
    {
        $response = $this->postJson('/api/admin/invitations/accept', [
            'token' => 'invalid-token-1234567890invalid-token-1234567890',
            'password' => 'PasswordBaru123!',
            'password_confirmation' => 'PasswordBaru123!',
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonPath('code', 'invitation_invalid');
    }

    public function test_resend_invitation_creates_new_token_and_invalidates_old_one(): void
    {
        Mail::fake();
        $superAdmin = $this->createSuperAdmin();
        $invitation = $this->createPendingInvitation('resend-token-1234567890resend-token-1234567890', [
            'invited_by' => $superAdmin->user_id,
        ]);

        Sanctum::actingAs($superAdmin);

        $response = $this->postJson('/api/admin/invitations/resend', [
            'invitation_id' => $invitation->id,
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('status', 'success');

        $invitation->refresh();
        $this->assertNotNull($invitation->cancelled_at);
        $this->assertDatabaseCount('admin_invitations', 2);
        Mail::assertQueued(AdminInvitationMail::class, 1);
    }

    public function test_cancel_invitation_marks_it_cancelled(): void
    {
        $superAdmin = $this->createSuperAdmin();
        $invitation = $this->createPendingInvitation('cancel-token-1234567890cancel-token-1234567890', [
            'invited_by' => $superAdmin->user_id,
        ]);

        Sanctum::actingAs($superAdmin);

        $response = $this->postJson('/api/admin/invitations/cancel', [
            'invitation_id' => $invitation->id,
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('status', 'success');

        $invitation->refresh();

        $this->assertNotNull($invitation->cancelled_at);
        $this->assertNull($invitation->user);
    }

    private function createSuperAdmin(): User
    {
        return User::create([
            'nama' => 'Super Admin',
            'email' => 'admin@vocaseek.com',
            'password' => 'admin12345',
            'role' => 'super_admin',
            'status' => 'active',
            'notelp' => '081234567890',
        ]);
    }

    private function createPendingInvitation(string $plainToken, array $overrides = []): AdminInvitation
    {
        $superAdmin = $overrides['invited_by'] ?? $this->createSuperAdmin()->user_id;

        return AdminInvitation::create([
            'user_id' => $overrides['user_id'] ?? null,
            'name' => $overrides['name'] ?? 'Staff Admin',
            'email' => $overrides['email'] ?? 'staff@vocaseek.com',
            'phone' => $overrides['phone'] ?? '08123456789',
            'role' => 'staff_admin',
            'token_hash' => hash('sha256', $plainToken),
            'expires_at' => $overrides['expires_at'] ?? now()->addDay(),
            'invited_by' => $superAdmin,
        ]);
    }
}
