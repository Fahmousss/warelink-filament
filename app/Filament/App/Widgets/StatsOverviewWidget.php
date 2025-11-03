<?php

namespace App\Filament\App\Widgets;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Shipment;
use Filament\Support\Colors\Color;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class StatsOverviewWidget extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            BaseWidget\Stat::make('Pending POs', PurchaseOrder::pending()->count())
                ->description('Requiring approval')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color(Color::Orange)
                ->chart([7, 3, 4, 5, 6, 3, 5]),

            BaseWidget\Stat::make('Today\'s Shipments', Shipment::whereDate('created_at', today())->count())
                ->description('Incoming deliveries')
                ->descriptionIcon('heroicon-m-truck')
                ->color(Color::Blue)
                ->chart([2, 4, 6, 8, 5, 3, 7]),

            BaseWidget\Stat::make('Total Stock Value', 'Rp '.number_format(Product::sum('price'), 0, ',', '.'))
                ->description('Current inventory value')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color(Color::Emerald)
                ->chart([8, 9, 7, 8, 6, 9, 8]),
        ];
    }
}
