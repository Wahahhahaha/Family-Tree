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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable()->unique();
            $table->text('description')->nullable();
            $table->datetime('event_date');
            $table->string('location')->nullable();
            $table->string('status')->default('planning');
            $table->integer('created_by');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('event_responses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->integer('member_id');
            $table->enum('status', ['going', 'not_going', 'maybe']);
            $table->timestamps();
            $table->unique(['event_id', 'member_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_responses');
        Schema::dropIfExists('events');
    }
};
