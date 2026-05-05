<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }

        if (Schema::hasTable('lowongans')) {
            DB::statement("ALTER TABLE lowongans MODIFY status ENUM('ACTIVE','OPEN','CLOSED','DRAFT') NOT NULL DEFAULT 'DRAFT'");
        }

        if (Schema::hasTable('job_applications')) {
            DB::statement("ALTER TABLE job_applications MODIFY status ENUM('PENDING','REVIEW','INTERVIEW','SHORTLISTED','ACCEPTED','REJECTED') NOT NULL DEFAULT 'PENDING'");
        }
    }

    public function down(): void
    {
        if (! in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }

        if (Schema::hasTable('job_applications')) {
            DB::statement("ALTER TABLE job_applications MODIFY status ENUM('PENDING','SHORTLISTED','REJECTED') NOT NULL DEFAULT 'PENDING'");
        }

        if (Schema::hasTable('lowongans')) {
            DB::statement("ALTER TABLE lowongans MODIFY status ENUM('OPEN','CLOSED','DRAFT') NOT NULL DEFAULT 'DRAFT'");
        }
    }
};
