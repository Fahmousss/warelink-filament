<?php

namespace App\Filament\Supplier\Resources\Shipments\Schemas;

use App\Enums\ShipmentStatus;
use App\Filament\Resources\PurchaseOrders\PurchaseOrderResource;
use App\Models\Shipment;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontFamily;
use Filament\Support\Enums\TextSize;
use Filament\Support\Icons\Heroicon;

class ShipmentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('status_banner')
                    ->hiddenLabel()
                    ->state(fn ($record) => new \Illuminate\Support\HtmlString(
                        match ($record->status) {
                            ShipmentStatus::DRAFT => '
                                    <div class="flex items-center gap-3 p-4 bg-gray-50 dark:bg-gray-900/20 border border-gray-200 dark:border-gray-700 rounded-lg">
                                        <svg class="w-6 h-6 text-gray-600 dark:text-gray-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"></path>
                                        </svg>
                                        <div class="flex-1">
                                            <h3 class="font-semibold text-gray-900 dark:text-gray-100">Draft</h3>
                                            <p class="text-sm text-gray-800 dark:text-gray-200">Shipment is being prepared</p>
                                        </div>
                                    </div>
                                ',
                            ShipmentStatus::SHIPPED => '
                                    <div class="flex items-center gap-3 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"></path>
                                            <path d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H10a1 1 0 001-1V5a1 1 0 00-1-1H3zM14 7a1 1 0 00-1 1v6.05A2.5 2.5 0 0115.95 16H17a1 1 0 001-1v-5a1 1 0 00-.293-.707l-2-2A1 1 0 0015 7h-1z"></path>
                                        </svg>
                                        <div class="flex-1">
                                            <h3 class="font-semibold text-blue-900 dark:text-blue-100">Shipped</h3>
                                            <p class="text-sm text-blue-800 dark:text-blue-200">Shipment is in transit to warehouse</p>
                                        </div>
                                    </div>
                                ',
                            ShipmentStatus::ARRIVED => '
                                    <div class="flex items-center gap-3 p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg">
                                        <svg class="w-6 h-6 text-amber-600 dark:text-amber-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3zm11 4a1 1 0 10-2 0v4a1 1 0 102 0V7zm-3 1a1 1 0 10-2 0v3a1 1 0 102 0V8zM8 9a1 1 0 00-2 0v2a1 1 0 102 0V9z" clip-rule="evenodd"></path>
                                        </svg>
                                        <div class="flex-1">
                                            <h3 class="font-semibold text-amber-900 dark:text-amber-100">Arrived</h3>
                                            <p class="text-sm text-amber-800 dark:text-amber-200">Shipment has arrived at warehouse - ready for receiving</p>
                                        </div>
                                    </div>
                                ',
                            ShipmentStatus::PROCESSED => '
                                    <div class="flex items-center gap-3 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                                        <svg class="w-6 h-6 text-green-600 dark:text-green-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                        <div class="flex-1">
                                            <h3 class="font-semibold text-green-900 dark:text-green-100">Processed</h3>
                                            <p class="text-sm text-green-800 dark:text-green-200">Shipment has been processed and goods received</p>
                                        </div>
                                    </div>
                                ',
                        }
                    ))
                    ->columnSpanFull(),
                // Main Content Grid
                Grid::make(2)
                    ->schema([
                        // Section 1: Shipment Information
                        Section::make('Shipment Information')
                            ->icon('heroicon-o-truck')
                            ->description('ASN and delivery details')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TextEntry::make('shipment_number')
                                            ->label('ASN Number')
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
                                    ]),

                                Grid::make(2)
                                    ->schema([
                                        TextEntry::make('purchaseOrder.po_number')
                                            ->label('Purchase Order')
                                            ->icon('heroicon-m-shopping-cart')
                                            ->iconColor('primary')
                                            ->url(fn ($record) => PurchaseOrderResource::getUrl('view', ['record' => $record]))
                                            ->weight('semibold')
                                            ->columnSpan(1),

                                        TextEntry::make('supplier.name')
                                            ->label('Supplier')
                                            ->icon('heroicon-m-building-storefront')
                                            ->iconColor('gray')
                                            ->weight('semibold')
                                            ->columnSpan(1),
                                    ]),

                                Grid::make(2)
                                    ->schema([
                                        TextEntry::make('delivery_order_number')
                                            ->label('DO Number')
                                            ->icon('heroicon-m-document-text')
                                            ->iconColor('gray')
                                            ->fontFamily(FontFamily::Mono)
                                            ->columnSpan(1),

                                        TextEntry::make('shipping_date')
                                            ->label('Shipping Date')
                                            ->date('F j, Y')
                                            ->icon('heroicon-m-calendar-days')
                                            ->iconColor('gray')
                                            ->hint(fn ($record) => $record->shipping_date->diffForHumans())
                                            ->columnSpan(1),
                                    ]),

                                TextEntry::make('estimated_arrival_date')
                                    ->label('Estimated Arrival')
                                    ->date('F j, Y')
                                    ->placeholder('Not specified')
                                    ->icon('heroicon-m-clock')
                                    ->iconColor('gray'),

                                TextEntry::make('notes')
                                    ->label('Notes')
                                    ->placeholder('No notes')
                                    ->columnSpanFull(),

                                ImageEntry::make('do_scan_path')
                                    ->label('Delivery Order Scan')
                                    ->disk('public')
                                    ->visibility('public')
                                    ->height(300)
                                    ->visible(fn ($record) => $record->do_scan_path)
                                    ->columnSpanFull(),
                            ])
                            ->collapsible()
                            ->columnSpan(1),

                        // Section 2: Shipped Items
                        Section::make('Shipped Items')
                            ->icon('heroicon-o-cube')
                            ->description('Products included in this shipment')
                            ->schema([
                                RepeatableEntry::make('details')
                                    ->schema([
                                        Grid::make(3)
                                            ->schema([
                                                TextEntry::make('product.name')
                                                    ->label('Product')
                                                    ->icon('heroicon-m-cube')
                                                    ->weight('semibold')
                                                    ->hint(fn ($record) => "Code: {$record->product->product_code}")
                                                    ->columnSpan(2),

                                                TextEntry::make('quantity_shipped')
                                                    ->label('Quantity')
                                                    ->badge()
                                                    ->color('info')
                                                    ->formatStateUsing(fn ($state, $record) => "{$state} {$record->product->unit}")
                                                    ->icon('heroicon-m-truck')
                                                    ->columnSpan(1),
                                            ]),

                                        TextEntry::make('notes')
                                            ->label('Notes')
                                            ->placeholder('No notes')
                                            ->columnSpanFull(),
                                    ])
                                    ->columnSpanFull(),

                                TextEntry::make('shipment_summary')
                                    ->label('Summary')
                                    ->state(function ($record) {
                                        $totalQty = $record->total_quantity_shipped;
                                        $totalItems = $record->total_items;

                                        return new \Illuminate\Support\HtmlString('
                                        <div class="grid grid-cols-2 gap-4">
                                            <div class="p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                                                <div class="text-xs text-blue-600 dark:text-blue-400 font-medium uppercase">Total Items</div>
                                                <div class="text-xl font-bold text-blue-900 dark:text-blue-100 mt-1">'.$totalItems.'</div>
                                            </div>
                                            <div class="p-3 bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800 rounded-lg">
                                                <div class="text-xs text-purple-600 dark:text-purple-400 font-medium uppercase">Total Quantity</div>
                                                <div class="text-xl font-bold text-purple-900 dark:text-purple-100 mt-1">'.number_format($totalQty).'</div>
                                            </div>
                                        </div>
                                    ');
                                    }),
                            ])
                            ->collapsible()
                            ->columnSpan(1),
                    ])
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
                                TextEntry::make('deleted_at')
                                    ->label('Deleted At')
                                    ->icon(Heroicon::Trash)
                                    ->visible(fn ($record) => is_null($record->deleted_at)),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed()
                    ->columnSpanFull(),
            ]);
    }
}
