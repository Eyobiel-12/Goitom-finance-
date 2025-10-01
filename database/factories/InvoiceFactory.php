<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Client;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invoice>
 */
class InvoiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = $this->faker->randomFloat(2, 100, 5000);
        $taxRate = $this->faker->randomElement([0, 9, 21]);
        $taxAmount = $subtotal * ($taxRate / 100);
        $totalAmount = $subtotal + $taxAmount;

        return [
            'user_id' => User::factory(),
            'client_id' => Client::factory(),
            'project_id' => Project::factory(),
            'invoice_number' => 'INV-' . $this->faker->unique()->numerify('####'),
            'status' => $this->faker->randomElement(['draft', 'sent', 'paid', 'overdue', 'cancelled']),
            'issue_date' => $this->faker->dateTimeBetween('-6 months', 'now'),
            'due_date' => $this->faker->dateTimeBetween('now', '+30 days'),
            'paid_date' => $this->faker->optional(0.3)->dateTimeBetween('-1 month', 'now'),
            'subtotal' => $subtotal,
            'tax_rate' => $taxRate,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
            'notes' => $this->faker->optional()->paragraph(),
            'terms' => $this->faker->optional()->sentence(),
            'currency' => 'EUR',
        ];
    }
}
