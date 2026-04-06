<?php

namespace Tests\Feature;

use App\Ai\Agents\ProjectAssistant;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Ai\Ai;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('it handles a create task message', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);
    $project = Project::factory()->create(['user_id' => $user->id]);

    // Mock the AI response
    Ai::fakeAgent(ProjectAssistant::class, [
        [
            'action' => 'create',
            'resource_type' => 'task',
            'data' => [
                'title' => 'Test AI Task',
                'project_id' => $project->id,
            ],
            'confirmation_message' => "I'll create the task 'Test AI Task' in project '{$project->name}'.",
        ]
    ]);

    $response = $this->postJson('/chat', [
        'message' => "Create a task 'Test AI Task' in project '{$project->name}'",
    ]);

    $response->assertStatus(200)
        ->assertJsonFragment([
            'action' => 'create',
            'resource_type' => 'task',
            'data' => ['title' => 'Test AI Task', 'project_id' => $project->id],
        ]);
});

test('it resolves recent task context from database', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);
    
    // Create a task that should appear in "Recent Activity" context
    $task = Task::factory()->create([
        'creator_id' => $user->id,
        'title' => 'Finish API Docs'
    ]);

    // Mock AI response where it "uses" the ID it found in context
    Ai::fakeAgent(ProjectAssistant::class, [
        [
            'action' => 'update',
            'resource_type' => 'task',
            'task_id' => $task->id,
            'data' => ['is_complete' => true],
            'confirmation_message' => "Marking 'Finish API Docs' as complete.",
        ]
    ]);

    // Ask to update the task without providing ID
    $response = $this->postJson('/chat', [
        'message' => "Mark 'Finish API Docs' as complete",
    ]);

    $response->assertStatus(200)
        ->assertJsonFragment([
            'action' => 'update',
            'task_id' => $task->id,
        ]);
});

test('it allows listing open tasks globally', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);
    
    $tasks = Task::factory()->count(3)->create([
        'creator_id' => $user->id,
        'is_complete' => false
    ]);

    Ai::fakeAgent(ProjectAssistant::class, [
        [
            'action' => 'list',
            'resource_type' => 'task',
            'filters' => ['is_complete' => false],
            'confirmation_message' => "Listing your open tasks.",
        ]
    ]);

    $response = $this->postJson('/chat', [
        'message' => "What are my open tasks?",
    ]);

    $response->assertStatus(200)
        ->assertJsonFragment([
            'action' => 'list',
            'filters' => ['is_complete' => false],
        ]);
});

test('it handles errors gracefully when AI returns empty structured data', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    Ai::fakeAgent(ProjectAssistant::class, [
        ['invalid' => 'data'] 
    ]);

    $response = $this->postJson('/chat', [
        'message' => "something confusing",
    ]);

    $response->assertStatus(422)
        ->assertJson(['error' => 'The AI returned an unexpected response format. Please try again.']);
});
