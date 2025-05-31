<?php

namespace Tests\Unit\Models;

use App\Models\GameSession;
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

    // Add more tests here for relationships, scopes, accessors, mutators, etc.
} 