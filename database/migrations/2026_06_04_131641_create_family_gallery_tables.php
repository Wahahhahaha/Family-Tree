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
        Schema::create('family_gallery_albums', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title', 120)->index();
            $table->string('description', 1000)->nullable();
            $table->unsignedInteger('created_by_userid')->index();
            $table->unsignedInteger('updated_by_userid')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('family_gallery_photos', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('album_id')->index();
            $table->unsignedInteger('uploader_userid')->index();
            $table->string('title');
            $table->text('caption')->nullable();
            $table->string('file_path');
            $table->string('file_name')->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->timestamp('uploaded_at')->nullable()->index();
            $table->timestamps();
            $table->index(['album_id', 'uploaded_at'], 'family_album_uploaded_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('family_gallery_photos');
        Schema::dropIfExists('family_gallery_albums');
    }
};
