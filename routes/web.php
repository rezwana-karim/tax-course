<?php

use App\Http\Controllers\CourseController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/courses');
});

// Web routes for views
Route::get('/courses', [CourseController::class, 'indexView'])->name('courses.index');
Route::get('/courses/create', [CourseController::class, 'create'])->name('courses.create');
Route::get('/courses/{id}', [CourseController::class, 'showView'])->name('courses.show');
Route::get('/courses/{id}/edit', [CourseController::class, 'edit'])->name('courses.edit');

// API routes for AJAX operations
Route::prefix('api')->group(function () {
    Route::get('/courses', [CourseController::class, 'index'])->name('api.courses.index');
    Route::post('/courses', [CourseController::class, 'store'])->name('api.courses.store');
    Route::get('/courses/{id}', [CourseController::class, 'show'])->name('api.courses.show');
    Route::put('/courses/{id}', [CourseController::class, 'update'])->name('api.courses.update');
    Route::delete('/courses/{id}', [CourseController::class, 'destroy'])->name('api.courses.destroy');
});
