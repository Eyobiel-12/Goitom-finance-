<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ImportJob;
use App\Models\Expense;
use App\Models\User;
use App\Services\ExpenseCategorizationService;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

final class BankStatementImportService
{
    public function __construct(
        private readonly ExpenseCategorizationService $categorizationService
    ) {}

    /**
     * Process CSV file and create expenses
     */
    public function processBankStatement(User $user, string $filePath, array $mapping): ImportJob
    {
        $importJob = ImportJob::create([
            'user_id' => $user->id,
            'type' => 'bank_statement',
            'filename' => basename($filePath),
            'file_path' => $filePath,
            'mapping' => $mapping,
            'status' => 'processing',
        ]);

        try {
            $this->processCsvFile($importJob, $user);
            $importJob->update(['status' => 'completed']);
        } catch (\Exception $e) {
            $importJob->update([
                'status' => 'failed',
                'notes' => $e->getMessage(),
            ]);
        }

        return $importJob;
    }

    /**
     * Process the actual CSV file
     */
    private function processCsvFile(ImportJob $importJob, User $user): void
    {
        $filePath = $importJob->file_path;
        $mapping = $importJob->mapping;

        if (!file_exists($filePath)) {
            throw new \Exception("File not found: {$filePath}");
        }

        $handle = fopen($filePath, 'r');
        if (!$handle) {
            throw new \Exception("Could not open file: {$filePath}");
        }

        $rowCount = 0;
        $successfulRows = 0;
        $failedRows = 0;
        $errors = [];

        // Skip header row
        $header = fgetcsv($handle);
        $rowCount++;

        while (($row = fgetcsv($handle)) !== false) {
            $rowCount++;
            
            try {
                $this->processRow($row, $mapping, $user);
                $successfulRows++;
            } catch (\Exception $e) {
                $failedRows++;
                $errors[] = "Row {$rowCount}: " . $e->getMessage();
            }

            // Update progress every 10 rows
            if ($rowCount % 10 === 0) {
                $importJob->update([
                    'processed_rows' => $rowCount,
                    'successful_rows' => $successfulRows,
                    'failed_rows' => $failedRows,
                    'errors' => $errors,
                ]);
            }
        }

        fclose($handle);

        // Final update
        $importJob->update([
            'total_rows' => $rowCount - 1, // Exclude header
            'processed_rows' => $rowCount - 1,
            'successful_rows' => $successfulRows,
            'failed_rows' => $failedRows,
            'errors' => $errors,
        ]);
    }

    /**
     * Process a single CSV row
     */
    private function processRow(array $row, array $mapping, User $user): void
    {
        $data = $this->mapRowData($row, $mapping);
        
        // Validate required fields
        if (empty($data['amount']) || empty($data['date'])) {
            throw new \Exception('Missing required fields: amount or date');
        }

        // Parse amount (handle different formats)
        $amount = $this->parseAmount($data['amount']);
        if ($amount === null) {
            throw new \Exception('Invalid amount format: ' . $data['amount']);
        }

        // Parse date
        $date = $this->parseDate($data['date']);
        if (!$date) {
            throw new \Exception('Invalid date format: ' . $data['date']);
        }

        // Auto-categorize if vendor is provided
        $category = $data['category'] ?? null;
        if (!$category && !empty($data['vendor'])) {
            $category = $this->categorizationService->categorizeExpense(
                $user,
                $data['vendor'],
                $data['description'] ?? ''
            );
        }

        // Create expense
        Expense::create([
            'user_id' => $user->id,
            'description' => $data['description'] ?? 'Bank import: ' . ($data['vendor'] ?? 'Unknown'),
            'vendor' => $data['vendor'] ?? null,
            'category' => $category ?? 'Overig',
            'amount' => abs($amount), // Always positive for expenses
            'expense_date' => $date,
            'notes' => 'Imported from bank statement',
            'is_billable' => false,
        ]);
    }

    /**
     * Map CSV row data according to mapping configuration
     */
    private function mapRowData(array $row, array $mapping): array
    {
        $data = [];
        
        foreach ($mapping as $field => $columnIndex) {
            if (isset($row[$columnIndex])) {
                $data[$field] = trim($row[$columnIndex]);
            }
        }

        return $data;
    }

    /**
     * Parse amount from various formats
     */
    private function parseAmount(string $amount): ?float
    {
        // Remove currency symbols and spaces
        $amount = preg_replace('/[€$£¥\s]/', '', $amount);
        
        // Replace comma with dot for decimal
        $amount = str_replace(',', '.', $amount);
        
        // Remove thousands separators
        $amount = preg_replace('/\.(?=\d{3})/', '', $amount);
        
        return is_numeric($amount) ? (float) $amount : null;
    }

    /**
     * Parse date from various formats
     */
    private function parseDate(string $date): ?Carbon
    {
        $formats = [
            'Y-m-d',
            'd-m-Y',
            'd/m/Y',
            'm/d/Y',
            'Y/m/d',
            'd.m.Y',
        ];

        foreach ($formats as $format) {
            try {
                return Carbon::createFromFormat($format, $date);
            } catch (\Exception $e) {
                continue;
            }
        }

        return null;
    }

    /**
     * Get suggested column mapping for CSV headers
     */
    public function suggestMapping(array $headers): array
    {
        $suggestions = [];
        
        foreach ($headers as $index => $header) {
            $header = strtolower(trim($header));
            
            if (str_contains($header, 'datum') || str_contains($header, 'date')) {
                $suggestions['date'] = $index;
            } elseif (str_contains($header, 'bedrag') || str_contains($header, 'amount') || str_contains($header, 'saldo')) {
                $suggestions['amount'] = $index;
            } elseif (str_contains($header, 'omschrijving') || str_contains($header, 'description') || str_contains($header, 'naam')) {
                $suggestions['description'] = $index;
            } elseif (str_contains($header, 'tegenrekening') || str_contains($header, 'account')) {
                $suggestions['vendor'] = $index;
            } elseif (str_contains($header, 'categorie') || str_contains($header, 'category')) {
                $suggestions['category'] = $index;
            }
        }

        return $suggestions;
    }
}
