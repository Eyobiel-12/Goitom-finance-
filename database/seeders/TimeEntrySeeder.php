<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TimeEntry;
use App\Models\Project;
use App\Models\User;

final class TimeEntrySeeder extends Seeder
{
    public function run(): void
    {
        $project = Project::query()->first();
        $user = User::query()->first();
        if (! $project || ! $user) {
            return;
        }

        for ($d = 0; $d < 5; $d++) {
            TimeEntry::create([
                'project_id' => $project->id,
                'user_id' => $user->id,
                'work_date' => now()->subDays($d)->toDateString(),
                'hours' => 2.5,
                'rate' => 65,
                'description' => 'Seeded work entry ' . ($d + 1),
            ]);
        }
    }
}


