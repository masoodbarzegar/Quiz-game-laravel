<?php

namespace App\Policies;

use App\Models\Question;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class QuestionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['manager', 'corrector', 'general']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Question $question): Response
    {
        if ($user->hasAnyRole(['manager', 'corrector'])) {
            return Response::allow();
        }

        if ($user->isGeneral() && $question->created_by === $user->id) {
            return Response::allow();
        }

        return Response::denyWithStatus(403, 'You do not have permission to view this question.');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): Response
    {
        if ($user->hasAnyRole(['manager', 'general'])) {
            return Response::allow();
        }
        return Response::denyWithStatus(403, 'You do not have permission to create questions.');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Question $question): Response
    {
        if ($user->isManager() || $user->isCorrector()) {
            return Response::allow();
        }

        if ($user->isGeneral() && $question->created_by === $user->id && ($question->status === 'pending' || $question->status === 'rejected')) {
            return Response::allow();
        }

        return Response::denyWithStatus(403, 'You do not have permission to update this question or it is not in an editable state.');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Question $question): Response
    {
        if ($user->isManager()) {
            return Response::allow();
        }
        return Response::denyWithStatus(403, 'You do not have permission to delete this question.');
    }

    /**
     * Determine whether the user can approve the question.
     */
    public function approve(User $user, Question $question): Response
    {
        if ($user->isManager() || $user->isCorrector()) {
            return Response::allow();
        }
        return Response::denyWithStatus(403, 'You do not have permission to approve questions.');
    }

    /**
     * Determine whether the user can reject the question.
     */
    public function reject(User $user, Question $question): Response
    {
        if ($user->isManager() || $user->isCorrector()) {
            return Response::allow();
        }
        return Response::denyWithStatus(403, 'You do not have permission to reject questions.');
    }

    // Add other policy methods like restore, forceDelete if needed, for example:
    // public function restore(User $user, Question $question): bool
    // {
    //     return $user->isManager();
    // }

    // public function forceDelete(User $user, Question $question): bool
    // {
    //     return $user->isManager();
    // }
} 