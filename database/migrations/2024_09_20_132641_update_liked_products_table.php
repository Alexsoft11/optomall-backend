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
        Schema::table('liked_products', function (Blueprint $table) {
            $table->dropColumn(['title_uz', 'title_en', 'title_ru']);
            $table->string('title');
            $table->string('provider');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('liked_products', function (Blueprint $table) {
            $table->string('title_uz');
            $table->string('title_en');
            $table->string('title_ru');
            $table->dropColumn(['title', 'provider']);
        });
    }
};
