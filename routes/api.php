<?php

use App\Http\Controllers\FileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TagController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\SignupController;
use App\Http\Controllers\SelectController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProgressController;
use App\Http\Controllers\ScreenshotController;
use App\Http\Controllers\TaskCommentController;
use App\Http\Controllers\LLMController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

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

 Route::get('/files/{file}', [FileController::class, 'show'])
        ->name('files.show')
        ->where('file', '[a-zA-Z0-9]+\.[a-z]+');
Route::get('/tasks/{task}/attachments/{attachment}/download', [TaskController::class, 'downloadAttachment']);

Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::get('/users', function () {
        return User::with('roles', 'permissions')->get();
    });

    Route::get('/roles', fn() => Role::with('permissions')->get());

    Route::get('/permissions', fn() => Permission::all());

    Route::post(
        '/roles',
        fn(Request $r) =>
        Role::create(['name' => $r->name, 'guard_name' => 'sanctum'])
    );

    Route::post(
        '/permissions',
        fn(Request $r) =>
        Permission::create(['name' => $r->name, 'guard_name' => 'sanctum',])
    );

    Route::post('/roles/{role}/permissions', function (Request $r, Role $role) {
        $role->syncPermissions($r->permissions);
        return $role->load('permissions');
    });
});


Route::middleware(['auth:sanctum'])->group(function () {

    Route::get('/me', function () {

        /** @var \App\Models\User $user */
        $user = Auth::user();

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'roles' => $user->getRoleNames(),
            'permissions' => $user->getAllPermissions()->pluck('name'),
        ];
    });

    Route::get('search/{searchQuery}', SearchController::class);

    // Project Routes
    Route::get('projects/{id}/tasks/export', [ProjectController::class, 'export'])->name("projects.export");
    Route::post('projects/{id}/tasks/import', [ProjectController::class, 'import'])->name("projects.import");
    Route::apiResource('projects', ProjectController::class);

    // Task Routes
    Route::delete('tasks/bulk', [TaskController::class, 'bulkDelete']);
    Route::apiResource('tasks', TaskController::class);
    Route::post('tasks/{task}/tags/{tagId}', [TaskController::class, 'addTag']);
    Route::delete('tasks/{task}/tags/{tagId}', [TaskController::class, 'destroyTag']);
    Route::post('tasks/{task}/followers/{userId}', [TaskController::class, 'addFollower']);
    Route::delete('tasks/{task}/followers/{userId}', [TaskController::class, 'destroyFollower']);
    Route::post('tasks/{task}/attachments', [TaskController::class, 'addAttachment']);
    Route::delete('tasks/{task}/attachments/{tagId}', [TaskController::class, 'destroyAttachment']);
    Route::apiResource('tasks/{task}/comments', TaskCommentController::class);
    Route::apiResource('tasks/{task}/progresses', ProgressController::class);

    // Screenshot Routes
    Route::apiResource('screenshots', ScreenshotController::class);

    // Select User Routes
    Route::get('select/users', [SelectController::class, 'users']);

    // Tag Routes
    Route::apiResource('tags', TagController::class);

    // File routes
    Route::post('/files', [FileController::class, 'store'])->name('files.store');
    Route::delete('/files/{file}', [FileController::class, 'destroy'])
        ->name('files.destroy')
        ->where('file', '[a-zA-Z0-9]+\.[a-z]+');

    // LLM routes
    Route::prefix('llm')->group(function () {
        Route::post('/rephrase', [LLMController::class, 'rephrase']);
        Route::post('/generate-description', [LLMController::class, 'generateDescription']);
        Route::post('/generate-title', [LLMController::class, 'generateTitle']);
    });
});
