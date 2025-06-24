<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\TaskController;
use App\Http\Controllers\API\HabitController;
use App\Http\Controllers\API\DashboardController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Authentication routes
Route::prefix('auth')->group(function () {
      Route::post('register', [AuthController::class, 'register']);
      Route::post('login', [AuthController::class, 'login']);
      Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
      Route::get('user', [AuthController::class, 'user'])->middleware('auth:sanctum');
});

// Protected routes
Route::middleware('auth:sanctum')->group(function () {

      // User profile routes
      Route::prefix('user')->group(function () {
            Route::get('profile', [UserController::class, 'profile']);
            Route::put('profile', [UserController::class, 'updateProfile']);
            Route::get('stats', [UserController::class, 'stats']);
      });

      // Dashboard routes
      Route::prefix('dashboard')->group(function () {
            Route::get('/', [DashboardController::class, 'index']);
            Route::get('summary', [DashboardController::class, 'summary']);
      });

      // Task routes
      Route::prefix('tasks')->group(function () {
            Route::get('/', [TaskController::class, 'index']);
            Route::post('/', [TaskController::class, 'store']);
            Route::get('{task}', [TaskController::class, 'show']);
            Route::put('{task}', [TaskController::class, 'update']);
            Route::delete('{task}', [TaskController::class, 'destroy']);
            Route::patch('{task}/complete', [TaskController::class, 'complete']);
            Route::patch('{task}/incomplete', [TaskController::class, 'incomplete']);

            // Sub-task routes
            Route::post('{task}/sub-tasks', [TaskController::class, 'storeSubTask']);
            Route::put('{task}/sub-tasks/{subTask}', [TaskController::class, 'updateSubTask']);
            Route::delete('{task}/sub-tasks/{subTask}', [TaskController::class, 'destroySubTask']);
            Route::patch('{task}/sub-tasks/{subTask}/complete', [TaskController::class, 'completeSubTask']);
            Route::patch('{task}/sub-tasks/{subTask}/incomplete', [TaskController::class, 'incompleteSubTask']);
      });

      // Habit routes
      Route::prefix('habits')->group(function () {
            Route::get('/', [HabitController::class, 'index']);
            Route::post('/', [HabitController::class, 'store']);
            Route::get('{habit}', [HabitController::class, 'show']);
            Route::put('{habit}', [HabitController::class, 'update']);
            Route::delete('{habit}', [HabitController::class, 'destroy']);
            Route::patch('{habit}/complete', [HabitController::class, 'complete']);
            Route::get('{habit}/completions', [HabitController::class, 'completions']);
            Route::get('due-today', [HabitController::class, 'dueToday']);
      });
});
