<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lowongans', function (Blueprint $table) {
            $table->dropForeign(['company_profile_id']);
            $table->foreign('company_profile_id')
                ->references('id')
                ->on('company_profiles')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('lowongans', function (Blueprint $table) {
            $table->dropForeign(['company_profile_id']);
            $table->foreign('company_profile_id')
                ->references('id')
                ->on('company_profiles');
        });
    }
};
