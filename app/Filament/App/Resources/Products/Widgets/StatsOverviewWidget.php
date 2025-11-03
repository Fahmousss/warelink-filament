<?php

namespace App\Filament\App\Resources\Products\Widgets;

use App\Models\Product;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget as BaseStatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class StatsOverviewWidget extends BaseStatsOverviewWidget
{
    // protected ?string $heading = 'Product Overview';

    protected function getStats(): array
    {
        $totalProducts = Product::active()->count();
        $lowStockCount = Product::lowStock()->count();
        $totalValue = Product::sum('price');
        $outOfStockCount = Product::outOfStock()->count();

        return [
            Stat::make('Total Products', $totalProducts)
                ->description('Active products in inventory')
                ->descriptionIcon('heroicon-m-cube')
                ->color('info')
                ->chart([7, 3, 4, 5, 6, 3, 5]),

            Stat::make('Low Stock Items', $lowStockCount)
                ->description('Products below minimum stock')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color($lowStockCount > 0 ? 'warning' : 'success')
                ->chart([3, 2, 4, 3, 5, 4, 3]),

            Stat::make('Out of Stock Items', $outOfStockCount)
                ->description('Product with 0 stock')
                ->descriptionIcon(Heroicon::XCircle)
                ->color($outOfStockCount > 0 ? 'danger' : 'success')
                ->chart([0, 1, $outOfStockCount]),

            Stat::make('Total Stock Value', 'Rp '.Number::format($totalValue, 0))
                ->description('Current inventory value')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success')
                ->chart([8, 9, 7, 8, 6, 9, 8]),
        ];
    }
}
