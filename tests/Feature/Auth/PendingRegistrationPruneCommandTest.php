<?php

namespace Tests\Feature\Auth;

use App\Models\PendingRegistration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PendingRegistrationPruneCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_prune_command_removes_expired_pending_registration_and_files(): void
    {
        $expired = PendingRegistration::create([
            'nama' => 'Expired Company',
            'email' => 'expired@example.com',
            'password' => 'password123',
            'role' => 'company',
            'notelp' => '08123456789',
            'company_payload' => [
                'nama_perusahaan' => 'Expired Inc',
                'nib' => '12345',
                'loa_pdf' => 'company/documents/pending/loa.pdf',
                'akta_pdf' => 'company/documents/pending/akta.pdf',
            ],
        ]);

        $expired->forceFill([
            'created_at' => now()->subHours(3),
            'updated_at' => now()->subHours(3),
        ])->save();

        $fresh = PendingRegistration::create([
            'nama' => 'Fresh User',
            'email' => 'fresh@example.com',
            'password' => 'password123',
            'role' => 'intern',
            'notelp' => '08123456780',
        ]);

        $this->artisan('pending-registrations:prune', ['--hours' => 2])
            ->expectsOutput('Pruned 1 pending registration(s).')
            ->assertExitCode(0);

        $this->assertDatabaseMissing('pending_registrations', ['id' => $expired->id]);
        $this->assertDatabaseHas('pending_registrations', ['id' => $fresh->id]);
    }
}
