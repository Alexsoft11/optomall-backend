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
        // Check if the table does not exist and create it
        if (!Schema::hasTable('hints')) {
            Schema::create('hints', function (Blueprint $table) {
                $table->id();
                $table->string('taobao_subcategory_id')->nullable();
                $table->string('name')->nullable();
                $table->timestamps();
            });
        } else {
            Schema::table('hints', function (Blueprint $table) {
                $table->id();
                $table->string('taobao_subcategory_id')->nullable();
                $table->string('name')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hints');
    }
};
