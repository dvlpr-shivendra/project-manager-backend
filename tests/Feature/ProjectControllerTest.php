<?php

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Laravel\Sanctum\Sanctum;
use Maatwebsite\Excel\Facades\Excel;

uses(RefreshDatabase::class);

test('it can list projects', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    Project::factory()->count(3)->create(['user_id' => $user->id]);

    $response = $this->getJson('/projects');

    $response->assertStatus(200)
        ->assertJsonCount(3, 'data')
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'description',
                    'user_id',
                    'tasks_count',
                    'completed_tasks_count',
                ]
            ]
        ]);
});

test('it can create a project', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $projectData = [
        'name' => 'New Project',
        'description' => 'Project Description',
    ];

    $response = $this->postJson('/projects', $projectData);

    $response->assertStatus(201)
        ->assertJsonFragment($projectData);

    $this->assertDatabaseHas('projects', array_merge($projectData, ['user_id' => $user->id]));
});

test('it can show a project', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $project = Project::factory()->create(['user_id' => $user->id]);

    $response = $this->getJson("/projects/{$project->id}");

    $response->assertStatus(200)
        ->assertJsonFragment([
            'id' => $project->id,
            'name' => $project->name,
        ]);
});

test('it can update a project', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $project = Project::factory()->create(['user_id' => $user->id]);

    $updatedData = [
        'name' => 'Updated Project Name',
    ];

    $response = $this->putJson("/projects/{$project->id}", $updatedData);

    $response->assertStatus(200)
        ->assertJsonFragment($updatedData);

    $this->assertDatabaseHas('projects', [
        'id' => $project->id,
        'name' => 'Updated Project Name',
    ]);
});

test('it can delete a project', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $project = Project::factory()->create(['user_id' => $user->id]);

    $response = $this->deleteJson("/projects/{$project->id}");

    $response->assertStatus(200);

    $this->assertDatabaseMissing('projects', ['id' => $project->id]);
});

test('it can export tasks', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $project = Project::factory()->create(['user_id' => $user->id]);

    Excel::fake();

    $response = $this->getJson("/projects/{$project->id}/tasks/export");

    $response->assertStatus(200);

    Excel::assertDownloaded('tasks.xlsx');
});

test('it can import tasks', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $project = Project::factory()->create(['user_id' => $user->id]);

    Excel::fake();

    $file = UploadedFile::fake()->create('tasks.xlsx');

    $response = $this->postJson("/projects/{$project->id}/tasks/import", [
        'file' => $file,
    ]);

    $response->assertStatus(204);

    Excel::assertImported('tasks.xlsx');
});
