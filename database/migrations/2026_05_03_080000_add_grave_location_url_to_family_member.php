<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        try {
            if (Schema::hasTable('family_member') && !Schema::hasColumn('family_member', 'grave_location_url')) {
                Schema::table('family_member', function (Blueprint $table) {
                    $table->text('grave_location_url')->nullable()->after('deaddate');
                });
            }
        } catch (\Throwable $e) {
            try {
                DB::statement('ALTER TABLE `family_member` ADD COLUMN `grave_location_url` TEXT NULL AFTER `deaddate`');
            } catch (\Throwable $inner) {
                // Fallback intentionally silent to avoid blocking deployment.
            }
        }
    }

    public function down(): void
    {
        try {
            if (Schema::hasTable('family_member') && Schema::hasColumn('family_member', 'grave_location_url')) {
                Schema::table('family_member', function (Blueprint $table) {
                    $table->dropColumn('grave_location_url');
                });
            }
        } catch (\Throwable $e) {
            try {
                DB::statement('ALTER TABLE `family_member` DROP COLUMN `grave_location_url`');
            } catch (\Throwable $inner) {
                // Fallback intentionally silent to avoid blocking rollback in constrained environments.
            }
        }
    }
};
