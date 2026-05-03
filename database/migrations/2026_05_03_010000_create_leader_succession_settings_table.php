<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('leader_succession_settings')) {
            return;
        }

        Schema::create('leader_succession_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('owner_userid')->unique();
            $table->unsignedInteger('heir_memberid')->nullable();
            $table->string('pin_hash');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leader_succession_settings');
    }
};
