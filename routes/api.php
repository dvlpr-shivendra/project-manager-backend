<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\SignupController;
use App\Http\Controllers\SelectController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ScreenshotController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\TaskCommentController;
use App\Http\Controllers\TaskStatusController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('login', LoginController::class);
Route::post('signup', SignupController::class);

Route::middleware(['auth:sanctum'])->group(function () {
    // Project Routes
    Route::apiResource('projects', ProjectController::class);

    // Task Routes
    Route::apiResource('tasks', TaskController::class);
    Route::post('tasks/{task}/tags/{tagId}', [TaskController::class, 'addTag']);
    Route::delete('tasks/{task}/tags/{tagId}', [TaskController::class, 'destroyTag']);
    Route::apiResource('tasks/{task}/comments', TaskCommentController::class);

    // Screenshot Routes
    Route::apiResource('screenshots', ScreenshotController::class);

    // Uses Routes
    Route::get('users', [SelectController::class, 'users']);

    // Tag Routes
    Route::apiResource('tags', TagController::class)->only(['index', 'store']);
});
