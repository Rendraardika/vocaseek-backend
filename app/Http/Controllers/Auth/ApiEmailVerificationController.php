<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\CompanyProfile;
use App\Models\InternProfile;
use App\Models\PendingRegistration;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use App\Notifications\VerifyPendingRegistrationForSpa;

class ApiEmailVerificationController extends Controller
{
    public function verify(Request $request, int $id, string $hash): RedirectResponse
    {
        $frontendUrl = rtrim(config('app.public_frontend_url', config('app.frontend_url')), '/');
        $user = User::find($id);

        if (! $user || ! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return redirect()->away($frontendUrl.'/email-verification?status=invalid');
        }

        if (! $request->hasValidSignature(false)) {
            return redirect()->away($frontendUrl.'/email-verification?status=expired');
        }

        if (! $user->hasVerifiedEmail()) {
            if ($user->markEmailAsVerified()) {
                event(new Verified($user));
            }
        }

        return redirect()->away(
            $frontendUrl.'/email-verification?status=success&email='.urlencode($user->email)
        );
    }

    public function verifyPending(Request $request, int $id, string $hash): RedirectResponse
    {
        $frontendUrl = rtrim(config('app.public_frontend_url', config('app.frontend_url')), '/');
        $pendingRegistration = PendingRegistration::find($id);

        if (! $pendingRegistration || ! hash_equals((string) $hash, sha1($pendingRegistration->email))) {
            return redirect()->away($frontendUrl.'/email-verification?status=invalid');
        }

        if (! $request->hasValidSignature(false)) {
            return redirect()->away($frontendUrl.'/email-verification?status=expired');
        }

        $existingUser = User::where('email', $pendingRegistration->email)->first();

        if ($existingUser) {
            return redirect()->away(
                $frontendUrl.'/email-verification?status=already-processed&email='.urlencode($existingUser->email)
            );
        }

        $user = DB::transaction(function () use ($pendingRegistration) {
            $userData = [
                'nama' => $pendingRegistration->nama,
                'email' => $pendingRegistration->email,
                'password' => $pendingRegistration->password,
                'role' => $pendingRegistration->role,
                'notelp' => $pendingRegistration->notelp,
            ];

            if (User::supportsPreferredLocale() && filled($pendingRegistration->preferred_locale)) {
                $userData['preferred_locale'] = $pendingRegistration->preferred_locale;
            }

            if (User::supportsEmailVerificationColumn()) {
                $userData['email_verified_at'] = now();
            }

            $user = User::create($userData);

            if ($pendingRegistration->role === 'company') {
                $payload = $pendingRegistration->company_payload ?? [];

                CompanyProfile::create([
                    'user_id' => $user->user_id,
                    'nama_perusahaan' => $payload['nama_perusahaan'] ?? $pendingRegistration->nama,
                    'notelp' => $payload['notelp'] ?? $pendingRegistration->notelp,
                    'nib' => $payload['nib'] ?? null,
                    'loa_pdf' => $payload['loa_pdf'] ?? null,
                    'akta_pdf' => $payload['akta_pdf'] ?? null,
                    'status_mitra' => $payload['status_mitra'] ?? 'pending',
                ]);
            } elseif ($pendingRegistration->role === 'intern') {
                InternProfile::create([
                    'user_id' => $user->user_id,
                    'is_profile_complete' => 0,
                ]);
            }

            $pendingRegistration->delete();

            return $user;
        });

        event(new Verified($user));

        return redirect()->away(
            $frontendUrl.'/email-verification?status=success&email='.urlencode($user->email)
        );
    }

    public function resend(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email:rfc'],
        ]);

        $user = User::where('email', $validated['email'])->first();
        $pendingRegistration = PendingRegistration::where('email', $validated['email'])->first();

        if ($pendingRegistration) {
            Notification::route('mail', $pendingRegistration->email)
                ->notify(new VerifyPendingRegistrationForSpa($pendingRegistration));
        }

        if ($user && ! $user->hasVerifiedEmail()) {
            $user->sendEmailVerificationNotification();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Jika email terdaftar dan belum diverifikasi, link verifikasi baru sudah dikirim.',
        ]);
    }
}
