<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    /**
     * TAHAP 1: Kirim Link Reset (Layar Input Email di Figma)
     */
    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:users,email']);

        // Laravel akan mengurus pembuatan token dan pengiriman email secara otomatis
        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? response()->json(['message' => 'Link reset kata sandi telah dikirim ke email Anda.'])
            : response()->json(['message' => 'Gagal mengirim email.'], 500);
    }

    /**
     * TAHAP 2 & 3: Reset Password (Layar Input Password Baru & Berhasil)
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token'    => 'required',
            'email'    => 'required|email|exists:users,email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => $password
                ])->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? response()->json(['message' => 'Kata sandi berhasil diperbarui!'])
            : response()->json(['message' => 'Token tidak valid atau sudah kadaluarsa.'], 400);
    }
}
