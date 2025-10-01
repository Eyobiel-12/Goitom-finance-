<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->enum('interval', ['daily','weekly','monthly','quarterly','yearly'])->default('monthly');
            $table->date('next_run_at');
            $table->decimal('amount', 12, 2);
            $table->decimal('vat_rate', 5, 2)->default(0);
            $table->string('currency', 3)->default('EUR');
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};


