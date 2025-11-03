<?php

namespace App\Filament\Supplier\Pages;

use App\Filament\Supplier\Resources\Shipments\Pages\CreateShipment;
use App\Filament\Supplier\Widgets\NewPurchaseOrdersWidget;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-home';

    public function getHeaderActions(): array
    {
        return [
            Action::make('create_shipment')
                ->label('Create New Shipment')
                ->icon('heroicon-m-truck')
                ->url(CreateShipment::getUrl()),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            NewPurchaseOrdersWidget::class,
        ];
    }
}
