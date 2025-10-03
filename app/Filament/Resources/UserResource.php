<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Filament\Resources\UserResource\Actions\BulkUserActions;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Gebruikers';
    protected static ?string $modelLabel = 'Gebruiker';
    protected static ?string $pluralModelLabel = 'Gebruikers';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationGroup = 'Users';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255),
                Forms\Components\DateTimePicker::make('email_verified_at'),
                Forms\Components\Select::make('role')
                    ->options([
                        'user' => 'Gebruiker',
                        'support' => 'Support',
                        'admin' => 'Admin',
                        'super_admin' => 'Super Admin',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('business_name')
                    ->maxLength(255),
                Forms\Components\TextInput::make('vat_number')
                    ->maxLength(255),
                Forms\Components\Textarea::make('business_address')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('business_city')
                    ->maxLength(255),
                Forms\Components\TextInput::make('business_postal_code')
                    ->maxLength(255),
                Forms\Components\TextInput::make('business_country')
                    ->required()
                    ->maxLength(255)
                    ->default('NL'),
                Forms\Components\TextInput::make('business_phone')
                    ->tel()
                    ->maxLength(255),
                Forms\Components\TextInput::make('business_email')
                    ->email()
                    ->maxLength(255),
                Forms\Components\TextInput::make('logo_path')
                    ->maxLength(255),
                Forms\Components\TextInput::make('currency')
                    ->required()
                    ->maxLength(3)
                    ->default('EUR'),
                Forms\Components\TextInput::make('language')
                    ->required()
                    ->maxLength(5)
                    ->default('en'),
                Forms\Components\Toggle::make('onboarding_completed')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email_verified_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('role')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'super_admin' => 'danger',
                        'admin' => 'warning',
                        'support' => 'info',
                        'user' => 'success',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('business_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('vat_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('business_city')
                    ->searchable(),
                Tables\Columns\TextColumn::make('business_postal_code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('business_country')
                    ->searchable(),
                Tables\Columns\TextColumn::make('business_phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('business_email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('logo_path')
                    ->searchable(),
                Tables\Columns\TextColumn::make('currency')
                    ->searchable(),
                Tables\Columns\TextColumn::make('language')
                    ->searchable(),
                Tables\Columns\IconColumn::make('onboarding_completed')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->options([
                        'user' => 'Gebruiker',
                        'support' => 'Support',
                        'admin' => 'Admin',
                        'super_admin' => 'Super Admin',
                    ]),
                Tables\Filters\Filter::make('verified')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('email_verified_at')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                       Tables\Actions\Action::make('impersonate')
                           ->label('Inloggen als')
                           ->icon('heroicon-o-user-circle')
                           ->color('warning')
                           ->url(fn (User $record): string => route('admin.impersonate', $record))
                           ->openUrlInNewTab(false)
                           ->visible(fn (User $record): bool => 
                               auth()->user()->canImpersonate() && $record->canBeImpersonated()
                           ),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    BulkUserActions::verifyEmails(),
                    BulkUserActions::promoteToAdmin(),
                    BulkUserActions::sendNotification(),
                    BulkUserActions::exportUsers(),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
