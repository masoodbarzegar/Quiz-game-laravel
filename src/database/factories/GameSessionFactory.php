<?php

namespace Database\Factories;

use App\Models\Game;
use App\Models\GameSession;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GameSessionFactory extends Factory
{
    protected $model = GameSession::class;

    public function definition()
    {
        return [
            'client_id' => \App\Models\Client::factory(),
            'game_id' => Game::factory(),
            'status' => $this->faker->randomElement(['in_progress', 'completed', 'abandoned']),
            'score' => $this->faker->numberBetween(0, 100),
            'correct_answers' => $this->faker->numberBetween(0, 20),
            'questions_answered' => $this->faker->numberBetween(0, 20),
            'total_time_taken' => $this->faker->numberBetween(60, 1800),
            'end_reason' => $this->faker->randomElement(['completed', 'user_exit', 'lives_exhausted']),
            'started_at' => now(),
            'ended_at' => now()->addMinutes($this->faker->numberBetween(1, 30)),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function inProgress()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'in_progress',
                'score' => 0,
                'correct_answers' => 0,
                'questions_answered' => 0,
                'total_time_taken' => 0,
                'end_reason' => null,
                'ended_at' => null,
            ];
        });
    }

    public function completed()
    {
        return $this->state(function (array $attributes) {
            $score = $this->faker->numberBetween(0, 100);
            $questionsAnswered = $this->faker->numberBetween(10, 20);
            $correctAnswers = $this->faker->numberBetween(0, $questionsAnswered);
            
            return [
                'status' => 'completed',
                'score' => $score,
                'correct_answers' => $correctAnswers,
                'questions_answered' => $questionsAnswered,
                'total_time_taken' => $this->faker->numberBetween(300, 1800),
                'end_reason' => 'completed',
                'ended_at' => now()->addMinutes($this->faker->numberBetween(5, 30)),
            ];
        });
    }

    public function abandoned()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'abandoned',
                'end_reason' => 'user_exit',
                'ended_at' => now()->addMinutes($this->faker->numberBetween(1, 5)),
            ];
        });
    }

    public function share(Request $request): array
    {
        return array_merge(Inertia::getShared(), [
            'errors' => function () use ($request) { /* ... */ },
        ]);
    }
} 