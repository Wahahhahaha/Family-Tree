<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('family_gallery_photos', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('family_id')->index();
            $table->unsignedInteger('album_id')->index();
            $table->unsignedInteger('uploader_userid')->index();
            $table->string('title', 255);
            $table->text('caption')->nullable();
            $table->string('privacy_status', 30)->default('public_family')->index();
            $table->string('file_path', 255);
            $table->string('file_name', 255)->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->timestamp('uploaded_at')->nullable()->index();
            $table->timestamps();

            $table->index(['family_id', 'album_id', 'uploaded_at'], 'family_gallery_photos_family_album_uploaded_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('family_gallery_photos');
    }
};
