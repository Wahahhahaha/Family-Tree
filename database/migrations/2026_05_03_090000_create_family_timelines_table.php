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
            if (!Schema::hasTable('family_timelines')) {
                Schema::create('family_timelines', function (Blueprint $table) {
                    $table->bigIncrements('id');
                    $table->unsignedBigInteger('family_id')->index();
                    $table->unsignedInteger('family_member_id')->nullable()->index();
                    $table->unsignedBigInteger('user_id')->nullable()->index();
                    $table->string('title', 255);
                    $table->text('description')->nullable();
                    $table->date('event_date')->nullable()->index();
                    $table->unsignedSmallInteger('event_year')->nullable()->index();
                    $table->string('category', 30)->index();
                    $table->string('location', 255)->nullable();
                    $table->string('attachment_path', 255)->nullable();
                    $table->unsignedBigInteger('created_by_userid')->nullable()->index();
                    $table->unsignedBigInteger('updated_by_userid')->nullable()->index();
                    $table->timestamps();

                    $table->index(['family_id', 'family_member_id', 'category'], 'family_timelines_scope_index');
                    $table->index(['family_id', 'event_date', 'event_year'], 'family_timelines_date_index');
                });
            }
        } catch (\Throwable $e) {
            try {
                DB::statement(<<<'SQL'
CREATE TABLE IF NOT EXISTS `family_timelines` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `family_id` bigint unsigned NOT NULL,
  `family_member_id` int unsigned DEFAULT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `event_date` date DEFAULT NULL,
  `event_year` smallint unsigned DEFAULT NULL,
  `category` varchar(30) NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `attachment_path` varchar(255) DEFAULT NULL,
  `created_by_userid` bigint unsigned DEFAULT NULL,
  `updated_by_userid` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `family_timelines_family_id_index` (`family_id`),
  KEY `family_timelines_family_member_id_index` (`family_member_id`),
  KEY `family_timelines_user_id_index` (`user_id`),
  KEY `family_timelines_event_date_index` (`event_date`),
  KEY `family_timelines_event_year_index` (`event_year`),
  KEY `family_timelines_category_index` (`category`),
  KEY `family_timelines_created_by_userid_index` (`created_by_userid`),
  KEY `family_timelines_updated_by_userid_index` (`updated_by_userid`),
  KEY `family_timelines_scope_index` (`family_id`, `family_member_id`, `category`),
  KEY `family_timelines_date_index` (`family_id`, `event_date`, `event_year`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL
                );
            } catch (\Throwable $inner) {
                // Fallback intentionally silent to avoid blocking deployment.
            }
        }
    }

    public function down(): void
    {
        try {
            if (Schema::hasTable('family_timelines')) {
                Schema::dropIfExists('family_timelines');
            }
        } catch (\Throwable $e) {
            try {
                DB::statement('DROP TABLE IF EXISTS `family_timelines`');
            } catch (\Throwable $inner) {
                // Fallback intentionally silent to avoid blocking rollback in constrained environments.
            }
        }
    }
};
