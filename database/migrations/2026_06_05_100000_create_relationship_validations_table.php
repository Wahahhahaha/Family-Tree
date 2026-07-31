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
        Schema::create('relationship_validations', function (Blueprint $table) {
            $table->id();
            $table->integer('requested_by_member_id');
            $table->integer('target_member_id')->nullable();
            $table->enum('action_type', ['divorce', 'delete_child', 'delete_partner', 'delete_member']);
            $table->string('document_path')->nullable();
            $table->text('reason')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('admin_notes')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('relationship_validations');
    }
};
