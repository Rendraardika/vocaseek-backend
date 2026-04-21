<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::create('intern_profiles', function (Blueprint $table) {
        $table->id('intern_id');
        $table->foreignId('user_id')->constrained('users', 'user_id')->onDelete('cascade');
        
        $table->string('foto')->nullable();
        $table->text('tentang_saya')->nullable();
        $table->string('tempat_lahir')->nullable();
        $table->date('tanggal_lahir')->nullable();
        $table->string('jenis_kelamin')->nullable();
        
        // Kontak & Sosmed (Sesuai Gambar 3)
        $table->string('notelp')->nullable();
        $table->string('instagram')->nullable();
        $table->string('linkedin')->nullable();

        $table->string('provinsi')->nullable();
        $table->string('kabupaten')->nullable();
        $table->text('detail_alamat')->nullable();
        
        // Data Akademik & Dokumen (Sesuai Gambar 2)
        $table->string('universitas')->nullable();
        $table->string('jurusan')->nullable();
        $table->string('cv_pdf')->nullable();
        $table->string('portofolio_pdf')->nullable();
        
        // Fitur Pre-Test & Status
        $table->integer('skor_pretest')->default(0);
        $table->boolean('is_profile_complete')->default(false);
        $table->timestamps();
    });
}

    public function down(): void
    {
        Schema::dropIfExists('intern_profiles');
    }
};