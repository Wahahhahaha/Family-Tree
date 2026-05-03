<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('family_member')) {
            return;
        }

        Schema::table('family_member', function (Blueprint $table) {
            if (!Schema::hasColumn('family_member', 'pending_phonenumber')) {
                $table->string('pending_phonenumber')->nullable()->after('phonenumber');
            }

            if (!Schema::hasColumn('family_member', 'phone_verification_otp_hash')) {
                $table->string('phone_verification_otp_hash')->nullable()->after('pending_phonenumber');
            }

            if (!Schema::hasColumn('family_member', 'phone_verification_otp_expires_at')) {
                $table->timestamp('phone_verification_otp_expires_at')->nullable()->after('phone_verification_otp_hash');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('family_member')) {
            return;
        }

        Schema::table('family_member', function (Blueprint $table) {
            $columnsToDrop = [];

            if (Schema::hasColumn('family_member', 'phone_verification_otp_expires_at')) {
                $columnsToDrop[] = 'phone_verification_otp_expires_at';
            }

            if (Schema::hasColumn('family_member', 'phone_verification_otp_hash')) {
                $columnsToDrop[] = 'phone_verification_otp_hash';
            }

            if (Schema::hasColumn('family_member', 'pending_phonenumber')) {
                $columnsToDrop[] = 'pending_phonenumber';
            }

            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
