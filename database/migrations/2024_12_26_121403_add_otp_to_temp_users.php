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
        Schema::table('temp_users', function (Blueprint $table) {
            // Make all existing columns nullable
            $table->string('email')->nullable()->change();
            $table->string('password')->nullable()->change();
            $table->string('confirm_password')->nullable()->change();

            // Add new columns for phone number and OTP
            $table->string('phone_number')->nullable()->after('confirm_password');
            $table->string('otp')->nullable()->after('phone_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('temp_users', function (Blueprint $table) {
            // Revert changes (making columns non-nullable and removing new columns)
            $table->string('email')->nullable(false)->change();
            $table->string('password')->nullable(false)->change();
            $table->string('confirm_password')->nullable(false)->change();

            // Drop the added columns
            $table->dropColumn(['phone_number', 'otp']);
        });
    }
};
