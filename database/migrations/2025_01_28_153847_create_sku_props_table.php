<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSkuPropsTable extends Migration
{
    public function up()
    {
        Schema::create('sku_props', function (Blueprint $table) {
            $table->id('pid');
            $table->unsignedBigInteger('product_local_id'); // Foreign key to the products table
            $table->string('prop_name'); // Property name (e.g., 尺码)
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('product_local_id')->references('id')->on('product_locals')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('sku_props');
    }
}
