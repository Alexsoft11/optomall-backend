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
            $table->renameColumn('title', 'title_uz');
            $table->string('title_en')->nullable();
            $table->string('title_ru')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('liked_products', function (Blueprint $table) {
            $table->renameColumn('title_uz', 'title');
            $table->dropColumn(['title_en', 'title_ru']);
        });
    }
};
