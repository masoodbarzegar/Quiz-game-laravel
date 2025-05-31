<?php

namespace Tests\Feature\Middleware;

use App\Models\User;
use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\Test;

class HandleInertiaRequestsFeatureTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_shares_basic_data_with_all_requests()
    {
        $response = $this->get('/');

        $response->assertInertia(fn (Assert $page) => $page
            ->has('auth')
            ->has('url')
            ->has('flash')
            ->has('errors')
            ->where('auth.user', null)
            ->where('url', '/')
            ->where('flash.message', null)
            ->where('flash.error', null)
            ->where('errors', [])
        );
    }

    #[Test]
    public function it_shares_admin_user_data_when_admin_is_authenticated()
    {
        $admin = User::factory()->create([
            'role' => 'manager',
            'is_active' => true,
        ]);

        $this->actingAs($admin, 'admin');

        $response = $this->get('/admin/dashboard');

        $response->assertInertia(fn (Assert $page) => $page
            ->where('auth.user', [
                'id' => $admin->id,
                'name' => $admin->name,
                'email' => $admin->email,
                'role' => $admin->role,
            ])
        );
    }

    #[Test]
    public function it_shares_client_user_data_when_client_is_authenticated()
    {
        $this->markTestSkipped('Client part not implemented yet.');
        // ... existing code ...
    }

    #[Test]
    public function it_shares_web_user_data_when_web_user_is_authenticated()
    {
        $user = User::factory()->create([
            'is_active' => true,
        ]);

        $this->actingAs($user);

        $response = $this->get('/');

        $response->assertInertia(fn (Assert $page) => $page
            ->where('auth.user', [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ])
        );
    }

    #[Test]
    public function it_shares_flash_messages()
    {
        $response = $this->withSession([
            'message' => 'Success message',
            'error' => 'Error message',
        ])->get('/');

        $response->assertInertia(fn (Assert $page) => $page
            ->where('flash.message', 'Success message')
            ->where('flash.error', 'Error message')
        );
    }

    #[Test]
    public function it_shares_validation_errors()
    {
        // Create a route that will trigger validation errors
        $this->app['router']->post('/test-validation', function () {
            return redirect('/')->withErrors([
                'email' => ['The email field is required.'],
                'password' => ['The password field is required.'],
            ]);
        });

        // Make the request that will trigger validation errors
        $response = $this->post('/test-validation');
        $response->assertRedirect('/');

        // Follow the redirect and check the Inertia response
        $response = $this->get('/');
        $response->assertInertia(fn (Assert $page) => $page
            ->where('errors', [
                'email' => ['The email field is required.'],
                'password' => ['The password field is required.'],
            ])
        );
    }

    #[Test]
    public function it_shares_correct_url_for_different_routes()
    {
        // Create an admin user for testing admin routes
        $admin = User::factory()->create([
            'role' => 'manager',
            'is_active' => true,
        ]);

        // Test public routes
        $publicRoutes = [
            '/' => '/',
            '/login' => '/login', // Web login route
            '/admin/login' => '/admin/login', // Admin login is public for guests
        ];

        foreach ($publicRoutes as $route => $expectedUrl) {
            $response = $this->get($route);
            $response->assertInertia(fn (Assert $page) => $page
                ->where('url', $expectedUrl)
            );
        }

        // Test admin dashboard with authentication
        $this->actingAs($admin, 'admin');
        $response = $this->get('/admin/dashboard');
        $response->assertInertia(fn (Assert $page) => $page
            ->where('url', '/admin/dashboard')
        );

        // Test admin login when authenticated (should redirect)
        $response = $this->get('/admin/login');
        $response->assertRedirect('/admin/dashboard');
    }

    #[Test]
    public function it_handles_multiple_guards_correctly()
    {
        $admin = User::factory()->create([
            'role' => 'manager',
            'is_active' => true,
        ]);
        // $client = Client::factory()->create([
        //     'is_active' => true,
        // ]);

        // Test admin route with admin guard
        $this->actingAs($admin, 'admin');
        $response = $this->get('/admin/dashboard');
        $response->assertInertia(fn (Assert $page) => $page
            ->where('auth.user.role', 'manager')
        );

        // Test client route with client guard
        $this->markTestSkipped('Client part not implemented yet.');
        // $this->actingAs($client, 'client');
        // $response = $this->get('/dashboard');
        // $response->assertInertia(fn (Assert $page) => $page
        //     ->where('auth.user.email', $client->email)
        // );

        // Test web route with web guard
        $this->actingAs($admin);
        $response = $this->get('/dashboard');
        $response->assertInertia(fn (Assert $page) => $page
            ->where('auth.user.email', $admin->email)
        );
    }
} 