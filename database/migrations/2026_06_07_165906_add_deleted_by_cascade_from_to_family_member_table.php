<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('family_member', function (Blueprint $table) {
            $table->unsignedInteger('deleted_by_cascade_from')->nullable()->after('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('family_member', function (Blueprint $table) {
            $table->dropColumn('deleted_by_cascade_from');
        });
    }
};
