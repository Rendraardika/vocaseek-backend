<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\CompanyProfile;
use App\Models\Lowongan;
use App\Models\JobApplication;
use App\Models\InternProfile;
use Illuminate\Support\Facades\Hash;

class VocaseekSeeder extends Seeder
{
    public function run(): void
    {
        // 1. DATA PERUSAHAAN (Sesuai UI Dashboard - Bank Mandiri)
        $companyUser = User::create([
            'nama' => 'HRD Bank Mandiri',
            'email' => 'hrd@mandiri.com',
            'password' => Hash::make('password'),
            'role' => 'company'
        ]);

        $cp = CompanyProfile::create([
            'user_id' => $companyUser->user_id,
            'nama_perusahaan' => 'Bank Mandiri',
            'notelp' => '021-123456',
            'nib' => '1234567890123',
            'status_mitra' => 'active'
        ]);

        // 2. DATA LOWONGAN (Sesuai Dashboard Gambar 1)
        $job1 = Lowongan::create([
            'company_profile_id' => $cp->id,
            'judul_posisi' => 'Senior UI/UX Designer',
            'deskripsi_pekerjaan' => 'Mendesain antarmuka aplikasi perbankan masa depan.',
            'persyaratan' => 'Figma, Adobe XD, Understanding of Design System.',
            'lokasi' => 'Jakarta (WFO)',
            'tipe_magang' => 'Full-time',
            'status' => 'ACTIVE'
        ]);

        $job2 = Lowongan::create([
            'company_profile_id' => $cp->id,
            'judul_posisi' => 'Frontend Engineer',
            'deskripsi_pekerjaan' => 'Slicing design Figma ke React.js.',
            'persyaratan' => 'React.js, Tailwind, Axios.',
            'lokasi' => 'Remote',
            'tipe_magang' => 'Remote',
            'status' => 'ACTIVE'
        ]);

        // 3. DATA TALENT (Bagus, Rizky, Adi, Siti - Sesuai Gambar 1)
        $talents = [
            ['name' => 'Bagus Setiawan', 'email' => 'bagus.s@gmail.com', 'pos' => $job1->id, 'status' => 'PENDING'],
            ['name' => 'Rizky Pratama', 'email' => 'rizky.dev@yahoo.com', 'pos' => $job2->id, 'status' => 'SHORTLISTED'],
            ['name' => 'Adi Wijaya', 'email' => 'adi.wijaya@gmail.com', 'pos' => $job1->id, 'status' => 'REJECTED'],
            ['name' => 'Siti Aminah', 'email' => 'siti.a@data.io', 'pos' => $job2->id, 'status' => 'PENDING'],
        ];

        foreach ($talents as $t) {
            $user = User::create([
                'nama' => $t['name'],
                'email' => $t['email'],
                'password' => Hash::make('password'),
                'role' => 'intern'
            ]);

            // Buat Profil Intern (Biar Gambar 2 & 3 tidak kosong)
            InternProfile::create([
                'user_id' => $user->user_id,
                'tentang_saya' => 'Saya adalah talenta muda yang berdedikasi tinggi.',
                'universitas' => 'UPN Veteran Jawa Timur',
                'jurusan' => 'Informatika',
                'skor_pretest' => 85
            ]);

            // Buat Lamaran Kerja
            JobApplication::create([
                'user_id' => $user->user_id,
                'job_id' => $t['pos'],
                'status' => $t['status']
            ]);
        }
    }
}