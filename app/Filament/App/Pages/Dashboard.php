<?php

namespace App\Filament\App\Pages;

use App\Filament\App\Resources\Products\Pages\ListProducts;
use App\Filament\Widgets\AccountWidget;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-home';

    public function getHeaderActions(): array
    {
        return [

            Action::make('stock_report')
                ->label('View Stock Report')
                ->icon('heroicon-m-document-chart-bar')
                ->url(ListProducts::getUrl()),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            AccountWidget::class,
        ];
    }
}
