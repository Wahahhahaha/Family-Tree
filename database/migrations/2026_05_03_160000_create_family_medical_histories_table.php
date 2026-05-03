<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('family_medical_histories')) {
            Schema::create('family_medical_histories', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('family_id')->default(1)->index();
                $table->unsignedBigInteger('family_member_id')->index();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('title', 255);
                $table->string('allergy_name', 255)->nullable();
                $table->date('medical_date');
                $table->string('category', 40)->index();
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('created_by_userid')->nullable()->index();
                $table->unsignedBigInteger('updated_by_userid')->nullable()->index();
                $table->timestamps();
                $table->index(['family_id', 'family_member_id', 'category'], 'family_medical_histories_scope_index');
                $table->index(['family_member_id', 'medical_date'], 'family_medical_histories_member_date_index');
                $table->index(['family_id', 'medical_date'], 'family_medical_histories_family_date_index');
            });
        }

        DB::statement(<<<'SQL'
CREATE TABLE IF NOT EXISTS `family_medical_histories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `family_id` bigint unsigned NOT NULL DEFAULT 1,
  `family_member_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `allergy_name` varchar(255) DEFAULT NULL,
  `medical_date` date NOT NULL,
  `category` varchar(40) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_by_userid` bigint unsigned DEFAULT NULL,
  `updated_by_userid` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `family_medical_histories_family_id_index` (`family_id`),
  KEY `family_medical_histories_family_member_id_index` (`family_member_id`),
  KEY `family_medical_histories_user_id_index` (`user_id`),
  KEY `family_medical_histories_category_index` (`category`),
  KEY `family_medical_histories_medical_date_index` (`medical_date`),
  KEY `family_medical_histories_created_by_userid_index` (`created_by_userid`),
  KEY `family_medical_histories_updated_by_userid_index` (`updated_by_userid`),
  KEY `family_medical_histories_scope_index` (`family_id`, `family_member_id`, `category`),
  KEY `family_medical_histories_member_date_index` (`family_member_id`, `medical_date`),
  KEY `family_medical_histories_family_date_index` (`family_id`, `medical_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    }

    public function down(): void
    {
        if (Schema::hasTable('family_medical_histories')) {
            Schema::dropIfExists('family_medical_histories');
        } else {
            DB::statement('DROP TABLE IF EXISTS `family_medical_histories`');
        }
    }
};
