<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\QuestionController;
// use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

// Test route to verify middleware
Route::get('test-middleware', function () {
    \Log::info('Test route accessed');
    return inertia('Admin/Test');
})->name('test');

// Admin authentication routes
Route::middleware(['guest:admin'])->group(function () {
    Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AuthController::class, 'login']);
});

Route::middleware(['auth:admin'])->group(function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Manager only routes
    Route::middleware('admin.role:manager')->group(function () {
        // User management
        Route::get('users', [AdminUserController::class, 'index'])->name('users.index');
        Route::get('users/create', [AdminUserController::class, 'create'])->name('users.create');
        Route::post('users', [AdminUserController::class, 'store'])->name('users.store');
        Route::get('users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
        Route::put('users/{user}', [AdminUserController::class, 'update'])->name('users.update');
        Route::delete('users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');
        Route::post('users/{user}/toggle-status', [AdminUserController::class, 'toggleStatus'])->name('users.toggle-status');
    });

    // Question management routes (accessible by manager, corrector, and general)
    Route::middleware('admin.role:manager,corrector,general')->group(function () {
        Route::get('questions', [QuestionController::class, 'index'])->name('questions.index');
        Route::get('questions/{question}/edit', [QuestionController::class, 'edit'])->name('questions.edit');
        Route::put('questions/{question}', [QuestionController::class, 'update'])->name('questions.update');
    });

    // Question approval routes (accessible by manager and corrector)
    Route::middleware('admin.role:manager,corrector')->group(function () {
        Route::post('questions/{question}/approve', [QuestionController::class, 'approve'])->name('questions.approve');
        Route::post('questions/{question}/reject', [QuestionController::class, 'reject'])->name('questions.reject');
    });

    // Question creation routes (accessible by manager and general)
    Route::middleware('admin.role:manager,general')->group(function () {
        Route::get('questions/create', [QuestionController::class, 'create'])->name('questions.create');
        Route::post('questions', [QuestionController::class, 'store'])->name('questions.store');
    });

    // Manager only question routes
    Route::middleware('admin.role:manager')->group(function () {
        Route::delete('questions/{question}', [QuestionController::class, 'destroy'])->name('questions.destroy');
    });
}); 