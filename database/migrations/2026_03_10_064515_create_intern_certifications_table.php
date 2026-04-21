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
       Schema::create('intern_certifications', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained('users', 'user_id')->onDelete('cascade');
    $table->string('name'); // Contoh: Certified Solidworks Professional
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('intern_certifications');
    }
};
