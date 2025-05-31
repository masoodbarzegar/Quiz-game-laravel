<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class TestMiddleware
{
    public function __construct()
    {
        Log::info('TestMiddleware constructed');
    }

    public function handle(Request $request, Closure $next): Response
    {
        Log::info('TestMiddleware handling request', [
            'path' => $request->path(),
            'method' => $request->method(),
            'route_name' => optional($request->route())->getName(),
            'route_action' => optional($request->route())->getActionName(),
            'is_profile_update_put' => $request->is('profile') && $request->isMethod('PUT'),
            'authenticated_user_id_web' => auth()->id(), // Check default guard
            'authenticated_user_id_client' => auth('client')->id(), // Check client guard
            'has_user_client' => auth('client')->check(),
        ]);

        if ($request->is('profile') && $request->isMethod('PUT')) {
            Log::debug('TestMiddleware: PUT /profile request details', [
                'request_data' => $request->all(),
                'user_resolver_web' => auth()->user(),
                'user_resolver_client' => auth('client')->user(),
            ]);
        }

        return $next($request);
    }
} 