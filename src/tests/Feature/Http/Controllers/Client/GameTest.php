<?php

namespace Tests\Feature\Http\Controllers\Client;

use App\Models\Client;
use App\Models\Game;
use App\Models\GameSession;
use App\Models\Question;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\Test;

class GameTest extends TestCase
{
    use RefreshDatabase;

    protected $client;
    protected $game;
    protected $questions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = Client::factory()->create();
        $this->game = Game::factory()->create([
            'name' => 'Test Game',
            'slug' => 'test-game',
            'difficulty' => 'medium',
            'time_limit' => 30,
            'question_count' => 21,
            'points_per_question' => 5,
            'skip_limit' => 3,
        ]);

        // Create questions for the game
        Question::factory()->count(10)->create([
            'difficulty_level' => 'easy',
        ]);
        Question::factory()->count(6)->create([
            'difficulty_level' => 'medium',
        ]);
        Question::factory()->count(5)->create([
            'difficulty_level' => 'hard',
        ]);

        $this->questions = Question::get();

    }

    #[Test]
    public function it_can_display_available_games()
    {
        $response = $this->actingAs($this->client, 'client')
            ->get(route('games.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Games/Index')
            ->has('games', 1)
            ->has('games.0', fn (Assert $game) => $game
                ->where('name', 'Test Game')
                ->where('slug', 'test-game')
                ->where('difficulty', 'medium')
                ->etc()
            )
        );
    }

    #[Test]
    public function it_can_display_game_details()
    {
        $response = $this->actingAs($this->client, 'client')
            ->get(route('games.show', $this->game->slug));

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Games/Show')
            ->has('game', fn (Assert $game) => $game
                ->where('name', 'Test Game')
                ->where('slug', 'test-game')
                ->where('difficulty', 'medium')
                ->where('time_limit', 30)
                ->where('question_count', 21)
                ->etc()
            )
        );
    }

    #[Test]
    public function it_can_start_new_game_session()
    {
        $response = $this->actingAs($this->client, 'client');
        $response = $this->followingRedirects()
            ->get(route('play.start', $this->game->slug));

        $response->assertStatus(200);

        $this->assertDatabaseHas('game_sessions', [
            'client_id' => $this->client->id,
            'game_id' => $this->game->id,
            'status' => 'in_progress',
        ]);
    }

    #[Test]
    public function it_redirects_to_active_session_if_one_exists_on_start(): void
    {
        $client = Client::factory()->create();
        $game = Game::factory()->create();
        $activeSession = GameSession::factory()->create([
            'client_id' => $client->id,
            'game_id' => $game->id,
            'status' => 'in_progress'
        ]);

        $response = $this->actingAs($client, 'client')->get(route('play.start', $game));

        $response->assertRedirect(route('play.game', [$game->slug, $activeSession->id]));
    }

    #[Test]
    public function it_can_display_game_play_page()
    {
        $session = GameSession::factory()->create([
            'client_id' => $this->client->id,
            'game_id' => $this->game->id,
            'status' => 'in_progress',
        ]);

        $response = $this->actingAs($this->client, 'client')
            ->get(route('play.game', [$this->game->slug, $session->id]));

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Client/Play/Play')
            ->has('game', fn (Assert $game) => $game
                ->where('name', 'Test Game')
                ->where('slug', 'test-game')
                ->etc()
            )
            ->has('session', fn (Assert $sessionData ) => $sessionData 
                ->where('id', $session->id)
                ->where('status', 'in_progress')
                ->etc()
            )
        );
    }

    #[Test]
    public function it_can_end_game_session()
    {
        $session = GameSession::factory()->create([
            'client_id' => $this->client->id,
            'game_id' => $this->game->id,
            'status' => 'in_progress',
        ]);

        $answers = $this->questions->map(fn ($q) => [
            'question_id' => $q->id,
            'selected_answer' => '1', // must be string
            'is_correct' => true,
            'time_taken' => 10,
        ])->toArray();

        $postData = [
            '_token' => csrf_token(),
            'answers' => $answers,
            'final_score' => 3,
            'lives_remaining' => 3,
            'time_remaining' => 25,
            'total_time_taken' => 5,
            'end_reason' => 'user_exit',
            'questions_answered' => 1,
            'correct_answers' => 1,
            'incorrect_answers' => 0
        ];

        $response = $this->actingAs($this->client, 'client')
            ->post(route('play.end', [$this->game->slug, $session->id]), $postData);
      
        $response->assertStatus(302); // redirect to result page
        $response->assertRedirect(route('play.result', [$this->game->slug, $session->id]));
        
        $this->assertDatabaseHas('game_sessions', [
            'id' => $session->id,
            'status' => 'completed',
            'score' => 3,
        ]);
    }

    #[Test]
    public function it_can_display_game_results()
    {
        $session = GameSession::factory()->create([
            'client_id' => $this->client->id,
            'game_id' => $this->game->id,
            'status' => 'completed',
            'score' => 85,
            'correct_answers' => 17,
            'questions_answered' => 20,
            'total_time_taken' => 1200,
            'end_reason' => 'completed',
        ]);

        $response = $this->actingAs($this->client, 'client')
            ->get(route('play.result', [$this->game->slug, $session->id]));

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Client/Play/Result')
            ->has('game', fn (Assert $game) => $game
                ->where('name', 'Test Game')
                ->where('slug', 'test-game')
                ->where('difficulty', 'medium')
                ->where('time_limit', 30)
                ->where('question_count', 21)
                ->where('points_per_question', 5)
                ->etc()
            )

            ->has('session', fn (Assert $session) => $session
                ->where('score', 85)
                ->where('correct_answers', 17)
                ->where('questions_answered', 20)
                ->where('end_reason', 'completed')
                ->etc()
            )
        );
    }

    #[Test]
    public function it_prevents_accessing_game_without_authentication()
    {

        $response = $this->get(route('play.start', $this->game->slug));
        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function it_prevents_accessing_other_users_game_session()
    {
        $otherClient = Client::factory()->create();
        $session = GameSession::factory()->create([
            'client_id' => $otherClient->id,
            'game_id' => $this->game->id,
            'status' => 'in_progress',
        ]);

        $response = $this->actingAs($this->client, 'client')
            ->get(route('play.game', [$this->game->slug, $session->id]));

        $response->assertStatus(403);
    }

    #[Test]
    public function it_validates_game_session_ownership_for_results()
    {
        $otherClient = Client::factory()->create();
        $session = GameSession::factory()->create([
            'client_id' => $otherClient->id,
            'game_id' => $this->game->id,
            'status' => 'completed',
        ]);

        $response = $this->actingAs($this->client, 'client')
            ->get(route('play.result', [$this->game->slug, $session->id]));
        
        $response->assertStatus(403);
    }

    #[Test]
    public function end_game_action_requires_valid_data()
    {
        $session = GameSession::factory()->create([
            'client_id' => $this->client->id,
            'game_id' => $this->game->id,
            'status' => 'in_progress',
        ]);

        // Test with missing main fields
        $responseMissing = $this->actingAs($this->client, 'client')
            ->post(route('play.end', [$this->game->slug, $session->id]), [
                '_token' => csrf_token(),
                // Missing: answers, final_score, lives_remaining, time_remaining, etc.
            ]);
        $responseMissing->assertSessionHasErrors([
            'answers', 'final_score', 'lives_remaining', 'time_remaining', 
            'total_time_taken', 'end_reason', 'questions_answered', 
            'correct_answers', 'incorrect_answers'
        ]);

        // Test with invalid answers array structure
        $question = Question::factory()->create();
        $invalidAnswersPayload = [
            '_token' => csrf_token(),
            'answers' => [
                [
                    // Missing question_id
                    'selected_answer' => 'some answer',
                    'is_correct' => true,
                    'time_taken' => 10,
                ],
                [
                    'question_id' => $question->id,
                    'selected_answer' => 'another answer',
                    'is_correct' => 'not-a-boolean', // Invalid type
                    'time_taken' => 'not-an-integer', // Invalid type
                ]
            ],
            // Provide other required fields to isolate answers validation
            'final_score' => 0,
            'lives_remaining' => 1,
            'time_remaining' => 100,
            'total_time_taken' => 20,
            'end_reason' => 'test',
            'questions_answered' => 2,
            'correct_answers' => 1,
            'incorrect_answers' => 1,
        ];

        $responseInvalidAnswers = $this->actingAs($this->client, 'client')
            ->post(route('play.end', [$this->game->slug, $session->id]), $invalidAnswersPayload);
        
        $responseInvalidAnswers->assertSessionHasErrors([
            'answers.0.question_id', 
            'answers.1.is_correct',
            'answers.1.time_taken'
        ]);
    }
} 