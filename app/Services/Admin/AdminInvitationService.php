<?php

namespace App\Services\Admin;

use App\Mail\AdminInvitationMail;
use App\Models\AdminInvitation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AdminInvitationService
{
    public function invite(array $payload, User $inviter, ?string $frontendBaseUrl = null): array
    {
        return DB::transaction(function () use ($payload, $inviter, $frontendBaseUrl) {
            [$invitation, $plainToken, $activationUrl] = $this->createInvitationRecord(
                null,
                $payload,
                $inviter,
                $frontendBaseUrl,
            );

            $this->sendInvitationEmail($invitation, $activationUrl);

            return [null, $invitation, $activationUrl];
        });
    }

    public function verifyToken(string $plainToken): AdminInvitation
    {
        $invitation = $this->findByPlainToken($plainToken);

        if (!$invitation) {
            throw ValidationException::withMessages([
                'token' => 'Tautan aktivasi tidak valid atau sudah tidak dapat digunakan.',
            ]);
        }

        if ($invitation->isUsed()) {
            throw ValidationException::withMessages([
                'token' => 'Tautan aktivasi sudah digunakan sebelumnya.',
            ]);
        }

        if ($invitation->isCancelled()) {
            throw ValidationException::withMessages([
                'token' => 'Tautan aktivasi tidak valid atau sudah tidak dapat digunakan.',
            ]);
        }

        if ($invitation->isExpired()) {
            throw ValidationException::withMessages([
                'token' => 'Tautan aktivasi sudah kedaluwarsa.',
            ]);
        }

        return $invitation->loadMissing(['user', 'inviter']);
    }

    public function accept(string $plainToken, string $password): AdminInvitation
    {
        return DB::transaction(function () use ($plainToken, $password) {
            $invitation = $this->verifyToken($plainToken);
            $user = $invitation->user;

            if (!$user) {
                $existingUser = User::query()
                    ->where('email', $invitation->email)
                    ->first();

                if ($existingUser) {
                    throw ValidationException::withMessages([
                        'token' => 'Email undangan sudah terdaftar di sistem. Silakan hubungi admin master.',
                    ]);
                }

                $user = User::create([
                    'nama' => $invitation->name,
                    'email' => $invitation->email,
                    'password' => $password,
                    'role' => $invitation->role,
                    'status' => 'active',
                    'invited_by' => $invitation->invited_by,
                    'notelp' => $invitation->phone,
                ]);
            } else {
                $user->forceFill([
                    'password' => $password,
                    'status' => 'active',
                ]);
            }

            if (Schema::hasColumn('users', 'email_verified_at')) {
                $user->email_verified_at = now();
            }

            $user->save();

            $invitation->forceFill([
                'user_id' => $user->user_id,
                'used_at' => now(),
            ])->save();

            AdminInvitation::query()
                ->where('email', $invitation->email)
                ->where('id', '!=', $invitation->id)
                ->whereNull('used_at')
                ->whereNull('cancelled_at')
                ->update(['cancelled_at' => now()]);

            return $invitation->fresh(['user', 'inviter']);
        });
    }

    public function resend(AdminInvitation $invitation, User $inviter, ?string $frontendBaseUrl = null): array
    {
        if ($invitation->isUsed()) {
            throw ValidationException::withMessages([
                'invitation_id' => 'Undangan yang sudah digunakan tidak dapat dikirim ulang.',
            ]);
        }

        return DB::transaction(function () use ($invitation, $inviter, $frontendBaseUrl) {
            AdminInvitation::query()
                ->where('email', $invitation->email)
                ->whereNull('used_at')
                ->whereNull('cancelled_at')
                ->update(['cancelled_at' => now()]);

            $user = $invitation->user;

            if ($user) {
                $user->forceFill([
                    'nama' => $invitation->name,
                    'email' => $invitation->email,
                    'notelp' => $invitation->phone,
                    'role' => $invitation->role,
                    'status' => 'pending_invitation',
                    'invited_by' => $inviter->user_id,
                ])->save();
            }

            [$newInvitation, $plainToken, $activationUrl] = $this->createInvitationRecord(
                $user,
                [
                    'nama' => $invitation->name,
                    'email' => $invitation->email,
                    'notelp' => $invitation->phone,
                    'role' => $invitation->role,
                ],
                $inviter,
                $frontendBaseUrl,
            );

            $this->sendInvitationEmail($newInvitation, $activationUrl);

            return [$newInvitation, $activationUrl];
        });
    }

    public function cancel(AdminInvitation $invitation): AdminInvitation
    {
        if ($invitation->isUsed()) {
            throw ValidationException::withMessages([
                'invitation_id' => 'Undangan yang sudah digunakan tidak dapat dibatalkan.',
            ]);
        }

        return DB::transaction(function () use ($invitation) {
            $invitation->forceFill([
                'cancelled_at' => now(),
            ])->save();

            if ($invitation->user && $invitation->user->status !== 'active') {
                $invitation->user->forceFill([
                    'status' => 'disabled',
                ])->save();
            }

            return $invitation->fresh(['user', 'inviter']);
        });
    }

    public function buildActivationUrl(string $plainToken, ?string $frontendBaseUrl = null): string
    {
        return rtrim($this->resolveFrontendBaseUrl($frontendBaseUrl), '/')
            .'/admin/activate?token='.urlencode($plainToken);
    }

    public function isDebugActivationLinkEnabled(): bool
    {
        return app()->hasDebugModeEnabled();
    }

    private function createInvitationRecord(?User $user, array $payload, User $inviter, ?string $frontendBaseUrl = null): array
    {
        $plainToken = Str::random(64);
        $tokenHash = hash('sha256', $plainToken);

        $invitation = AdminInvitation::create([
            'user_id' => $user?->user_id,
            'name' => $payload['nama'],
            'email' => $payload['email'],
            'phone' => $payload['notelp'] ?? null,
            'role' => $payload['role'],
            'token_hash' => $tokenHash,
            'expires_at' => now()->addDay(),
            'invited_by' => $inviter->user_id,
        ]);

        return [$invitation, $plainToken, $this->buildActivationUrl($plainToken, $frontendBaseUrl)];
    }

    private function sendInvitationEmail(AdminInvitation $invitation, string $activationUrl): void
    {
        Mail::to($invitation->email)->send(
            new AdminInvitationMail($invitation->fresh(['inviter']), $activationUrl)
        );
    }

    private function findByPlainToken(string $plainToken): ?AdminInvitation
    {
        return AdminInvitation::query()
            ->where('token_hash', hash('sha256', $plainToken))
            ->latest('id')
            ->first();
    }

    private function resolveFrontendBaseUrl(?string $frontendBaseUrl = null): string
    {
        $candidate = trim((string) $frontendBaseUrl);

        if ($candidate !== '') {
            $isValidUrl = filter_var($candidate, FILTER_VALIDATE_URL);
            $scheme = parse_url($candidate, PHP_URL_SCHEME);

            if ($isValidUrl && in_array($scheme, ['http', 'https'], true)) {
                return $candidate;
            }
        }

        return (string) config('app.public_frontend_url', config('app.frontend_url'));
    }
}
