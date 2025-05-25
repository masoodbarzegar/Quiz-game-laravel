<?php

namespace App\Policies;

use App\Models\Question;
use App\Models\User;

class QuestionPolicy
{
    public function viewAny(User $user)
    {
        return $user->hasRole(['manager', 'corrector', 'general']);
    }

    public function view(User $user, Question $question)
    {
        return $user->hasRole(['manager', 'corrector', 'general']);
    }

    public function create(User $user)
    {
        return $user->hasRole(['manager', 'general']);
    }

    public function update(User $user, Question $question)
    {
        // Allow managers and correctors to edit any question
        if ($user->hasRole(['manager', 'corrector'])) {
            return true;
        }

        // General users can only edit their own questions if status is pending or rejected (not approved)
        if ($user->hasRole('general')) {
            return $question->created_by === $user->id && in_array($question->status, ['pending', 'rejected']);
        }

        return false;
    }

    public function delete(User $user, Question $question)
    {
        // Only managers can delete questions
        return $user->hasRole('manager');
    }

    public function approve(User $user, Question $question)
    {
        // Both managers and correctors can approve/reject questions
        return $user->hasRole(['manager', 'corrector']);
    }

    public function restore(User $user, Question $question)
    {
        // Only managers can restore deleted questions
        return $user->hasRole('manager');
    }

    public function forceDelete(User $user, Question $question)
    {
        // Only managers can permanently delete questions
        return $user->hasRole('manager');
    }
} 