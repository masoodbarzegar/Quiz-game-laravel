<?php

namespace Tests\Unit\Middleware;

use App\Http\Middleware\HandleInertiaRequests;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use Inertia\Inertia;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class HandleInertiaRequestsTest extends TestCase
{
    use RefreshDatabase;

    protected $middleware;

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
        
        // Create a test view for Inertia
        view()->addLocation(resource_path('views'));
        view()->addNamespace('app', resource_path('views'));
        
        $this->middleware = new HandleInertiaRequests();
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
    public function it_shares_user_data_when_authenticated(): void
    {
        $user = User::factory()->create([
            'role' => 'manager',
            'is_active' => true,
        ]);

        Auth::guard('admin')->login($user);
        session()->put('user', [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
        ]);

        $request = Request::create('/admin/dashboard', 'GET');
        $request->headers->set('X-Inertia', 'true');
        $request->headers->set('Accept', 'application/json');
        $request->setLaravelSession($this->app['session.store']);

        $next = function ($request) {
            $inertiaResponse = Inertia::render('Admin/Dashboard');
            return new Response(
                $inertiaResponse->toResponse($request)->getContent(),
                200,
                ['Content-Type' => 'application/json']
            );
        };

        $response = $this->middleware->handle($request, $next);
        
        // Debug the response
        $content = $response->getContent();
        Log::debug('Inertia Test Response', [
            'content' => $content,
            'content_type' => $response->headers->get('Content-Type'),
            'status' => $response->getStatusCode(),
            'headers' => $response->headers->all(),
            'raw_content' => bin2hex($content) // This will help us see any hidden characters
        ]);

        $this->assertJson($content);
        $page = json_decode($content, true);

        $this->assertEquals('Admin/Dashboard', $page['component']);
        $this->assertEquals([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
        ], $page['props']['auth']['user']);
    }

    #[Test]
    public function it_shares_null_user_data_when_not_authenticated(): void
    {
        $request = Request::create('/admin/login', 'GET');
        $request->headers->set('X-Inertia', 'true');
        $request->headers->set('Accept', 'application/json');
        $request->setLaravelSession($this->app['session.store']);

        $next = function ($request) {
            $inertiaResponse = Inertia::render('Admin/Auth/Login');
            return new Response(
                $inertiaResponse->toResponse($request)->getContent(),
                200,
                ['Content-Type' => 'application/json']
            );
        };

        $response = $this->middleware->handle($request, $next);

        $this->assertJson($response->getContent());
        $page = json_decode($response->getContent(), true);

        $this->assertEquals('Admin/Auth/Login', $page['component']);
        $this->assertNull($page['props']['auth']['user']);
    }

    #[Test]
    public function it_shares_flash_messages(): void
    {
        $user = User::factory()->create([
            'role' => 'manager',
            'is_active' => true,
        ]);

        Auth::guard('admin')->login($user);
        session()->put('user', [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
        ]);
        session()->put('message', 'Test flash message');
        session()->put('error', 'Test error message');

        $request = Request::create('/admin/dashboard', 'GET');
        $request->headers->set('X-Inertia', 'true');
        $request->headers->set('Accept', 'application/json');
        $request->setLaravelSession($this->app['session.store']);

        $next = function ($request) {
            $inertiaResponse = Inertia::render('Admin/Dashboard');
            return new Response(
                $inertiaResponse->toResponse($request)->getContent(),
                200,
                ['Content-Type' => 'application/json']
            );
        };

        $response = $this->middleware->handle($request, $next);

        $this->assertJson($response->getContent());
        $page = json_decode($response->getContent(), true);

        $this->assertEquals('Admin/Dashboard', $page['component']);
        $this->assertEquals('Test flash message', $page['props']['flash']['message']);
        $this->assertEquals('Test error message', $page['props']['flash']['error']);
    }

    #[Test]
    public function it_handles_errors_correctly(): void
    {
        $user = User::factory()->create([
            'role' => 'manager',
            'is_active' => true,
        ]);

        Auth::guard('admin')->login($user);
        session()->put('user', [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
        ]);

        $errors = new ViewErrorBag();
        $errors->put('default', new MessageBag([
            'email' => ['The email field is required.'],
            'password' => ['The password field is required.'],
        ]));
        session()->put('errors', $errors);

        $request = Request::create('/admin/dashboard', 'GET');
        $request->headers->set('X-Inertia', 'true');
        $request->headers->set('Accept', 'application/json');
        $request->setLaravelSession($this->app['session.store']);

        $next = function ($request) {
            $inertiaResponse = Inertia::render('Admin/Dashboard');
            return new Response(
                $inertiaResponse->toResponse($request)->getContent(),
                200,
                ['Content-Type' => 'application/json']
            );
        };

        $response = $this->middleware->handle($request, $next);

        $this->assertJson($response->getContent());
        $page = json_decode($response->getContent(), true);

        $this->assertEquals('Admin/Dashboard', $page['component']);
        $this->assertEquals([
            'email' => ['The email field is required.'],
            'password' => ['The password field is required.'],
        ], $page['props']['errors']);
    }
} 