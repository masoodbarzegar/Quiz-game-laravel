<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\User;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    /**
     * Display the admin dashboard.
     */
    public function index()
    {
        // Log detailed auth information
        Log::info('Dashboard access - Auth details:', [
            'is_authenticated' => Auth::guard('admin')->check(),
            'current_guard' => Auth::getDefaultDriver(),
            'session_id' => request()->session()->getId(),
            'session_has_user' => request()->session()->has('user'),
            'session_user' => request()->session()->get('user'),
            'auth_user' => Auth::guard('admin')->user() ? [
                'id' => Auth::guard('admin')->user()->id,
                'name' => Auth::guard('admin')->user()->name,
                'email' => Auth::guard('admin')->user()->email,
                'role' => Auth::guard('admin')->user()->role,
            ] : null,
            'all_guards_status' => [
                'web' => Auth::guard('web')->check(),
                'admin' => Auth::guard('admin')->check(),
                'client' => Auth::guard('client')->check(),
            ],
            'flash_messages' => [
                'error' => session('error'),
                'message' => session('message'),
            ],
        ]);

        // Optimize client queries
        $clients = Client::select('id', 'is_active', 'created_at')
            ->latest()
            ->get();

        // Optimize question queries
        $questions = Question::select('id', 'question_text', 'category', 'difficulty_level', 'status', 'created_at')
            ->latest()
            ->get();

        // Calculate stats using collections
        $stats = [
            'total_clients' => $clients->count(),
            'active_clients' => $clients->where('is_active', true)->count(),
            'total_questions' => $questions->count(),
            'pending_questions' => $questions->where('status', 'pending')->count(),
            'approved_questions' => $questions->where('status', 'approved')->count(),
            'rejected_questions' => $questions->where('status', 'rejected')->count(),
        ];

        // Get role-specific data based on the admin's role
        $user = Auth::guard('admin')->user();
        
        if (!$user) {
            Log::warning('No authenticated user found in DashboardController');
            return redirect()->route('admin.login')
                ->with('error', 'Please log in to access the dashboard.');
        }

        $roleData = match($user->role) {
            'manager' => [
                // Use the already fetched clients collection
                'recent_clients' => $clients
                    ->take(5)
                    ->map(fn($client) => $client->only([
                        'id', 'created_at', 'is_active'
                    ])),
            ],
            'corrector' => [
                // Use the already fetched questions collection
                'pending_questions' => $questions->where('status', 'pending')
                    ->take(5)
                    ->map(fn($q) => collect($q->toArray())->only([
                        'id', 'question_text', 'category', 'difficulty_level', 'status', 'created_at'
                    ]))->values(),
            ],
            'general' => [
                'total_questions' => $questions->count(),
            ],
            default => [],
        };

        // Log query performance
        Log::info('Dashboard queries executed:', [
            'queries' => DB::getQueryLog(),
            'memory_usage' => memory_get_usage(true),
        ]);

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
            'roleData' => $roleData,
            'flash' => [
                'error' => session('error'),
                'success' => session('success'),
                'message' => session('message'),
            ],
        ]);
    }
} 