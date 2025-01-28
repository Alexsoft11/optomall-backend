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
        Schema::create('sku_values', function (Blueprint $table) {
            $table->id('vid');
            $table->unsignedBigInteger('sku_prop_id'); // Foreign key to sku_props
            $table->string('name'); // Value name (e.g., S, M, L)
            $table->string('imageUrl')->nullable(); // Image URL for the value
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('sku_prop_id')->references('pid')->on('sku_props')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sku_values');
    }
};
