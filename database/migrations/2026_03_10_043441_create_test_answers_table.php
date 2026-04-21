<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::create('test_answers', function (Blueprint $table) {
        $table->id();
        // Menggunakan user_id sebagai foreign key
        $table->foreignId('user_id')->constrained('users', 'user_id')->onDelete('cascade');
        $table->text('question_text');
        $table->text('user_answer');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test_answers');
    }
};
