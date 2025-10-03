<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('otp_attempts', function (Blueprint $table) {
            $table->id();
            $table->string('email')->index();
            $table->string('type')->index(); // login|registration|admin
            $table->string('ip_address', 64)->nullable();
            $table->string('user_agent')->nullable();
            $table->boolean('success')->default(false)->index();
            $table->string('reason')->nullable(); // rate_limited|invalid|expired|sent|verified
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otp_attempts');
    }
};


