<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->foreignId('game_id')->constrained('games')->onDelete('cascade');
            $table->integer('score')->default(0);
            $table->integer('correct_answers')->default(0);
            $table->integer('incorrect_answers')->default(0);
            $table->integer('questions_answered')->default(0);
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->enum('status', ['in_progress', 'completed', 'abandoned'])->default('in_progress');
            $table->enum('end_reason', ['timer', 'lives_exhausted', 'completed', 'user_exit'])->nullable();
            $table->timestamps();
        });

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

    public function down(): void
    {
        Schema::dropIfExists('game_session_answers');
        Schema::dropIfExists('game_sessions');
    }
}; 