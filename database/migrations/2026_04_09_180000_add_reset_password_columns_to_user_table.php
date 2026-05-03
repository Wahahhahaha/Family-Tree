<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('user')) {
            return;
        }

        Schema::table('user', function (Blueprint $table) {
            if (!Schema::hasColumn('user', 'reset_password_token')) {
                $table->string('reset_password_token')->nullable()->after('levelid');
            }

            if (!Schema::hasColumn('user', 'reset_password_token_expired')) {
                $table->dateTime('reset_password_token_expired')->nullable()->after('reset_password_token');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('user')) {
            return;
        }

        Schema::table('user', function (Blueprint $table) {
            if (Schema::hasColumn('user', 'reset_password_token_expired')) {
                $table->dropColumn('reset_password_token_expired');
            }

            if (Schema::hasColumn('user', 'reset_password_token')) {
                $table->dropColumn('reset_password_token');
            }
        });
    }
};

