<?php

namespace Database\Factories;

use App\Models\Question;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuestionFactory extends Factory
{
    protected $model = Question::class;

    public function definition()
    {
        // Generate 4 unique choices
        $choices = collect([
            $this->faker->sentence(3),
            $this->faker->sentence(3),
            $this->faker->sentence(3),
            $this->faker->sentence(3),
        ])->unique()->values()->toArray();

        // Ensure we have exactly 4 choices by adding more if needed
        while (count($choices) < 4) {
            $newChoice = $this->faker->sentence(3);
            if (!in_array($newChoice, $choices)) {
                $choices[] = $newChoice;
            }
        }

        return [
            'question_text' => $this->faker->sentence(10) . '?',
            'choices' => $choices,
            'correct_choice' => $this->faker->numberBetween(1, 4),
            'explanation' => $this->faker->optional(0.7)->paragraph(),
            'difficulty_level' => $this->faker->randomElement(['easy', 'medium', 'hard']),
            'category' => $this->faker->optional(0.8)->word(),
            'status' => $this->faker->randomElement(['pending', 'approved', 'rejected']),
            'created_by' => User::factory(),
            'approved_by' => null,
            'rejected_by' => null,
            'rejection_reason' => null,
            'approved_at' => null,
            'rejected_at' => null,
        ];
    }

    /**
     * Configure the model factory.
     */
    public function configure()
    {
        return $this->afterMaking(function (Question $question) {
            if ($question->status === 'approved' && $question->approved_by === null) {
                $question->approved_by = User::factory()->create()->id;
                $question->approved_at = now();
            } elseif ($question->status === 'rejected' && $question->rejected_by === null) {
                $question->rejected_by = User::factory()->create()->id;
                $question->rejected_at = now();
                $question->rejection_reason = $this->faker->sentence(10);
            }
        });
    }

    /**
     * Indicate that the question is pending.
     */
    public function pending()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'pending',
                'approved_by' => null,
                'approved_at' => null,
                'rejected_by' => null,
                'rejected_at' => null,
                'rejection_reason' => null,
            ];
        });
    }

    /**
     * Indicate that the question is approved.
     */
    public function approved()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'approved',
                'approved_by' => User::factory()->create()->id,
                'approved_at' => now(),
                'rejected_by' => null,
                'rejected_at' => null,
                'rejection_reason' => null,
            ];
        });
    }

    /**
     * Indicate that the question is rejected.
     */
    public function rejected()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'rejected',
                'rejected_by' => User::factory()->create()->id,
                'rejected_at' => now(),
                'rejection_reason' => $this->faker->sentence(10),
                'approved_by' => null,
                'approved_at' => null,
            ];
        });
    }

    /**
     * Set a specific difficulty level.
     */
    public function difficulty(string $level)
    {
        return $this->state(function (array $attributes) use ($level) {
            return [
                'difficulty_level' => $level,
            ];
        });
    }
} 