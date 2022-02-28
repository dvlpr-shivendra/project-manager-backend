<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\SignupController;
use App\Http\Controllers\SelectController;
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

Route::post('login', LoginController::class);
Route::post('signup', SignupController::class);

Route::middleware(['auth:sanctum'])->group(function () {
    // Project Routes
    Route::resource('projects', ProjectController::class);

    // Task Routes
    Route::resource('tasks', TaskController::class);

    // Uses Routes
    Route::get('users', [SelectController::class, 'users']);
});
