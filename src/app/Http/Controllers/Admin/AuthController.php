<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LoginRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest:admin')->except('logout');
    }

    /**
     * Show the admin login form.
     */
    public function showLoginForm()
    {
        return Inertia::render('Admin/Auth/Login');
    }

    /**
     * Handle admin login request.
     */
    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();
        
        Log::info('Attempting admin login:', [
            'email' => $credentials['email'],
            'guard' => 'admin',
            'session_id' => $request->session()->getId(),
        ]);

        // First check if user exists and is active
        $user = User::where('email', $credentials['email'])->first();
        
        if ($user && !$user->is_active) {
            Log::warning('Inactive admin login attempt:', [
                'email' => $credentials['email'],
                'user_id' => $user->id,
                'session_id' => $request->session()->getId(),
            ]);

            return Inertia::render('Admin/Auth/Login', [
                'errors' => [
                    'email' => 'This account is inactive. Please contact an administrator.'
                ],
                'email' => $credentials['email']
            ]);
        }

        // Try to authenticate
        if (Auth::guard('admin')->attempt($credentials)) {
            $request->session()->regenerate();
            
            $user = Auth::guard('admin')->user();
            
            // Store user data in session
            $request->session()->put('user', [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ]);

            Log::info('Admin login successful:', [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                ],
                'session_id' => $request->session()->getId(),
            ]);

            return redirect()->route('admin.dashboard')->with('success', 'Login successful!');
        }

        Log::warning('Admin login failed:', [
            'email' => $credentials['email'],
            'session_id' => $request->session()->getId(),
        ]);

        return Inertia::render('Admin/Auth/Login', [
            'errors' => [
                'email' => 'The provided credentials do not match our records.'
            ],
            'email' => $credentials['email']
        ]);
    }

    /**
     * Handle admin logout request.
     */
    public function logout(Request $request)
    {
        $user = Auth::guard('admin')->user();
        Log::info('Admin logout:', [
            'user' => $user ? [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ] : null,
            'session_id' => $request->session()->getId(),
        ]);

        Auth::guard('admin')->logout();
        $request->session()->forget('user');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
} 