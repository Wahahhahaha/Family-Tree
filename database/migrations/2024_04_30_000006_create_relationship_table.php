<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (Schema::hasTable('relationship')) return;
        Schema::create('relationship', function (Blueprint $table) {
            $table->integer('relationid')->autoIncrement();
            $table->integer('memberid');
            $table->integer('relatedmemberid');
            $table->string('relationtype');
            $table->string('child_parenting_mode')->default('with_current_partner');
        });
    }
    public function down(): void { Schema::dropIfExists('relationship'); }
};
