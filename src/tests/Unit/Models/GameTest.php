<?php

namespace Tests\Unit\Models;

use App\Models\Game;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

    // TODO: Add tests to ensure 100% coverage for the Game model
    // Consider testing relationships, scopes, accessors, mutators, etc.
} 