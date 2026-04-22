<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'status')) {
                $table->string('status', 32)->default('active')->after('role');
            }

            if (!Schema::hasColumn('users', 'invited_by')) {
                $table->unsignedBigInteger('invited_by')->nullable()->after('status');
            }
        });

        if (Schema::hasColumn('users', 'invited_by')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreign('invited_by')
                    ->references('user_id')
                    ->on('users')
                    ->nullOnDelete();
            });
        }

        Schema::table('users', function (Blueprint $table) {
            $table->string('password')->nullable()->change();
        });

        $users = DB::table('users')->select('user_id', 'status')->get();

        foreach ($users as $user) {
            $normalizedStatus = match (strtolower((string) ($user->status ?? ''))) {
                'active' => 'active',
                'inactive', 'disabled' => 'disabled',
                'pending_invitation' => 'pending_invitation',
                default => 'active',
            };

            DB::table('users')
                ->where('user_id', $user->user_id)
                ->update(['status' => $normalizedStatus]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'invited_by')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign(['invited_by']);
                $table->dropColumn('invited_by');
            });
        }

        if (Schema::hasColumn('users', 'status')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }

        Schema::table('users', function (Blueprint $table) {
            $table->string('password')->nullable(false)->change();
        });
    }
};
