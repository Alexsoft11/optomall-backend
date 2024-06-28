<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_category_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->string('price');
            $table->string('image');
            $table->string('location');
            $table->string('product_url');
            $table->text('description')->nullable();
            $table->integer('quantity')->default(0);
            $table->decimal('discount_percent', 5, 2)->nullable();
            $table->decimal('discounted_price', 8, 2)->nullable();
            $table->decimal('rating', 3, 2)->nullable();
            $table->integer('sold')->default(0);
            $table->timestamps();
        });

        Schema::create('product_pictures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('image_url')->nullable();
            $table->boolean('is_main')->default(false);
            $table->timestamps();
        });

        Schema::create('product_videos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('preview_url')->nullable();
            $table->string('url');
            $table->timestamps();
        });

        Schema::create('product_colors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('property_name');
            $table->string('value');
            $table->string('image_url')->nullable();
            $table->string('mini_image_url')->nullable();
            $table->timestamps();
        });

        Schema::create('product_sizes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('property_name');
            $table->string('value');
            $table->timestamps();
        });

        Schema::create('product_compositions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('property_name');
            $table->string('value');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('product_compositions');
        Schema::dropIfExists('product_sizes');
        Schema::dropIfExists('product_colors');
        Schema::dropIfExists('product_videos');
        Schema::dropIfExists('product_pictures');
        Schema::dropIfExists('products');
    }
};
