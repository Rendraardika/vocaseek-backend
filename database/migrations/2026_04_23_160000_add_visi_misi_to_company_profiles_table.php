<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company_profile', function (Blueprint $table) {
            $table->text('visi')->nullable()->after('deskripsi');
            $table->text('misi')->nullable()->after('visi');
        });
    }

    public function down(): void
    {
        Schema::table('company_profile', function (Blueprint $table) {
            $table->dropColumn(['visi', 'misi']);
        });
    }
};
