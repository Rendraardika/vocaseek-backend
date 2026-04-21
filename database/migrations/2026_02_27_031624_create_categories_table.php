<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up(): void
{
    // Tabel 1: Kategori
    Schema::create('categories', function (Blueprint $table) {
        $table->id();
        $table->string('nama_kategori');
        $table->string('icon')->nullable(); 
        $table->timestamps();
    });

    // Tabel 2: Lowongan (Ganti nama jadi job_listings)
    Schema::create('job_listings', function (Blueprint $table) {
        $table->id();
        // Gunakan parameter kedua 'user_id' karena Primary Key di users adalah user_id
        $table->foreignId('user_id')->constrained('users', 'user_id')->onDelete('cascade');
        $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
        $table->string('judul_pekerjaan');
        $table->string('lokasi');
        $table->string('perusahaan');
        $table->enum('tipe', ['Full Time', 'Internship', 'Freelance', 'Contract Base']);
        $table->string('gaji')->nullable();
        $table->timestamps();
    });
}

    public function down(): void
    {
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('categories');
    }
};