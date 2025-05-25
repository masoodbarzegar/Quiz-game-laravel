<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\LoginRequest;
use App\Http\Requests\Client\RegisterRequest;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class AuthController extends Controller
{
    /**
     * Show the client login form.
     */
    public function showLoginForm()
    {
        return Inertia::render('Client/Auth/Login');
    }

    /**
     * Show the client registration form.
     */
    public function showRegisterForm()
    {
        return Inertia::render('Client/Auth/Register');
    }

    /**
     * Handle client registration request.
     */
    public function register(RegisterRequest $request)
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);

        $client = Client::create($data);

        Auth::guard('client')->login($client);

        return redirect()->intended(route('client.dashboard'));
    }

    /**
     * Handle client login request.
     */
    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();

        if (Auth::guard('client')->attempt($credentials)) {
            $request->session()->regenerate();

            return redirect()->intended(route('client.dashboard'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Handle client logout request.
     */
    public function logout(Request $request)
    {
        Auth::guard('client')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('client.login');
    }
} 