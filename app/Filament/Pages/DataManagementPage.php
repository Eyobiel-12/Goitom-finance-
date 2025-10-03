<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Invoice;
use App\Models\Expense;
use App\Models\Client;

class DataManagementPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-arrow-down-tray';
    protected static string $view = 'filament.pages.data-management';
    protected static ?string $navigationLabel = 'Data Management';
    protected static ?string $title = 'Data Export & Import';
    protected static ?string $navigationGroup = 'System';
    protected static ?int $navigationSort = 20;

    public function exportUsers()
    {
        $users = User::all();
        $csvData = [];
        $csvData[] = ['ID', 'Naam', 'E-mail', 'Rol', 'Bedrijf', 'Aangemaakt', 'E-mail Geverifieerd'];
        
        foreach ($users as $user) {
            $csvData[] = [
                $user->id,
                $user->name,
                $user->email,
                $user->role,
                $user->business_name ?? '',
                $user->created_at->format('d-m-Y H:i'),
                $user->email_verified_at ? 'Ja' : 'Nee',
            ];
        }
        
        $filename = 'users_export_' . now()->format('Y-m-d_H-i-s') . '.csv';
        $this->saveCsvFile($csvData, $filename);
        
        Notification::make()
            ->title('Export voltooid')
            ->body("Gebruikers geëxporteerd naar {$filename}")
            ->success()
            ->send();
    }

    public function exportInvoices()
    {
        $invoices = Invoice::with(['user', 'client'])->get();
        $csvData = [];
        $csvData[] = ['ID', 'Factuurnummer', 'Klant', 'Gebruiker', 'Status', 'Bedrag', 'Datum', 'Vervaldatum'];
        
        foreach ($invoices as $invoice) {
            $csvData[] = [
                $invoice->id,
                $invoice->invoice_number,
                $invoice->client->name ?? '',
                $invoice->user->name ?? '',
                $invoice->status,
                $invoice->total_amount,
                $invoice->issue_date->format('d-m-Y'),
                $invoice->due_date->format('d-m-Y'),
            ];
        }
        
        $filename = 'invoices_export_' . now()->format('Y-m-d_H-i-s') . '.csv';
        $this->saveCsvFile($csvData, $filename);
        
        Notification::make()
            ->title('Export voltooid')
            ->body("Facturen geëxporteerd naar {$filename}")
            ->success()
            ->send();
    }

    public function exportExpenses()
    {
        $expenses = Expense::with(['user', 'project'])->get();
        $csvData = [];
        $csvData[] = ['ID', 'Beschrijving', 'Leverancier', 'Categorie', 'Bedrag', 'Datum', 'Gebruiker', 'Project'];
        
        foreach ($expenses as $expense) {
            $csvData[] = [
                $expense->id,
                $expense->description,
                $expense->vendor ?? '',
                $expense->category,
                $expense->amount,
                $expense->expense_date->format('d-m-Y'),
                $expense->user->name ?? '',
                $expense->project->name ?? '',
            ];
        }
        
        $filename = 'expenses_export_' . now()->format('Y-m-d_H-i-s') . '.csv';
        $this->saveCsvFile($csvData, $filename);
        
        Notification::make()
            ->title('Export voltooid')
            ->body("Uitgaven geëxporteerd naar {$filename}")
            ->success()
            ->send();
    }

    public function exportClients()
    {
        $clients = Client::with('user')->get();
        $csvData = [];
        $csvData[] = ['ID', 'Naam', 'E-mail', 'Bedrijf', 'Telefoon', 'Stad', 'Land', 'Gebruiker'];
        
        foreach ($clients as $client) {
            $csvData[] = [
                $client->id,
                $client->name,
                $client->email ?? '',
                $client->company ?? '',
                $client->phone ?? '',
                $client->city ?? '',
                $client->country ?? '',
                $client->user->name ?? '',
            ];
        }
        
        $filename = 'clients_export_' . now()->format('Y-m-d_H-i-s') . '.csv';
        $this->saveCsvFile($csvData, $filename);
        
        Notification::make()
            ->title('Export voltooid')
            ->body("Klanten geëxporteerd naar {$filename}")
            ->success()
            ->send();
    }

    private function saveCsvFile(array $data, string $filename): void
    {
        $filepath = storage_path('app/exports/' . $filename);
        
        // Ensure directory exists
        if (!file_exists(dirname($filepath))) {
            mkdir(dirname($filepath), 0755, true);
        }
        
        $file = fopen($filepath, 'w');
        
        // Add BOM for proper UTF-8 encoding in Excel
        fwrite($file, "\xEF\xBB\xBF");
        
        foreach ($data as $row) {
            fputcsv($file, $row, ';'); // Use semicolon for Dutch Excel compatibility
        }
        
        fclose($file);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export_users')
                ->label('Exporteer Gebruikers')
                ->icon('heroicon-o-users')
                ->color('primary')
                ->action('exportUsers'),
            
            Action::make('export_invoices')
                ->label('Exporteer Facturen')
                ->icon('heroicon-o-document-text')
                ->color('success')
                ->action('exportInvoices'),
            
            Action::make('export_expenses')
                ->label('Exporteer Uitgaven')
                ->icon('heroicon-o-banknotes')
                ->color('warning')
                ->action('exportExpenses'),
            
            Action::make('export_clients')
                ->label('Exporteer Klanten')
                ->icon('heroicon-o-user-group')
                ->color('info')
                ->action('exportClients'),
        ];
    }
}
