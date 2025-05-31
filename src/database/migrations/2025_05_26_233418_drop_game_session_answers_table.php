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
        Schema::dropIfExists('game_session_answers');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('game_session_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_session_id')->constrained()->onDelete('cascade');
            $table->foreignId('question_id')->constrained()->onDelete('cascade');
            $table->string('selected_answer');
            $table->boolean('is_correct');
            $table->integer('points_earned')->default(0);
            $table->integer('time_taken')->nullable(); // Time taken to answer in seconds
            $table->timestamps();
        });
    }
};
