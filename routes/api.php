<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TokenController;
use App\Http\Controllers\SignupController;
use App\Http\Controllers\ProjectController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('token', TokenController::class);
Route::post('signup', SignupController::class);
Route::resource('projects', ProjectController::class)->middleware(['auth:sanctum']);
Route::resource('tasks', TaskController::class)->middleware(['auth:sanctum']);
Route::post('tasks/{task}/tag', [TaskController::class, 'addTag'])->middleware(['auth:sanctum']);
