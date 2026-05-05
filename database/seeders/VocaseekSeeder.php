<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\CompanyProfile;
use App\Models\Lowongan;
use App\Models\JobApplication;
use App\Models\InternProfile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class VocaseekSeeder extends Seeder
{
    public function run(): void
    {
        // 1. DATA PERUSAHAAN (Sesuai UI Dashboard - Bank Mandiri)
        $companyUser = User::updateOrCreate(
            ['email' => 'hrd@mandiri.com'],
            array_filter([
                'nama' => 'HRD Bank Mandiri',
                'password' => Hash::make('password'),
                'role' => 'company',
                'status' => Schema::hasColumn('users', 'status') ? 'active' : null,
                'email_verified_at' => Schema::hasColumn('users', 'email_verified_at') ? now() : null,
            ], fn ($value) => $value !== null)
        );

        $cp = CompanyProfile::updateOrCreate(
            ['user_id' => $companyUser->user_id],
            [
                'nama_perusahaan' => 'Bank Mandiri',
                'industri' => 'Perbankan',
                'ukuran_perusahaan' => '1000+ karyawan',
                'deskripsi' => 'Bank Mandiri adalah mitra demo Vocaseek untuk pengujian alur lowongan dan lamaran.',
                'alamat_kantor_pusat' => 'Jakarta',
                'notelp' => '021-123456',
                'nib' => '1234567890123',
                'status_mitra' => 'active',
            ]
        );

        // 2. DATA LOWONGAN (Sesuai Dashboard Gambar 1)
        $job1 = Lowongan::updateOrCreate(
            ['company_profile_id' => $cp->id, 'judul_pekerjaan' => 'Senior UI/UX Designer'],
            [
                'judul_posisi' => 'Senior UI/UX Designer',
                'kategori_pekerjaan' => 'Design',
                'tipe_pekerjaan' => 'Internship',
                'deskripsi_pekerjaan' => 'Mendesain antarmuka aplikasi perbankan masa depan.',
                'persyaratan' => 'Figma, Adobe XD, Understanding of Design System.',
                'lokasi' => 'Jakarta',
                'tipe_magang' => 'onsite',
                'pengaturan_kerja' => 'onsite',
                'gaji_per_bulan' => '2500000 - 3500000',
                'gaji_min' => 2500000,
                'gaji_max' => 3500000,
                'tanggal_penutupan_lamaran' => now()->addDays(30)->toDateString(),
                'tanggal_mulai_kerja' => now()->addDays(45)->toDateString(),
                'tgl_tutup_lamaran' => now()->addDays(30)->toDateString(),
                'tgl_mulai_kerja' => now()->addDays(45)->toDateString(),
                'status' => 'ACTIVE',
            ]
        );

        $job2 = Lowongan::updateOrCreate(
            ['company_profile_id' => $cp->id, 'judul_pekerjaan' => 'Frontend Engineer'],
            [
                'judul_posisi' => 'Frontend Engineer',
                'kategori_pekerjaan' => 'Technology',
                'tipe_pekerjaan' => 'Internship',
                'deskripsi_pekerjaan' => 'Slicing design Figma ke React.js.',
                'persyaratan' => 'React.js, Tailwind, Axios.',
                'lokasi' => 'Remote',
                'tipe_magang' => 'remote',
                'pengaturan_kerja' => 'remote',
                'gaji_per_bulan' => '3000000 - 4500000',
                'gaji_min' => 3000000,
                'gaji_max' => 4500000,
                'tanggal_penutupan_lamaran' => now()->addDays(21)->toDateString(),
                'tanggal_mulai_kerja' => now()->addDays(40)->toDateString(),
                'tgl_tutup_lamaran' => now()->addDays(21)->toDateString(),
                'tgl_mulai_kerja' => now()->addDays(40)->toDateString(),
                'status' => 'ACTIVE',
            ]
        );

        // 3. DATA TALENT (Bagus, Rizky, Adi, Siti - Sesuai Gambar 1)
        $talents = [
            ['name' => 'Bagus Setiawan', 'email' => 'bagus.s@gmail.com', 'pos' => $job1->id, 'status' => 'PENDING'],
            ['name' => 'Rizky Pratama', 'email' => 'rizky.dev@yahoo.com', 'pos' => $job2->id, 'status' => 'SHORTLISTED'],
            ['name' => 'Adi Wijaya', 'email' => 'adi.wijaya@gmail.com', 'pos' => $job1->id, 'status' => 'REJECTED'],
            ['name' => 'Siti Aminah', 'email' => 'siti.a@data.io', 'pos' => $job2->id, 'status' => 'PENDING'],
        ];

        foreach ($talents as $t) {
            $user = User::updateOrCreate(
                ['email' => $t['email']],
                array_filter([
                    'nama' => $t['name'],
                    'password' => Hash::make('password'),
                    'role' => 'intern',
                    'status' => Schema::hasColumn('users', 'status') ? 'active' : null,
                    'email_verified_at' => Schema::hasColumn('users', 'email_verified_at') ? now() : null,
                ], fn ($value) => $value !== null)
            );

            // Buat Profil Intern (Biar Gambar 2 & 3 tidak kosong)
            InternProfile::updateOrCreate(
                ['user_id' => $user->user_id],
                [
                    'tentang_saya' => 'Saya adalah talenta muda yang berdedikasi tinggi.',
                    'universitas' => 'UPN Veteran Jawa Timur',
                    'jurusan' => 'Informatika',
                    'jenjang' => 'S1',
                    'ipk' => 3.75,
                    'tahun_masuk' => '2022',
                    'tahun_lulus' => '2026',
                    'skor_pretest' => 85,
                    'is_profile_complete' => true,
                    'test_started_at' => now()->subDay(),
                    'test_finished_at' => now()->subDay()->addMinutes(20),
                ]
            );

            // Buat Lamaran Kerja
            JobApplication::updateOrCreate(
                ['user_id' => $user->user_id, 'job_id' => $t['pos']],
                ['status' => $t['status']]
            );
        }

        $pretestUser = User::updateOrCreate(
            ['email' => 'pretest.intern@vocaseek.com'],
            array_filter([
                'nama' => 'Nanda Pretest',
                'password' => Hash::make('password'),
                'role' => 'intern',
                'status' => Schema::hasColumn('users', 'status') ? 'active' : null,
                'email_verified_at' => Schema::hasColumn('users', 'email_verified_at') ? now() : null,
            ], fn ($value) => $value !== null)
        );

        InternProfile::updateOrCreate(
            ['user_id' => $pretestUser->user_id],
            [
                'tentang_saya' => 'Akun demo untuk menguji alur pre-test dari awal.',
                'universitas' => 'UPN Veteran Jawa Timur',
                'jurusan' => 'Sistem Informasi',
                'jenjang' => 'S1',
                'ipk' => 3.60,
                'tahun_masuk' => '2022',
                'tahun_lulus' => '2026',
                'skor_pretest' => 0,
                'is_profile_complete' => true,
                'test_started_at' => null,
                'test_finished_at' => null,
            ]
        );
    }
}
