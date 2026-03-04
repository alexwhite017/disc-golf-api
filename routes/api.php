<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\DiscController;
use App\Http\Controllers\HoleController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\RoundController;
use App\Http\Controllers\RoundPlayerController;
use App\Http\Controllers\ScoreController;
use App\Http\Controllers\StatsController;
use App\Http\Controllers\UserSearchController;
use Illuminate\Support\Facades\Route;

// Auth
Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');

Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::patch('/user', [AuthController::class, 'updateProfile']);
    Route::get('/me/stats', [StatsController::class, 'show']);
});

// Courses (public read, admin write)
Route::get('/courses', [CourseController::class, 'index'])->middleware('throttle:60,1');
Route::get('/courses/{course}', [CourseController::class, 'show'])->middleware('throttle:60,1');

Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
    Route::post('/courses', [CourseController::class, 'store']);
    Route::put('/courses/{course}', [CourseController::class, 'update']);
    Route::patch('/courses/{course}', [CourseController::class, 'update']);
    Route::delete('/courses/{course}', [CourseController::class, 'destroy']);
});

// Holes (nested under course, public read, admin write)
Route::get('/courses/{course}/holes', [HoleController::class, 'index'])->middleware('throttle:60,1');
Route::get('/courses/{course}/holes/{hole}', [HoleController::class, 'show'])->middleware('throttle:60,1');

Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
    Route::post('/courses/{course}/holes', [HoleController::class, 'store']);
    Route::put('/courses/{course}/holes/{hole}', [HoleController::class, 'update']);
    Route::patch('/courses/{course}/holes/{hole}', [HoleController::class, 'update']);
    Route::delete('/courses/{course}/holes/{hole}', [HoleController::class, 'destroy']);
});

// Rounds (auth, owner only)
Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
    Route::get('/rounds', [RoundController::class, 'index']);
    Route::post('/rounds', [RoundController::class, 'store']);
    Route::get('/rounds/{round}', [RoundController::class, 'show']);
    Route::put('/rounds/{round}', [RoundController::class, 'update']);
    Route::patch('/rounds/{round}', [RoundController::class, 'update']);
    Route::delete('/rounds/{round}', [RoundController::class, 'destroy']);

    // Scores (nested under round)
    Route::post('/rounds/{round}/scores', [ScoreController::class, 'store']);
    Route::put('/rounds/{round}/scores/{score}', [ScoreController::class, 'update']);
    Route::patch('/rounds/{round}/scores/{score}', [ScoreController::class, 'update']);
    Route::delete('/rounds/{round}/scores/{score}', [ScoreController::class, 'destroy']);

    // Round players
    Route::post('/rounds/{round}/players', [RoundPlayerController::class, 'store']);
    Route::delete('/rounds/{round}/players/{user}', [RoundPlayerController::class, 'destroy']);

    // User search
    Route::get('/users', [UserSearchController::class, 'index'])->middleware('throttle:30,1');

    // Discs (auth, owner only)
    Route::get('/discs', [DiscController::class, 'index']);
    Route::post('/discs', [DiscController::class, 'store']);
    Route::get('/discs/{disc}', [DiscController::class, 'show']);
    Route::put('/discs/{disc}', [DiscController::class, 'update']);
    Route::patch('/discs/{disc}', [DiscController::class, 'update']);
    Route::delete('/discs/{disc}', [DiscController::class, 'destroy']);
});

// Leaderboard (public)
Route::get('/leaderboard', [LeaderboardController::class, 'index'])->middleware('throttle:60,1');
Route::get('/leaderboard/{course}', [LeaderboardController::class, 'show'])->middleware('throttle:60,1');
