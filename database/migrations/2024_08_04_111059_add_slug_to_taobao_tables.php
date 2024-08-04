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
        Schema::table('taobao_categories', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('name_ru');
        });

        Schema::table('taobao_subcategory_names', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('subcategory_name_ru');
        });

        Schema::table('taobao_subcategories', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('subcategory_ru');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('taobao_categories', function (Blueprint $table) {
            $table->dropColumn('slug');
        });

        Schema::table('taobao_subcategory_names', function (Blueprint $table) {
            $table->dropColumn('slug');
        });

        Schema::table('taobao_subcategories', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
