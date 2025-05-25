<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Question extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'question_text',
        'choices',
        'correct_choice',
        'explanation',
        'difficulty_level',
        'category',
        'status', // pending, approved, rejected
        'created_by',
        'approved_by',
        'rejected_by',
        'rejection_reason',
    ];

    protected $casts = [
        'choices' => 'array',
        'correct_choice' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    // Relationships
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejecter()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeByDifficulty($query, $level)
    {
        return $query->where('difficulty_level', $level);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    // Accessors & Mutators
    public function getCorrectAnswerAttribute()
    {
        return $this->choices[$this->correct_choice - 1] ?? null;
    }

    public function setChoicesAttribute($value)
    {
        if (is_string($value)) {
            $value = json_decode($value, true);
        }
        // Ensure we have exactly 4 choices
        if (count($value) !== 4) {
            throw new \InvalidArgumentException('A question must have exactly 4 choices.');
        }
        $this->attributes['choices'] = json_encode($value);
    }

    public function setCorrectChoiceAttribute($value)
    {
        if ($value < 1 || $value > 4) {
            throw new \InvalidArgumentException('Correct choice must be between 1 and 4.');
        }
        $this->attributes['correct_choice'] = $value;
    }
} 