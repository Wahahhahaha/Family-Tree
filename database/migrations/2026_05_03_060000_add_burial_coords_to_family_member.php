<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::table('family_member', function (Blueprint $table) {
            $table->decimal('burial_latitude', 10, 8)->nullable();
            $table->decimal('burial_longitude', 11, 8)->nullable();
        });
    }
    public function down() {
        Schema::table('family_member', function (Blueprint $table) {
            $table->dropColumn(['burial_latitude', 'burial_longitude']);
        });
    }
};
