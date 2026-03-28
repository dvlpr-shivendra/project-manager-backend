<?php

use App\Models\Progress;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('it can list progresses for a task', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $task = Task::factory()->create();
    Progress::factory()->count(3)->create(['task_id' => $task->id]);

    $response = $this->getJson("/tasks/{$task->id}/progresses");

    $response->assertStatus(200)
        ->assertJsonCount(3, 'data');
});

test('it can store a progress for a task', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $task = Task::factory()->create();

    $progressData = [
        'duration' => 3600,
    ];

    $response = $this->postJson("/tasks/{$task->id}/progresses", $progressData);

    $response->assertStatus(201)
        ->assertJsonFragment(['duration' => 3600]);

    $this->assertDatabaseHas('progress', [
        'task_id' => $task->id,
        'user_id' => $user->id,
        'duration' => 3600,
    ]);
});
