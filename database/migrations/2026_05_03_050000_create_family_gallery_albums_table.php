<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('family_gallery_albums', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('family_id')->index();
            $table->string('title', 120);
            $table->string('description', 1000)->nullable();
            $table->unsignedInteger('created_by_userid')->index();
            $table->unsignedInteger('updated_by_userid')->nullable()->index();
            $table->timestamps();

            $table->index(['family_id', 'title'], 'family_gallery_albums_family_title_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('family_gallery_albums');
    }
};
