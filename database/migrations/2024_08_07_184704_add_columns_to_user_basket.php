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
        Schema::table('baskets', function (Blueprint $table) {
            $table->string('color');
            $table->string('quantity');
            $table->string('max_quantity');
            $table->string('size');
            $table->string('skuId');
            $table->text('image_url');
            $table->string('title');
            $table->string('price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('baskets', function (Blueprint $table) {
            $table->dropColumn('color');
            $table->dropColumn('quantity');
            $table->dropColumn('max_quantity');
            $table->dropColumn('size');
            $table->dropColumn('skuId');
            $table->dropColumn('image_url');
            $table->dropColumn('title');
            $table->dropColumn('price');
        });
    }
};
