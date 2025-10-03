<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Client;
use App\Models\Project;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Mail\InvoiceMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class InvoiceTest extends TestCase
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

    public function test_user_can_view_invoices_index(): void
    {
        Invoice::factory()->count(3)->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->get('/invoices');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Invoices/Index')
            ->has('invoices.data', 3)
        );
    }

    public function test_user_can_create_invoice(): void
    {
        $invoiceData = [
            'client_id' => $this->client->id,
            'project_id' => $this->project->id,
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'items' => [
                [
                    'description' => 'Web Development',
                    'quantity' => 10,
                    'unit_price' => 75.00,
                ],
                [
                    'description' => 'Design Services',
                    'quantity' => 5,
                    'unit_price' => 50.00,
                ],
            ],
            'tax_rate' => 21,
            'notes' => 'Test invoice',
            'terms' => 'Payment within 30 days',
        ];

        $response = $this->actingAs($this->user)->post('/invoices', $invoiceData);

        $response->assertRedirect();
        $this->assertDatabaseHas('invoices', [
            'user_id' => $this->user->id,
            'client_id' => $this->client->id,
            'project_id' => $this->project->id,
            'subtotal' => 1000.00,
            'tax_rate' => 21,
            'tax_amount' => 210.00,
            'total_amount' => 1210.00,
        ]);

        $invoice = Invoice::where('user_id', $this->user->id)->first();
        $this->assertDatabaseHas('invoice_items', [
            'invoice_id' => $invoice->id,
            'description' => 'Web Development',
            'quantity' => 10,
            'unit_price' => 75.00,
            'total_price' => 750.00,
        ]);
    }

    public function test_invoice_creation_validates_required_fields(): void
    {
        $response = $this->actingAs($this->user)->post('/invoices', []);

        $response->assertSessionHasErrors(['client_id', 'issue_date', 'items', 'tax_rate']);
    }

    public function test_invoice_creation_validates_items_array(): void
    {
        $invoiceData = [
            'client_id' => $this->client->id,
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'items' => [],
            'tax_rate' => 21,
        ];

        $response = $this->actingAs($this->user)->post('/invoices', $invoiceData);

        $response->assertSessionHasErrors(['items']);
    }

    public function test_user_can_view_their_invoice(): void
    {
        $invoice = Invoice::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->get("/invoices/{$invoice->id}");

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Invoices/Show')
            ->has('invoice')
        );
    }

    public function test_user_cannot_view_other_users_invoice(): void
    {
        $otherUser = User::factory()->create();
        $invoice = Invoice::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($this->user)->get("/invoices/{$invoice->id}");

        $response->assertStatus(403);
    }

    public function test_user_can_update_invoice(): void
    {
        $invoice = Invoice::factory()->create(['user_id' => $this->user->id]);
        InvoiceItem::factory()->create(['invoice_id' => $invoice->id]);

        $updateData = [
            'client_id' => $this->client->id,
            'project_id' => $this->project->id,
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'status' => 'sent',
            'items' => [
                [
                    'description' => 'Updated Service',
                    'quantity' => 1,
                    'unit_price' => 100.00,
                ],
            ],
            'tax_rate' => 21,
            'notes' => 'Updated invoice',
            'terms' => 'Updated terms',
        ];

        $response = $this->actingAs($this->user)->put("/invoices/{$invoice->id}", $updateData);

        $response->assertRedirect();
        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'status' => 'sent',
            'total_amount' => 121.00,
        ]);
    }

    public function test_user_can_delete_invoice(): void
    {
        $invoice = Invoice::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->delete("/invoices/{$invoice->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('invoices', ['id' => $invoice->id]);
    }

    public function test_user_cannot_delete_other_users_invoice(): void
    {
        $otherUser = User::factory()->create();
        $invoice = Invoice::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($this->user)->delete("/invoices/{$invoice->id}");

        $response->assertStatus(403);
    }

    public function test_invoice_pdf_generation(): void
    {
        $invoice = Invoice::factory()->create(['user_id' => $this->user->id]);
        InvoiceItem::factory()->create(['invoice_id' => $invoice->id]);

        $response = $this->actingAs($this->user)->get("/invoices/{$invoice->id}/pdf");

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_invoice_email_sending(): void
    {
        Mail::fake();
        
        $invoice = Invoice::factory()->create(['user_id' => $this->user->id]);

        $emailData = [
            'message' => 'Please find attached invoice.',
        ];

        $response = $this->actingAs($this->user)->post("/invoices/{$invoice->id}/email", $emailData);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        Mail::assertSent(InvoiceMail::class);
    }

    public function test_invoice_number_generation(): void
    {
        $invoiceData = [
            'client_id' => $this->client->id,
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'items' => [
                [
                    'description' => 'Test Service',
                    'quantity' => 1,
                    'unit_price' => 100.00,
                ],
            ],
            'tax_rate' => 21,
        ];

        $response = $this->actingAs($this->user)->post('/invoices', $invoiceData);

        $invoice = Invoice::where('user_id', $this->user->id)->first();
        $this->assertStringStartsWith('INV-', $invoice->invoice_number);
    }

    public function test_invoice_calculations_are_correct(): void
    {
        $invoiceData = [
            'client_id' => $this->client->id,
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'items' => [
                [
                    'description' => 'Service 1',
                    'quantity' => 2,
                    'unit_price' => 100.00,
                ],
                [
                    'description' => 'Service 2',
                    'quantity' => 3,
                    'unit_price' => 50.00,
                ],
            ],
            'tax_rate' => 20,
        ];

        $this->actingAs($this->user)->post('/invoices', $invoiceData);

        $invoice = Invoice::where('user_id', $this->user->id)->first();
        
        // Subtotal: (2 * 100) + (3 * 50) = 200 + 150 = 350
        $this->assertEquals(350.00, $invoice->subtotal);
        
        // Tax: 350 * 0.20 = 70
        $this->assertEquals(70.00, $invoice->tax_amount);
        
        // Total: 350 + 70 = 420
        $this->assertEquals(420.00, $invoice->total_amount);
    }
}
