<?php

namespace Tests\Unit\Models;

use App\Models\Client;
use App\Models\Game;
use App\Models\GameSession;
use App\Models\Question;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class GameSessionTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_be_created_using_factory()
    {
        $gameSession = GameSession::factory()->create();

        $this->assertDatabaseHas('game_sessions', [
            'id' => $gameSession->id,
        ]);
    }

    #[Test]
    public function game_session_belongs_to_a_client(): void
    {
        $client = Client::factory()->create();
        $gameSession = GameSession::factory()->create(['client_id' => $client->id]);

        $this->assertInstanceOf(Client::class, $gameSession->client);
        $this->assertEquals($client->id, $gameSession->client->id);
    }

    #[Test]
    public function game_session_belongs_to_a_game(): void
    {
        $game = Game::factory()->create();
        $gameSession = GameSession::factory()->create(['game_id' => $game->id]);

        $this->assertInstanceOf(Game::class, $gameSession->game);
        $this->assertEquals($game->id, $gameSession->game->id);
    }

    #[Test]
    public function is_active_returns_true_for_in_progress_status(): void
    {
        $gameSession = GameSession::factory()->make(['status' => 'in_progress']);
        $this->assertTrue($gameSession->isActive());
    }

    #[Test]
    public function is_active_returns_false_for_other_statuses(): void
    {
        $gameSessionCompleted = GameSession::factory()->make(['status' => 'completed']);
        $this->assertFalse($gameSessionCompleted->isActive());

        $gameSessionPaused = GameSession::factory()->make(['status' => 'paused']); // Assuming other statuses
        $this->assertFalse($gameSessionPaused->isActive());
    }

    #[Test]
    public function end_game_method_updates_status_reason_and_ended_at(): void
    {
        $gameSession = GameSession::factory()->create([
            'status' => 'in_progress',
            'ended_at' => null,
            'end_reason' => null,
        ]);
        $reason = 'timer';

        $this->assertNull($gameSession->ended_at);
        $this->assertNull($gameSession->end_reason);

        $gameSession->endGame($reason);
        $gameSession->refresh();

        $this->assertEquals('completed', $gameSession->status);
        $this->assertEquals($reason, $gameSession->end_reason);
        $this->assertNotNull($gameSession->ended_at);
    }

    #[Test]
    public function get_remaining_lives_calculates_correctly(): void
    {
        $gameSession = GameSession::factory()->make(['incorrect_answers' => 0]);
        $this->assertEquals(3, $gameSession->getRemainingLives());

        $gameSession->incorrect_answers = 1;
        $this->assertEquals(2, $gameSession->getRemainingLives());

        $gameSession->incorrect_answers = 3;
        $this->assertEquals(0, $gameSession->getRemainingLives());

        $gameSession->incorrect_answers = 4; // More than 3
        $this->assertEquals(-1, $gameSession->getRemainingLives()); // Or 0 depending on desired behavior
    }

    #[Test]
    public function has_lives_remaining_works_correctly(): void
    {
        $gameSession = GameSession::factory()->make();

        $gameSession->incorrect_answers = 0;
        $this->assertTrue($gameSession->hasLivesRemaining());

        $gameSession->incorrect_answers = 2;
        $this->assertTrue($gameSession->hasLivesRemaining());

        $gameSession->incorrect_answers = 3;
        $this->assertFalse($gameSession->hasLivesRemaining());

        $gameSession->incorrect_answers = 4;
        $this->assertFalse($gameSession->hasLivesRemaining());
    }

    #[Test]
    public function get_answers_attribute_returns_collection_of_objects(): void
    {
        $examData = [
            ['question_id' => 1, 'selected_answer' => 'A', 'is_correct' => true],
            ['question_id' => 2, 'selected_answer' => 'B', 'is_correct' => false],
        ];
        $gameSession = GameSession::factory()->make(['exam_data' => $examData]);

        $answers = $gameSession->answers;
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $answers);
        $this->assertCount(2, $answers);
        $this->assertIsObject($answers->first());
        $this->assertEquals(1, $answers->first()->question_id);
    }

    #[Test]
    public function get_answers_attribute_returns_empty_collection_if_exam_data_is_null(): void
    {
        $gameSession = GameSession::factory()->make(['exam_data' => null]);
        $answers = $gameSession->answers;
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $answers);
        $this->assertTrue($answers->isEmpty());
    }

    #[Test]
    public function record_answer_updates_session_correctly_for_correct_answer(): void
    {
        $gameSession = GameSession::factory()->create([
            'score' => 0,
            'correct_answers' => 0,
            'incorrect_answers' => 0,
            'questions_answered' => 0,
            'exam_data' => null,
        ]);
        $question = Question::factory()->create(['difficulty_level' => 'medium']); // 5 points for medium

        $gameSession->recordAnswer($question, 'Answer A', true, 10);

        $this->assertEquals(1, $gameSession->questions_answered);
        $this->assertEquals(1, $gameSession->correct_answers);
        $this->assertEquals(0, $gameSession->incorrect_answers);
        $this->assertEquals(5, $gameSession->score); // Medium difficulty = 5 points
        $this->assertCount(1, $gameSession->exam_data);
        $this->assertEquals($question->id, $gameSession->exam_data[0]['question_id']);
        $this->assertEquals('Answer A', $gameSession->exam_data[0]['selected_answer']);
        $this->assertTrue($gameSession->exam_data[0]['is_correct']);
        $this->assertEquals(5, $gameSession->exam_data[0]['points_earned']);
        $this->assertEquals('medium', $gameSession->exam_data[0]['difficulty_level']);
    }

    #[Test]
    public function record_answer_updates_session_correctly_for_incorrect_answer(): void
    {
        $gameSession = GameSession::factory()->create([
            'score' => 10,
            'correct_answers' => 1,
            'incorrect_answers' => 0,
            'questions_answered' => 1,
            'exam_data' => [['question_id' => 99, 'selected_answer' => 'Previous', 'is_correct' => true, 'time_taken' => 5, 'points_earned' => 10, 'difficulty_level' => 'hard']],
        ]);
        $question = Question::factory()->create(['difficulty_level' => 'easy']); // 0 points for incorrect

        $gameSession->recordAnswer($question, 'Answer B', false, 12);

        $this->assertEquals(2, $gameSession->questions_answered);
        $this->assertEquals(1, $gameSession->correct_answers); // Unchanged
        $this->assertEquals(1, $gameSession->incorrect_answers);
        $this->assertEquals(10, $gameSession->score); // Unchanged for incorrect answer
        $this->assertCount(2, $gameSession->exam_data);
        $this->assertEquals($question->id, $gameSession->exam_data[1]['question_id']);
        $this->assertFalse($gameSession->exam_data[1]['is_correct']);
        $this->assertEquals(0, $gameSession->exam_data[1]['points_earned']);
    }

    #[Test]
    public function record_answer_uses_provided_points_if_not_null(): void
    {
        $gameSession = GameSession::factory()->create([
            'score' => 0,
            'correct_answers' => 0,
            'incorrect_answers' => 0,
            'questions_answered' => 0,
            'exam_data' => null,
        ]);
        $question = Question::factory()->create(['difficulty_level' => 'hard']); // Normally 8 points
        $customPoints = 15;

        $gameSession->recordAnswer($question, 'Answer C', true, 8, $customPoints);

        $this->assertEquals(1, $gameSession->questions_answered);
        $this->assertEquals(1, $gameSession->correct_answers);
        $this->assertEquals($customPoints, $gameSession->score);
        $this->assertCount(1, $gameSession->exam_data);
        $this->assertEquals($customPoints, $gameSession->exam_data[0]['points_earned']);
    }
} 