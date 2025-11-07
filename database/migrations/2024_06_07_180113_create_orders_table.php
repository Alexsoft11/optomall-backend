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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('total_price');
            $table->string('status')->default('processing');
            $table->string('user_name');
            $table->string('user_phone');
            $table->string('location_url');
            $table->string('location_name')->nullable();
            $table->string('delivery_time')->nullable();
            $table->string('is_cheking')->default(false);
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->string('product_id');
            $table->string('title');
            $table->text('image_url')->nullable();
            $table->integer('quantity');
            $table->string('price');
            $table->string('color');
            $table->string('size');
            $table->timestamps();
        });

        Schema::create('order_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->text('image')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_checks');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
