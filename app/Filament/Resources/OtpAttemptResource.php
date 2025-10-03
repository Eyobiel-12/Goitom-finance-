<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\OtpAttemptResource\Pages;
use App\Models\OtpAttempt;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Filters\Filter;
use Illuminate\Support\Collection;

final class OtpAttemptResource extends Resource
{
    protected static ?string $model = OtpAttempt::class;
    protected static ?string $navigationGroup = 'Audit & Logs';
    protected static ?string $navigationIcon = 'heroicon-o-shield-check';
    protected static ?string $navigationLabel = 'OTP Attempts';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('email')->disabled(),
            Forms\Components\TextInput::make('type')->disabled(),
            Forms\Components\TextInput::make('ip_address')->disabled(),
            Forms\Components\TextInput::make('user_agent')->disabled(),
            Forms\Components\Toggle::make('success')->disabled(),
            Forms\Components\TextInput::make('reason')->disabled(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('created_at')->label('Tijd')->dateTime()->sortable()->searchable(),
            Tables\Columns\TextColumn::make('email')->searchable(),
            Tables\Columns\BadgeColumn::make('type')->colors([
                'primary' => 'login',
                'warning' => 'registration',
                'info' => 'admin',
            ])->sortable(),
            Tables\Columns\IconColumn::make('success')->boolean(),
            Tables\Columns\TextColumn::make('reason')->sortable()->toggleable(),
            Tables\Columns\TextColumn::make('ip_address')->toggleable(),
            Tables\Columns\TextColumn::make('user_agent')->label('Agent')->limit(40)->toggleable(isToggledHiddenByDefault: true),
        ])->filters([
            Tables\Filters\SelectFilter::make('type')->options([
                'login' => 'Login',
                'registration' => 'Registration',
                'admin' => 'Admin',
            ]),
            Tables\Filters\TernaryFilter::make('success'),
            Tables\Filters\Filter::make('ip')
                ->form([
                    Forms\Components\TextInput::make('ip')->label('IP adres')
                ])
                ->query(fn($q, array $data) => $q->when($data['ip'] ?? null, fn($qq, $ip) => $qq->where('ip_address', 'like', "%$ip%"))),
            Tables\Filters\Filter::make('agent')
                ->form([
                    Forms\Components\TextInput::make('agent')->label('User-Agent')
                ])
                ->query(fn($q, array $data) => $q->when($data['agent'] ?? null, fn($qq, $ua) => $qq->where('user_agent', 'like', "%$ua%"))),
            Filter::make('created_at_range')
                ->form([
                    Forms\Components\DatePicker::make('from')->label('Vanaf'),
                    Forms\Components\DatePicker::make('until')->label('Tot'),
                ])
                ->query(function ($query, array $data) {
                    return $query
                        ->when($data['from'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
                        ->when($data['until'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '<=', $date));
                }),
        ])->bulkActions([
            BulkAction::make('exportCsv')
                ->label('Export CSV')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(function (Collection $records, BulkAction $action) {
                    $headers = ['created_at','email','type','success','reason','ip_address','user_agent'];
                    $lines = [];
                    $lines[] = implode(',', $headers);
                    foreach ($records as $r) {
                        $row = [
                            $r->created_at,
                            $r->email,
                            $r->type,
                            $r->success ? '1' : '0',
                            $r->reason,
                            $r->ip_address,
                            str_replace(["\n","\r",','], ' ', (string) $r->user_agent),
                        ];
                        $lines[] = implode(',', array_map(fn($v) => '"'.str_replace('"','""',(string)$v).'"', $row));
                    }
                    $csv = implode("\n", $lines);
                    $action->download($csv, 'otp_attempts.csv');
                })
        ])->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOtpAttempts::route('/'),
        ];
    }
}


