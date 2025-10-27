<?php

use App\Http\Controllers\CourseController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/courses');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Web routes for courses
Route::get('/courses', [CourseController::class, 'indexView'])->name('courses.index');

// Protected course routes (require authentication) - must come before {id} routes
Route::middleware('auth')->group(function () {
    Route::get('/courses/create', [CourseController::class, 'create'])->name('courses.create');
    Route::get('/courses/{id}/edit', [CourseController::class, 'edit'])->name('courses.edit');
});

// Public course view routes (parameterized routes should come after specific routes)
Route::get('/courses/{id}', [CourseController::class, 'showView'])->name('courses.show');

// API routes for AJAX operations
Route::prefix('api')->group(function () {
    Route::get('/courses', [CourseController::class, 'index'])->name('api.courses.index');
    Route::get('/courses/{id}', [CourseController::class, 'show'])->name('api.courses.show');
    
    Route::middleware('auth')->group(function () {
        Route::post('/courses', [CourseController::class, 'store'])->name('api.courses.store');
        Route::put('/courses/{id}', [CourseController::class, 'update'])->name('api.courses.update');
        Route::delete('/courses/{id}', [CourseController::class, 'destroy'])->name('api.courses.destroy');
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
