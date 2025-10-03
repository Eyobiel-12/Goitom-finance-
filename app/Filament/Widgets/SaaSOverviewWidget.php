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

        // Drempels
        $mrrColor = $mrr >= 1000 ? 'success' : ($mrr >= 300 ? 'warning' : 'danger');
        $activeColor = $activeUsers >= 10 ? 'success' : ($activeUsers >= 3 ? 'warning' : 'danger');
        $signupColor = $newSignups >= 5 ? 'success' : ($newSignups >= 2 ? 'warning' : 'danger');
        $ticketColor = $openTickets <= 2 ? 'success' : ($openTickets <= 5 ? 'warning' : 'danger');

        return [
            Stat::make('Actieve Gebruikers (30d)', $activeUsers)
                ->description('Gebruikers actief deze maand')
                ->descriptionIcon('heroicon-m-users')
                ->color($activeColor),
            Stat::make('Maandelijkse Omzet', '€' . number_format($mrr, 2, ',', '.'))
                ->description('Recurring Revenue deze maand')
                ->descriptionIcon('heroicon-m-currency-euro')
                ->color($mrrColor),
            Stat::make('Nieuwe Aanmeldingen', $newSignups)
                ->description('Deze week')
                ->descriptionIcon('heroicon-m-user-plus')
                ->color($signupColor),
            Stat::make('Open Support Tickets', $openTickets)
                ->description('Wachten op behandeling')
                ->descriptionIcon('heroicon-m-ticket')
                ->color($ticketColor),
        ];
    }
}
