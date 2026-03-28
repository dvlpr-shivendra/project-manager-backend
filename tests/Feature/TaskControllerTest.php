<?php

use App\Models\Project;
use App\Models\Task;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('it can list tasks for a project', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $project = Project::factory()->create(['user_id' => $user->id]);
    Task::factory()->count(3)->create(['project_id' => $project->id]);

    $response = $this->getJson("/tasks?project_id={$project->id}");

    $response->assertStatus(200)
        ->assertJsonCount(3, 'data');
});

test('it can create a task', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $project = Project::factory()->create(['user_id' => $user->id]);

    $taskData = [
        'project_id' => $project->id,
        'title' => 'New Task',
    ];

    $response = $this->postJson('/tasks', $taskData);

    $response->assertStatus(201);
    $this->assertDatabaseHas('tasks', [
        'project_id' => $project->id,
        'creator_id' => $user->id,
    ]);
});

test('it can show a task', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $task = Task::factory()->create();

    $response = $this->getJson("/tasks/{$task->id}");

    $response->assertStatus(200)
        ->assertJsonFragment(['id' => $task->id]);
});

test('it can update a task', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $task = Task::factory()->create();

    $updatedData = [
        'title' => 'Updated Task Title',
        'is_complete' => true,
    ];

    $response = $this->putJson("/tasks/{$task->id}", $updatedData);

    $response->assertStatus(200)
        ->assertJsonFragment($updatedData);
});

test('it can delete a task', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $task = Task::factory()->create();

    $response = $this->deleteJson("/tasks/{$task->id}");

    $response->assertStatus(200);
    $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
});

test('it can bulk delete tasks', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $tasks = Task::factory()->count(3)->create();
    $ids = $tasks->pluck('id')->toArray();

    $response = $this->deleteJson('/tasks/bulk', ['ids' => $ids]);

    $response->assertStatus(204);
    foreach ($ids as $id) {
        $this->assertDatabaseMissing('tasks', ['id' => $id]);
    }
});

test('it can add and remove a tag from a task', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $task = Task::factory()->create();
    $tag = Tag::factory()->create();

    // Add tag
    $response = $this->postJson("/tasks/{$task->id}/tags/{$tag->id}");
    $response->assertStatus(200);
    $this->assertTrue($task->fresh()->tags->contains($tag->id));

    // Remove tag
    $response = $this->deleteJson("/tasks/{$task->id}/tags/{$tag->id}");
    $response->assertStatus(200);
    $this->assertFalse($task->fresh()->tags->contains($tag->id));
});

test('it can add and remove a follower from a task', function () {
    $user = User::factory()->create();
    $follower = User::factory()->create();
    Sanctum::actingAs($user);

    $task = Task::factory()->create();

    // Add follower
    $response = $this->postJson("/tasks/{$task->id}/followers/{$follower->id}");
    $response->assertStatus(200);
    $this->assertTrue($task->fresh()->followers->contains($follower->id));

    // Remove follower
    $response = $this->deleteJson("/tasks/{$task->id}/followers/{$follower->id}");
    $response->assertStatus(200);
    $this->assertFalse($task->fresh()->followers->contains($follower->id));
});

test('it can add and remove an attachment from a task', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $task = Task::factory()->create();

    Storage::fake('public');
    $file = UploadedFile::fake()->create('document.pdf', 100);

    // Add attachment
    $response = $this->postJson("/tasks/{$task->id}/attachments", [
        'file' => $file,
    ]);

    $response->assertStatus(201);
    $attachmentId = $response->json('id');
    $this->assertDatabaseHas('attachments', ['id' => $attachmentId, 'task_id' => $task->id]);

    // Remove attachment
    $response = $this->deleteJson("/tasks/{$task->id}/attachments/{$attachmentId}");
    $response->assertStatus(200);
    $this->assertDatabaseMissing('attachments', ['id' => $attachmentId]);
});
