<?php

use App\Models\Task;
use App\Models\TaskComment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('it can list comments for a task', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $task = Task::factory()->create();
    TaskComment::factory()->count(3)->create(['task_id' => $task->id]);

    $response = $this->getJson("/tasks/{$task->id}/comments");

    $response->assertStatus(200)
        ->assertJsonCount(3);
});

test('it can store a comment for a task', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $task = Task::factory()->create();

    $commentData = [
        'body' => 'This is a comment',
    ];

    $response = $this->postJson("/tasks/{$task->id}/comments", $commentData);

    $response->assertStatus(201)
        ->assertJsonFragment(['body' => 'This is a comment']);

    $this->assertDatabaseHas('task_comments', [
        'task_id' => $task->id,
        'user_id' => $user->id,
        'body' => 'This is a comment',
    ]);
});

test('it can show a comment', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $task = Task::factory()->create();
    $comment = TaskComment::factory()->create(['task_id' => $task->id]);

    $response = $this->getJson("/tasks/{$task->id}/comments/{$comment->id}");

    $response->assertStatus(200)
        ->assertJsonFragment(['id' => $comment->id]);
});

test('it can delete a comment', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $task = Task::factory()->create();
    $comment = TaskComment::factory()->create(['task_id' => $task->id]);

    $response = $this->deleteJson("/tasks/{$task->id}/comments/{$comment->id}");

    $response->assertStatus(204);

    $this->assertDatabaseMissing('task_comments', ['id' => $comment->id]);
});
