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
        Schema::table('order_checks', function (Blueprint $table) {
            // Drop the foreign key constraint if it exists
            $table->dropForeign(['order_id']);

            // Rename the column
            $table->renameColumn('order_id', 'order_group_id');

            // Add the foreign key constraint for the new column
            $table->foreign('order_group_id')->references('id')->on('order_groups')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_checks', function (Blueprint $table) {
            // Drop the foreign key constraint for the new column
            $table->dropForeign(['order_group_id']);

            // Rename the column back to the original name
            $table->renameColumn('order_group_id', 'order_id');

            // Add the foreign key constraint for the original column
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
        });
    }
};
