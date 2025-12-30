<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cached_products', function (Blueprint $table) {
            $table->id();
            $table->string('external_id', 20)->index();
            $table->string('provider', 20)->default('1688');
            $table->string('language', 5)->default('en');
            $table->string('title', 500)->nullable();
            $table->text('description')->nullable();
            $table->decimal('price', 12, 2)->nullable();
            $table->string('image', 500)->nullable();
            $table->json('images')->nullable();
            $table->json('skus')->nullable();
            $table->json('sku_props')->nullable();
            $table->json('props')->nullable();
            $table->json('tier_prices')->nullable();
            $table->json('seller_info')->nullable();
            $table->json('raw_data')->nullable();
            $table->integer('sales')->default(0);
            $table->decimal('rating', 3, 2)->default(0);
            $table->boolean('is_available')->default(true);
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->unique(['external_id', 'provider', 'language'], 'cached_products_unique');
            $table->index(['synced_at']);
            $table->index(['is_available']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cached_products');
    }
};
