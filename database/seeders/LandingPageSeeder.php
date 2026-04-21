<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Job;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class LandingPageSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Bersihkan tabel agar tidak terjadi duplikat (Urutan penting!)
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Job::truncate();
        Category::truncate();
        User::where('email', 'admin@vocaseek.com')->delete();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 2. Buat User Admin (Sebagai pemilik lowongan)
        $user = User::create([
            'nama' => 'Admin Vocaseek',
            'email' => 'admin@vocaseek.com',
            'password' => Hash::make('password'),
            'role' => 'company'
        ]);

        // 3. Buat Kategori (Sesuai dengan UI Landing Page)
        $categories = [
            ['nama_kategori' => 'Graphics & Design', 'icon' => 'bi-palette'],
            ['nama_kategori' => 'Code & Programing', 'icon' => 'bi-code-slash'],
            ['nama_kategori' => 'Digital Marketing', 'icon' => 'bi-megaphone'],
            ['nama_kategori' => 'Video & Animation', 'icon' => 'bi-play-btn'],
            ['nama_kategori' => 'Music & Audio', 'icon' => 'bi-music-note-beamed'],
            ['nama_kategori' => 'Account & Finance', 'icon' => 'bi-bank'],
            ['nama_kategori' => 'Health & Care', 'icon' => 'bi-heart-pulse'],
            ['nama_kategori' => 'Data & Science', 'icon' => 'bi-graph-up'],
        ];

        foreach ($categories as $cat) {
            Category::create($cat);
        }

        // 4. Ambil Kategori untuk Relasi Lowongan
        $codeCat = Category::where('nama_kategori', 'Code & Programing')->first();
        $designCat = Category::where('nama_kategori', 'Graphics & Design')->first();

        // 5. Buat Data Lowongan Contoh (Featured Jobs)
        if ($user && $codeCat && $designCat) {
            Job::create([
                'user_id' => $user->user_id,
                'category_id' => $codeCat->id,
                'judul_pekerjaan' => 'Software Engineer',
                'lokasi' => 'Jakarta, Indonesia',
                'perusahaan' => 'Apple Inc',
                'tipe' => 'Full Time',
                'gaji' => 'Rp 15.000.000'
            ]);

            Job::create([
                'user_id' => $user->user_id,
                'category_id' => $designCat->id,
                'judul_pekerjaan' => 'Senior UI/UX Designer',
                'lokasi' => 'Surabaya, Indonesia',
                'perusahaan' => 'Upwork',
                'tipe' => 'Contract Base',
                'gaji' => 'Rp 10.000.000'
            ]);
        }
    }
}