<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Client;
use App\Models\Project;
use App\Models\Invoice;
use App\Models\Expense;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_dashboard_displays_correct_statistics(): void
    {
        // Create test data
        $client = Client::factory()->create(['user_id' => $this->user->id]);
        $project = Project::factory()->create(['user_id' => $this->user->id]);
        
        // Create invoices
        Invoice::factory()->create([
            'user_id' => $this->user->id,
            'client_id' => $client->id,
            'status' => 'paid',
            'total_amount' => 1000.00,
        ]);
        
        Invoice::factory()->create([
            'user_id' => $this->user->id,
            'client_id' => $client->id,
            'status' => 'paid',
            'total_amount' => 500.00,
        ]);

        // Create expenses
        Expense::factory()->create([
            'user_id' => $this->user->id,
            'amount' => 200.00,
        ]);

        $response = $this->actingAs($this->user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->has('stats')
            ->where('stats.totalInvoices', 2)
            ->where('stats.totalRevenue', 1500)
            ->where('stats.totalExpenses', 200)
            ->where('stats.netProfit', 1300)
        );
    }

    public function test_dashboard_shows_recent_invoices(): void
    {
        $client = Client::factory()->create(['user_id' => $this->user->id]);
        
        Invoice::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'client_id' => $client->id,
        ]);

        $response = $this->actingAs($this->user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->has('recentInvoices', 3)
        );
    }

    public function test_dashboard_shows_recent_expenses(): void
    {
        Expense::factory()->count(3)->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->has('recentExpenses', 3)
        );
    }

    public function test_dashboard_calculates_overdue_invoices(): void
    {
        $client = Client::factory()->create(['user_id' => $this->user->id]);
        
        Invoice::factory()->create([
            'user_id' => $this->user->id,
            'client_id' => $client->id,
            'status' => 'sent',
            'due_date' => now()->subDays(5)->toDateString(),
        ]);

        $response = $this->actingAs($this->user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->where('stats.overdueInvoices', 1)
        );
    }

    public function test_dashboard_shows_monthly_revenue(): void
    {
        $client = Client::factory()->create(['user_id' => $this->user->id]);
        
        Invoice::factory()->create([
            'user_id' => $this->user->id,
            'client_id' => $client->id,
            'status' => 'paid',
            'total_amount' => 1000.00,
            'paid_date' => now()->subMonth()->toDateString(),
        ]);

        $response = $this->actingAs($this->user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->has('monthlyRevenue')
        );
    }

    public function test_dashboard_shows_monthly_expenses(): void
    {
        Expense::factory()->create([
            'user_id' => $this->user->id,
            'amount' => 500.00,
            'expense_date' => now()->subMonth()->toDateString(),
        ]);

        $response = $this->actingAs($this->user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->has('monthlyExpenses')
        );
    }

    public function test_dashboard_only_shows_user_data(): void
    {
        $otherUser = User::factory()->create();
        $otherClient = Client::factory()->create(['user_id' => $otherUser->id]);
        
        // Create invoice for other user
        Invoice::factory()->create([
            'user_id' => $otherUser->id,
            'client_id' => $otherClient->id,
            'total_amount' => 1000.00,
        ]);

        // Create invoice for current user
        $client = Client::factory()->create(['user_id' => $this->user->id]);
        Invoice::factory()->create([
            'user_id' => $this->user->id,
            'client_id' => $client->id,
            'status' => 'paid',
            'total_amount' => 500.00,
            'paid_date' => now(),
        ]);

        $response = $this->actingAs($this->user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->where('stats.totalInvoices', 1)
            ->where('stats.totalRevenue', 500)
        );
    }
}
