<?php

namespace App\Filament\Supplier\Widgets;

use App\Filament\Resources\PurchaseOrders\Pages\ViewPurchaseOrder;
use App\Models\PurchaseOrder;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class NewPurchaseOrdersWidget extends BaseWidget
{
    protected static ?string $heading = 'New Purchase Orders';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 1;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                PurchaseOrder::query()
                    ->where('supplier_id', auth()->user()->supplier_id)
                    ->where('status', 'Pending')
                    ->latest()
                    ->limit(5)
            )
            ->columns([
                TextColumn::make('po_number')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('order_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('total_amount')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->sortable(),
            ])
            ->recordActions([
                Action::make('create_shipment')
                    ->label('Create Shipment')
                    ->icon('heroicon-m-truck')
                    ->url(fn (PurchaseOrder $record): string => ViewPurchaseOrder::getUrl(['record' => $record])),
            ]);
    }
}
