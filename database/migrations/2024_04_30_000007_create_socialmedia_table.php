<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (Schema::hasTable('socialmedia')) return;
        Schema::create('socialmedia', function (Blueprint $table) {
            $table->integer('socialid')->autoIncrement();
            $table->string('socialname');
            $table->string('socialicon', 100)->nullable();
        });
    }
    public function down(): void { Schema::dropIfExists('socialmedia'); }
};
