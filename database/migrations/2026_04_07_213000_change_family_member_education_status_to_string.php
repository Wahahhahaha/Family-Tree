<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('family_member') || !Schema::hasColumn('family_member', 'education_status')) {
            return;
        }

        DB::statement('ALTER TABLE family_member MODIFY education_status VARCHAR(255) NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('family_member') || !Schema::hasColumn('family_member', 'education_status')) {
            return;
        }

        DB::table('family_member')->update(['education_status' => null]);
        DB::statement('ALTER TABLE family_member MODIFY education_status INT(11) NULL');
    }
};
