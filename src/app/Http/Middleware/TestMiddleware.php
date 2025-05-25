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
        ]);

        return $next($request);
    }
} 