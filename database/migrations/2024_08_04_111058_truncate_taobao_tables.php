<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Truncate tables to remove all data
        DB::table('taobao_subcategories')->truncate();
        DB::table('taobao_subcategory_names')->truncate();
        DB::table('taobao_categories')->truncate();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Optionally, you can choose to leave this method empty or restore data if needed
    }
};
