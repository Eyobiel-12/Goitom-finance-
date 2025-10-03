<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

final class QuickActionsWidget extends Widget
{
    protected static string $view = 'filament.widgets.quick-actions';
    protected static ?int $sort = -1; // bovenaan
}


