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
        Schema::table('order_items', function (Blueprint $table) {
            $table->decimal('original_price_yuan', 10, 2)->nullable()->after('price');
            $table->decimal('local_delivery_yuan', 10, 2)->nullable()->after('original_price_yuan');
            $table->decimal('profit_amount', 10, 2)->nullable()->after('local_delivery_yuan');
        });

        Schema::table('basket_items', function (Blueprint $table) {
            $table->decimal('original_price_yuan', 10, 2)->nullable()->after('price');
            $table->decimal('local_delivery_yuan', 10, 2)->nullable()->after('original_price_yuan');
            $table->decimal('profit_amount', 10, 2)->nullable()->after('local_delivery_yuan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['original_price_yuan', 'local_delivery_yuan', 'profit_amount']);
        });

        Schema::table('basket_items', function (Blueprint $table) {
            $table->dropColumn(['original_price_yuan', 'local_delivery_yuan', 'profit_amount']);
        });
    }
};
