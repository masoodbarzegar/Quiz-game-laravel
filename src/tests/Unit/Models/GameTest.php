<?php

namespace Tests\Unit\Models;

use App\Models\Game;
use App\Models\GameSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class GameTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_be_created_using_factory()
    {
        $game = Game::factory()->create();

        $this->assertDatabaseHas('games', [
            'id' => $game->id,
        ]);
    }

    #[Test]
    public function game_has_many_sessions(): void
    {
        $game = Game::factory()->create();
        GameSession::factory()->count(3)->create(['game_id' => $game->id]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $game->sessions);
        $this->assertCount(3, $game->sessions);
        $this->assertInstanceOf(GameSession::class, $game->sessions->first());
    }

    #[Test]
    public function get_image_url_attribute_returns_correct_url_or_null(): void
    {
        Storage::fake('public'); // Fake the public storage

        $gameWithPath = Game::factory()->make(['image_path' => 'games/image.jpg']);
        $this->assertEquals(asset('storage/games/image.jpg'), $gameWithPath->image_url);

        $gameWithoutPath = Game::factory()->make(['image_path' => null]);
        $this->assertNull($gameWithoutPath->image_url);
    }

    #[Test]
    public function active_scope_returns_only_active_games(): void
    {
        Game::factory()->create(['is_active' => true]);
        Game::factory()->create(['is_active' => true]);
        Game::factory()->create(['is_active' => false]);

        $activeGames = Game::active()->get();
        $this->assertCount(2, $activeGames);
        foreach ($activeGames as $game) {
            $this->assertTrue($game->is_active);
        }
    }

    #[Test]
    public function by_difficulty_scope_returns_games_of_specified_difficulty(): void
    {
        Game::factory()->create(['difficulty' => 'easy']);
        Game::factory()->create(['difficulty' => 'medium']);
        Game::factory()->create(['difficulty' => 'easy']);

        $easyGames = Game::byDifficulty('easy')->get();
        $this->assertCount(2, $easyGames);
        foreach ($easyGames as $game) {
            $this->assertEquals('easy', $game->difficulty);
        }

        $mediumGames = Game::byDifficulty('medium')->get();
        $this->assertCount(1, $mediumGames);
        $this->assertEquals('medium', $mediumGames->first()->difficulty);
    }

    #[Test]
    public function get_formatted_stats_attribute_returns_correctly_formatted_data(): void
    {
        $rawStats = [
            'total_players' => 12345,
            'average_score' => 85.678,
            'completion_rate' => '75.5%'
        ];
        $game = Game::factory()->make(['stats' => $rawStats]);

        $formattedStats = $game->formatted_stats;
        $this->assertEquals('12,345', $formattedStats['total_players']);
        $this->assertEquals('85.7', $formattedStats['average_score']); // number_format rounds
        $this->assertEquals('75.5%', $formattedStats['completion_rate']);
    }

    #[Test]
    public function get_formatted_stats_returns_defaults_if_stats_are_null_or_missing_keys(): void
    {
        $gameNullStats = Game::factory()->make(['stats' => null]);
        $formattedNull = $gameNullStats->formatted_stats;
        $this->assertEquals('0', $formattedNull['total_players']);
        $this->assertEquals('0.0', $formattedNull['average_score']);
        $this->assertEquals('0%', $formattedNull['completion_rate']);

        $gameMissingKeys = Game::factory()->make(['stats' => ['total_players' => 10]]); // Other keys missing
        $formattedMissing = $gameMissingKeys->formatted_stats;
        $this->assertEquals('10', $formattedMissing['total_players']);
        $this->assertEquals('0.0', $formattedMissing['average_score']);
        $this->assertEquals('0%', $formattedMissing['completion_rate']);
    }

    #[Test]
    public function update_stats_sets_default_stats_if_no_completed_sessions(): void
    {
        $game = Game::factory()->create();
        // Ensure some sessions exist, but none are 'completed'
        GameSession::factory()->count(2)->create(['game_id' => $game->id, 'status' => 'in_progress']);

        $game->updateStats();
        $game->refresh();

        $expectedDefaultStats = [
            'total_players' => 0,
            'average_score' => 0,
            'completion_rate' => '0%',
            'total_sessions' => 0,
            'total_questions_answered' => 0,
            'total_correct_answers' => 0,
            'average_time_per_question' => 0,
        ];
        $this->assertEquals($expectedDefaultStats, $game->stats);
    }

    #[Test]
    public function update_stats_calculates_correctly_with_completed_sessions(): void
    {
        $game = Game::factory()->create();

        // Session 1: Completed
        GameSession::factory()->create([
            'game_id' => $game->id,
            'status' => 'completed',
            'score' => 100,
            'questions_answered' => 10,
            'correct_answers' => 8,
            'exam_data' => [
                ['question_id' => 1, 'time_taken' => 10],
                ['question_id' => 2, 'time_taken' => 12],
            ] // Total time: 22 for 2 qs in exam_data, but method sums from all sessions' exam_data
        ]);

        // Session 2: Completed
        GameSession::factory()->create([
            'game_id' => $game->id,
            'status' => 'completed',
            'score' => 150,
            'questions_answered' => 12,
            'correct_answers' => 10,
            'exam_data' => [
                ['question_id' => 3, 'time_taken' => 8],
                ['question_id' => 4, 'time_taken' => 10],
                ['question_id' => 5, 'time_taken' => 15],
            ] // Total time: 33
        ]);

        // Session 3: In Progress (should be ignored by completedSessions query)
        GameSession::factory()->create(['game_id' => $game->id, 'status' => 'in_progress', 'score' => 50]);

        $game->updateStats();
        $game->refresh();

        $this->assertEquals(2, $game->stats['total_players']); // 2 completed sessions
        $this->assertEquals(2, $game->stats['total_sessions']);
        $this->assertEquals(125, $game->stats['average_score']); // (100 + 150) / 2
        // Total questions answered from completed sessions: 10 + 12 = 22
        $this->assertEquals(22, $game->stats['total_questions_answered']);
        // Total correct answers: 8 + 10 = 18
        $this->assertEquals(18, $game->stats['total_correct_answers']);
        // Total time from exam_data: (10+12) + (8+10+15) = 22 + 33 = 55
        // Average time: 55 / 22 = 2.5
        $this->assertEquals(2.5, $game->stats['average_time_per_question']);
        
        // Completion rate: (2 completed / 3 total sessions for this game) * 100, rounded
        $this->assertEquals(round((2/3)*100) . '%', $game->stats['completion_rate']);
    }

    // TODO: Add tests to ensure 100% coverage for the Game model
    // Consider testing relationships, scopes, accessors, mutators, etc.
} 