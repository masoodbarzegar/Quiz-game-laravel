<?php

use App\Http\Controllers\Client\AuthController;
// use App\Http\Controllers\Client\GameController;
// use App\Http\Controllers\Client\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

// Public routes
Route::get('/', function () {
    return Inertia::render('Home');
})->name('home');

// Route::get('/about', function () {
//     return Inertia::render('About');
// })->name('about');

// Route::get('/contact', function () {
//     return Inertia::render('Contact');
// })->name('contact');

// Games routes
// Route::prefix('games')->name('games.')->group(function () {
//     Route::get('/', function () {
//         return Inertia::render('Games/Index');
//     })->name('index');

//     Route::get('/quiz', function () {
//         return Inertia::render('Games/Quiz/Intro');
//     })->name('quiz');
// });

// Authentication routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Protected client routes
Route::middleware('auth:client')->group(function () {
    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Profile
    // Route::get('/profile', [ProfileController::class, 'edit'])->name('profile');
    // Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Game playing area
    // Route::prefix('play')->name('play.')->group(function () {
    //     Route::get('/quiz', [GameController::class, 'quiz'])->name('quiz');
    //     Route::post('/quiz/submit', [GameController::class, 'submitQuiz'])->name('quiz.submit');
    //     Route::get('/quiz/results', [GameController::class, 'quizResults'])->name('quiz.results');
    // });
});

// Protected web user routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');
});

// Admin routes - apply web middleware to all admin routes
Route::prefix('admin')->name('admin.')->middleware('web')->group(function () {
    Log::info('Loading admin routes');
    
    // Debug route to show middleware
    Route::get('debug-middleware', function () {
        $middleware = [
            'route' => request()->route()->middleware(),
            'current' => request()->route()->gatherMiddleware(),
        ];
        Log::info('Debug middleware', $middleware);
        return response()->json($middleware);
    })->name('debug.middleware');
    
    // Test route with middleware applied directly
    Route::get('test-direct', function () {
        Log::info('Test direct route accessed');
        return inertia('Admin/Test');
    })->middleware([\App\Http\Middleware\TestMiddleware::class])->name('test.direct');
    
    // Test route in web.php
    Route::get('test-web', function () {
        Log::info('Test web route accessed');
        return inertia('Admin/Test');
    })->name('test.web');
    
    require __DIR__.'/admin.php';
});