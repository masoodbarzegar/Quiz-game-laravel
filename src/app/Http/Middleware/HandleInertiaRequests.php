<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class HandleInertiaRequests extends Middleware
{
    public function share(Request $request): array
    {
        $user = null;

        // Log middleware execution for admin routes with security context
        if ($request->is('admin/*')) {
            Log::info('Inertia share middleware:', [
                'is_admin_route' => true,
                'path' => $request->path(),
                'method' => $request->method(),
                'authenticated' => Auth::guard('admin')->check(),
                'ip' => $request->ip()
            ]);
        }

        // Check authentication in order of priority
        if ($request->is('admin/*') && Auth::guard('admin')->check()) {
            $authUser = Auth::guard('admin')->user();
            $user = [
                'id' => $authUser->id,
                'name' => $authUser->name,
                'email' => $authUser->email,
                'role' => $authUser->role,
            ];
        } elseif (Auth::guard('client')->check()) {
            $authUser = Auth::guard('client')->user();
            $user = [
                'id' => $authUser->id,
                'name' => $authUser->name,
                'email' => $authUser->email,
            ];
        } elseif (Auth::guard('web')->check()) {
            $authUser = Auth::guard('web')->user();
            $user = [
                'id' => $authUser->id,
                'name' => $authUser->name,
                'email' => $authUser->email,
            ];
        }

        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $user,
            ],
            'url' => $request->getRequestUri(),
            'flash' => [
                'message' => fn () => $request->session()->get('message'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'errors' => fn () => $request->session()->get('errors')
                ? $request->session()->get('errors')->getBag('default')->getMessages()
                : (object) [],
        ]);
    }
}