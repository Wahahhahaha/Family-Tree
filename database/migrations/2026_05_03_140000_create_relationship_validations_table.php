<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('relationship_validations')) {
            return;
        }

        try {
            Schema::create('relationship_validations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('family_id')->default(1)->index();
                $table->unsignedBigInteger('requested_by')->index();
                $table->unsignedBigInteger('requested_by_member_id')->nullable()->index();
                $table->enum('action_type', ['divorce', 'delete_child', 'delete_partner'])->index();
                $table->unsignedBigInteger('target_member_id')->nullable()->index();
                $table->unsignedBigInteger('target_user_id')->nullable()->index();
                $table->unsignedBigInteger('partner_id')->nullable()->index();
                $table->unsignedBigInteger('child_id')->nullable()->index();
                $table->string('document_path', 255);
                $table->text('reason');
                $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->index();
                $table->text('admin_notes')->nullable();
                $table->unsignedBigInteger('verified_by')->nullable()->index();
                $table->timestamp('verified_at')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->timestamp('rejected_at')->nullable();
                $table->timestamps();

                $table->index(['family_id', 'status', 'created_at'], 'relationship_validations_family_status_created_index');
                $table->index(['family_id', 'action_type', 'status'], 'relationship_validations_family_action_status_index');
            });
        } catch (\Throwable $e) {
            DB::statement(<<<'SQL'
CREATE TABLE `relationship_validations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `family_id` bigint unsigned NOT NULL DEFAULT 1,
  `requested_by` bigint unsigned NOT NULL,
  `requested_by_member_id` bigint unsigned DEFAULT NULL,
  `action_type` enum('divorce','delete_child','delete_partner') NOT NULL,
  `target_member_id` bigint unsigned DEFAULT NULL,
  `target_user_id` bigint unsigned DEFAULT NULL,
  `partner_id` bigint unsigned DEFAULT NULL,
  `child_id` bigint unsigned DEFAULT NULL,
  `document_path` varchar(255) NOT NULL,
  `reason` text NOT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `admin_notes` text DEFAULT NULL,
  `verified_by` bigint unsigned DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `rejected_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `relationship_validations_family_id_index` (`family_id`),
  KEY `relationship_validations_requested_by_index` (`requested_by`),
  KEY `relationship_validations_requested_by_member_id_index` (`requested_by_member_id`),
  KEY `relationship_validations_action_type_index` (`action_type`),
  KEY `relationship_validations_target_member_id_index` (`target_member_id`),
  KEY `relationship_validations_target_user_id_index` (`target_user_id`),
  KEY `relationship_validations_partner_id_index` (`partner_id`),
  KEY `relationship_validations_child_id_index` (`child_id`),
  KEY `relationship_validations_status_index` (`status`),
  KEY `relationship_validations_verified_by_index` (`verified_by`),
  KEY `relationship_validations_family_status_created_index` (`family_id`, `status`, `created_at`),
  KEY `relationship_validations_family_action_status_index` (`family_id`, `action_type`, `status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('relationship_validations')) {
            return;
        }

        try {
            Schema::dropIfExists('relationship_validations');
        } catch (\Throwable $e) {
            DB::statement('DROP TABLE IF EXISTS `relationship_validations`');
        }
    }
};
