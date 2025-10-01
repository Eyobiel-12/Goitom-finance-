<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Client;
use App\Models\Project;
use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Services\InvoiceService;
use App\Services\DashboardService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Inertia\Inertia;
use Inertia\Response;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Mail\InvoiceMail;
use App\Mail\InvoiceReminderMail;
use Illuminate\Support\Facades\Mail;

final class InvoiceController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly InvoiceService $invoiceService,
        private readonly DashboardService $dashboardService
    ) {}
    public function index(Request $request): Response
    {
        $invoices = Invoice::where('user_id', $request->user()->id)
            ->with(['client', 'project'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return Inertia::render('Invoices/Index', [
            'invoices' => $invoices,
        ]);
    }

    public function create(Request $request): Response
    {
        $clients = Client::where('user_id', $request->user()->id)
            ->orderBy('name')
            ->get();

        $projects = Project::where('user_id', $request->user()->id)
            ->where('status', 'active')
            ->with('client')
            ->orderBy('name')
            ->get();

        return Inertia::render('Invoices/Create', [
            'clients' => $clients,
            'projects' => $projects,
        ]);
    }

    public function store(StoreInvoiceRequest $request): RedirectResponse
    {
        $invoice = $this->invoiceService->createInvoice($request->user(), $request->validated());
        
        // Clear dashboard cache
        $this->dashboardService->clearDashboardCache($request->user());

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Factuur succesvol aangemaakt.');
    }

    public function show(Invoice $invoice): Response
    {
        $this->authorize('view', $invoice);

        $invoice->load(['client', 'project', 'items', 'payments']);

        return Inertia::render('Invoices/Show', [
            'invoice' => $invoice,
        ]);
    }

    public function edit(Invoice $invoice): Response
    {
        $this->authorize('update', $invoice);

        $clients = Client::where('user_id', $invoice->user_id)
            ->orderBy('name')
            ->get();

        $projects = Project::where('user_id', $invoice->user_id)
            ->where('status', 'active')
            ->with('client')
            ->orderBy('name')
            ->get();

        $invoice->load('items');

        return Inertia::render('Invoices/Edit', [
            'invoice' => $invoice,
            'clients' => $clients,
            'projects' => $projects,
        ]);
    }

    public function update(UpdateInvoiceRequest $request, Invoice $invoice): RedirectResponse
    {
        $this->invoiceService->updateInvoice($invoice, $request->validated());
        
        // Clear dashboard cache
        $this->dashboardService->clearDashboardCache($request->user());

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Factuur succesvol bijgewerkt.');
    }

    public function destroy(Invoice $invoice): RedirectResponse
    {
        $this->authorize('delete', $invoice);

        $invoice->delete();

                return redirect()->route('invoices.index')
                    ->with('success', 'Factuur succesvol verwijderd.');
            }

            public function pdf(Invoice $invoice)
            {
                $this->authorize('view', $invoice);

                $invoice->load(['client', 'project', 'items', 'user']);

                $pdf = Pdf::loadView('invoices.pdf', compact('invoice'));
                
                return $pdf->download("factuur-{$invoice->invoice_number}.pdf");
            }

            // New method for sending invoice via email
            public function email(Request $request, Invoice $invoice)
            {
                $this->authorize('view', $invoice);

                $validated = $request->validate([
                    'message' => 'nullable|string|max:1000',
                ]);

                $invoice->load(['client', 'project', 'items', 'user']);

                // Send email
                Mail::to($invoice->client->email)
                    ->send(new InvoiceMail($invoice, $validated['message'] ?? null));

                return redirect()->route('invoices.show', $invoice)
                    ->with('success', 'Factuur succesvol per email verzonden naar ' . $invoice->client->email);
            }

            // New method for sending payment reminder
            public function sendReminder(Request $request, Invoice $invoice)
            {
                $this->authorize('view', $invoice);

                $validated = $request->validate([
                    'message' => 'nullable|string|max:1000',
                ]);

                $invoice->load(['client', 'project', 'items', 'user']);

                // Send reminder email
                Mail::to($invoice->client->email)
                    ->send(new InvoiceReminderMail($invoice, $validated['message'] ?? null));

                return redirect()->route('invoices.show', $invoice)
                    ->with('success', 'Betalingherinnering succesvol verzonden naar ' . $invoice->client->email);
            }
        }
