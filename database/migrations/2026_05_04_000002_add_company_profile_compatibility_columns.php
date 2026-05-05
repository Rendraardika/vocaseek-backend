<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('company_profiles')) {
            return;
        }

        Schema::table('company_profiles', function (Blueprint $table) {
            if (! Schema::hasColumn('company_profiles', 'industri')) {
                $table->string('industri')->nullable()->after('nama_perusahaan');
            }

            if (! Schema::hasColumn('company_profiles', 'ukuran_perusahaan')) {
                $table->string('ukuran_perusahaan')->nullable()->after('industri');
            }

            if (! Schema::hasColumn('company_profiles', 'website_url')) {
                $table->string('website_url')->nullable()->after('ukuran_perusahaan');
            }

            if (! Schema::hasColumn('company_profiles', 'deskripsi')) {
                $table->text('deskripsi')->nullable()->after('website_url');
            }

            if (! Schema::hasColumn('company_profiles', 'alamat_kantor_pusat')) {
                $table->text('alamat_kantor_pusat')->nullable()->after('notelp');
            }

            if (! Schema::hasColumn('company_profiles', 'logo_perusahaan')) {
                $table->string('logo_perusahaan')->nullable()->after('akta_pdf');
            }

            if (! Schema::hasColumn('company_profiles', 'banner_perusahaan')) {
                $table->string('banner_perusahaan')->nullable()->after('logo_perusahaan');
            }

            if (! Schema::hasColumn('company_profiles', 'linkedin_url')) {
                $table->string('linkedin_url')->nullable()->after('status_mitra');
            }

            if (! Schema::hasColumn('company_profiles', 'instagram_url')) {
                $table->string('instagram_url')->nullable()->after('linkedin_url');
            }

            if (! Schema::hasColumn('company_profiles', 'twitter_url')) {
                $table->string('twitter_url')->nullable()->after('instagram_url');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('company_profiles')) {
            return;
        }

        Schema::table('company_profiles', function (Blueprint $table) {
            $columns = [
                'twitter_url',
                'instagram_url',
                'linkedin_url',
                'banner_perusahaan',
                'logo_perusahaan',
                'alamat_kantor_pusat',
                'deskripsi',
                'website_url',
                'ukuran_perusahaan',
                'industri',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('company_profiles', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
