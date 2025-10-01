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
        Schema::create('recurring_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->foreignId('project_id')->nullable()->constrained()->onDelete('set null');
            
            // Template data
            $table->string('template_name'); // "Monthly Website Maintenance"
            $table->json('invoice_data'); // Complete invoice data as JSON
            
            // Recurrence settings
            $table->enum('frequency', ['weekly', 'monthly', 'quarterly', 'yearly']);
            $table->integer('day_of_month')->nullable(); // 1-31 for monthly/quarterly/yearly
            $table->integer('day_of_week')->nullable(); // 1-7 for weekly (1=Monday)
            
            // Status and dates
            $table->boolean('is_active')->default(true);
            $table->date('start_date');
            $table->date('end_date')->nullable(); // Optional end date
            $table->date('last_generated')->nullable();
            $table->date('next_due')->nullable();
            
            // Auto-send settings
            $table->boolean('auto_send')->default(false);
            $table->integer('send_days_before')->default(0); // Send X days before due date
            
            $table->timestamps();
            
            $table->index(['user_id', 'is_active']);
            $table->index(['next_due', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recurring_invoices');
    }
};