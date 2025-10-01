<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Only add indexes if tables exist
        if (Schema::hasTable('invoices')) {
            Schema::table('invoices', function (Blueprint $table) {
                // Add indexes for frequently queried fields
                $table->index(['user_id', 'status']);
                $table->index(['user_id', 'due_date']);
                $table->index(['user_id', 'created_at']);
                $table->index(['client_id']);
                $table->index(['project_id']);
                $table->index(['status']);
                $table->index(['due_date']);
                $table->index(['paid_date']);
            });
        }

        if (Schema::hasTable('expenses')) {
            Schema::table('expenses', function (Blueprint $table) {
                // Add indexes for frequently queried fields
                $table->index(['user_id', 'expense_date']);
                $table->index(['user_id', 'category']);
                $table->index(['project_id']);
                $table->index(['expense_date']);
                $table->index(['category']);
            });
        }

        if (Schema::hasTable('clients')) {
            Schema::table('clients', function (Blueprint $table) {
                // Add indexes for frequently queried fields
                $table->index(['user_id']);
                $table->index(['email']);
            });
        }

        if (Schema::hasTable('projects')) {
            Schema::table('projects', function (Blueprint $table) {
                // Add indexes for frequently queried fields
                $table->index(['user_id', 'status']);
                $table->index(['client_id']);
                $table->index(['status']);
            });
        }

        if (Schema::hasTable('payments')) {
            Schema::table('payments', function (Blueprint $table) {
                // Add indexes for frequently queried fields
                $table->index(['invoice_id']);
                $table->index(['payment_date']);
            });
        }

        if (Schema::hasTable('invoice_items')) {
            Schema::table('invoice_items', function (Blueprint $table) {
                // Add indexes for frequently queried fields
                $table->index(['invoice_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Only drop indexes if tables exist
        if (Schema::hasTable('invoices')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->dropIndex(['user_id', 'status']);
                $table->dropIndex(['user_id', 'due_date']);
                $table->dropIndex(['user_id', 'created_at']);
                $table->dropIndex(['client_id']);
                $table->dropIndex(['project_id']);
                $table->dropIndex(['status']);
                $table->dropIndex(['due_date']);
                $table->dropIndex(['paid_date']);
            });
        }

        if (Schema::hasTable('expenses')) {
            Schema::table('expenses', function (Blueprint $table) {
                $table->dropIndex(['user_id', 'expense_date']);
                $table->dropIndex(['user_id', 'category']);
                $table->dropIndex(['project_id']);
                $table->dropIndex(['expense_date']);
                $table->dropIndex(['category']);
            });
        }

        if (Schema::hasTable('clients')) {
            Schema::table('clients', function (Blueprint $table) {
                $table->dropIndex(['user_id']);
                $table->dropIndex(['email']);
            });
        }

        if (Schema::hasTable('projects')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->dropIndex(['user_id', 'status']);
                $table->dropIndex(['client_id']);
                $table->dropIndex(['status']);
            });
        }

        if (Schema::hasTable('payments')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->dropIndex(['invoice_id']);
                $table->dropIndex(['payment_date']);
            });
        }

        if (Schema::hasTable('invoice_items')) {
            Schema::table('invoice_items', function (Blueprint $table) {
                $table->dropIndex(['invoice_id']);
            });
        }
    }
};
