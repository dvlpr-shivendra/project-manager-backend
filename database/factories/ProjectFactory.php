<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $user = User::first() ?? User::factory(1)->create()->first();

        return [
            'name' => $this->faker->sentence(2),
            'description' => $this->faker->paragraph(4),
            'user_id' => $user->id,
        ];
    }
}
