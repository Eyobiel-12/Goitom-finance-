<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SupportTicketResource\Pages;
use App\Filament\Resources\SupportTicketResource\RelationManagers;
use App\Models\SupportTicket;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SupportTicketResource extends Resource
{
    protected static ?string $model = SupportTicket::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';
    protected static ?string $navigationLabel = 'Support Tickets';
    protected static ?string $modelLabel = 'Support Ticket';
    protected static ?string $pluralModelLabel = 'Support Tickets';
    protected static ?int $navigationSort = 6;
    protected static ?string $navigationGroup = 'Support';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->label('Gebruiker')
                    ->required()
                    ->searchable(),
                Forms\Components\TextInput::make('subject')
                    ->label('Onderwerp')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->label('Beschrijving')
                    ->required()
                    ->rows(4)
                    ->columnSpanFull(),
                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        'open' => 'Open',
                        'in_progress' => 'In Behandeling',
                        'resolved' => 'Opgelost',
                        'closed' => 'Gesloten',
                    ])
                    ->required(),
                Forms\Components\Select::make('priority')
                    ->label('Prioriteit')
                    ->options([
                        'low' => 'Laag',
                        'medium' => 'Gemiddeld',
                        'high' => 'Hoog',
                        'urgent' => 'Urgent',
                    ])
                    ->required(),
                Forms\Components\Select::make('assigned_to')
                    ->relationship('assignedTo', 'name')
                    ->label('Toegewezen aan')
                    ->searchable(),
                Forms\Components\Textarea::make('resolution')
                    ->label('Oplossing')
                    ->rows(3)
                    ->columnSpanFull(),
                Forms\Components\DateTimePicker::make('resolved_at')
                    ->label('Opgelost op'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Gebruiker')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('subject')
                    ->label('Onderwerp')
                    ->searchable()
                    ->limit(40),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'open' => 'gray',
                        'in_progress' => 'warning',
                        'resolved' => 'success',
                        'closed' => 'danger',
                    }),
                Tables\Columns\TextColumn::make('priority')
                    ->label('Prioriteit')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'low' => 'gray',
                        'medium' => 'info',
                        'high' => 'warning',
                        'urgent' => 'danger',
                    }),
                Tables\Columns\TextColumn::make('assignedTo.name')
                    ->label('Toegewezen aan')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Aangemaakt')
                    ->dateTime('d-m-Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'open' => 'Open',
                        'in_progress' => 'In Behandeling',
                        'resolved' => 'Opgelost',
                        'closed' => 'Gesloten',
                    ]),
                Tables\Filters\SelectFilter::make('priority')
                    ->label('Prioriteit')
                    ->options([
                        'low' => 'Laag',
                        'medium' => 'Gemiddeld',
                        'high' => 'Hoog',
                        'urgent' => 'Urgent',
                    ]),
                Tables\Filters\SelectFilter::make('assigned_to')
                    ->label('Toegewezen aan')
                    ->relationship('assignedTo', 'name'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('resolve')
                    ->label('Oplossen')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(function (SupportTicket $record) {
                        $record->update([
                            'status' => 'resolved',
                            'resolved_at' => now(),
                        ]);
                    })
                    ->visible(fn (SupportTicket $record): bool => $record->status !== 'resolved'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('assign_to_me')
                        ->label('Toewijzen aan mij')
                        ->icon('heroicon-o-user-plus')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->update(['assigned_to' => auth()->id()]);
                            }
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSupportTickets::route('/'),
            'create' => Pages\CreateSupportTicket::route('/create'),
            'edit' => Pages\EditSupportTicket::route('/{record}/edit'),
        ];
    }
}
