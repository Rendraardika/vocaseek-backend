<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pending_registrations', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100);
            $table->string('email')->unique();
            $table->text('password');
            $table->enum('role', ['intern', 'company', 'super_admin', 'staff_admin'])->default('intern');
            $table->string('notelp', 20)->nullable();
            $table->string('preferred_locale', 10)->nullable();
            $table->json('company_payload')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pending_registrations');
    }
};
