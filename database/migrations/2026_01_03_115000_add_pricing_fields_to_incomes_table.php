<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('incomes', function (Blueprint $table) {
            $table->decimal('original_price_yuan', 12, 2)->nullable()->after('amount');
            $table->decimal('local_delivery_yuan', 12, 2)->nullable()->after('original_price_yuan');
            $table->decimal('profit_amount', 12, 2)->nullable()->after('local_delivery_yuan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('incomes', function (Blueprint $table) {
            $table->dropColumn(['original_price_yuan', 'local_delivery_yuan', 'profit_amount']);
        });
    }
};
