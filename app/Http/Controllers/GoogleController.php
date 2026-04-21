<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\InternProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;

class GoogleController extends Controller
{
    public function redirectToGoogle()
    {
        $redirectUrl = $this->getGoogleRedirectUrl();

        Log::info('Redirecting to Google OAuth.', [
            'redirect_url' => $redirectUrl,
            'app_url' => config('app.url'),
        ]);

        return Socialite::driver('google')
            ->redirectUrl($redirectUrl)
            ->stateless()
            ->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')
                ->redirectUrl($this->getGoogleRedirectUrl())
                ->stateless()
                ->user();
            
            $user = $this->findOrCreateGoogleUser($googleUser);
            Auth::login($user);
            request()->session()->regenerate();

            return redirect()->intended(route('dashboard'));

        } catch (\Exception $e) {
            DB::rollback();
            $message = config('app.debug') ? $e->getMessage() : 'Login Google gagal.';

            Log::error('Google login failed.', [
                'message' => $e->getMessage(),
                'redirect_url' => $this->getGoogleRedirectUrl(),
            ]);

            return redirect()->route('login')->withErrors([
                'google' => $message,
            ]);
        }
    }

    public function loginWithGoogleToken(Request $request)
    {
        $request->validate([
            'access_token' => 'required|string',
        ]);

        try {
            $googleUser = Socialite::driver('google')->userFromToken($request->access_token);
            $user = $this->findOrCreateGoogleUser($googleUser);
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'status' => 'success',
                'message' => 'Login Google berhasil.',
                'token' => $token,
                'role' => $user->role,
                'user' => [
                    'user_id' => $user->user_id,
                    'nama' => $user->nama,
                    'email' => $user->email,
                    'google_id' => $user->google_id,
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('Google token login failed.', [
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Google token tidak valid atau login Google gagal.',
            ], 401);
        }
    }

    private function getGoogleRedirectUrl(): string
    {
        if (app()->runningInConsole()) {
            return config('services.google.redirect')
                ?: rtrim(config('app.url'), '/') . '/api/auth/google/callback';
        }

        return url('/api/auth/google/callback');
    }

    private function findOrCreateGoogleUser(object $googleUser): User
    {
        return DB::transaction(function () use ($googleUser) {
            $user = User::where('email', $googleUser->getEmail())->first();

            if (!$user) {
                $payload = [
                    'nama' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'role' => 'intern',
                    'password' => bcrypt(Str::random(16)),
                    'notelp' => '-',
                ];

                if (User::supportsEmailVerificationColumn()) {
                    $payload['email_verified_at'] = Carbon::now();
                }

                $user = User::create($payload);

                InternProfile::create([
                    'user_id' => $user->user_id,
                    'is_profile_complete' => false,
                ]);
            } else {
                $updates = [];

                if (! $user->google_id) {
                    $updates['google_id'] = $googleUser->getId();
                }

                if (User::supportsEmailVerificationColumn() && ! $user->email_verified_at) {
                    $updates['email_verified_at'] = Carbon::now();
                }

                if ($updates !== []) {
                    $user->update($updates);
                }
            }

            return $user;
        });
    }
}
