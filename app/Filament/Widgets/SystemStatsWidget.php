<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Filament\Support\Colors\Color;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Illuminate\Support\Facades\DB;

class SystemStatsWidget extends BaseWidget
{
    protected ?string $pollingInterval = '10s';

    protected function getStats(): array
    {
        return [
            BaseWidget\Stat::make('Total Users', User::count())
                ->description('Registered users')
                ->descriptionIcon('heroicon-m-users')
                ->color(Color::Blue),

            BaseWidget\Stat::make('Total Products', Product::active()->count())
                ->description('In master data')
                ->descriptionIcon('heroicon-m-cube')
                ->color(Color::Emerald),

            BaseWidget\Stat::make('Total Suppliers', Supplier::active()->count())
                ->description('Active suppliers')
                ->descriptionIcon('heroicon-m-building-office')
                ->color(Color::Orange),

            // BaseWidget\Stat::make('Database Size', $this->getDatabaseSize())
            //     ->description('Total size')
            //     ->descriptionIcon('heroicon-m-server')
            //     ->color(Color::Gray),
        ];
    }

    // private function getDatabaseSize(): string
    // {
    //     $size = DB::select('SELECT pg_database_size(current_database()) as size')[0]->size;

    //     return round($size / 1024 / 1024, 2).' MB';
    // }
}
