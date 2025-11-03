<?php

namespace App\Filament\Pages;

use App\Filament\Resources\PurchaseOrders\Pages\CreatePurchaseOrder;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-home';

    public function getHeaderActions(): array
    {
        return [
            Action::make('create_po')
                ->label('Create Purchase Order')
                ->icon('heroicon-m-plus')
                ->url(CreatePurchaseOrder::getUrl()),

        ];
    }
}
