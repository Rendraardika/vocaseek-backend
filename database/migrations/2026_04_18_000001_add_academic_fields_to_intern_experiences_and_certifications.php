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
        Schema::table('intern_experiences', function (Blueprint $table) {
            if (!Schema::hasColumn('intern_experiences', 'type')) {
                $table->string('type')->nullable()->after('title');
            }

            if (!Schema::hasColumn('intern_experiences', 'start_date')) {
                $table->date('start_date')->nullable()->after('company');
            }

            if (!Schema::hasColumn('intern_experiences', 'end_date')) {
                $table->date('end_date')->nullable()->after('start_date');
            }
        });

        Schema::table('intern_certifications', function (Blueprint $table) {
            if (!Schema::hasColumn('intern_certifications', 'issuer')) {
                $table->string('issuer')->nullable()->after('name');
            }

            if (!Schema::hasColumn('intern_certifications', 'issue_date')) {
                $table->date('issue_date')->nullable()->after('issuer');
            }

            if (!Schema::hasColumn('intern_certifications', 'certificate_number')) {
                $table->string('certificate_number')->nullable()->after('issue_date');
            }

            if (!Schema::hasColumn('intern_certifications', 'description')) {
                $table->text('description')->nullable()->after('certificate_number');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('intern_experiences', function (Blueprint $table) {
            $columns = ['type', 'start_date', 'end_date'];

            foreach ($columns as $column) {
                if (Schema::hasColumn('intern_experiences', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('intern_certifications', function (Blueprint $table) {
            $columns = ['issuer', 'issue_date', 'certificate_number', 'description'];

            foreach ($columns as $column) {
                if (Schema::hasColumn('intern_certifications', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
