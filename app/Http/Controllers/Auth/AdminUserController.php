<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Validation\Rules;

class AdminUserController extends Controller
{
    /**
     * 1. LIST ADMIN INTERNAL (Halaman Utama User Management - Gambar 1)
     */
    public function index()
    {
        // Ambil semua user dengan role admin (Super & Staff)
        $admins = User::whereIn('role', ['super_admin', 'staff_admin'])
            ->latest()
            ->paginate(10);

        return response()->json([
            'status' => 'success',
            // 3 Card Statistik di Atas (Total, Super, Staff)
            'stats' => [
                'total_admin' => User::whereIn('role', ['super_admin', 'staff_admin'])->count(),
                'super_admin' => User::where('role', 'super_admin')->count(),
                'staff_admin' => User::where('role', 'staff_admin')->count(),
            ],
            'data' => $admins->map(fn($user) => [
                'id' => $user->user_id,
                'user_id' => $user->user_id,
                'nama' => $user->nama,
                'name' => $user->nama,
                'full_name' => $user->nama,
                'email' => $user->email,
                'email_address' => $user->email,
                'foto' => $user->foto,
                'identity' => [
                    'nama' => $user->nama,
                    'email' => $user->email,
                    'foto' => $user->foto // Avatar profil
                ],
                'role' => strtoupper(str_replace('_', ' ', $user->role)), // Format: SUPER ADMIN
                'status' => $user->status ?? 'Active', // Status Badge: Active / Inactive
                'joined_at' => optional($user->created_at)->format('d M Y') ?? 'N/A'
            ]),
            'pagination' => [
                'total' => $admins->total(),
                'current_page' => $admins->currentPage(),
            ]
        ]);
    }

    
    public function store(Request $request)
    {
        
        $validated = $request->validate([
            'nama' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'notelp' => 'required|string',
            'role' => 'required|in:super_admin,staff_admin', 
            'password' => 'required|min:8|confirmed', 
        ]);

        $admin = User::create([
            'nama' => $validated['nama'],
            'email' => $validated['email'],
            'notelp' => $validated['notelp'],
            'role' => $validated['role'],
            'password' => $validated['password'],
            'status' => 'Active' 
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Admin Website Baru Berhasil Ditambahkan!',
            'data' => [
                'id' => $admin->user_id,
                'user_id' => $admin->user_id,
                'nama' => $admin->nama,
                'name' => $admin->nama,
                'full_name' => $admin->nama,
                'email' => $admin->email,
                'email_address' => $admin->email,
                'notelp' => $admin->notelp,
                'role' => strtoupper(str_replace('_', ' ', $admin->role)),
                'status' => $admin->status ?? 'Active',
            ]
        ]);
    }

    
    public function updateStatus(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $user->update(['status' => $request->status]); 

        return response()->json(['message' => 'Status Admin berhasil diubah']);
    }

    
    public function destroy($id)
    {
        $admin = User::findOrFail($id);
        
        
        if (auth()->id() == $admin->user_id) {
            return response()->json(['message' => 'Bahaya! Anda tidak bisa menghapus akun sendiri'], 403);
        }

        $admin->delete();
        return response()->json(['message' => 'Admin berhasil dihapus dari sistem']);
    }
}
