<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\InternProfile;
use App\Models\CompanyProfile;
use App\Models\PendingRegistration;
use App\Notifications\VerifyPendingRegistrationForSpa;
use App\Support\PasswordRules;
use Illuminate\Contracts\Auth\StatefulGuard;

class AuthController extends Controller
{
    private function webGuard(): StatefulGuard
    {
        return Auth::guard('web');
    }

    // 1. FUNGSI LOGIN
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email:rfc',
            'password' => 'required',
        ]);

        $existingUser = User::where('email', $request->email)->first();
        $pendingRegistration = PendingRegistration::where('email', $request->email)->first();

        if (! $existingUser) {
            if ($pendingRegistration) {
                return response()->json([
                    'status' => 'error',
                    'code' => 'email_pending_verification',
                    'message' => 'Akun Anda belum aktif. Silakan cek email lalu klik link verifikasi terlebih dahulu.',
                ], 403);
            }

            return response()->json([
                'status' => 'error',
                'code' => 'email_not_registered',
                'message' => 'Silahkan register terlebih dahulu.',
            ], 404);
        }

        if ($existingUser?->status === 'pending_invitation') {
            return response()->json([
                'status' => 'error',
                'code' => 'invitation_pending',
                'message' => 'Akun admin ini belum diaktifkan. Silakan buka tautan aktivasi dari email undangan Anda.',
            ], 403);
        }

        if ($existingUser?->status === 'disabled') {
            return response()->json([
                'status' => 'error',
                'code' => 'account_disabled',
                'message' => 'Akun Anda sedang dinonaktifkan. Hubungi admin master untuk bantuan lebih lanjut.',
            ], 403);
        }

        $guard = $this->webGuard();

        if ($guard->check()) {
            $guard->logout();
        }

        if (! $guard->attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Email atau Password salah'], 401);
        }

        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        $user = $guard->user();

        if (! $user->hasVerifiedEmail()) {
            $guard->logout();

            if ($request->hasSession()) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            return response()->json([
                'status' => 'error',
                'code' => 'email_unverified',
                'message' => 'Email Anda belum diverifikasi. Silakan cek inbox lalu klik link verifikasi terlebih dahulu.',
            ], 403);
        }

        if ($user->role === 'company') {
            $companyProfile = $user->companyProfile;

            if (!$companyProfile || $companyProfile->status_mitra !== 'active') {
                $guard->logout();

                if ($request->hasSession()) {
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                }

                return response()->json([
                    'status' => 'error',
                    'message' => __('messages.auth.company_not_approved'),
                ], 403);
            }
        }

        $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'token' => $token,
            'role' => $user->role, 
            'user' => $user->nama,
            'user_data' => [
                'user_id' => $user->user_id,
                'nama' => $user->nama,
                'email' => $user->email,
                'role' => $user->role,
                'notelp' => $user->notelp,
            ],
            'locale' => $user->getResolvedLocale(),
        ]);
    }

    // 2. FUNGSI REGISTER
    public function register(Request $request)
    {
        // Validasi dasar untuk semua role
        $rules = [
            'nama'     => 'required|string|max:100',
            'email'    => 'required|email:rfc|unique:users,email',
            'password' => array_merge(['required', 'confirmed'], PasswordRules::strong()),
            'notelp'   => ['required', 'string', 'max:20', 'regex:/^\+?[0-9]+$/'],
            'role'     => 'required|in:intern,company,super_admin,staff_admin',
        ];

        // Validasi tambahan khusus Company (Sesuai gambar UI Partner With Us)
        if ($request->role === 'company') {
            $rules['nama_perusahaan'] = 'required|string|max:255';
            $rules['nib']      = 'required|string|max:50';
            $rules['loa_pdf']  = 'required|mimes:pdf|max:5120'; // Sesuai UI max 5MB
            $rules['akta_pdf'] = 'required|mimes:pdf|max:5120';
        }

        $request->validate($rules, [
            'password.regex' => PasswordRules::message(),
        ]);

        try {
            $pendingRegistration = DB::transaction(function () use ($request) {
                $pendingRegistration = PendingRegistration::where('email', $request->email)->first();

                if ($pendingRegistration && $pendingRegistration->role === 'company' && $request->role !== 'company') {
                    $oldPayload = $pendingRegistration->company_payload ?? [];

                    foreach ([$oldPayload['loa_pdf'] ?? null, $oldPayload['akta_pdf'] ?? null] as $oldFile) {
                        if ($oldFile) {
                            Storage::disk('public')->delete($oldFile);
                        }
                    }
                }

                if ($pendingRegistration && ($pendingRegistration->role !== 'company' || $request->role !== 'company')) {
                    $pendingRegistration->company_payload = null;
                }

                $companyPayload = null;

                if ($request->role === 'company') {
                    $oldPayload = $pendingRegistration?->company_payload ?? [];

                    $loaPath = $request->file('loa_pdf')->store('company/documents/pending', 'public');
                    $aktaPath = $request->file('akta_pdf')->store('company/documents/pending', 'public');

                    foreach ([$oldPayload['loa_pdf'] ?? null, $oldPayload['akta_pdf'] ?? null] as $oldFile) {
                        if ($oldFile && $oldFile !== $loaPath && $oldFile !== $aktaPath) {
                            Storage::disk('public')->delete($oldFile);
                        }
                    }

                    $companyPayload = [
                        'nama_perusahaan' => $request->nama_perusahaan,
                        'notelp' => $request->notelp,
                        'nib' => $request->nib,
                        'loa_pdf' => $loaPath,
                        'akta_pdf' => $aktaPath,
                        'status_mitra' => 'pending',
                    ];
                }

                return PendingRegistration::updateOrCreate(
                    ['email' => $request->email],
                    [
                        'nama' => $request->nama,
                        'password' => $request->password,
                        'role' => $request->role,
                        'notelp' => $request->notelp,
                        'preferred_locale' => User::supportsPreferredLocale() ? app()->getLocale() : null,
                        'company_payload' => $companyPayload,
                    ]
                );
            });

            if (method_exists($request, 'afterResponse')) {
                $request->afterResponse(function () use ($pendingRegistration) {
                    Notification::route('mail', $pendingRegistration->email)
                        ->notify(new VerifyPendingRegistrationForSpa($pendingRegistration));
                });
            } else {
                Notification::route('mail', $pendingRegistration->email)
                    ->notify(new VerifyPendingRegistrationForSpa($pendingRegistration));
            }

            if ($pendingRegistration->role === 'company') {
                return response()->json([
                    'status'  => 'success',
                    'message' => 'Registrasi company berhasil. Data Anda akan dibuat setelah email diverifikasi. Setelah itu akun tetap harus menunggu persetujuan mitra sebelum bisa login.',
                    'user'    => $pendingRegistration->nama,
                    'role'    => $pendingRegistration->role,
                    'email'   => $pendingRegistration->email,
                    'locale'  => $pendingRegistration->preferred_locale ?? app()->getLocale(),
                ], 201);
            }

            return response()->json([
                'status'  => 'success',
                'message' => 'Registrasi berhasil. Akun Anda akan dibuat setelah link verifikasi di email diklik.',
                'user'    => $pendingRegistration->nama,
                'role'    => $pendingRegistration->role,
                'email'   => $pendingRegistration->email,
                'locale'  => $pendingRegistration->preferred_locale ?? app()->getLocale(),
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal registrasi: ' . $e->getMessage()
            ], 500);
        }
    }

    // 3. FUNGSI LOGOUT
    public function logout(Request $request)
    {
        if ($request->user()) {
            $request->user()->currentAccessToken()->delete();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil Logout!'
        ]);
    }

    public function me(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], 401);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'user_id' => $user->user_id,
                'nama' => $user->nama,
                'email' => $user->email,
                'role' => $user->role,
                'notelp' => $user->notelp,
                'preferred_locale' => $user->getResolvedLocale(),
                'google_id' => $user->google_id,
            ],
        ]);
    }
}
