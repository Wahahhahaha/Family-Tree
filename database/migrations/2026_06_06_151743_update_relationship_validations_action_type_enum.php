<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE relationship_validations MODIFY COLUMN action_type ENUM('divorce', 'delete_child', 'delete_partner', 'delete_member')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE relationship_validations MODIFY COLUMN action_type ENUM('divorce', 'delete_child', 'delete_partner')");
    }
};
