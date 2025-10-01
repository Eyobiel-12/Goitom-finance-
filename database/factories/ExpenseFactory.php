<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Expense>
 */
class ExpenseFactory extends Factory
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
            'project_id' => Project::factory(),
            'description' => $this->faker->sentence(),
            'amount' => $this->faker->randomFloat(2, 10, 1000),
            'category' => $this->faker->randomElement(['office', 'travel', 'marketing', 'software', 'other']),
            'expense_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'is_billable' => $this->faker->boolean(70),
            'notes' => $this->faker->optional()->paragraph(),
        ];
    }
}
