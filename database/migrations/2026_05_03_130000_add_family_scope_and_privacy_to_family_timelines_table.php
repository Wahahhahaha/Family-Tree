<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('family_timelines')) {
            Schema::table('family_timelines', function (Blueprint $table) {
                if (!Schema::hasColumn('family_timelines', 'family_id')) {
                    $table->unsignedBigInteger('family_id')->default(1)->after('id')->index();
                }

                if (!Schema::hasColumn('family_timelines', 'visibility')) {
                    $table->string('visibility', 30)->default('public_family')->after('category')->index();
                }
            });

            if (Schema::hasColumn('family_timelines', 'family_id')) {
                DB::table('family_timelines')
                    ->whereNull('family_id')
                    ->orWhere('family_id', 0)
                    ->update(['family_id' => 1]);
            }

            if (Schema::hasColumn('family_timelines', 'visibility')) {
                DB::table('family_timelines')
                    ->whereNull('visibility')
                    ->orWhere('visibility', '')
                    ->update(['visibility' => 'public_family']);
            }
        }

        if (!Schema::hasTable('family_timeline_viewers')) {
            Schema::create('family_timeline_viewers', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('timeline_id')->index();
                $table->unsignedBigInteger('userid')->index();
                $table->timestamps();
                $table->unique(['timeline_id', 'userid'], 'family_timeline_viewers_unique');
                $table->index(['userid', 'timeline_id'], 'family_timeline_viewers_user_index');
            });
        }

        try {
            DB::statement("CREATE TABLE IF NOT EXISTS `family_timeline_viewers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `timeline_id` bigint unsigned NOT NULL,
  `userid` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `family_timeline_viewers_unique` (`timeline_id`,`userid`),
  KEY `family_timeline_viewers_user_index` (`userid`,`timeline_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        } catch (\Throwable $e) {
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('family_timeline_viewers')) {
            Schema::drop('family_timeline_viewers');
        }

        try {
            DB::statement('DROP TABLE IF EXISTS `family_timeline_viewers`');
        } catch (\Throwable $e) {
        }

        if (Schema::hasTable('family_timelines')) {
            Schema::table('family_timelines', function (Blueprint $table) {
                if (Schema::hasColumn('family_timelines', 'visibility')) {
                    $table->dropColumn('visibility');
                }

                if (Schema::hasColumn('family_timelines', 'family_id')) {
                    $table->dropColumn('family_id');
                }
            });
        }

        try {
            DB::statement('ALTER TABLE `family_timelines` DROP COLUMN `visibility`');
        } catch (\Throwable $e) {
        }

        try {
            DB::statement('ALTER TABLE `family_timelines` DROP COLUMN `family_id`');
        } catch (\Throwable $e) {
        }
    }
};
