<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'game_id',
        'score',
        'correct_answers',
        'incorrect_answers',
        'questions_answered',
        'started_at',
        'ended_at',
        'status',
        'end_reason',
        'exam_data',
        'total_time_taken',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'score' => 'integer',
        'correct_answers' => 'integer',
        'incorrect_answers' => 'integer',
        'questions_answered' => 'integer',
        'exam_data' => 'array',
        'total_time_taken' => 'integer',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'in_progress';
    }

    public function endGame(string $reason): void
    {
        $this->update([
            'status' => 'completed',
            'end_reason' => $reason,
            'ended_at' => now(),
        ]);
    }

    public function recordAnswer(Question $question, string $selectedAnswer, bool $isCorrect, int $timeTaken, int $points = null): void
    {
        // If points not provided, calculate based on difficulty level
        if ($points === null) {
            $points = $isCorrect ? match($question->difficulty_level) {
                'easy' => 3,    // Level 1: 3 points
                'medium' => 5,  // Level 2: 5 points
                'hard' => 8,    // Level 3: 8 points
                default => 0,
            } : 0;
        }
        
        // Get current exam data or initialize empty array
        $examData = $this->exam_data ?? [];
        
        // Add new answer to exam data
        $examData[] = [
            'question_id' => $question->id,
            'selected_answer' => $selectedAnswer,
            'is_correct' => $isCorrect,
            'time_taken' => $timeTaken,
            'points_earned' => $points,
            'difficulty_level' => $question->difficulty_level,
            'answered_at' => now()->toIso8601String(),
        ];

        // Update the model
        $this->update([
            'exam_data' => $examData,
            'questions_answered' => $this->questions_answered + 1,
            'correct_answers' => $this->correct_answers + ($isCorrect ? 1 : 0),
            'incorrect_answers' => $this->incorrect_answers + ($isCorrect ? 0 : 1),
            'score' => $this->score + $points,
        ]);
    }

    public function getRemainingLives(): int
    {
        return 3 - $this->incorrect_answers;
    }

    public function hasLivesRemaining(): bool
    {
        return $this->getRemainingLives() > 0;
    }

    public function getAnswersAttribute()
    {
        return collect($this->exam_data ?? [])->map(function ($answer) {
            return (object) $answer;
        });
    }
} 