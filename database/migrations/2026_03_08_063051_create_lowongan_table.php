<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
   Schema::create('lowongans', function (Blueprint $table) {
    $table->id();
    $table->foreignId('company_profile_id')->constrained('company_profiles');
    $table->string('judul_pekerjaan');
    $table->string('kategori_pekerjaan'); // Contoh: Teknologi Informasi
    $table->string('tipe_pekerjaan');     // Contoh: Penuh Waktu
    $table->string('lokasi');
    $table->string('pengaturan_kerja');  // Contoh: WFO, Remote, Hybrid
    $table->bigInteger('gaji_min')->nullable();
    $table->bigInteger('gaji_max')->nullable();
    $table->text('deskripsi_pekerjaan');
    $table->text('persyaratan');
    $table->date('tgl_tutup_lamaran');
    $table->date('tgl_mulai_kerja');
    $table->enum('status', ['OPEN', 'CLOSED', 'DRAFT'])->default('DRAFT');
    $table->timestamps();
});
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lowongan');
    }
};
