<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Question\StoreQuestionRequest;
use App\Http\Requests\Admin\Question\UpdateQuestionRequest;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class QuestionController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:admin']);
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Question::class);

        $query = Question::query()
            ->with(['creator', 'approver', 'rejecter'])
            ->when($request->input('search'), function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('question_text', 'like', "%{$search}%")
                        ->orWhere('category', 'like', "%{$search}%");
                });
            })
            ->when($request->input('status'), function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->input('difficulty'), function ($query, $difficulty) {
                $query->where('difficulty_level', $difficulty);
            })
            ->when($request->input('category'), function ($query, $category) {
                $query->where('category', $category);
            });

        $questions = $query->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Admin/Questions/Index', [
            'questions' => $questions,
            'filters' => $request->only(['search', 'status', 'difficulty', 'category']),
            'categories' => Question::distinct()->pluck('category')->filter(),
            'can' => [
                'create' => auth()->user()->hasRole(['manager', 'general']),
                'edit' => auth()->user()->hasRole(['manager', 'corrector', 'general']),
                'approve' => auth()->user()->hasRole(['manager', 'corrector']),
                'delete' => auth()->user()->hasRole('manager'),
            ],
        ]);
    }

    public function create()
    {
        $this->authorize('create', Question::class);

        return Inertia::render('Admin/Questions/Create', [
            'difficultyLevels' => [
                'easy' => 'Easy',
                'medium' => 'Medium',
                'hard' => 'Hard'
            ],
            'categories' => Question::distinct()->pluck('category')->filter(),
        ]);
    }

    public function store(StoreQuestionRequest $request)
    {
        $this->authorize('create', Question::class);

        $question = Question::create([
            ...$request->validated(),
            'created_by' => auth()->id(),
            'status' => 'pending',
        ]);

        Log::info('Question created', [
            'admin_id' => auth()->id(),
            'question_id' => $question->id,
        ]);

        return redirect()
            ->route('admin.questions.index')
            ->with('success', 'Question created successfully and is pending approval.');
    }

    public function edit(Question $question)
    {
        $this->authorize('update', $question);

        return Inertia::render('Admin/Questions/Edit', [
            'question' => $question->load(['creator', 'approver', 'rejecter']),
            'difficultyLevels' => [
                'easy' => 'Easy',
                'medium' => 'Medium',
                'hard' => 'Hard'
            ],
            'categories' => Question::distinct()->pluck('category')->filter(),
        ]);
    }

    public function update(UpdateQuestionRequest $request, Question $question)
    {
        $this->authorize('update', $question);

        $data = $request->validated();

        // If status is being changed to approved or rejected
        if (isset($data['status']) && $data['status'] !== $question->status) {
            if ($data['status'] === 'approved') {
                $data['approved_by'] = auth()->id();
                $data['approved_at'] = now();
                $data['rejected_by'] = null;
                $data['rejected_at'] = null;
                $data['rejection_reason'] = null;
            } elseif ($data['status'] === 'rejected') {
                $data['rejected_by'] = auth()->id();
                $data['rejected_at'] = now();
                $data['approved_by'] = null;
                $data['approved_at'] = null;
            }
        }

        $question->update($data);

        Log::info('Question updated', [
            'admin_id' => auth()->id(),
            'question_id' => $question->id,
            'status' => $data['status'] ?? $question->status,
        ]);

        return redirect()
            ->route('admin.questions.index')
            ->with('success', 'Question updated successfully.');
    }

    public function destroy(Question $question)
    {
        $this->authorize('delete', $question);

        $question->delete();

        Log::info('Question deleted', [
            'admin_id' => auth()->id(),
            'question_id' => $question->id,
        ]);

        return back()->with('success', 'Question deleted successfully.');
    }

    public function approve(Question $question)
    {
        $this->authorize('approve', $question);

        $question->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'rejected_by' => null,
            'rejected_at' => null,
            'rejection_reason' => null,
        ]);

        Log::info('Question approved', [
            'admin_id' => auth()->id(),
            'question_id' => $question->id,
        ]);

        return back()->with('success', 'Question approved successfully.');
    }

    public function reject(Request $request, Question $question)
    {
        $this->authorize('approve', $question);

        $request->validate([
            'rejection_reason' => ['required', 'string', 'min:10'],
        ]);

        $question->update([
            'status' => 'rejected',
            'rejected_by' => auth()->id(),
            'rejected_at' => now(),
            'rejection_reason' => $request->rejection_reason,
            'approved_by' => null,
            'approved_at' => null,
        ]);

        Log::info('Question rejected', [
            'admin_id' => auth()->id(),
            'question_id' => $question->id,
        ]);

        return back()->with('success', 'Question rejected successfully.');
    }
} 