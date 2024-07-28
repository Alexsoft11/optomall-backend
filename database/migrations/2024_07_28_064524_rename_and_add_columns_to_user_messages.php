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
        Schema::table('user_messages', function (Blueprint $table) {
            $table->renameColumn('message', 'message_uz');
            $table->string('message_en')->nullable();
            $table->string('message_ru')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_messages', function (Blueprint $table) {
            $table->renameColumn('message_uz', 'message');
            $table->dropColumn(['message_en', 'message_ru']);
        });
    }
};
