<?php

namespace Tests\Feature\Http\Controllers\Client;

use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\Test;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_display_login_page()
    {
        $response = $this->get(route('login'));
        
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Client/Auth/Login')
        );
    }

    #[Test]
    public function it_can_display_register_page()
    {
        $response = $this->get(route('register'));
        
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Client/Auth/Register')
        );
    }

    #[Test]
    public function it_can_register_new_user()
    {
        $clientData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->post(route('register'), [
            '_token' => csrf_token(), 
            ...$clientData
        ]);

        $response->assertRedirect(route('profile'));
        $this->assertAuthenticated('client');
        
        $this->assertDatabaseHas('clients', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }

    #[Test]
    public function it_validates_registration_data()
    {
        $response = $this->post(route('register'), [
            'name' => '',
            'email' => 'invalid-email',
            'password' => 'short',
            'password_confirmation' => 'different',
            '_token' => csrf_token(),
        ]);

        $response->assertSessionHasErrors(['name', 'email', 'password']);
        $this->assertGuest();
    }

    #[Test]
    public function it_can_login_existing_user()
    {
        $client = Client::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post(route('login'), [
            'email' => 'test@example.com',
            'password' => 'password123',
            '_token' => csrf_token(),
        ]);

        $response->assertRedirect(route('profile'));
        $this->assertAuthenticated('client');
    }

    #[Test]
    public function it_validates_login_credentials()
    {
        $client = Client::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post(route('login'), [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
            '_token' => csrf_token(),
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    #[Test]
    public function it_can_logout_user()
    {
        $client = Client::factory()->create();
        
        $response = $this->actingAs($client, 'client')
            ->post(route('logout'),[
                '_token' => csrf_token(),
            ]);

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    #[Test]
    public function it_prevents_authenticated_user_from_accessing_login_page()
    {
        $client = Client::factory()->create();
        
        $response = $this->actingAs($client, 'client')
            ->get(route('login'));

        $response->assertRedirect(route('profile'));
    }

    #[Test]
    public function it_prevents_authenticated_user_from_accessing_register_page()
    {
        $client = Client::factory()->create();
        
        $response = $this->actingAs($client, 'client')
            ->get(route('register'));

        $response->assertRedirect(route('profile'));
    }

} 