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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('country')->nullable();
            $table->string('substatus_uz')->nullable();
            $table->string('substatus_ru')->nullable();
            $table->string('substatus_en')->nullable();
            $table->string('substatus_color')->nullable();
            $table->string('kg')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('country');
            $table->dropColumn('substatus_uz');
            $table->dropColumn('substatus_ru');
            $table->dropColumn('substatus_en');
            $table->dropColumn('substatus_color');
            $table->dropColumn('kg');
        });
    }
};
