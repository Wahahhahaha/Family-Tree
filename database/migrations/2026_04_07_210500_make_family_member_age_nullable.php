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
        if (!Schema::hasTable('family_member') || !Schema::hasColumn('family_member', 'age')) {
            return;
        }

        DB::statement('ALTER TABLE family_member MODIFY age INT(11) NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('family_member') || !Schema::hasColumn('family_member', 'age')) {
            return;
        }

        DB::table('family_member')->whereNull('age')->update(['age' => 0]);
        DB::statement('ALTER TABLE family_member MODIFY age INT(11) NOT NULL');
    }
};
