<?php

namespace Tests\Unit\Middleware;

use App\Http\Middleware\HandleInertiaRequests;
use App\Models\User;
use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use Inertia\Inertia;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class HandleInertiaRequestsTest extends TestCase
{
    use RefreshDatabase;

    protected HandleInertiaRequests $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        
        foreach (['web', 'admin', 'client'] as $guard) {
            Auth::guard($guard)->logout();
        }
        session()->flush();
        Inertia::flushShared();
        
        Log::spy();
        
        $this->middleware = new HandleInertiaRequests();
    }

    protected function tearDown(): void
    {
        foreach (['web', 'admin', 'client'] as $guard) {
            Auth::guard($guard)->logout();
        }
        session()->flush();
        Inertia::flushShared();
        parent::tearDown();
    }

    private function makeInertiaRequest(string $uri, string $method = 'GET'): Request
    {
        $request = Request::create($uri, $method);
        $request->headers->set('X-Inertia', 'true');
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');
        $request->headers->set('Accept', 'application/json, text/plain, */*');
        $request->setLaravelSession($this->app['session.store']);
        return $request;
    }

    #[Test]
    public function it_shares_admin_user_data_when_authenticated_on_admin_route(): void
    {
        $adminUser = User::factory()->create([
            'role' => 'manager',
            'is_active' => true,
        ]);
        Auth::guard('admin')->login($adminUser);

        $request = $this->makeInertiaRequest('/admin/dashboard');

        $next = function ($request) {
            return new JsonResponse();
        };

        $this->middleware->handle($request, $next);
        
        $sharedProps = Inertia::getShared();

        $this->assertArrayHasKey('auth', $sharedProps);
        $this->assertArrayHasKey('user', $sharedProps['auth']);
        $this->assertEquals([
            'id' => $adminUser->id,
            'name' => $adminUser->name,
            'email' => $adminUser->email,
            'role' => $adminUser->role,
        ], $sharedProps['auth']['user']);
    }
    
    #[Test]
    public function it_shares_client_user_data_when_authenticated_on_client_route(): void
    {
        $clientUser = Client::factory()->create();
        Auth::guard('client')->login($clientUser);

        $request = $this->makeInertiaRequest('/my-profile');

        $next = function ($request) {
            return new JsonResponse();
        };

        $this->middleware->handle($request, $next);
        $sharedProps = Inertia::getShared();

        $this->assertArrayHasKey('auth', $sharedProps);
        $this->assertArrayHasKey('user', $sharedProps['auth']);
        $this->assertEquals([
            'id' => $clientUser->id,
            'name' => $clientUser->name,
            'email' => $clientUser->email,
        ], $sharedProps['auth']['user']);
    }

    #[Test]
    public function it_shares_null_user_data_when_not_authenticated(): void
    {
        Auth::guard('admin')->logout();
        Auth::guard('client')->logout();
        Auth::guard('web')->logout();
        session()->flush();

        $request = $this->makeInertiaRequest('/admin/login');

        $next = function ($request) {
            return new JsonResponse();
        };

        $this->middleware->handle($request, $next);
        $sharedProps = Inertia::getShared();

        $this->assertArrayHasKey('auth', $sharedProps);
        $this->assertNull($sharedProps['auth']['user']);
    }

    #[Test]
    public function it_shares_flash_messages(): void
    {
        $adminUser = User::factory()->create(['role' => 'manager']);
        Auth::guard('admin')->login($adminUser);

        $this->app['session.store']->put('message', 'Test flash message');
        $this->app['session.store']->put('error', 'Test error message');

        $request = $this->makeInertiaRequest('/admin/dashboard');

        $next = function ($request) {
            return new JsonResponse();
        };

        $this->middleware->handle($request, $next);
        $sharedProps = Inertia::getShared();

        $this->assertArrayHasKey('flash', $sharedProps);
        $this->assertInstanceOf(\Closure::class, $sharedProps['flash']['message']);
        $this->assertInstanceOf(\Closure::class, $sharedProps['flash']['error']);
        $this->assertEquals('Test flash message', call_user_func($sharedProps['flash']['message']));
        $this->assertEquals('Test error message', call_user_func($sharedProps['flash']['error']));
    }

    #[Test]
    public function it_handles_validation_errors_correctly(): void
    {
        $adminUser = User::factory()->create(['role' => 'manager']);
        Auth::guard('admin')->login($adminUser);

        $errors = new ViewErrorBag();
        $errorMessages = [
            'email' => ['The email field is required.'],
            'password' => ['The password field is required.'],
        ];
        $errors->put('default', new MessageBag($errorMessages));
        
        $this->app['session.store']->put('errors', $errors);

        $request = $this->makeInertiaRequest('/admin/some-action-that-failed');

        $next = function ($request) {
            return new JsonResponse();
        };

        $this->middleware->handle($request, $next);
        $sharedProps = Inertia::getShared();

        $this->assertArrayHasKey('errors', $sharedProps);
        $this->assertInstanceOf(\Closure::class, $sharedProps['errors']);
        $this->assertEquals($errorMessages, call_user_func($sharedProps['errors']));
    }
} 