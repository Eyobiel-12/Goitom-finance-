<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use App\Services\DashboardService;
use Illuminate\Console\Command;

final class RefreshDashboardCache extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'dashboard:refresh-cache';

    /**
     * The console command description.
     */
    protected $description = 'Refresh dashboard cache for all users';

    public function __construct(
        private readonly DashboardService $dashboardService
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Refreshing dashboard cache for all users...');

        $users = User::all();
        $bar = $this->output->createProgressBar($users->count());

        foreach ($users as $user) {
            $this->dashboardService->clearDashboardCache($user);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Dashboard cache refreshed for {$users->count()} users.");

        return Command::SUCCESS;
    }
}