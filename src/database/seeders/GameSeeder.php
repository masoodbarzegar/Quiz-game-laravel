<?php

namespace Database\Seeders;

use App\Models\Game;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class GameSeeder extends Seeder
{
    public function run(): void
    {
        Game::updateOrCreate(
            ['slug' => Str::slug('Time-based English Quiz')],
            [
                'name' => 'Time-based English Quiz',
                'description' => 'Test your English knowledge with this time-based quiz. Challenge yourself with questions about grammar, vocabulary, and comprehension.',
                'long_description' => 'This comprehensive English quiz is designed to test your language skills under time pressure. You\'ll face questions covering various aspects of English, including grammar rules, vocabulary usage, reading comprehension, and common idioms. Perfect for those looking to improve their English proficiency or prepare for language tests.',
                'image_path' => 'games/english-quiz.jpg',
                'difficulty' => 'medium',
                'time_limit' => 6,
                'question_count' => 21,
                'points_per_question' => 10,
                'skip_limit' => 3,
                'topics' => [
                    'Grammar & Syntax',
                    'Vocabulary & Usage',
                    'Reading Comprehension',
                    'Common Idioms',
                    'Sentence Structure'
                ],
                'rules' => [
                    'You have 6 minutes to complete all 21 questions',
                    'Questions are distributed as: 10 easy (3pts), 6 medium (5pts), 5 hard (8pts)',
                    'You can skip up to 3 questions',
                    'Points are awarded based on correct answers',
                    'No external resources allowed during the quiz'
                ],
                'stats' => [
                    'total_players' => 0,
                    'average_score' => 0,
                    'completion_rate' => '0%'
                ],
                'is_active' => true
            ]
        );
    }
} 