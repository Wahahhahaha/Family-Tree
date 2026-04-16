<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('relationship') && !Schema::hasColumn('relationship', 'child_parenting_mode')) {
            Schema::table('relationship', function (Blueprint $table) {
                $table->string('child_parenting_mode')->nullable()->default('with_current_partner')->after('relationtype');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('relationship') && Schema::hasColumn('relationship', 'child_parenting_mode')) {
            Schema::table('relationship', function (Blueprint $table) {
                $table->dropColumn('child_parenting_mode');
            });
        }
    }
};
