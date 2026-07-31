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
        Schema::table('activity_log', function (Blueprint $table) {
            $table->integer('user_id')->nullable()->change();
            $table->longText('context')->nullable()->change();
            $table->string('ip_adress')->nullable()->change();
            $table->string('user_agent')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activity_log', function (Blueprint $table) {
            $table->integer('user_id')->nullable(false)->change();
            $table->longText('context')->nullable(false)->change();
            $table->string('ip_adress')->nullable(false)->change();
            $table->string('user_agent')->nullable(false)->change();
        });
    }
};
