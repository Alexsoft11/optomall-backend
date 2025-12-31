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
        Schema::table('cached_products', function (Blueprint $table) {
          
            
            $table->index('views');
            $table->index('provider');
            $table->index('language');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cached_products', function (Blueprint $table) {
            $table->dropIndex(['views']);
            $table->dropIndex(['provider']);
            $table->dropIndex(['language']);
        });
    }
};
