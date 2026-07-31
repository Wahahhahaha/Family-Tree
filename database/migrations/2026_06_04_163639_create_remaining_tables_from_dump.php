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
        Schema::create('employer', function (Blueprint $table) {
            $table->integer('employerid')->autoIncrement();
            $table->string('name');
            $table->string('email');
            $table->string('pending_email')->nullable();
            $table->string('email_verification_token')->nullable();
            $table->timestamp('email_verification_token_expires_at')->nullable();
            $table->string('phonenumber');
            $table->string('pending_phonenumber')->nullable();
            $table->string('phone_verification_otp_hash')->nullable();
            $table->timestamp('phone_verification_otp_expires_at')->nullable();
            $table->integer('roleid');
            $table->integer('userid');
            $table->softDeletes();
            $table->timestamp('created_at')->nullable()->useCurrent();
        });

        Schema::create('level', function (Blueprint $table) {
            $table->integer('levelid')->autoIncrement();
            $table->string('levelname');
        });

        Schema::create('role', function (Blueprint $table) {
            $table->integer('roleid')->autoIncrement();
            $table->string('rolename');
        });

        Schema::create('live_locations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('userid')->unique();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->decimal('accuracy', 8, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('activity_log', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->string('action');
            $table->integer('user_id');
            $table->longText('context'); // Note: The dump has a JSON check, but longText is enough for Laravel schema representation
            $table->string('ip_adress');
            $table->string('user_agent');
            $table->timestamps();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
        });

        Schema::create('landing_page_settings', function (Blueprint $table) {
            $table->id();
            $table->string('family_name')->nullable();
            $table->text('description')->nullable();
            $table->string('head_of_family_name')->nullable();
            $table->text('head_of_family_message')->nullable();
            $table->string('head_of_family_photo')->nullable();
            $table->string('created_by_name')->nullable();
            $table->string('created_by_photo')->nullable();
            $table->string('designed_by_name')->nullable();
            $table->string('designed_by_photo')->nullable();
            $table->string('approved_by_name')->nullable();
            $table->string('approved_by_photo')->nullable();
            $table->string('acknowledged_by_name')->nullable();
            $table->string('acknowledged_by_photo')->nullable();
            $table->timestamps();
        });

        Schema::create('leader_succession_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('owner_userid')->unique();
            $table->unsignedInteger('heir_memberid')->nullable()->index();
            $table->string('pin_hash')->nullable();
            $table->timestamps();
        });

        Schema::create('leader_succession_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('owner_userid')->index();
            $table->unsignedInteger('leader_memberid')->nullable()->index();
            $table->string('leader_name', 191)->nullable();
            $table->string('source', 50)->default('manual');
            $table->unsignedBigInteger('changed_by_userid')->nullable();
            $table->timestamp('changed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('socialmedia', function (Blueprint $table) {
            $table->integer('socialid')->autoIncrement();
            $table->string('socialname')->nullable()->unique();
            $table->string('prefix')->nullable();
            $table->string('socialicon', 100)->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unsignedBigInteger('deleted_by_userid')->nullable();
        });

        Schema::create('ownsocial', function (Blueprint $table) {
            $table->integer('ownid')->autoIncrement();
            $table->integer('socialid');
            $table->integer('memberid');
            $table->string('link');
        });

        Schema::create('member_articles', function (Blueprint $table) {
            $table->integer('member_id');
            $table->text('biography')->nullable();
            $table->timestamps();
        });

        Schema::create('family_timelines', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('family_member_id')->nullable()->index();
            $table->string('title');
            $table->date('event_date')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('family_medical_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('family_member_id')->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('title');
            $table->date('medical_date')->index();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by_userid')->nullable()->index();
            $table->unsignedBigInteger('updated_by_userid')->nullable()->index();
            $table->timestamps();
            $table->index(['family_member_id', 'medical_date'], 'member_date_index');
        });

        Schema::create('letters', function (Blueprint $table) {
            $table->id();
            $table->integer('sender_id');
            $table->integer('receiver_id');
            $table->string('subject');
            $table->text('content');
            $table->string('unlock_type', 20)->default('immediate');
            $table->unsignedInteger('unlock_value')->nullable();
            $table->timestamp('unlock_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

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
        Schema::dropIfExists('letters');
        Schema::dropIfExists('family_medical_histories');
        Schema::dropIfExists('family_timelines');
        Schema::dropIfExists('member_articles');
        Schema::dropIfExists('ownsocial');
        Schema::dropIfExists('socialmedia');
        Schema::dropIfExists('leader_succession_histories');
        Schema::dropIfExists('leader_succession_settings');
        Schema::dropIfExists('landing_page_settings');
        Schema::dropIfExists('activity_log');
        Schema::dropIfExists('live_locations');
        Schema::dropIfExists('role');
        Schema::dropIfExists('level');
        Schema::dropIfExists('employer');
    }
};
