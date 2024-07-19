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
        Schema::table('order_statuses', function (Blueprint $table) {
            $table->renameColumn('name', 'name_uz');
            $table->string('name_en')->nullable();
            $table->string('name_ru')->nullable();
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
        Schema::table('order_statuses', function (Blueprint $table) {
            $table->renameColumn('name_uz', 'name');
            $table->renameColumn('message_uz', 'message');
            $table->dropColumn(['name_en', 'name_ru', 'message_en', 'message_ru']);
        });
    }
};
