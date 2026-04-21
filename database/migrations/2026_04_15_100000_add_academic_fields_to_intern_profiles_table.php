<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('intern_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('intern_profiles', 'jenjang')) {
                $table->string('jenjang')->nullable()->after('jurusan');
            }

            if (!Schema::hasColumn('intern_profiles', 'ipk')) {
                $table->decimal('ipk', 3, 2)->nullable()->after('jenjang');
            }

            if (!Schema::hasColumn('intern_profiles', 'tahun_masuk')) {
                $table->string('tahun_masuk')->nullable()->after('ipk');
            }

            if (!Schema::hasColumn('intern_profiles', 'tahun_lulus')) {
                $table->string('tahun_lulus')->nullable()->after('tahun_masuk');
            }
        });
    }

    public function down(): void
    {
        Schema::table('intern_profiles', function (Blueprint $table) {
            $columnsToDrop = [];

            foreach (['jenjang', 'ipk', 'tahun_masuk', 'tahun_lulus'] as $column) {
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
