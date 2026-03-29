<?php

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('it returns user info and dashboard statistics', function () {
    $user = User::factory()->create(['name' => 'John Doe', 'email' => 'john@example.com']);
    Sanctum::actingAs($user);

    // Create some data for stats
    Project::factory()->count(2)->create(['user_id' => $user->id]);
    Task::factory()->create(['is_complete' => false, 'deadline' => now()->addDays(1)]); // Open
    Task::factory()->create(['is_complete' => true]); // Completed
    Task::factory()->create(['is_complete' => false, 'deadline' => now()->subDays(1)]); // Overdue

    $response = $this->getJson('/me');

    $response->assertStatus(200)
        ->assertJson([
            'id' => $user->id,
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'stats' => [
                'projects_count' => 2,
                'open_tasks_count' => 2, // 1 regular open + 1 overdue (which is also open)
                'completed_tasks_count' => 1,
                'overdue_tasks_count' => 1,
            ]
        ]);
});

test('it requires authentication', function () {
    $response = $this->getJson('/me');
    $response->assertStatus(401);
});
