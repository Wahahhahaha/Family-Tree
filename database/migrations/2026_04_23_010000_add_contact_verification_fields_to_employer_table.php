<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('employer')) {
            return;
        }

        Schema::table('employer', function (Blueprint $table) {
            if (!Schema::hasColumn('employer', 'pending_email')) {
                $table->string('pending_email')->nullable()->after('email');
            }

            if (!Schema::hasColumn('employer', 'email_verification_token')) {
                $table->string('email_verification_token')->nullable()->after('pending_email');
            }

            if (!Schema::hasColumn('employer', 'email_verification_token_expires_at')) {
                $table->timestamp('email_verification_token_expires_at')->nullable()->after('email_verification_token');
            }

            if (!Schema::hasColumn('employer', 'pending_phonenumber')) {
                $table->string('pending_phonenumber')->nullable()->after('phonenumber');
            }

            if (!Schema::hasColumn('employer', 'phone_verification_otp_hash')) {
                $table->string('phone_verification_otp_hash')->nullable()->after('pending_phonenumber');
            }

            if (!Schema::hasColumn('employer', 'phone_verification_otp_expires_at')) {
                $table->timestamp('phone_verification_otp_expires_at')->nullable()->after('phone_verification_otp_hash');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('employer')) {
            return;
        }

        Schema::table('employer', function (Blueprint $table) {
            $columnsToDrop = [];

            if (Schema::hasColumn('employer', 'phone_verification_otp_expires_at')) {
                $columnsToDrop[] = 'phone_verification_otp_expires_at';
            }

            if (Schema::hasColumn('employer', 'phone_verification_otp_hash')) {
                $columnsToDrop[] = 'phone_verification_otp_hash';
            }

            if (Schema::hasColumn('employer', 'pending_phonenumber')) {
                $columnsToDrop[] = 'pending_phonenumber';
            }

            if (Schema::hasColumn('employer', 'email_verification_token_expires_at')) {
                $columnsToDrop[] = 'email_verification_token_expires_at';
            }

            if (Schema::hasColumn('employer', 'email_verification_token')) {
                $columnsToDrop[] = 'email_verification_token';
            }

            if (Schema::hasColumn('employer', 'pending_email')) {
                $columnsToDrop[] = 'pending_email';
            }

            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
