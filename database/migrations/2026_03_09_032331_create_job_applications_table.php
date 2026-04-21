<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('job_applications', function (Blueprint $table) {
            $table->id('application_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('job_id'); 
            
            // Status sesuai UI Dashboard Gambar 1 & 8
            // PENDING (Kuning), SHORTLISTED (Hijau), REJECTED (Merah)
            $table->enum('status', ['PENDING', 'SHORTLISTED', 'REJECTED'])->default('PENDING'); 
            
            $table->timestamps();

            // Foreign Key ke Tabel Users (kolomnya user_id)
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            
            // Foreign Key ke Tabel Lowongan (merujuk ke tabel lowongan yang kita buat baru)
            $table->foreign('job_id')->references('id')->on('lowongan')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_applications');
    }
};