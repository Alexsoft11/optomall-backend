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
        Schema::table('basket_items', function (Blueprint $table) {
            if (!Schema::hasColumn('basket_items', 'max_quantity')) {
                $table->string('max_quantity')->nullable()->after('quantity');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('basket_items', function (Blueprint $table) {
            if (Schema::hasColumn('basket_items', 'max_quantity')) {
                $table->dropColumn('max_quantity');
            }
        });
    }
};
