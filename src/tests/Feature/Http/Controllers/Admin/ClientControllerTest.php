<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\Test;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\Log;

class ClientControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $manager;
    protected User $corrector;
    protected User $general;
    protected Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->manager = User::factory()->create(['role' => 'manager', 'is_active' => true]);
        $this->corrector = User::factory()->create(['role' => 'corrector', 'is_active' => true]);
        $this->general = User::factory()->create(['role' => 'general', 'is_active' => true]);

        $this->client = Client::factory()->create();
    }

    protected function getCsrfToken()
    {
        // Make a GET request to a known route to ensure the session starts and a token is generated.
        $response = $this->actingAs($this->manager, 'admin') // or any authenticated user
            ->get(route('admin.dashboard')); // Use admin dashboard route
        
        return csrf_token(); // Fetches token from the session
    }

    protected function withCsrfToken($method, $route, $data = [], $user = null)
    {
        $token = $this->getCsrfToken();
        $user = $user ?? $this->manager; // Default to manager if no user specified
        
        return $this->actingAs($user, 'admin')
            ->withSession(['_token' => $token]) // Prime the session with the token
            ->withHeader('X-CSRF-TOKEN', $token) // Set the header
            ->$method($route, array_merge($data, ['_token' => $token])); // Also include in POST data
    }

    // --- Authorization Tests for Index ---
    #[Test]
    public function manager_can_view_clients_list(): void
    {
        $response = $this->withCsrfToken('get', route('admin.clients.index'), [], $this->manager); // Temporarily disable CSRF

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page->component('Admin/Clients/Index'));
    }

    #[Test]
    public function corrector_cannot_view_clients_list(): void
    {
        $response = $this->actingAs($this->corrector, 'admin')
                 ->get(route('admin.clients.index'));

        $response->assertRedirect(route('admin.dashboard'));
        $response->assertSessionHas('error', "You don't have permission to access this page. Required role(s): manager");
    }

    #[Test]
    public function general_admin_cannot_view_clients_list(): void
    {
        $response = $this->actingAs($this->general, 'admin')
                 ->get(route('admin.clients.index'));
        
        $response->assertRedirect(route('admin.dashboard'));
        $response->assertSessionHas('error', "You don't have permission to access this page. Required role(s): manager");
    }

    // --- Create/Store Tests ---
    #[Test]
    public function manager_can_access_create_client_page(): void
    {
        $response = $this->actingAs($this->manager, 'admin')
                 ->get(route('admin.clients.create'));

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page->component('Admin/Clients/Create'));
    }

    #[Test]
    public function manager_can_store_new_client(): void
    {
        $clientData = [
            'name' => 'New Client Name',
            'email' => 'newclient@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'is_active' => true,
        ];

        $response = $this->withCsrfToken('post', route('admin.clients.store'), $clientData, $this->manager);

        $response->assertRedirect(route('admin.clients.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('clients', ['email' => 'newclient@example.com', 'is_active' => true]);
    }

    #[Test]
    public function manager_cannot_store_client_with_invalid_data(): void
    {
        $response = $this->withCsrfToken('post', route('admin.clients.store'), ['email' => 'not-an-email'], $this->manager);
        $response->assertSessionHasErrors('email');
        $response->assertSessionHasErrors('name'); // Assuming name is required
    }
    
    #[Test]
    public function corrector_cannot_store_new_client(): void
    {
        $clientData = Client::factory()->make()->toArray();
        $clientData['password'] = 'password';
        $clientData['password_confirmation'] = 'password';
        $response = $this->withCsrfToken('post', route('admin.clients.store'), $clientData, $this->corrector);
        $response->assertRedirect(route('admin.dashboard'));
        $response->assertSessionHas('error', "You don't have permission to access this page. Required role(s): manager");
    }

    // --- Edit/Update Tests ---
    #[Test]
    public function manager_can_access_edit_client_page(): void
    {
        $response = $this->actingAs($this->manager, 'admin')
                 ->get(route('admin.clients.edit', $this->client));

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Clients/Edit')
            ->has('client')
            ->where('client.id', $this->client->id)
        );
    }

    #[Test]
    public function manager_can_update_client(): void
    {
        $updateData = [
            'name' => 'Updated Client Name',
            'email' => $this->client->email, // Keep email same or provide a new valid one
            'is_active' => false,
        ];

        $response = $this->withCsrfToken('put', route('admin.clients.update', $this->client), $updateData, $this->manager);

        $response->assertRedirect(route('admin.clients.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('clients', ['id' => $this->client->id, 'name' => 'Updated Client Name', 'is_active' => false]);
    }
    
    #[Test]
    public function corrector_cannot_update_client(): void
    {
        $updateData = ['name' => 'Attempted Update'];
        $response = $this->withCsrfToken('put', route('admin.clients.update', $this->client), $updateData, $this->corrector);
        $response->assertRedirect(route('admin.dashboard'));
        $response->assertSessionHas('error', "You don't have permission to access this page. Required role(s): manager");
    }

    // --- Destroy Tests ---
    #[Test]
    public function manager_can_delete_client(): void
    {
        $response = $this->withCsrfToken('delete', route('admin.clients.destroy', $this->client), [], $this->manager);

        $response->assertRedirect(route('admin.clients.index'));
        $response->assertSessionHas('success');
        $this->assertSoftDeleted('clients', ['id' => $this->client->id]); // Assuming SoftDeletes
    }
    
    #[Test]
    public function corrector_cannot_delete_client(): void
    {
        $response = $this->withCsrfToken('delete', route('admin.clients.destroy', $this->client), [], $this->corrector);
        $response->assertRedirect(route('admin.dashboard'));
        $response->assertSessionHas('error', "You don't have permission to access this page. Required role(s): manager");
        $this->assertNotSoftDeleted('clients', ['id' => $this->client->id]);
    }
    
    // --- Toggle Active Status (Example of a custom action, if applicable) ---
    #[Test]
    public function manager_can_toggle_client_active_status(): void
    {
        $initialStatus = $this->client->is_active;

        // First, visit a known page to establish the "back" context
        $this->actingAs($this->manager, 'admin')->get(route('admin.clients.index'));
        
        $response = $this->withCsrfToken('post', route('admin.clients.toggle-active', $this->client), [
            'is_active' => !$initialStatus
        ], $this->manager);

        $response->assertRedirect(route('admin.clients.index')); // Should now go back to the index page
        $response->assertSessionHas('success');
        $this->client->refresh();
        $this->assertEquals(!$initialStatus, $this->client->is_active);
    }

} 