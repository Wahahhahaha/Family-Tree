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
        Schema::create('system', function (Blueprint $table) {
            $table->integer('systemid')->autoIncrement();
            $table->string('systemname')->nullable();
            $table->string('systemlogo')->nullable();
            $table->string('systemcontact')->nullable();
            $table->string('systemmanager')->nullable();
            $table->string('systemaddress')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system');
    }
};
