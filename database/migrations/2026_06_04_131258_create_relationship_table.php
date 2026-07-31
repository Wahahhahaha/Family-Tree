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
        Schema::create('relationship', function (Blueprint $table) {
            $table->integer('relationid')->autoIncrement();
            $table->integer('memberid');
            $table->integer('relatedmemberid');
            $table->string('relationtype');
            $table->string('child_parenting_mode')->nullable();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('relationship');
    }
};
