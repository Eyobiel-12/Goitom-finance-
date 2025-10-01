<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Client;
use App\Models\Project;
use App\Models\Expense;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Client $client;
    protected Project $project;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->client = Client::factory()->create(['user_id' => $this->user->id]);
        $this->project = Project::factory()->create(['user_id' => $this->user->id]);
    }

    public function test_user_can_view_expenses_index(): void
    {
        Expense::factory()->count(3)->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->get('/expenses');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Expenses/Index')
            ->has('expenses.data', 3)
        );
    }

    public function test_user_can_create_expense(): void
    {
        $expenseData = [
            'description' => 'Office supplies',
            'amount' => 150.00,
            'category' => 'office',
            'expense_date' => now()->toDateString(),
            'project_id' => $this->project->id,
            'is_billable' => true,
            'notes' => 'Purchased office supplies',
        ];

        $response = $this->actingAs($this->user)->post('/expenses', $expenseData);

        $response->assertRedirect();
        $this->assertDatabaseHas('expenses', [
            'user_id' => $this->user->id,
            'description' => 'Office supplies',
            'amount' => 150.00,
            'category' => 'office',
            'project_id' => $this->project->id,
            'is_billable' => true,
        ]);
    }

    public function test_expense_creation_validates_required_fields(): void
    {
        $response = $this->actingAs($this->user)->post('/expenses', []);

        $response->assertSessionHasErrors(['description', 'amount', 'expense_date']);
    }

    public function test_user_can_update_expense(): void
    {
        $expense = Expense::factory()->create(['user_id' => $this->user->id]);

        $updateData = [
            'description' => 'Updated expense',
            'amount' => 200.00,
            'category' => 'travel',
            'expense_date' => now()->toDateString(),
            'project_id' => $this->project->id,
            'is_billable' => false,
            'notes' => 'Updated notes',
        ];

        $response = $this->actingAs($this->user)->put("/expenses/{$expense->id}", $updateData);

        $response->assertRedirect();
        $this->assertDatabaseHas('expenses', [
            'id' => $expense->id,
            'description' => 'Updated expense',
            'amount' => 200.00,
            'category' => 'travel',
            'is_billable' => false,
        ]);
    }

    public function test_user_can_delete_expense(): void
    {
        $expense = Expense::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->delete("/expenses/{$expense->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('expenses', ['id' => $expense->id]);
    }

    public function test_user_cannot_access_other_users_expenses(): void
    {
        $otherUser = User::factory()->create();
        $expense = Expense::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($this->user)->get("/expenses/{$expense->id}");

        $response->assertStatus(403);
    }

    public function test_expense_amount_must_be_positive(): void
    {
        $expenseData = [
            'description' => 'Test expense',
            'amount' => -50.00,
            'expense_date' => now()->toDateString(),
        ];

        $response = $this->actingAs($this->user)->post('/expenses', $expenseData);

        $response->assertSessionHasErrors(['amount']);
    }
}
