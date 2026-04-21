<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;

class AdminProfileController extends Controller
{
    private function normalizePasswordPayload(Request $request): void
    {
        $request->merge([
            'current_password' => $request->input('current_password')
                ?? $request->input('old_password')
                ?? $request->input('currentPassword')
                ?? $request->input('oldPassword'),
            'password' => $request->input('password')
                ?? $request->input('new_password')
                ?? $request->input('newPassword'),
            'password_confirmation' => $request->input('password_confirmation')
                ?? $request->input('confirm_password')
                ?? $request->input('confirmPassword')
                ?? $request->input('new_password_confirmation'),
        ]);
    }

    
    public function show()
    {
        $user = auth()->user();

        return response()->json([
            'status' => 'success',
            'nama' => $user->nama,
            'name' => $user->nama,
            'full_name' => $user->nama,
            'email' => $user->email,
            'email_address' => $user->email,
            'role' => $user->role,
            'foto' => $user->foto ? asset('storage/' . ltrim($user->foto, '/')) : null,
            'data' => [
                'nama' => $user->nama,
                'name' => $user->nama,
                'full_name' => $user->nama,
                'email' => $user->email,
                'email_address' => $user->email,
                'notelp' => $user->notelp,
                'id_karyawan' => 'VK-2024-' . str_pad($user->user_id, 3, '0', STR_PAD_LEFT),
                'role_name' => $user->role === 'super_admin' ? 'Master Admin Platform' : 'Staff Admin Platform',
                'foto' => $user->foto ? asset('storage/' . ltrim($user->foto, '/')) : null,
                'terdaftar_sejak' => optional($user->created_at)->format('M Y') ?? 'N/A',
                'riwayat_aktivitas' => [
                    ['pesan' => 'Login Berhasil', 'waktu' => now()->format('H:i \W\I\B')],
                    ['pesan' => 'Memperbarui Pengguna: J. Doe', 'waktu' => 'Kemarin, 14:20 WIB'],
                ]
            ]
        ]);
    }

    
    public function changePassword(Request $request)
    {
        $this->normalizePasswordPayload($request);

        $request->validate([
            'current_password' => ['required', 'current_password'], 
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = auth()->user();

        $user->forceFill([
            'password' => Hash::make($request->string('password')->value()),
            'remember_token' => Str::random(60),
        ])->save();

        $user->tokens()->delete();

        return response()->json(['message' => 'Kata sandi berhasil diperbarui!']);
    }

    
    public function update(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'nullable|email|unique:users,email,' . $user->user_id . ',user_id',
            'notelp' => 'required|string',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'remove_foto' => 'nullable|boolean',
        ]);

        if ($request->boolean('remove_foto')) {
            if ($user->foto) {
                Storage::disk('public')->delete($user->foto);
            }

            $validated['foto'] = null;
        }

        if ($request->hasFile('foto')) {
            if ($user->foto) {
                Storage::disk('public')->delete($user->foto);
            }

            $validated['foto'] = $request->file('foto')->store('admin/photos', 'public');
        }

        $user->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Data profil berhasil disimpan!',
            'data' => [
                'nama' => $user->nama,
                'email' => $user->email,
                'notelp' => $user->notelp,
                'foto' => $user->foto ? asset('storage/' . ltrim($user->foto, '/')) : null,
            ],
        ]);
    }
}
