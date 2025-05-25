<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        if (!$request->expectsJson()) {
            if ($request->is('admin/*')) {
                return route('admin.login');
            }
            return route('login');
        }
        return null;
    }

    /**
     * Handle an unauthenticated user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  array  $guards
     * @return void
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    protected function unauthenticated($request, array $guards)
    {
        Log::info('Unauthenticated request:', [
            'path' => $request->path(),
            'guards' => $guards,
            'is_admin_route' => $request->is('admin/*'),
            'current_guard' => auth()->getDefaultDriver(),
        ]);

        parent::unauthenticated($request, $guards);
    }

    /**
     * Handle an incoming request.
     */
    public function handle($request, \Closure $next, ...$guards)
    {
        $this->authenticate($request, $guards);
    
        $guard = in_array('admin', $guards) ? 'admin' : (count($guards) ? $guards[0] : null);
        $user = auth($guard)->user();
    
        if ($guard === 'admin' && $user && !$user->is_active) {
            auth($guard)->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
    
            return redirect()->route('admin.login')->with('error', 'Your account is inactive. Please contact an administrator.');
        }
    
        return $next($request);
    }
} 