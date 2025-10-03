<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Drop existing unique index on invoice_number (if exists)
            try {
                $table->dropUnique('invoices_invoice_number_unique');
            } catch (\Throwable $e) {
                // ignore if index name doesn't exist on this environment
            }

            // Add composite unique index per user
            $table->unique(['user_id', 'invoice_number'], 'invoices_user_invoice_unique');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Drop composite and restore old unique on invoice_number
            try { $table->dropUnique('invoices_user_invoice_unique'); } catch (\Throwable $e) {}
            $table->unique('invoice_number', 'invoices_invoice_number_unique');
        });
    }
};


