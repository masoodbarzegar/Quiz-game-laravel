<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\GameSession;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class GameController extends Controller
{
    public function index()
    {
        $games = Game::active()->get();

        return Inertia::render('Games/Index', [
            'games' => $games->map(function ($game) {
                return [
                    'id' => $game->id,
                    'name' => $game->name,
                    'slug' => $game->slug,
                    'description' => $game->description,
                    'image_path' => $game->image_path,
                    'difficulty' => $game->difficulty,
                    'time_limit' => $game->time_limit,
                    'question_count' => $game->question_count,
                    'points_per_question' => $game->points_per_question,
                    'topics' => $game->topics,
                    'rules' => $game->rules,
                    'stats' => $game->stats,
                    'is_active' => $game->is_active,
                ];
            })
        ]);
    }

    public function show(Game $game)
    {
        return Inertia::render('Games/Show', [
            'game' => [
                'id' => $game->id,
                'name' => $game->name,
                'slug' => $game->slug,
                'description' => $game->description,
                'long_description' => $game->long_description,
                'image_path' => $game->image_path,
                'difficulty' => $game->difficulty,
                'time_limit' => $game->time_limit,
                'question_count' => $game->question_count,
                'points_per_question' => $game->points_per_question,
                'skip_limit' => $game->skip_limit,
                'topics' => $game->topics,
                'rules' => $game->rules,
                'stats' => $game->stats,
                'is_active' => $game->is_active,
            ]
        ]);
    }

    public function start(Game $game)
    {
        // Check if user has an active session
        $activeSession = GameSession::where('client_id', Auth::guard('client')->id())
            ->where('game_id', $game->id)
            ->where('status', 'in_progress')
            ->first();

        if ($activeSession) {
            return redirect()->route('play.game', ['game' => $game->slug, 'session' => $activeSession->id]);
        }

        // Create new game session
        $session = GameSession::create([
            'client_id' => Auth::guard('client')->id(),
            'game_id' => $game->id,
            'started_at' => now(),
            'status' => 'in_progress',
        ]);

        return redirect()->route('play.game', ['game' => $game->slug, 'session' => $session->id]);
    }

    public function play(Game $game, GameSession $session)
    {
        // Verify session belongs to current user and game
        if ($session->client_id !== Auth::guard('client')->id() || 
            $session->game_id !== $game->id || 
            !$session->isActive()) {
            abort(403, 'Unauthorized access to this game session.');
        }

        // Get questions for the game
        $questions = $this->getQuestionsForGame($game);
        Log::info(count($questions));
        // Store initial time in session
        if (!$session->time_remaining) {
            $session->update(['time_remaining' => $game->time_limit * 60]);
        }

        return Inertia::render('Client/Play/Play', [
            'game' => $game,
            'session' => $session,
            'questions' => $questions,
            'currentQuestion' => $questions->first(),
            'remainingLives' => $session->getRemainingLives(),
            'timeLimit' => $game->time_limit * 60, // Convert to seconds
            'timeRemaining' => $session->time_remaining,
        ]);
    }

    public function endGame(Request $request, Game $game, GameSession $session)
    {
        if ($session->client_id !== Auth::guard('client')->id() || !$session->isActive()) {
            return redirect()->route('games.show', $game->slug);
        }
        Log::info('endGame - answers');
        Log::info($request->answers);

        $request->validate([
            'answers' => 'required|array',
            'answers.*.question_id' => 'required|exists:questions,id',
            'answers.*.selected_answer' => 'required|string',
            'answers.*.is_correct' => 'required|boolean',
            'answers.*.time_taken' => 'required|integer|min:0',
            'final_score' => 'required|integer|min:0',
            'lives_remaining' => 'required|integer|min:0',
            'time_remaining' => 'required|integer|min:0',
            'total_time_taken' => 'required|integer|min:0',
            'end_reason' => 'required|string',
            'questions_answered' => 'required|integer|min:0',
            'correct_answers' => 'required|integer|min:0',
            'incorrect_answers' => 'required|integer|min:0',
        ]);

        // Get all questions to calculate points correctly
        $questionIds = collect($request->answers)->pluck('question_id')->unique();
        $questions = Question::whereIn('id', $questionIds)->get()->keyBy('id');

        // Update session with final game data
        $session->update([
            'score' => $request->final_score,
            'correct_answers' => $request->correct_answers,
            'incorrect_answers' => $request->incorrect_answers,
            'questions_answered' => $request->questions_answered,
            'ended_at' => now(),
            'status' => 'completed',
            'end_reason' => $request->end_reason,
            'total_time_taken' => $request->total_time_taken,
            'exam_data' => collect($request->answers)->map(function ($answer) use ($questions) {
                $question = $questions[$answer['question_id']] ?? null;
                $points = 0;
                if ($answer['is_correct'] && $question) {
                    $points = match($question->difficulty_level) {
                        'easy' => 3,    // Level 1: 3 points
                        'medium' => 5,  // Level 2: 5 points
                        'hard' => 8,    // Level 3: 8 points
                        default => 0,
                    };
                }
                return [
                    'question_id' => $answer['question_id'],
                    'selected_answer' => $answer['selected_answer'],
                    'is_correct' => $answer['is_correct'],
                    'time_taken' => $answer['time_taken'],
                    'points_earned' => $points,
                    'difficulty_level' => $question ? $question->difficulty_level : null,
                    'answered_at' => now()->toIso8601String(),
                ];
            })->toArray()
        ]);

        // Update game stats
        $game->updateStats();

        return redirect()->route('play.result', ['game' => $game->slug, 'session' => $session->id]);
    }

    public function result(Game $game, GameSession $session)
    {
        if ($session->client_id !== Auth::guard('client')->id()) {
            abort(403, 'Unauthorized access to this game session.');
        }
        Log::info($session);
        // Load questions for the answers
        $questionIds = collect($session->exam_data)->pluck('question_id')->unique();
        $questions = Question::whereIn('id', $questionIds)->get()->keyBy('id');

        // Map answers with their questions
        $answers = collect($session->exam_data)->map(function ($answer) use ($questions) {
            $answer['question'] = $questions[$answer['question_id']] ?? null;
            return (object) $answer;
        });

        return Inertia::render('Client/Play/Result', [
            'game' => [
                'id' => $game->id,
                'name' => $game->name,
                'slug' => $game->slug,
                'difficulty' => $game->difficulty,
                'time_limit' => $game->time_limit,
                'question_count' => $game->question_count,
                'points_per_question' => $game->points_per_question,
            ],
            'session' => [
                'id' => $session->id,
                'score' => $session->score,
                'correct_answers' => $session->correct_answers,
                'questions_answered' => $session->questions_answered,
                'ended_at' => $session->ended_at,
                'end_reason' => $session->end_reason,
                'total_time_taken' => $session->total_time_taken ?? 0,
                'exam_data' => $session->exam_data ?? [],
            ],
            'answers' => $answers,
        ]);
    }

    private function getQuestionsForGame(Game $game)
    {

        // Ensure we have enough questions for each difficulty level
        $requiredQuestions = [
            'easy' => 10,
            'medium' => 6,
            'hard' => 5,
        ];

        $selectedQuestions = collect();

        foreach ($requiredQuestions as $level => $count) {
            $questions = Question::where('status', 'approved')
                ->where('difficulty_level', $level)
                ->inRandomOrder()
                ->take($count)
                ->get();

            if ($questions->count() < $count) {
                Log::error("Not enough {$level} questions available. Required: {$count}, Available: {$questions->count()}");
            }

            $selectedQuestions = $selectedQuestions->concat($questions);
        }

        return $selectedQuestions;
    }

    private function getNextQuestion(GameSession $session, int $currentQuestionId)
    {
        $answeredQuestionIds = collect($session->exam_data)->pluck('question_id')->toArray();
        $answeredQuestionIds[] = $currentQuestionId;

        return Question::where('game_id', $session->game_id)
            ->where('is_active', true)
            ->whereNotIn('id', $answeredQuestionIds)
            ->inRandomOrder()
            ->first();
    }
} 