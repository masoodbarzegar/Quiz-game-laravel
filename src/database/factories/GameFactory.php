<?php

namespace Database\Factories;

use App\Models\Game;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class GameFactory extends Factory
{
    protected $model = Game::class;

    public function definition()
    {
        $name = $this->faker->unique()->words(3, true);
        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => $this->faker->paragraph,
            'long_description' => $this->faker->paragraph,
            'image_path' => $this->faker->imageUrl(),
            'difficulty' => $this->faker->randomElement(['Easy', 'Medium', 'Hard']),
            'time_limit' => $this->faker->numberBetween(15, 60),    
            'question_count' => $this->faker->numberBetween(10, 30),
            'points_per_question' => $this->faker->numberBetween(1, 5),
            'skip_limit' => $this->faker->numberBetween(1, 5),
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function inactive()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_active' => 0,
            ];
        });
    }
} 