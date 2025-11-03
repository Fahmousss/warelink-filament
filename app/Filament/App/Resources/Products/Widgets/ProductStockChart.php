<?php

namespace App\Filament\App\Resources\Products\Widgets;

use App\Models\Product;
use Filament\Widgets\ChartWidget;

class ProductStockChart extends ChartWidget
{
    protected ?string $heading = 'Stock Levels Overview';

    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $stockLevels = Product::select('name', 'stock_quantity', 'minimum_stock')
            ->orderBy('stock_quantity', 'desc')
            ->limit(10)
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Current Stock',
                    'data' => $stockLevels->pluck('stock_quantity')->toArray(),
                    'backgroundColor' => '#36A2EB',
                ],
                [
                    'label' => 'Minimum Stock',
                    'data' => $stockLevels->pluck('minimum_stock')->toArray(),
                    'backgroundColor' => '#FF6384',
                ],
            ],
            'labels' => $stockLevels->pluck('name')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
