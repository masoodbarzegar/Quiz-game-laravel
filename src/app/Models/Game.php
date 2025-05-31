<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Game extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'long_description',
        'image_path',
        'difficulty',
        'time_limit',
        'question_count',
        'points_per_question',
        'skip_limit',
        'topics',
        'rules',
        'stats',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'time_limit' => 'integer',
        'question_count' => 'integer',
        'points_per_question' => 'integer',
        'skip_limit' => 'integer',
        'topics' => 'array',
        'rules' => 'array',
        'stats' => 'array',
    ];

    public function sessions()
    {
        return $this->hasMany(GameSession::class);
    }

    public function getImageUrlAttribute()
    {
        return $this->image_path ? asset('storage/' . $this->image_path) : null;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByDifficulty($query, $difficulty)
    {
        return $query->where('difficulty', $difficulty);
    }

    public function getFormattedStatsAttribute()
    {
        return [
            'total_players' => number_format($this->stats['total_players'] ?? 0),
            'average_score' => number_format($this->stats['average_score'] ?? 0, 1),
            'completion_rate' => $this->stats['completion_rate'] ?? '0%'
        ];
    }

    public function updateStats(): void
    {
        $completedSessions = $this->sessions()
            ->where('status', 'completed')
            ->get();

        $totalSessions = $completedSessions->count();
        if ($totalSessions === 0) {
            $this->update([
                'stats' => [
                    'total_players' => 0,
                    'average_score' => 0,
                    'completion_rate' => '0%',
                    'total_sessions' => 0,
                    'total_questions_answered' => 0,
                    'total_correct_answers' => 0,
                    'average_time_per_question' => 0,
                ]
            ]);
            return;
        }

        $totalScore = $completedSessions->sum('score');
        $totalQuestionsAnswered = $completedSessions->sum('questions_answered');
        $totalCorrectAnswers = $completedSessions->sum('correct_answers');
        
        // Calculate average time per question from exam_data
        $totalTimeTaken = $completedSessions->sum(function ($session) {
            return collect($session->exam_data)->sum('time_taken');
        });

        $this->update([
            'stats' => [
                'total_players' => $totalSessions,
                'average_score' => round($totalScore / $totalSessions, 1),
                'completion_rate' => round(($totalSessions / $this->sessions()->count()) * 100) . '%',
                'total_sessions' => $totalSessions,
                'total_questions_answered' => $totalQuestionsAnswered,
                'total_correct_answers' => $totalCorrectAnswers,
                'average_time_per_question' => $totalQuestionsAnswered > 0 
                    ? round($totalTimeTaken / $totalQuestionsAnswered, 1) 
                    : 0,
            ]
        ]);
    }
} 