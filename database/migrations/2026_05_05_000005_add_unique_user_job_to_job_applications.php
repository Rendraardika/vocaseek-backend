<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement(<<<'SQL'
            DELETE ja1 FROM job_applications ja1
            INNER JOIN job_applications ja2
                ON ja1.user_id = ja2.user_id
                AND ja1.job_id = ja2.job_id
                AND ja1.application_id > ja2.application_id
        SQL);

        Schema::table('job_applications', function (Blueprint $table) {
            $table->unique(['user_id', 'job_id'], 'job_applications_user_id_job_id_unique');
        });
    }

    public function down(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->dropUnique('job_applications_user_id_job_id_unique');
        });
    }
};
