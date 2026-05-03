<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('family_member')) return;

        Schema::create('family_member', function (Blueprint $table) {
            $table->integer('memberid')->autoIncrement();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('pending_email')->nullable();
            $table->string('email_verification_token')->nullable();
            $table->timestamp('email_verification_token_expires_at')->nullable();
            $table->string('phonenumber')->nullable();
            $table->string('pending_phonenumber')->nullable();
            $table->string('phone_verification_otp_hash')->nullable();
            $table->timestamp('phone_verification_otp_expires_at')->nullable();
            $table->string('gender');
            $table->date('birthdate');
            $table->string('birthplace');
            $table->string('address');
            $table->string('bloodtype', 11)->nullable();
            $table->string('job')->nullable();
            $table->string('education_status')->nullable();
            $table->string('life_status');
            $table->string('marital_status');
            $table->date('deaddate')->nullable();
            $table->string('picture')->nullable();
            $table->integer('userid');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('family_member');
    }
};
