<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceResource\Pages;
use App\Filament\Resources\InvoiceResource\RelationManagers;
use App\Models\Invoice;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Facturen';
    protected static ?string $modelLabel = 'Factuur';
    protected static ?string $pluralModelLabel = 'Facturen';
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationGroup = 'Billing';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->label('Gebruiker')
                    ->required()
                    ->searchable(),
                Forms\Components\Select::make('client_id')
                    ->relationship('client', 'name')
                    ->label('Klant')
                    ->required()
                    ->searchable(),
                Forms\Components\Select::make('project_id')
                    ->relationship('project', 'name')
                    ->label('Project')
                    ->searchable(),
                Forms\Components\TextInput::make('invoice_number')
                    ->label('Factuurnummer')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Concept',
                        'sent' => 'Verzonden',
                        'paid' => 'Betaald',
                        'overdue' => 'Achterstallig',
                        'cancelled' => 'Geannuleerd',
                    ])
                    ->required(),
                Forms\Components\DatePicker::make('issue_date')
                    ->label('Factuurdatum')
                    ->required(),
                Forms\Components\DatePicker::make('due_date')
                    ->label('Vervaldatum')
                    ->required(),
                Forms\Components\DatePicker::make('paid_date')
                    ->label('Betaaldatum'),
                Forms\Components\TextInput::make('subtotal')
                    ->label('Subtotaal')
                    ->required()
                    ->numeric()
                    ->prefix('€'),
                Forms\Components\TextInput::make('tax_rate')
                    ->label('BTW Percentage')
                    ->required()
                    ->numeric()
                    ->default(21.00)
                    ->suffix('%'),
                Forms\Components\TextInput::make('tax_amount')
                    ->label('BTW Bedrag')
                    ->required()
                    ->numeric()
                    ->prefix('€'),
                Forms\Components\TextInput::make('total_amount')
                    ->label('Totaal Bedrag')
                    ->required()
                    ->numeric()
                    ->prefix('€'),
                Forms\Components\Textarea::make('notes')
                    ->label('Notities')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('terms')
                    ->label('Voorwaarden')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('currency')
                    ->label('Valuta')
                    ->required()
                    ->maxLength(3)
                    ->default('EUR'),
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
                Tables\Columns\TextColumn::make('client.name')
                    ->label('Klant')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('invoice_number')
                    ->label('Factuurnummer')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'sent' => 'warning',
                        'paid' => 'success',
                        'overdue' => 'danger',
                        'cancelled' => 'gray',
                    }),
                Tables\Columns\TextColumn::make('issue_date')
                    ->label('Factuurdatum')
                    ->date('d-m-Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('due_date')
                    ->label('Vervaldatum')
                    ->date('d-m-Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Totaal')
                    ->money('EUR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Aangemaakt')
                    ->dateTime('d-m-Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Concept',
                        'sent' => 'Verzonden',
                        'paid' => 'Betaald',
                        'overdue' => 'Achterstallig',
                        'cancelled' => 'Geannuleerd',
                    ]),
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Gebruiker')
                    ->relationship('user', 'name'),
                Tables\Filters\Filter::make('overdue')
                    ->label('Achterstallig')
                    ->query(fn (Builder $query): Builder => $query->where('due_date', '<', now())->where('status', '!=', 'paid')),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Van datum'),
                        Forms\Components\DatePicker::make('created_until')
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
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('mark_paid')
                    ->label('Markeer als Betaald')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(function (Invoice $record) {
                        $record->update([
                            'status' => 'paid',
                            'paid_date' => now(),
                        ]);
                    })
                    ->visible(fn (Invoice $record): bool => $record->status !== 'paid'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('mark_as_paid')
                        ->label('Markeer als Betaald')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->update([
                                    'status' => 'paid',
                                    'paid_date' => now(),
                                ]);
                            }
                        })
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('mark_as_sent')
                        ->label('Markeer als Verzonden')
                        ->icon('heroicon-o-paper-airplane')
                        ->color('warning')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                if ($record->status === 'draft') {
                                    $record->update(['status' => 'sent']);
                                }
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
            'index' => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'edit' => Pages\EditInvoice::route('/{record}/edit'),
        ];
    }
}
