<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ai_search_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('prompt');
            $table->json('generated_keywords')->nullable();
            $table->string('items_provider')->default('1688'); // 1688 or taobao
            $table->string('ai_model')->default('openai'); // openai or deepseek
            $table->boolean('feedback_like')->nullable();
            $table->text('feedback_comment')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_search_histories');
    }
};
