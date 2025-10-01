<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Session;

final class NotificationService
{
    public function success(string $message): void
    {
        Session::flash('notification', [
            'type' => 'success',
            'message' => $message,
        ]);
    }

    public function error(string $message): void
    {
        Session::flash('notification', [
            'type' => 'error',
            'message' => $message,
        ]);
    }

    public function warning(string $message): void
    {
        Session::flash('notification', [
            'type' => 'warning',
            'message' => $message,
        ]);
    }

    public function info(string $message): void
    {
        Session::flash('notification', [
            'type' => 'info',
            'message' => $message,
        ]);
    }

    public function getNotification(): ?array
    {
        return Session::get('notification');
    }

    public function clearNotification(): void
    {
        Session::forget('notification');
    }

    /**
     * Get success message for common operations.
     */
    public function getSuccessMessage(string $operation, string $entity): string
    {
        return match ($operation) {
            'created' => "{$entity} succesvol aangemaakt.",
            'updated' => "{$entity} succesvol bijgewerkt.",
            'deleted' => "{$entity} succesvol verwijderd.",
            'sent' => "{$entity} succesvol verzonden.",
            'paid' => "{$entity} succesvol gemarkeerd als betaald.",
            default => "{$entity} succesvol verwerkt.",
        };
    }

    /**
     * Get error message for common operations.
     */
    public function getErrorMessage(string $operation, string $entity): string
    {
        return match ($operation) {
            'create' => "Er is een fout opgetreden bij het aanmaken van {$entity}.",
            'update' => "Er is een fout opgetreden bij het bijwerken van {$entity}.",
            'delete' => "Er is een fout opgetreden bij het verwijderen van {$entity}.",
            'send' => "Er is een fout opgetreden bij het verzenden van {$entity}.",
            'access' => "U heeft geen toegang tot deze {$entity}.",
            default => "Er is een fout opgetreden bij het verwerken van {$entity}.",
        };
    }

    /**
     * Get warning message for common operations.
     */
    public function getWarningMessage(string $operation, string $entity): string
    {
        return match ($operation) {
            'overdue' => "Deze {$entity} is achterstallig.",
            'limit' => "U heeft de limiet bereikt voor {$entity}.",
            'duplicate' => "Een {$entity} met deze gegevens bestaat al.",
            default => "Waarschuwing: {$entity} vereist aandacht.",
        };
    }
}
