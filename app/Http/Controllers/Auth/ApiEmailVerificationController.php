<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ApiEmailVerificationController extends Controller
{
    public function verify(Request $request, int $id, string $hash): RedirectResponse
    {
        $frontendUrl = rtrim(config('app.public_frontend_url', config('app.frontend_url')), '/');
        $user = User::find($id);

        if (! $user || ! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return redirect()->away($frontendUrl.'/email-verification?status=invalid');
        }

        if (! $request->hasValidSignature()) {
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

    public function resend(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email:rfc,dns'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if ($user && ! $user->hasVerifiedEmail()) {
            $user->sendEmailVerificationNotification();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Jika email terdaftar dan belum diverifikasi, link verifikasi baru sudah dikirim.',
        ]);
    }
}
