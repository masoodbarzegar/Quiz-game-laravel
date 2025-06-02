<?php

namespace Tests\Unit\Models;

use App\Models\Client;
use App\Models\GameSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ClientTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_be_created_using_factory()
    {
        $client = Client::factory()->create();

        $this->assertDatabaseHas('clients', [
            'id' => $client->id,
        ]);
    }

    #[Test]
    public function client_has_many_game_sessions(): void
    {
        $client = Client::factory()->create();
        GameSession::factory()->count(3)->create(['client_id' => $client->id]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $client->gameSessions);
        $this->assertCount(3, $client->gameSessions);
        $this->assertInstanceOf(GameSession::class, $client->gameSessions->first());
    }

    #[Test]
    public function completed_games_returns_only_completed_sessions(): void
    {
        $client = Client::factory()->create();
        GameSession::factory()->create(['client_id' => $client->id, 'status' => 'completed']);
        GameSession::factory()->create(['client_id' => $client->id, 'status' => 'in_progress']);
        GameSession::factory()->create(['client_id' => $client->id, 'status' => 'completed']);

        $completedGames = $client->completedGames()->get();
        $this->assertCount(2, $completedGames);
        foreach ($completedGames as $session) {
            $this->assertEquals('completed', $session->status);
        }
    }

    #[Test]
    public function in_progress_games_returns_only_in_progress_sessions(): void
    {
        $client = Client::factory()->create();
        GameSession::factory()->create(['client_id' => $client->id, 'status' => 'completed']);
        GameSession::factory()->create(['client_id' => $client->id, 'status' => 'in_progress']);
        GameSession::factory()->create(['client_id' => $client->id, 'status' => 'in_progress']);

        $inProgressGames = $client->inProgressGames()->get();
        $this->assertCount(2, $inProgressGames);
        foreach ($inProgressGames as $session) {
            $this->assertEquals('in_progress', $session->status);
        }
    }

    // Add more tests here for relationships, scopes, accessors, mutators, etc.
} 