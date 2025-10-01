<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Project>
 */
class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'client_id' => Client::factory(),
            'name' => $this->faker->words(3, true) . ' Project',
            'description' => $this->faker->paragraph(),
            'status' => $this->faker->randomElement(['active', 'completed', 'on_hold']),
            'start_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'end_date' => $this->faker->dateTimeBetween('now', '+1 year'),
            'budget' => $this->faker->randomFloat(2, 1000, 50000),
            'hourly_rate' => $this->faker->randomFloat(2, 25, 150),
        ];
    }
}
