<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Models\User;
use Tests\TestCase;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Attributes\Test;
use Inertia\Testing\AssertableInertia as Assert;
use Mockery;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

class AuthTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Clear any existing auth state
        foreach (['web', 'admin', 'client'] as $guard) {
            Auth::guard($guard)->logout();
        }
        session()->flush();
        
        // Set up fresh spy for each test
        Log::spy();

        // Verify middleware is properly registered
        $this->assertTrue(
            in_array('web', Route::getRoutes()->getByName('admin.login')->middleware()),
            'Web middleware not found on admin login route'
        );
    }

    protected function tearDown(): void
    {
        // Clear all auth guards
        foreach (['web', 'admin', 'client'] as $guard) {
            Auth::guard($guard)->logout();
        }
        
        // Clear session
        session()->flush();
        
        parent::tearDown();
    }

    #[Test]
    public function admin_can_view_login_page()
    {
        // Expect Inertia middleware log for admin route
        Log::shouldReceive('info')
            ->with('Inertia share middleware:', Mockery::on(function ($args) {
                return $args['is_admin_route'] === true;
            }))
            ->once();

        $response = $this->get(route('admin.login'));

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $assert) => $assert
            ->component('Admin/Auth/Login')
            ->where('auth.user', null)  // Verify user is not authenticated
            ->where('url', '/admin/login')  // Verify correct URL is shared
        );
    }

    #[Test]
    public function admin_can_login_with_correct_credentials()
    {
        $admin = User::factory()->create([
            'email' => 'manager@quizgame.com',
            'password' => bcrypt('manager123'),
            'role' => 'manager',
            'is_active' => true,
        ]);

        // First get the login page to get the CSRF token
        Log::shouldReceive('info')
            ->with('Inertia share middleware:', Mockery::on(function ($args) {
                return $args['is_admin_route'] === true;
            }))
            ->once();
        $this->get(route('admin.login'));
        
        // Set up logging expectations for login attempt
        Log::shouldReceive('info')
            ->with('Attempting admin login:', Mockery::on(function ($args) {
                return $args['email'] === 'manager@quizgame.com';
            }))
            ->once();

        Log::shouldReceive('info')
            ->with('Admin login successful:', Mockery::on(function ($args) use ($admin) {
                return $args['user']['id'] === $admin->id;
            }))
            ->once();

        Log::shouldReceive('info')
            ->with('Inertia share middleware:', Mockery::on(function ($args) {
                return $args['is_admin_route'] === true;
            }))
            ->once();

        $response = $this->post(route('admin.login'), [
            'email' => 'manager@quizgame.com',
            'password' => 'manager123',
            '_token' => csrf_token(),
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticated('admin');
        
        // Verify session has user data
        $this->assertNotNull(session('user'));
        $this->assertEquals($admin->id, session('user')['id']);
        $this->assertEquals($admin->name, session('user')['name']);
        $this->assertEquals($admin->role, session('user')['role']);
    }

    #[Test]
    public function admin_cannot_login_with_incorrect_credentials()
    {
        $admin = User::factory()->create([
            'email' => 'manager@quizgame.com',
            'password' => bcrypt('manager123'),
            'role' => 'manager',
            'is_active' => true,
        ]);

        // First get the login page to get the CSRF token
        Log::shouldReceive('info')
            ->with('Inertia share middleware:', Mockery::on(function ($args) {
                return $args['is_admin_route'] === true;
            }))
            ->once();
        $this->get(route('admin.login'));

        // Set up logging expectations for login attempt
        Log::shouldReceive('info')
            ->with('Attempting admin login:', Mockery::on(function ($args) {
                return $args['email'] === 'manager@quizgame.com';
            }))
            ->once();

        Log::shouldReceive('warning')
            ->with('Admin login failed:', Mockery::on(function ($args) {
                return $args['email'] === 'manager@quizgame.com';
            }))
            ->once();

        Log::shouldReceive('info')
            ->with('Inertia share middleware:', Mockery::on(function ($args) {
                return $args['is_admin_route'] === true;
            }))
            ->once();

        $response = $this->post(route('admin.login'), [
            'email' => 'manager@quizgame.com',
            'password' => 'wrong-password',
            '_token' => csrf_token(),
        ]);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Auth/Login')
            ->where('errors.email', 'The provided credentials do not match our records.')
            ->where('email', 'manager@quizgame.com')
        );
        
        $this->assertGuest('admin');
    }

    #[Test]
    public function inactive_admin_cannot_login()
    {
        $admin = User::factory()->create([
            'email' => 'inactive@quizgame.com',
            'password' => bcrypt('password123'),
            'role' => 'manager',
            'is_active' => false,
        ]);

        // First get the login page to get the CSRF token
        Log::shouldReceive('info')
            ->with('Inertia share middleware:', Mockery::on(function ($args) {
                return $args['is_admin_route'] === true;
            }))
            ->once();
        $this->get(route('admin.login'));

        // Set up logging expectations for login attempt
        Log::shouldReceive('info')
            ->with('Attempting admin login:', Mockery::on(function ($args) {
                return $args['email'] === 'inactive@quizgame.com';
            }))
            ->once();

        Log::shouldReceive('warning')
            ->with('Inactive admin login attempt:', Mockery::on(function ($args) use ($admin) {
                return $args['email'] === 'inactive@quizgame.com'
                    && $args['user_id'] === $admin->id;
            }))
            ->once();

        Log::shouldReceive('info')
            ->with('Inertia share middleware:', Mockery::on(function ($args) {
                return $args['is_admin_route'] === true;
            }))
            ->once();

        $response = $this->post(route('admin.login'), [
            'email' => 'inactive@quizgame.com',
            'password' => 'password123',
            '_token' => csrf_token(),
        ]);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Auth/Login')
            ->where('errors.email', 'This account is inactive. Please contact an administrator.')
            ->where('email', 'inactive@quizgame.com')
        );
        
        $this->assertGuest('admin');
    }

    #[Test]
    public function admin_can_logout()
    {
        $admin = User::factory()->create([
            'role' => 'manager',
            'is_active' => true,
        ]);

        $this->actingAs($admin, 'admin');
        $this->assertAuthenticated('admin');

        // First get any page to get the CSRF token
        Log::shouldReceive('info')
            ->with('Inertia share middleware:', Mockery::on(function ($args) {
                return $args['is_admin_route'] === true;
            }))
            ->once();
        $this->get(route('admin.dashboard'));

        // Set up logging expectations for logout
        Log::shouldReceive('info')
            ->with('Admin logout:', Mockery::on(function ($args) use ($admin) {
                return $args['user']['id'] === $admin->id;
            }))
            ->once();

        Log::shouldReceive('info')
            ->with('Inertia share middleware:', Mockery::on(function ($args) {
                return $args['is_admin_route'] === true;
            }))
            ->once();

        $response = $this->post(route('admin.logout'), [
            '_token' => csrf_token(),
        ]);

        $response->assertRedirect(route('admin.login'));
        $this->assertGuest('admin');
        $this->assertNull(session('user'));
    }

    #[Test]
    public function authenticated_admin_cannot_access_login_page()
    {
        $admin = User::factory()->create([
            'role' => 'manager',
            'is_active' => true,
        ]);

        $this->actingAs($admin, 'admin');

        // Set up logging expectations
        Log::shouldReceive('info')
            ->with('Inertia share middleware:', Mockery::on(function ($args) {
                return $args['is_admin_route'] === true;
            }))
            ->once();

        $response = $this->get(route('admin.login'));

        $response->assertRedirect(route('admin.dashboard'));
    }

    #[Test]
    public function unauthenticated_admin_cannot_access_dashboard()
    {
        // Set up logging expectations for both possible paths
        // Either the Authenticate middleware logs the unauthenticated request
        // Or the HandleInertiaRequests middleware logs the admin route access
        Log::shouldReceive('info')
            ->with('Unauthenticated request:', Mockery::on(function ($args) {
                return $args['is_admin_route'] === true;
            }))
            ->once();

        // The Inertia middleware might or might not run depending on the middleware chain
        // So we don't make it a requirement
        Log::shouldReceive('info')
            ->with('Inertia share middleware:', Mockery::on(function ($args) {
                return $args['is_admin_route'] === true;
            }))
            ->zeroOrMoreTimes();

        $response = $this->get(route('admin.dashboard'));

        $response->assertRedirect(route('admin.login'));
        $this->assertGuest('admin');
    }
} 