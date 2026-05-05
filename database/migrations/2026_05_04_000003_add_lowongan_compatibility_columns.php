<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('lowongans')) {
            return;
        }

        Schema::table('lowongans', function (Blueprint $table) {
            if (! Schema::hasColumn('lowongans', 'judul_posisi')) {
                $table->string('judul_posisi')->nullable()->after('company_profile_id');
            }

            if (! Schema::hasColumn('lowongans', 'tipe_magang')) {
                $table->string('tipe_magang')->nullable()->after('lokasi');
            }

            if (! Schema::hasColumn('lowongans', 'gaji_per_bulan')) {
                $table->string('gaji_per_bulan')->nullable()->after('pengaturan_kerja');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('lowongans')) {
            return;
        }

        Schema::table('lowongans', function (Blueprint $table) {
            foreach (['gaji_per_bulan', 'tipe_magang', 'judul_posisi'] as $column) {
                if (Schema::hasColumn('lowongans', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
