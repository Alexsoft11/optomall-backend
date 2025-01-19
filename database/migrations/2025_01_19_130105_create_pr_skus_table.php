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
        Schema::create('pr_skus', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_local_id');
            $table->string('sku');
            $table->integer('stock')->default(0);
            $table->decimal('price', 10, 2);
            $table->timestamps();

            $table->foreign('product_local_id')->references('id')->on('product_locals')->onDelete('cascade');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pr_skus');
    }
};
