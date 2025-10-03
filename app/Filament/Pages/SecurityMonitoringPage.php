<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use App\Models\AuditLog;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;

class SecurityMonitoringPage extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';
    protected static string $view = 'filament.pages.security-monitoring';
    protected static ?string $navigationLabel = 'Security Monitoring';
    protected static ?string $title = 'Security & Activity Monitoring';
    protected static ?string $navigationGroup = 'Security';
    protected static ?int $navigationSort = 10;

    public function table(Table $table): Table
    {
        return $table
            ->query(AuditLog::query()->latest())
            ->columns([
                TextColumn::make('user.name')
                    ->label('Gebruiker')
                    ->searchable()
                    ->sortable(),
                
                BadgeColumn::make('action')
                    ->label('Actie')
                    ->colors([
                        'success' => 'created',
                        'warning' => 'updated',
                        'danger' => 'deleted',
                        'gray' => 'viewed',
                    ]),
                
                TextColumn::make('model_type')
                    ->label('Model')
                    ->formatStateUsing(fn (string $state): string => class_basename($state))
                    ->searchable(),
                
                TextColumn::make('model_id')
                    ->label('ID')
                    ->numeric()
                    ->sortable(),
                
                TextColumn::make('ip_address')
                    ->label('IP Adres')
                    ->searchable()
                    ->copyable(),
                
                TextColumn::make('user_agent')
                    ->label('Browser')
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 50 ? $state : null;
                    }),
                
                TextColumn::make('created_at')
                    ->label('Tijd')
                    ->dateTime('d-m-Y H:i:s')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('action')
                    ->label('Actie')
                    ->options([
                        'created' => 'Aangemaakt',
                        'updated' => 'Bijgewerkt',
                        'deleted' => 'Verwijderd',
                        'viewed' => 'Bekeken',
                    ]),
                
                SelectFilter::make('model_type')
                    ->label('Model')
                    ->options([
                        'App\Models\User' => 'Gebruikers',
                        'App\Models\Invoice' => 'Facturen',
                        'App\Models\Expense' => 'Uitgaven',
                        'App\Models\Client' => 'Klanten',
                        'App\Models\Project' => 'Projecten',
                    ]),
                
                Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_from')
                            ->label('Van datum'),
                        DatePicker::make('created_until')
                            ->label('Tot datum'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('30s'); // Auto-refresh every 30 seconds
    }

    public function getSecurityStats(): array
    {
        $last24Hours = now()->subDay();
        $last7Days = now()->subWeek();
        
        return [
            'total_logs' => AuditLog::count(),
            'logs_24h' => AuditLog::where('created_at', '>=', $last24Hours)->count(),
            'logs_7d' => AuditLog::where('created_at', '>=', $last7Days)->count(),
            'unique_users' => AuditLog::distinct('user_id')->count('user_id'),
            'unique_ips' => AuditLog::distinct('ip_address')->count('ip_address'),
            'deletions_24h' => AuditLog::where('action', 'deleted')
                ->where('created_at', '>=', $last24Hours)
                ->count(),
        ];
    }
}
