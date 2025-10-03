<?php

namespace App\Filament\Resources\UserResource\Actions;

use App\Models\User;
use Filament\Tables\Actions\BulkAction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Mail;
use App\Mail\UserNotificationMail;

class BulkUserActions
{
    public static function verifyEmails(): BulkAction
    {
        return BulkAction::make('verify_emails')
            ->label('Verifieer E-mails')
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->action(function (Collection $records) {
                $count = 0;
                foreach ($records as $record) {
                    if (!$record->email_verified_at) {
                        $record->update(['email_verified_at' => now()]);
                        $count++;
                    }
                }
                
                \Filament\Notifications\Notification::make()
                    ->title('E-mails geverifieerd')
                    ->body("{$count} gebruikers hebben hun e-mail geverifieerd.")
                    ->success()
                    ->send();
            })
            ->deselectRecordsAfterCompletion()
            ->requiresConfirmation();
    }

    public static function promoteToAdmin(): BulkAction
    {
        return BulkAction::make('promote_to_admin')
            ->label('Promoveer naar Admin')
            ->icon('heroicon-o-shield-check')
            ->color('warning')
            ->action(function (Collection $records) {
                $count = 0;
                foreach ($records as $record) {
                    if ($record->role === 'user') {
                        $record->update(['role' => 'admin']);
                        $count++;
                    }
                }
                
                \Filament\Notifications\Notification::make()
                    ->title('Gebruikers gepromoveerd')
                    ->body("{$count} gebruikers zijn gepromoveerd naar admin.")
                    ->success()
                    ->send();
            })
            ->deselectRecordsAfterCompletion()
            ->requiresConfirmation();
    }

    public static function sendNotification(): BulkAction
    {
        return BulkAction::make('send_notification')
            ->label('Verstuur Notificatie')
            ->icon('heroicon-o-bell')
            ->color('info')
            ->form([
                \Filament\Forms\Components\TextInput::make('subject')
                    ->label('Onderwerp')
                    ->required()
                    ->maxLength(255),
                \Filament\Forms\Components\Textarea::make('message')
                    ->label('Bericht')
                    ->required()
                    ->rows(4),
            ])
            ->action(function (Collection $records, array $data) {
                $count = 0;
                foreach ($records as $record) {
                    if ($record->email) {
                        Mail::to($record->email)->send(
                            new UserNotificationMail($data['subject'], $data['message'])
                        );
                        $count++;
                    }
                }
                
                \Filament\Notifications\Notification::make()
                    ->title('Notificaties verzonden')
                    ->body("{$count} notificaties zijn verzonden.")
                    ->success()
                    ->send();
            })
            ->deselectRecordsAfterCompletion();
    }

    public static function exportUsers(): BulkAction
    {
        return BulkAction::make('export_users')
            ->label('Exporteer Gebruikers')
            ->icon('heroicon-o-arrow-down-tray')
            ->color('gray')
            ->action(function (Collection $records) {
                $csvData = [];
                $csvData[] = ['Naam', 'E-mail', 'Rol', 'Aangemaakt', 'E-mail Geverifieerd'];
                
                foreach ($records as $record) {
                    $csvData[] = [
                        $record->name,
                        $record->email,
                        $record->role,
                        $record->created_at->format('d-m-Y H:i'),
                        $record->email_verified_at ? 'Ja' : 'Nee',
                    ];
                }
                
                $filename = 'users_export_' . now()->format('Y-m-d_H-i-s') . '.csv';
                $filepath = storage_path('app/exports/' . $filename);
                
                // Ensure directory exists
                if (!file_exists(dirname($filepath))) {
                    mkdir(dirname($filepath), 0755, true);
                }
                
                $file = fopen($filepath, 'w');
                foreach ($csvData as $row) {
                    fputcsv($file, $row);
                }
                fclose($file);
                
                \Filament\Notifications\Notification::make()
                    ->title('Export voltooid')
                    ->body("Gebruikers geëxporteerd naar {$filename}")
                    ->success()
                    ->send();
            })
            ->deselectRecordsAfterCompletion();
    }
}
