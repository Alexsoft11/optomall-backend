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
        Schema::create('taobao_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('unique_id');
            $table->string('name_uz')->nullable();
            $table->string('name_en')->nullable();
            $table->string('name_ru')->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });

        Schema::create('taobao_subcategory_names', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('unique_id');
            $table->unsignedBigInteger('taobao_category_id');
            $table->string('subcategory_name_uz')->nullable();
            $table->string('subcategory_name_en')->nullable();
            $table->string('subcategory_name_ru')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });


        Schema::create('taobao_subcategories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('unique_id');
            $table->unsignedBigInteger('taobao_subcategory_name_id');
            $table->string('subcategory_uz')->nullable();
            $table->string('subcategory_en')->nullable();
            $table->string('subcategory_ru')->nullable();
            $table->boolean('is_show')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('taobao_categories');
        Schema::dropIfExists('taobao_subcategory_names');
        Schema::dropIfExists('taobao_subcategories');
    }
};
