<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table): void {
            $table->id();
            $table->morphs('documentable'); // client/project/invoice/expense
            $table->string('disk')->default('s3');
            $table->string('path');
            $table->string('original_name');
            $table->unsignedBigInteger('size')->nullable();
            $table->string('mime_type', 191)->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};


