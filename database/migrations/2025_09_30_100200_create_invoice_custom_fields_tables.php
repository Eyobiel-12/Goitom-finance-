<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_custom_field_definitions', function (Blueprint $table): void {
            $table->id();
            $table->string('key')->unique();
            $table->string('label');
            $table->enum('type', ['text', 'number', 'date'])->default('text');
            $table->boolean('required')->default(false);
            $table->timestamps();
        });

        Schema::create('invoice_custom_field_values', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('definition_id')->constrained('invoice_custom_field_definitions')->cascadeOnDelete();
            $table->text('value')->nullable();
            $table->timestamps();
            $table->unique(['invoice_id','definition_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_custom_field_values');
        Schema::dropIfExists('invoice_custom_field_definitions');
    }
};


