<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        // If no guard is specified, use the default guard
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            // Only check the specified guard
            if (Auth::guard($guard)->check()) {
                // For admin routes, only redirect if authenticated as admin
                if ($guard === 'admin') {
                    return redirect()->route('admin.dashboard');
                }
                
                // For client routes, only redirect if authenticated as client
                if ($guard === 'client') {
                    return redirect()->route('client.dashboard');
                }
                
                // For web guard (default), redirect to home
                if ($guard === null || $guard === 'web') {
                    return redirect(RouteServiceProvider::HOME);
                }
            }
        }

        return $next($request);
    }
} 