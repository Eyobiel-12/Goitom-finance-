<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\Invoice;
use App\Models\SupportTicket;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class SaaSOverviewWidget extends BaseWidget
{
    protected ?string $heading = 'Overzicht';
    protected static ?int $sort = 0;

    protected function getStats(): array
    {
        $activeUsers = User::where('created_at', '>=', now()->subDays(30))->count();
        $mrr = Invoice::where('status', 'paid')
            ->where('created_at', '>=', now()->subMonth())
            ->sum('total_amount');
        $newSignups = User::where('created_at', '>=', now()->subWeek())->count();
        $openTickets = SupportTicket::where('status', 'open')->count();

        return [
            Stat::make('Actieve Gebruikers (30d)', $activeUsers)
                ->description('Gebruikers actief deze maand')
                ->descriptionIcon('heroicon-m-users')
                ->color('success'),
            Stat::make('Maandelijkse Omzet', '€' . number_format($mrr, 2, ',', '.'))
                ->description('Recurring Revenue deze maand')
                ->descriptionIcon('heroicon-m-currency-euro')
                ->color('primary'),
            Stat::make('Nieuwe Aanmeldingen', $newSignups)
                ->description('Deze week')
                ->descriptionIcon('heroicon-m-user-plus')
                ->color('info'),
            Stat::make('Open Support Tickets', $openTickets)
                ->description('Wachten op behandeling')
                ->descriptionIcon('heroicon-m-ticket')
                ->color('warning'),
        ];
    }
}
