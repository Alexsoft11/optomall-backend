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
        Schema::create('basket_items', function (Blueprint $table) {
            $table->id();
            $table->string('basket_id');
            $table->string('product_id');
            $table->string('color');
            $table->string('quantity');
            $table->string('max_quantity');
            $table->string('size');
            $table->string('skuId');
            $table->text('image_url');
            $table->string('title');
            $table->string('price');
            $table->string('provider');
            $table->string('seller_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('basket_items');
    }
};
