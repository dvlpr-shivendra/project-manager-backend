<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\=Task>
 */
class TaskFactory extends Factory
{
    public function definition()
    {
        $user = User::first() ?? User::factory(1)->create()->first();
        $project = Project::first() ?? Project::factory(1)->create()->first();

        return [
            'title' => $this->faker->sentence(5),
            'description' => $this->faker->paragraph(),
            'creator_id' => $user->id,
            'project_id' => $project->id,
        ];
    }
}
