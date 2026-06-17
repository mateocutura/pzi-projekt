<?php

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MentorController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\StatisticsController;
use App\Http\Controllers\Api\SubmissionController;
use Illuminate\Support\Facades\Route;

Route::get('/status', function () {
    return response()->json(['status' => 'ok', 'timestamp' => now()->toISOString()]);
});

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth:sanctum')->group(function () {


    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);


    Route::middleware('role:student')->group(function () {
        Route::get('/projects', [ProjectController::class, 'index']);
        Route::post('/projects', [ProjectController::class, 'store']);
        Route::get('/projects/{project}', [ProjectController::class, 'show']);
        Route::put('/projects/{project}', [ProjectController::class, 'update']);
        Route::post('/projects/{project}/submissions', [SubmissionController::class, 'store']);
        Route::put('/projects/{project}/submissions/{submission}', [SubmissionController::class, 'update']);
    });


    Route::middleware('role:mentor')->prefix('mentor')->group(function () {
        Route::get('/projects', [MentorController::class, 'index']);
        Route::get('/projects/{project}', [MentorController::class, 'show']);
        Route::put('/projects/{project}/status', [MentorController::class, 'updateStatus']);
        Route::put('/projects/{project}/submissions/{submission}', [MentorController::class, 'reviewSubmission']);
        Route::put('/projects/{project}/grade', [MentorController::class, 'gradeProject']);
    });


    Route::middleware('role:mentor,admin')->get('/statistics', [StatisticsController::class, 'index']);


    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::get('/users', [AdminController::class, 'listUsers']);
        Route::put('/users/{user}', [AdminController::class, 'updateUser']);
        Route::get('/projects', [AdminController::class, 'listProjects']);
        Route::put('/projects/{project}/assign-mentor', [AdminController::class, 'assignMentor']);
    });
});

