<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('intern_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('intern_profiles', 'surat_rekomendasi_pdf')) {
                $table->string('surat_rekomendasi_pdf')->nullable()->after('portofolio_pdf');
            }

            if (!Schema::hasColumn('intern_profiles', 'ktp_pdf')) {
                $table->string('ktp_pdf')->nullable()->after('surat_rekomendasi_pdf');
            }

            if (!Schema::hasColumn('intern_profiles', 'transkrip_nilai_pdf')) {
                $table->string('transkrip_nilai_pdf')->nullable()->after('ktp_pdf');
            }
        });
    }

    public function down(): void
    {
        Schema::table('intern_profiles', function (Blueprint $table) {
            $columnsToDrop = [];

            foreach (['surat_rekomendasi_pdf', 'ktp_pdf', 'transkrip_nilai_pdf'] as $column) {
                if (Schema::hasColumn('intern_profiles', $column)) {
                    $columnsToDrop[] = $column;
                }
            }

            if ($columnsToDrop !== []) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
