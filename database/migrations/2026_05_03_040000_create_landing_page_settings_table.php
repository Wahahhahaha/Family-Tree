<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('landing_page_settings')) {
            return;
        }

        Schema::create('landing_page_settings', function (Blueprint $table) {
            $table->id();
            $table->string('family_name', 255)->nullable();
            $table->text('description')->nullable();
            $table->string('head_of_family_name', 255)->nullable();
            $table->text('head_of_family_message')->nullable();
            $table->string('head_of_family_photo', 255)->nullable();
            $table->string('created_by_name', 255)->nullable();
            $table->string('created_by_photo', 255)->nullable();
            $table->string('designed_by_name', 255)->nullable();
            $table->string('designed_by_photo', 255)->nullable();
            $table->string('approved_by_name', 255)->nullable();
            $table->string('approved_by_photo', 255)->nullable();
            $table->string('acknowledged_by_name', 255)->nullable();
            $table->string('acknowledged_by_photo', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('landing_page_settings');
    }
};
