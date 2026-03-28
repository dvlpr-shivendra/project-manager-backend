<?php

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('it can store a screenshot', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $task = Task::factory()->create();

    Storage::fake();
    $screenshot = UploadedFile::fake()->image('screenshot.png', 1280, 720);

    $response = $this->postJson('/screenshots', [
        'task_id' => $task->id,
        'screenshot' => $screenshot,
    ]);

    $response->assertStatus(204);

    $this->assertDatabaseHas('progress', [
        'task_id' => $task->id,
        'user_id' => $user->id,
    ]);

    $this->assertDatabaseHas('screenshots', [
        'path' => 'screenshots/' . $screenshot->hashName(),
    ]);

    Storage::assertExists('screenshots/' . $screenshot->hashName());
});
