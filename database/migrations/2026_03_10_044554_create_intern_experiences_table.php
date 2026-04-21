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
        Schema::create('intern_experiences', function (Blueprint $table) {
    $table->id();
    // Menggunakan user_id agar relasi ke User lancar
    $table->foreignId('user_id')->constrained('users', 'user_id')->onDelete('cascade');
    $table->string('title');   
    $table->string('company'); 
    $table->string('period');  
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('intern_experiences');
    }
};
