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
        Schema::create('skus', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_local_id');
            $table->integer('quantity')->default(0);
            $table->decimal('price', 10, 2);
            $table->decimal('total_price', 10, 2); // Nullable if optional
            $table->decimal('origin_price', 10, 2)->nullable(); // Nullable if optional
            $table->string('properties'); // Nullable if optional
            $table->string('properties_name'); // Nullable if optional
            $table->integer('sale_count')->default(0); // Default to 0 if no sales yet
            $table->string('specid')->nullable(); // Nullable if optional
            $table->string('sku_id');
            $table->timestamps();

            $table->foreign('product_local_id')->references('id')->on('product_locals')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('skus');
    }
};
