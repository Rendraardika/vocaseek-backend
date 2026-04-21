<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('intern_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('intern_profiles', 'dokumen_pendidikan_pdf')) {
                $table->string('dokumen_pendidikan_pdf')->nullable()->after('tahun_lulus');
            }
        });
    }

    public function down(): void
    {
        Schema::table('intern_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('intern_profiles', 'dokumen_pendidikan_pdf')) {
                $table->dropColumn('dokumen_pendidikan_pdf');
            }
        });
    }
};
