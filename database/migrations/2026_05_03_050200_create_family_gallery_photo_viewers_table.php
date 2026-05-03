<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('family_gallery_photo_viewers', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('family_id')->index();
            $table->unsignedInteger('photo_id')->index();
            $table->unsignedInteger('user_id')->index();
            $table->timestamps();

            $table->unique(['photo_id', 'user_id'], 'family_gallery_photo_viewers_unique');
            $table->index(['family_id', 'user_id'], 'family_gallery_photo_viewers_family_user_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('family_gallery_photo_viewers');
    }
};
