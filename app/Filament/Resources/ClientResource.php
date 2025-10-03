<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClientResource\Pages;
use App\Filament\Resources\ClientResource\RelationManagers;
use App\Models\Client;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ClientResource extends Resource
{
    protected static ?string $model = Client::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Klanten';
    protected static ?string $modelLabel = 'Klant';
    protected static ?string $pluralModelLabel = 'Klanten';
    protected static ?int $navigationSort = 4;
    protected static ?string $navigationGroup = 'CRM';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->label('Gebruiker')
                    ->required()
                    ->searchable(),
                Forms\Components\TextInput::make('name')
                    ->label('Naam')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->label('E-mail')
                    ->email()
                    ->maxLength(255),
                Forms\Components\TextInput::make('phone')
                    ->label('Telefoon')
                    ->tel()
                    ->maxLength(255),
                Forms\Components\TextInput::make('company')
                    ->label('Bedrijf')
                    ->maxLength(255),
                Forms\Components\TextInput::make('vat_number')
                    ->label('BTW Nummer')
                    ->maxLength(255),
                Forms\Components\Textarea::make('address')
                    ->label('Adres')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('city')
                    ->label('Stad')
                    ->maxLength(255),
                Forms\Components\TextInput::make('postal_code')
                    ->label('Postcode')
                    ->maxLength(255),
                Forms\Components\TextInput::make('country')
                    ->label('Land')
                    ->required()
                    ->maxLength(255)
                    ->default('NL'),
                Forms\Components\Textarea::make('notes')
                    ->label('Notities')
                    ->columnSpanFull(),
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
                Tables\Columns\TextColumn::make('name')
                    ->label('Naam')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Telefoon')
                    ->searchable(),
                Tables\Columns\TextColumn::make('company')
                    ->label('Bedrijf')
                    ->searchable(),
                Tables\Columns\TextColumn::make('city')
                    ->label('Stad')
                    ->searchable(),
                Tables\Columns\TextColumn::make('country')
                    ->label('Land')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Aangemaakt')
                    ->dateTime('d-m-Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Gebruiker')
                    ->relationship('user', 'name'),
                Tables\Filters\Filter::make('has_company')
                    ->label('Met Bedrijf')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('company')),
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
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListClients::route('/'),
            'create' => Pages\CreateClient::route('/create'),
            'edit' => Pages\EditClient::route('/{record}/edit'),
        ];
    }
}
