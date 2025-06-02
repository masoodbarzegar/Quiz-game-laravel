<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Client;
use App\Models\Game;
use App\Models\GameSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\Test;
use Illuminate\Support\Facades\Log;

class GameReportControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $manager;
    protected User $corrector;
    protected User $general;
    protected Game $game;
    protected Client $client;
    protected GameSession $gameSession;

    protected function setUp(): void
    {
        parent::setUp();

        $this->manager = User::factory()->create(['role' => 'manager', 'is_active' => true]);
        $this->corrector = User::factory()->create(['role' => 'corrector', 'is_active' => true]);
        $this->general = User::factory()->create(['role' => 'general', 'is_active' => true]);

        $this->game = Game::factory()->create();
        $this->client = Client::factory()->create();
        $this->gameSession = GameSession::factory()->create([
            'game_id' => $this->game->id,
            'client_id' => $this->client->id,
            'status' => 'completed',
            'score' => 100,
        ]);
    }

    // --- Authorization Tests for Index ---
    #[Test]
    public function manager_can_view_game_reports_index(): void
    {
        $response = $this->actingAs($this->manager, 'admin')->get(route('admin.game-reports.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/GameReports/Index')
        );
    }

    #[Test]
    public function corrector_cannot_view_game_reports_index(): void
    {
        $response = $this->actingAs($this->corrector, 'admin')->get(route('admin.game-reports.index'));
        $response->assertRedirect(route('admin.dashboard'));
        $response->assertSessionHas('error', "You don't have permission to access this page. Required role(s): manager");

    }

    #[Test]
    public function general_admin_cannot_view_game_reports_index(): void
    {
        $response = $this->actingAs($this->general, 'admin')->get(route('admin.game-reports.index'));
        $response->assertRedirect(route('admin.dashboard'));
        $response->assertSessionHas('error', "You don't have permission to access this page. Required role(s): manager");
    }

   
    #[Test]
    public function manager_can_filter_game_reports_by_client(): void
    {
        GameSession::factory()->count(2)->create(['client_id' => $this->client->id, 'status' => 'completed']);
        $otherClient = Client::factory()->create();
        GameSession::factory()->count(3)->create(['client_id' => $otherClient->id, 'status' => 'completed']);
        
        // Including the one from setUp(), total 3 for $this->client
        $response = $this->actingAs($this->manager, 'admin')->get(route('admin.game-reports.index', ['client_id' => $this->client->id]));

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->has('gameSessions.data', 3)
        );
    }

    // --- Unauthenticated Access ---
    #[Test]
    public function unauthenticated_user_cannot_access_game_reports(): void
    {
        $this->get(route('admin.game-reports.index'))->assertRedirect(route('admin.login'));
    }
} 