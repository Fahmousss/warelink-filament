<?php

namespace App\Filament\Widgets;

use Filament\Widgets\AccountWidget as WidgetsAccountWidget;
use Filament\Widgets\Widget;

class AccountWidget extends WidgetsAccountWidget
{
    // protected string $view = 'filament.widgets.account-widget';

    protected int|string|array $columnSpan = 'full';
}
