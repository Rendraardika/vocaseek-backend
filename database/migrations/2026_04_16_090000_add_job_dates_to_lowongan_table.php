<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function resolveTableName(): ?string
    {
        if (Schema::hasTable('lowongan')) {
            return 'lowongan';
        }

        if (Schema::hasTable('lowongans')) {
            return 'lowongans';
        }

        return null;
    }

    public function up(): void
    {
        $tableName = $this->resolveTableName();

        if (! $tableName) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            if (! Schema::hasColumn($tableName, 'tanggal_penutupan_lamaran')) {
                $table->date('tanggal_penutupan_lamaran')->nullable()->after('gaji_per_bulan');
            }

            if (! Schema::hasColumn($tableName, 'tanggal_mulai_kerja')) {
                $table->date('tanggal_mulai_kerja')->nullable()->after('tanggal_penutupan_lamaran');
            }
        });
    }

    public function down(): void
    {
        $tableName = $this->resolveTableName();

        if (! $tableName) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            if (Schema::hasColumn($tableName, 'tanggal_mulai_kerja')) {
                $table->dropColumn('tanggal_mulai_kerja');
            }

            if (Schema::hasColumn($tableName, 'tanggal_penutupan_lamaran')) {
                $table->dropColumn('tanggal_penutupan_lamaran');
            }
        });
    }
};
