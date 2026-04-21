<?php

namespace App\Http\Controllers\Auth; // Pastikan foldernya app/Http/Controllers/Auth

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\InternProfile;
use App\Models\CompanyProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{
    public function register(Request $request)
    {
        // 1. Validasi
        $rules = [
            'nama'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'notelp'   => 'required|string|max:20',
            'role'     => 'required|in:intern,company',
        ];

        if ($request->role === 'company') {
            $rules['nama_perusahaan'] = 'required|string|max:255';
            $rules['nib']  = 'required|string|max:50';
            $rules['loa_pdf']  = 'required|mimes:pdf|max:2048';
            $rules['akta_pdf'] = 'required|mimes:pdf|max:2048';
        }

        $request->validate($rules);

        $user = DB::transaction(function () use ($request) {

            // 2. Simpan ke tabel users (Sesuaikan primary key kalau kamu pakai user_id)
            $user = User::create([
                'nama'     => $request->nama,
                'email'    => $request->email,
                'password' => $request->password,
                'role'     => $request->role,
                'notelp'   => $request->notelp,
            ]);

            // 3. Jika role company
            if ($request->role === 'company') {
                $loaPath  = $request->file('loa_pdf')->store('documents/loa', 'public');
                $aktaPath = $request->file('akta_pdf')->store('documents/akta', 'public');

                CompanyProfile::create([
                    'user_id'         => $user->user_id, // Pakai user_id sesuai migrasi kamu
                    'nama_perusahaan' => $request->nama_perusahaan,
                    'notelp'          => $request->notelp,
                    'nib'             => $request->nib,
                    'loa_pdf'         => $loaPath,
                    'akta_pdf'        => $aktaPath,
                ]);
            } 
            else {
                InternProfile::create([
                    'user_id' => $user->user_id, // Pakai user_id sesuai migrasi kamu
                    'is_profile_complete' => false
                ]);
            }

            return $user;
        });

        // 4. Otomatis Login setelah daftar
        Auth::login($user);

        // 5. Redirect pakai Inertia (Bukan JSON)
        if ($user->role === 'company') {
            return redirect()->route('company.dashboard');
        }

        return redirect()->route('dashboard'); // Dashboard default buat intern
    }
}
