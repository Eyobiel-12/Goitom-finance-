<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Daily jobs for automation
Schedule::command('dashboard:refresh-cache')->dailyAt('02:00');
Schedule::command('db:backup --compress')->dailyAt('03:00');
Schedule::command('invoices:generate-recurring')->dailyAt('09:00');
Schedule::command('invoices:send-reminders')->dailyAt('10:00');
Schedule::command('otp:cleanup')->hourly(); // Clean up expired OTPs every hour
Schedule::command('model:prune')->dailyAt('04:00'); // Auto-prune prunable models (OtpAttempt > 90 dagen)

// Monthly jobs
Schedule::command('reports:send-monthly')->monthlyOn(1, '08:00');
