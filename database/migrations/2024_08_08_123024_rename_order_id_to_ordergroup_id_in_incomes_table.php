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
        Schema::table('incomes', function (Blueprint $table) {
            // Drop the existing foreign key constraint and column
            $table->dropForeign(['order_id']);
            $table->dropColumn('order_id');

            // Add the new column with the foreign key constraint
            $table->string('order_group_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('incomes', function (Blueprint $table) {
            // Drop the new foreign key constraint and column
            $table->dropColumn('order_group_id');
        });
    }
};
