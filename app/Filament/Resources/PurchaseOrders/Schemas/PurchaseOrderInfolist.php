<?php

namespace App\Filament\Resources\PurchaseOrders\Schemas;

use App\Enums\PurchaseOrderStatus;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontFamily;
use Filament\Support\Enums\TextSize;

class PurchaseOrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                TextEntry::make('status_banner')
                    ->hiddenLabel()
                    ->columnSpanFull()
                    ->state(fn ($record) => new \Illuminate\Support\HtmlString(
                        match ($record->status) {
                            PurchaseOrderStatus::PENDING => '
                                    <div class="flex items-center gap-3 p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg">
                                        <svg class="w-6 h-6 text-amber-600 dark:text-amber-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                        </svg>
                                        <div class="flex-1">
                                            <h3 class="font-semibold text-amber-900 dark:text-amber-100">Order Pending</h3>
                                            <p class="text-sm text-amber-800 dark:text-amber-200">Waiting for goods to be received</p>
                                        </div>
                                    </div>
                                ',
                            PurchaseOrderStatus::PARTIAL => '
                                    <div class="flex items-center gap-3 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"></path>
                                        </svg>
                                        <div class="flex-1">
                                            <h3 class="font-semibold text-blue-900 dark:text-blue-100">Partially Received</h3>
                                            <p class="text-sm text-blue-800 dark:text-blue-200">'.$record->received_percentage.'% of items have been received</p>
                                        </div>
                                    </div>
                                ',
                            PurchaseOrderStatus::COMPLETED => '
                                    <div class="flex items-center gap-3 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                                        <svg class="w-6 h-6 text-green-600 dark:text-green-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                        <div class="flex-1">
                                            <h3 class="font-semibold text-green-900 dark:text-green-100">Order Completed</h3>
                                            <p class="text-sm text-green-800 dark:text-green-200">All items have been received successfully</p>
                                        </div>
                                    </div>
                                ',
                            PurchaseOrderStatus::CANCELLED => '
                                    <div class="flex items-center gap-3 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                                        <svg class="w-6 h-6 text-red-600 dark:text-red-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                        </svg>
                                        <div class="flex-1">
                                            <h3 class="font-semibold text-red-900 dark:text-red-100">Order Cancelled</h3>
                                            <p class="text-sm text-red-800 dark:text-red-200">This purchase order has been cancelled</p>
                                        </div>
                                    </div>
                                ',
                        }
                    )),

                // Order Overview
                Section::make('Purchase Order Details')
                    ->icon('heroicon-o-document-text')
                    ->description('Order identification and basic information')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('po_number')
                                    ->label('PO Number')
                                    ->icon('heroicon-m-hashtag')
                                    ->iconColor('primary')
                                    ->copyable()
                                    ->copyMessage('Copied!')
                                    ->fontFamily(FontFamily::Mono)
                                    ->size(TextSize::Large)
                                    ->weight('bold')
                                    ->columnSpan(1),

                                TextEntry::make('status')
                                    ->badge()
                                    ->size(TextSize::Large)
                                    ->columnSpan(1),

                                TextEntry::make('total_amount')
                                    ->label('Total Amount')
                                    ->money('IDR')
                                    ->icon('heroicon-m-banknotes')
                                    ->iconColor('success')
                                    ->size(TextSize::Large)
                                    ->weight('bold')
                                    ->color('success')
                                    ->columnSpan(1),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextEntry::make('supplier.name')
                                    ->label('Supplier')
                                    ->icon('heroicon-m-building-storefront')
                                    ->iconColor('gray')
                                    ->size(TextSize::Large)
                                    ->weight('semibold')
                                    ->hint(fn ($record) => $record->supplier->code)
                                    ->columnSpan(1),

                                TextEntry::make('order_date')
                                    ->label('Order Date')
                                    ->date('F j, Y')
                                    ->icon('heroicon-m-calendar-days')
                                    ->iconColor('gray')
                                    ->hint(fn ($record) => $record->order_date->diffForHumans())
                                    ->columnSpan(1),
                            ]),

                        TextEntry::make('expected_delivery_date')
                            ->label('Expected Delivery')
                            ->date('F j, Y')
                            ->placeholder('Not specified')
                            ->icon('heroicon-m-truck')
                            ->iconColor('gray'),

                        TextEntry::make('notes')
                            ->label('Notes')
                            ->placeholder('No notes')
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->columnSpanFull(),

                // Order Items
                Section::make('Order Items')
                    ->icon('heroicon-o-cube')
                    ->description('Products and quantities ordered')
                    ->schema([
                        RepeatableEntry::make('details')
                            ->schema([
                                Grid::make(6)
                                    ->schema([
                                        TextEntry::make('product.name')
                                            ->label('Product')
                                            ->icon('heroicon-m-cube')
                                            ->weight('semibold')
                                            ->hint(fn ($record) => "Code: {$record->product->product_code}")
                                            ->columnSpan(2),

                                        TextEntry::make('quantity_ordered')
                                            ->label('Ordered')
                                            ->badge()
                                            ->color('info')
                                            ->formatStateUsing(fn ($state, $record) => "{$state} {$record->product->unit}")
                                            ->columnSpan(1),

                                        TextEntry::make('quantity_received')
                                            ->label('Received')
                                            ->badge()
                                            ->color(fn ($state, $record) => $state >= $record->quantity_ordered ? 'success' : 'warning'
                                            )
                                            ->formatStateUsing(fn ($state, $record) => "{$state} {$record->product->unit}")
                                            ->columnSpan(1),

                                        TextEntry::make('price')
                                            ->label('Price')
                                            ->money('IDR')
                                            ->columnSpan(1),

                                        TextEntry::make('subtotal')
                                            ->label('Subtotal')
                                            ->money('IDR')
                                            ->weight('bold')
                                            ->color('success')
                                            ->columnSpan(1),
                                    ]),

                                TextEntry::make('notes')
                                    ->label('Notes')
                                    ->placeholder('No notes')
                                    ->columnSpanFull(),
                            ])
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->columnSpanFull(),

                // Audit Information
                Section::make('Audit Information')
                    ->icon('heroicon-o-clock')
                    ->description('Record history and timestamps')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label('Created')
                                    ->dateTime('M d, Y H:i')
                                    ->icon('heroicon-m-plus-circle')
                                    ->iconColor('success')
                                    ->since()
                                    ->tooltip(fn ($record) => $record->created_at->format('F j, Y \a\t g:i A')),

                                TextEntry::make('updated_at')
                                    ->label('Last Modified')
                                    ->dateTime('M d, Y H:i')
                                    ->icon('heroicon-m-arrow-path')
                                    ->iconColor('gray')
                                    ->since()
                                    ->tooltip(fn ($record) => $record->updated_at->format('F j, Y \a\t g:i A')),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed()
                    ->columnSpanFull(),
            ]);
    }
}
