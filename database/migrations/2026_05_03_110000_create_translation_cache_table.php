<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('translation_cache')) {
            return;
        }

        Schema::create('translation_cache', function (Blueprint $table) {
            $table->id();
            $table->string('cache_key', 120);
            $table->string('source_locale', 12);
            $table->string('target_locale', 12);
            $table->string('source_hash', 64);
            $table->longText('source_text');
            $table->longText('translated_text');
            $table->timestamps();

            $table->unique(['cache_key', 'source_locale', 'target_locale', 'source_hash'], 'translation_cache_unique');
            $table->index(['cache_key', 'target_locale'], 'translation_cache_lookup_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('translation_cache');
    }
};
