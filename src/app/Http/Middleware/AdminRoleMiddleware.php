<?php

namespace App\Http\Middleware;

use Closure;

class AdminRoleMiddleware
{
    public function handle($request, Closure $next, ...$roles)
    {
        \Log::info('AdminRoleMiddleware check:', [
            'path' => $request->path(),
            'user_role' => $request->user()?->role,
            'required_roles' => $roles,
            'has_access' => $request->user() && $request->user()->hasAnyRole($roles)
        ]);

        if (!$request->user() || !$request->user()->hasAnyRole($roles)) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'unauthorized'], 403);
            }
            
            $roleNames = implode(', ', $roles);
            $errorMessage = "You don't have permission to access this page. Required role(s): {$roleNames}";
            
            \Log::info('Access denied, redirecting with flash message:', [
                'error_message' => $errorMessage,
                'session_id' => $request->session()->getId()
            ]);

            return redirect()->route('admin.dashboard')
                ->with('error', $errorMessage);
        }

        return $next($request);
    }
} 