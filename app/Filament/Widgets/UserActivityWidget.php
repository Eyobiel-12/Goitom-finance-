<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class UserActivityWidget extends BaseWidget
{
    protected ?string $heading = 'Gebruikersactiviteit';
    protected static ?int $sort = 4;

    protected function getStats(): array
    {
        $newUsersThisWeek = User::where('created_at', '>=', now()->subWeek())->count();
        $activeUsersThisMonth = User::where('created_at', '>=', now()->subMonth())->count();
        $verifiedUsers = User::whereNotNull('email_verified_at')->count();
        $adminUsers = User::whereIn('role', ['admin', 'super_admin'])->count();

        return [
            Stat::make('Nieuwe Gebruikers (7d)', $newUsersThisWeek)
                ->description('Nieuwe aanmeldingen deze week')
                ->descriptionIcon('heroicon-m-user-plus')
                ->color('success'),
            Stat::make('Actieve Gebruikers (30d)', $activeUsersThisMonth)
                ->description('Gebruikers actief deze maand')
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),
            Stat::make('Geverifieerde Gebruikers', $verifiedUsers)
                ->description('E-mail geverifieerd')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
            Stat::make('Admin Gebruikers', $adminUsers)
                ->description('Admin en Super Admin rollen')
                ->descriptionIcon('heroicon-m-shield-check')
                ->color('warning'),
        ];
    }
}
