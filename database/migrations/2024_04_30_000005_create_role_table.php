<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (Schema::hasTable('role')) return;
        Schema::create('role', function (Blueprint $table) {
            $table->integer('roleid')->autoIncrement();
            $table->string('rolename');
        });
    }
    public function down(): void { Schema::dropIfExists('role'); }
};
