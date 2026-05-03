<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('letters')) {
            return;
        }

        Schema::table('letters', function (Blueprint $table) {
            if (!Schema::hasColumn('letters', 'unlock_type')) {
                $table->string('unlock_type', 20)->default('immediate')->after('content');
            }

            if (!Schema::hasColumn('letters', 'unlock_value')) {
                $table->unsignedInteger('unlock_value')->nullable()->after('unlock_type');
            }

            if (!Schema::hasColumn('letters', 'unlock_at')) {
                $table->timestamp('unlock_at')->nullable()->after('unlock_value');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('letters')) {
            return;
        }

        Schema::table('letters', function (Blueprint $table) {
            if (Schema::hasColumn('letters', 'unlock_at')) {
                $table->dropColumn('unlock_at');
            }

            if (Schema::hasColumn('letters', 'unlock_value')) {
                $table->dropColumn('unlock_value');
            }

            if (Schema::hasColumn('letters', 'unlock_type')) {
                $table->dropColumn('unlock_type');
            }
        });
    }
};
