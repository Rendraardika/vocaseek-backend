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
        Schema::table('intern_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('intern_profiles', 'test_started_at')) {
                $table->timestamp('test_started_at')->nullable()->after('skor_pretest');
            }

            if (!Schema::hasColumn('intern_profiles', 'test_finished_at')) {
                $table->timestamp('test_finished_at')->nullable()->after('test_started_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('intern_profiles', function (Blueprint $table) {
            $columnsToDrop = [];

            if (Schema::hasColumn('intern_profiles', 'test_started_at')) {
                $columnsToDrop[] = 'test_started_at';
            }

            if (Schema::hasColumn('intern_profiles', 'test_finished_at')) {
                $columnsToDrop[] = 'test_finished_at';
            }

            if ($columnsToDrop !== []) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
