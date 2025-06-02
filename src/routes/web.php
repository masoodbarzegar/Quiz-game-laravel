<?php

use App\Http\Controllers\Client\AuthController;
use App\Http\Controllers\Client\ProfileController;
use App\Http\Controllers\Client\GameController;
use App\Http\Controllers\ContactController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Public routes
Route::get('/', function () {
    return Inertia::render('Home');
})->name('home');

Route::get('/about', function () {
    return Inertia::render('About');
})->name('about');

Route::get('/contact', [ContactController::class, 'show'])->name('contact');
Route::post('/contact', [ContactController::class, 'submit'])->name('contact.submit');


// Authentication routes (for clients)
Route::middleware('guest:client')->group(function () {
    Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AuthController::class, 'login']);
    Route::get('register', [AuthController::class, 'showRegistrationForm'])->name('register');
    Route::post('register', [AuthController::class, 'register']);
});

// Authenticated client routes
Route::middleware('auth:client')->group(function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    
    // Profile Management
    Route::get('profile', [ProfileController::class, 'edit'])->name('profile');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
});

// Public routes
Route::get('/games', [GameController::class, 'index'])->name('games.index');
Route::get('/games/{game:slug}', [GameController::class, 'show'])->name('games.show');

// Protected routes
Route::middleware(['auth:client'])->group(function () {
    // Game session routes
    Route::get('/play/{game:slug}/start', [GameController::class, 'start'])->name('play.start');
    Route::get('/play/{game:slug}/{session}', [GameController::class, 'play'])->name('play.game');
    Route::post('/play/{game:slug}/{session}/answer', [GameController::class, 'submitAnswer'])->name('play.answer');
    Route::post('/play/{game:slug}/{session}/end', [GameController::class, 'endGame'])->name('play.end');
    Route::get('/play/{game:slug}/result/{session}', [GameController::class, 'result'])->name('play.result');
});

// Admin routes - apply web middleware to all admin routes
Route::prefix('admin')->name('admin.')->middleware('web')->group(function () {
    require __DIR__.'/admin.php';
});