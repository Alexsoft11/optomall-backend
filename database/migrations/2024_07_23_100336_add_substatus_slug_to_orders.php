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
<<<<<<< HEAD
            if (!Schema::hasColumn('orders', 'substatus_slug')) {
                $table->string('substatus_slug')->nullable();
            }
=======
            $table->string('substatus_slug')->nullable();
>>>>>>> f5eca257537594624650101f97d06a537e55343c
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('substatus_slug');
        });
    }
};
