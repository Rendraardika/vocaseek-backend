<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $companyProfileTable = $this->ensureCompanyProfilesTable();
        $tableName = $this->ensureLowongansTable();

        if (! $companyProfileTable || ! $tableName) {
            return;
        }

        $this->ensureCompanyProfileCompatibilityColumns($companyProfileTable);
        $this->ensureCompatibilityColumns($tableName);

        if (in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true) && Schema::hasColumn($tableName, 'status')) {
            DB::statement("ALTER TABLE {$tableName} MODIFY status ENUM('ACTIVE','OPEN','CLOSED','DRAFT') NOT NULL DEFAULT 'DRAFT'");
        }

        $this->replaceCompanyProfileForeignKey(true);
    }

    public function down(): void
    {
        if (! Schema::hasTable('lowongans')) {
            return;
        }

        $this->replaceCompanyProfileForeignKey(false);
    }

    private function ensureLowongansTable(): ?string
    {
        if (Schema::hasTable('lowongans')) {
            return 'lowongans';
        }

        if (Schema::hasTable('lowongan')) {
            Schema::rename('lowongan', 'lowongans');

            return 'lowongans';
        }

        return null;
    }

    private function ensureCompanyProfilesTable(): ?string
    {
        if (Schema::hasTable('company_profiles')) {
            return 'company_profiles';
        }

        if (Schema::hasTable('company_profile')) {
            Schema::rename('company_profile', 'company_profiles');

            return 'company_profiles';
        }

        return null;
    }

    private function ensureCompanyProfileCompatibilityColumns(string $tableName): void
    {
        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            if (! Schema::hasColumn($tableName, 'industri')) {
                $table->string('industri')->nullable()->after('nama_perusahaan');
            }

            if (! Schema::hasColumn($tableName, 'ukuran_perusahaan')) {
                $table->string('ukuran_perusahaan')->nullable()->after('industri');
            }

            if (! Schema::hasColumn($tableName, 'website_url')) {
                $table->string('website_url')->nullable()->after('ukuran_perusahaan');
            }

            if (! Schema::hasColumn($tableName, 'deskripsi')) {
                $table->text('deskripsi')->nullable()->after('website_url');
            }

            if (! Schema::hasColumn($tableName, 'alamat_kantor_pusat')) {
                $table->text('alamat_kantor_pusat')->nullable()->after('notelp');
            }

            if (! Schema::hasColumn($tableName, 'logo_perusahaan')) {
                $table->string('logo_perusahaan')->nullable()->after('akta_pdf');
            }

            if (! Schema::hasColumn($tableName, 'banner_perusahaan')) {
                $table->string('banner_perusahaan')->nullable()->after('logo_perusahaan');
            }

            if (! Schema::hasColumn($tableName, 'visi')) {
                $table->text('visi')->nullable()->after('status_mitra');
            }

            if (! Schema::hasColumn($tableName, 'misi')) {
                $table->text('misi')->nullable()->after('visi');
            }

            if (! Schema::hasColumn($tableName, 'linkedin_url')) {
                $table->string('linkedin_url')->nullable()->after('misi');
            }

            if (! Schema::hasColumn($tableName, 'instagram_url')) {
                $table->string('instagram_url')->nullable()->after('linkedin_url');
            }

            if (! Schema::hasColumn($tableName, 'twitter_url')) {
                $table->string('twitter_url')->nullable()->after('instagram_url');
            }
        });
    }

    private function ensureCompatibilityColumns(string $tableName): void
    {
        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            if (! Schema::hasColumn($tableName, 'judul_posisi')) {
                $table->string('judul_posisi')->nullable()->after('company_profile_id');
            }

            if (! Schema::hasColumn($tableName, 'tipe_magang')) {
                $table->string('tipe_magang')->nullable()->after('lokasi');
            }

            if (! Schema::hasColumn($tableName, 'gaji_per_bulan')) {
                $table->string('gaji_per_bulan')->nullable()->after('pengaturan_kerja');
            }
        });
    }

    private function replaceCompanyProfileForeignKey(bool $cascade): void
    {
        $foreignKey = $this->findForeignKeyName('lowongans', 'company_profile_id');

        if ($foreignKey) {
            DB::statement("ALTER TABLE lowongans DROP FOREIGN KEY `{$foreignKey}`");
        }

        Schema::table('lowongans', function (Blueprint $table) use ($cascade) {
            $foreign = $table->foreign('company_profile_id')
                ->references('id')
                ->on('company_profiles');

            if ($cascade) {
                $foreign->cascadeOnDelete();
            }
        });
    }

    private function findForeignKeyName(string $tableName, string $columnName): ?string
    {
        $result = DB::selectOne(<<<'SQL'
            SELECT CONSTRAINT_NAME AS constraint_name
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = ?
              AND COLUMN_NAME = ?
              AND REFERENCED_TABLE_NAME IS NOT NULL
            LIMIT 1
        SQL, [$tableName, $columnName]);

        return $result?->constraint_name;
    }
};
