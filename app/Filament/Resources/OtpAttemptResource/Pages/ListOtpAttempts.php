<?php

declare(strict_types=1);

namespace App\Filament\Resources\OtpAttemptResource\Pages;

use App\Filament\Resources\OtpAttemptResource;
use Filament\Resources\Pages\ListRecords;

final class ListOtpAttempts extends ListRecords
{
    protected static string $resource = OtpAttemptResource::class;
    protected static ?string $title = 'OTP Attempts';
}


