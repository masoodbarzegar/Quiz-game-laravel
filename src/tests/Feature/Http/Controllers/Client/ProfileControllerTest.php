<?php

namespace Tests\Feature\Http\Controllers\Client;

use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
// use Inertia\Testing\AssertableInertia as Assert; // Uncomment if using Inertia assertions

class ProfileControllerTest extends TestCase
{
    use RefreshDatabase;

    protected Client $clientUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->clientUser = Client::factory()->create();
    }

    #[Test] 
    public function client_can_view_their_profile_when_authenticated()
    {
        $this->actingAs($this->clientUser, 'client');

        // Assuming route name is 'client.profile.show' or similar
        $response = $this->get(route('profile')); // Or whatever your route is for viewing/editing profile

        $response->assertStatus(200);
        // $response->assertInertia(fn (Assert $page) => $page->component('Client/Profile/Edit'));
    }

    #[Test]
    public function guest_is_redirected_when_trying_to_view_profile()
    {
        $response = $this->get(route('profile'));

        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function client_can_update_their_profile()
    {
        $this->actingAs($this->clientUser, 'client');

        $updateData = [
            'name' => 'New Name',
            'email' => 'newemail@example.com',
            '_token' => csrf_token(),
        ];

        $response = $this->put(route('profile.update'), $updateData);

        $response->assertRedirect(); 
        $this->clientUser->refresh(); // Refresh the model instance
        $this->assertDatabaseHas('clients', [
            'id' => $this->clientUser->id,
            'name' => 'New Name',
            'email' => 'newemail@example.com',
        ]);
    }

    // TODO: Add tests for other actions in ProfileController (e.g., password update if separate)
    // Test validation errors for profile updates.
} 