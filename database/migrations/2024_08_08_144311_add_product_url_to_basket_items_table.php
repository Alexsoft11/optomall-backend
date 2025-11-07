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
        Schema::table('basket_items', function (Blueprint $table) {
            $table->string('product_url')->nullable(); // Adjust the type if necessary
        });
    }

    public function down()
    {
        Schema::table('basket_items', function (Blueprint $table) {
            $table->dropColumn('product_url');
        });
    }
};
