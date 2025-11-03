<?php

namespace App\Filament\App\Widgets;

use App\Filament\Resources\PurchaseOrders\Pages\ViewPurchaseOrder;
use App\Models\PurchaseOrder;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestPurchaseOrders extends BaseWidget
{
    protected static ?string $heading = '';

    // protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 3;

    public function table(Table $table): Table
    {
        return $table
            ->poll()
            ->query(
                PurchaseOrder::query()
                    ->whereIn('status', ['Pending', 'Partial'])
                    ->latest()
                    ->limit(5)
            )
            ->columns([
                TextColumn::make('po_number')
                    ->sortable(),
                TextColumn::make('supplier.name')
                    ->sortable(),
                TextColumn::make('order_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                TextColumn::make('total_amount')
                    ->money('IDR')
                    ->sortable(),
            ])
            ->recordActions([
                // Action::make('view')
                //     ->url(fn (PurchaseOrder $record): string => ViewPurchaseOrder::getUrl(['record' => $record]))
                //     ->icon('heroicon-m-eye'),
            ]);
    }
}
