<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExpenseResource\Pages;
use App\Filament\Resources\ExpenseResource\RelationManagers;
use App\Models\Expense;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ExpenseResource extends Resource
{
    protected static ?string $model = Expense::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Uitgaven';
    protected static ?string $modelLabel = 'Uitgave';
    protected static ?string $pluralModelLabel = 'Uitgaven';
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationGroup = 'Finance';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->label('Gebruiker')
                    ->required()
                    ->searchable(),
                Forms\Components\Select::make('project_id')
                    ->relationship('project', 'name')
                    ->label('Project')
                    ->searchable(),
                Forms\Components\TextInput::make('description')
                    ->label('Beschrijving')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('vendor')
                    ->label('Leverancier')
                    ->maxLength(255),
                Forms\Components\Select::make('category')
                    ->label('Categorie')
                    ->options([
                        'office' => 'Kantoor',
                        'travel' => 'Reizen',
                        'meals' => 'Maaltijden',
                        'equipment' => 'Uitrusting',
                        'software' => 'Software',
                        'marketing' => 'Marketing',
                        'utilities' => 'Nutsvoorzieningen',
                        'other' => 'Overig',
                    ])
                    ->required()
                    ->searchable(),
                Forms\Components\TextInput::make('amount')
                    ->label('Bedrag')
                    ->required()
                    ->numeric()
                    ->prefix('€'),
                Forms\Components\DatePicker::make('expense_date')
                    ->label('Uitgave Datum')
                    ->required(),
                Forms\Components\TextInput::make('receipt_path')
                    ->label('Bon Pad')
                    ->maxLength(255),
                Forms\Components\Textarea::make('notes')
                    ->label('Notities')
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('is_billable')
                    ->label('Factureerbaar')
                    ->required(),
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
                Tables\Columns\TextColumn::make('description')
                    ->label('Beschrijving')
                    ->searchable()
                    ->limit(30),
                Tables\Columns\TextColumn::make('vendor')
                    ->label('Leverancier')
                    ->searchable(),
                Tables\Columns\TextColumn::make('category')
                    ->label('Categorie')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'office' => 'blue',
                        'travel' => 'green',
                        'meals' => 'orange',
                        'equipment' => 'purple',
                        'software' => 'indigo',
                        'marketing' => 'pink',
                        'utilities' => 'yellow',
                        'other' => 'gray',
                    }),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Bedrag')
                    ->money('EUR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('expense_date')
                    ->label('Datum')
                    ->date('d-m-Y')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_billable')
                    ->label('Factureerbaar')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Aangemaakt')
                    ->dateTime('d-m-Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->label('Categorie')
                    ->options([
                        'office' => 'Kantoor',
                        'travel' => 'Reizen',
                        'meals' => 'Maaltijden',
                        'equipment' => 'Uitrusting',
                        'software' => 'Software',
                        'marketing' => 'Marketing',
                        'utilities' => 'Nutsvoorzieningen',
                        'other' => 'Overig',
                    ]),
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Gebruiker')
                    ->relationship('user', 'name'),
                Tables\Filters\Filter::make('is_billable')
                    ->label('Factureerbaar')
                    ->query(fn (Builder $query): Builder => $query->where('is_billable', true)),
                Tables\Filters\Filter::make('expense_date')
                    ->form([
                        Forms\Components\DatePicker::make('expense_from')
                            ->label('Van datum'),
                        Forms\Components\DatePicker::make('expense_until')
                            ->label('Tot datum'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['expense_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('expense_date', '>=', $date),
                            )
                            ->when(
                                $data['expense_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('expense_date', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('mark_as_billable')
                        ->label('Markeer als Factureerbaar')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->update(['is_billable' => true]);
                            }
                        })
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('mark_as_non_billable')
                        ->label('Markeer als Niet Factureerbaar')
                        ->icon('heroicon-o-x-circle')
                        ->color('gray')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->update(['is_billable' => false]);
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
            'index' => Pages\ListExpenses::route('/'),
            'create' => Pages\CreateExpense::route('/create'),
            'edit' => Pages\EditExpense::route('/{record}/edit'),
        ];
    }
}
