<?php

namespace App\Filament\App\Widgets;

use App\Models\GoodsReceipt;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\TrendValue;

class WeeklyReceiptChart extends ChartWidget
{
    protected ?string $heading = 'Weekly Receipts';

    protected static ?int $sort = 2;

    // protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $data = GoodsReceipt::query()
            ->with('details')
            ->whereBetween('created_at', [
                now()->subWeek()->startOfDay(),
                now()->endOfDay(),
            ])
            ->get()
            ->groupBy(function ($receipt) {
                return $receipt->created_at->format('Y-m-d');
            })
            ->map(function ($receipts) {
                return new TrendValue(
                    date: $receipts->first()->created_at,
                    aggregate: $receipts->sum('total_quantity_received')
                );
            })
            ->values();

        return [
            'datasets' => [
                [
                    'label' => 'Total Received Items',
                    'data' => $data->pluck('aggregate'),
                    'backgroundColor' => '#4ade80',
                    'borderColor' => '#16a34a',
                    'borderWidth' => 2,
                    'borderRadius' => 4,
                ],
            ],
            'labels' => $data->map(fn ($value) => $value->date),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
