<?php

namespace App\Filament\App\Resources\Products\Schemas;

use App\Models\Product;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontFamily;
use Filament\Support\Enums\TextSize;
use Illuminate\Support\HtmlString;

class ProductInfolist
{
    // public static function configure(Schema $schema): Schema
    // {
    //     return $schema
    //         ->components([
    //             TextEntry::make('product_code'),
    //             TextEntry::make('name'),
    //             TextEntry::make('description')
    //                 ->placeholder('-')
    //                 ->columnSpanFull(),
    //             TextEntry::make('unit'),
    //             TextEntry::make('price')
    //                 ->money(),
    //             TextEntry::make('stock_quantity')
    //                 ->numeric(),
    //             TextEntry::make('minimum_stock')
    //                 ->numeric(),
    //             IconEntry::make('is_active')
    //                 ->boolean(),
    //             TextEntry::make('created_at')
    //                 ->dateTime()
    //                 ->placeholder('-'),
    //             TextEntry::make('updated_at')
    //                 ->dateTime()
    //                 ->placeholder('-'),
    //             TextEntry::make('deleted_at')
    //                 ->dateTime()
    //                 ->visible(fn (Product $record): bool => $record->trashed()),
    //         ]);
    // }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Status Banners
                TextEntry::make('low_stock_warning')
                    ->hiddenLabel()
                    ->columnSpanFull()
                    ->state(new HtmlString('
                            <div class="flex items-center gap-3 p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg">
                                <svg class="w-6 h-6 text-amber-600 dark:text-amber-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                                <div class="flex-1">
                                    <h3 class="font-semibold text-amber-900 dark:text-amber-100">Low Stock Alert</h3>
                                    <p class="text-sm text-amber-800 dark:text-amber-200">Stock level is below minimum threshold. Consider reordering.</p>
                                </div>
                            </div>
                        '))
                    ->visible(fn ($record) => $record && $record->stock_quantity <= $record->minimum_stock && $record->stock_quantity > 0),

                TextEntry::make('out_of_stock_warning')
                    ->hiddenLabel()
                    ->columnSpanFull()
                    ->state(new HtmlString('
                            <div class="flex items-center gap-3 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                                <svg class="w-6 h-6 text-red-600 dark:text-red-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                </svg>
                                <div class="flex-1">
                                    <h3 class="font-semibold text-red-900 dark:text-red-100">Out of Stock</h3>
                                    <p class="text-sm text-red-800 dark:text-red-200">This product is currently unavailable. Reorder immediately.</p>
                                </div>
                            </div>
                        '))
                    ->visible(fn ($record) => $record && $record->stock_quantity <= 0),

                TextEntry::make('inactive_warning')
                    ->hiddenLabel()
                    ->columnSpanFull()
                    ->state(new HtmlString('
                            <div class="flex items-center gap-3 p-4 bg-gray-50 dark:bg-gray-900/20 border border-gray-200 dark:border-gray-700 rounded-lg">
                                <svg class="w-6 h-6 text-gray-600 dark:text-gray-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019.542 10C18.268 5.943 14.478 3 10 3a9.958 9.958 0 00-4.512 1.074l-1.78-1.781zm4.261 4.26l1.514 1.515a2.003 2.003 0 012.45 2.45l1.514 1.514a4 4 0 00-5.478-5.478z" clip-rule="evenodd"></path>
                                    <path d="M12.454 16.697L9.75 13.992a4 4 0 01-3.742-3.741L2.335 6.578A9.98 9.98 0 00.458 10c1.274 4.057 5.065 7 9.542 7 .847 0 1.669-.105 2.454-.303z"></path>
                                </svg>
                                <div class="flex-1">
                                    <h3 class="font-semibold text-gray-900 dark:text-gray-100">Product Inactive</h3>
                                    <p class="text-sm text-gray-800 dark:text-gray-200">This product is currently hidden and not available for purchase.</p>
                                </div>
                            </div>
                        '))
                    ->visible(fn ($record) => $record && ! $record->is_active),

                TextEntry::make('deleted_warning')
                    ->hiddenLabel()
                    ->columnSpanFull()
                    ->state(new HtmlString('
                            <div class="flex items-center gap-3 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                                <svg class="w-6 h-6 text-red-600 dark:text-red-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                                <div class="flex-1">
                                    <h3 class="font-semibold text-red-900 dark:text-red-100">Product Deleted</h3>
                                    <p class="text-sm text-red-800 dark:text-red-200">This product has been deleted and is no longer available.</p>
                                </div>
                            </div>
                        '))
                    ->visible(fn ($record) => $record && $record->trashed()),

                // Product Overview Section
                Section::make('Product Overview')
                    ->icon('heroicon-o-cube')
                    ->description('Basic product identification and details')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('product_code')
                                    ->label('Product Code (SKU)')
                                    ->icon('heroicon-m-hashtag')
                                    ->iconColor('primary')
                                    ->copyable()
                                    ->copyMessage('Product code copied!')
                                    ->copyMessageDuration(1500)
                                    ->fontFamily(FontFamily::Mono)
                                    ->size(TextSize::Large)
                                    ->weight('bold')
                                    ->columnSpan([
                                        'sm' => 3,
                                        'md' => 1,
                                    ]),

                                TextEntry::make('unit')
                                    ->label('Unit')
                                    ->badge()
                                    ->color('gray')
                                    ->icon('heroicon-m-cube-transparent')
                                    ->formatStateUsing(fn (string $state): string => match ($state) {
                                        'pcs' => 'Pieces (pcs)',
                                        'box' => 'Box',
                                        'pack' => 'Pack',
                                        'kg' => 'Kilogram (kg)',
                                        'g' => 'Gram (g)',
                                        'l' => 'Liter (l)',
                                        'ml' => 'Milliliter (ml)',
                                        'm' => 'Meter (m)',
                                        'cm' => 'Centimeter (cm)',
                                        'dozen' => 'Dozen',
                                        'set' => 'Set',
                                        default => $state,
                                    })
                                    ->columnSpan([
                                        'sm' => 3,
                                        'md' => 1,
                                    ]),

                                IconEntry::make('is_active')
                                    ->label('Status')
                                    ->boolean()
                                    ->trueIcon('heroicon-o-check-circle')
                                    ->falseIcon('heroicon-o-x-circle')
                                    ->trueColor('success')
                                    ->falseColor('danger')
                                    ->tooltip(fn ($state) => $state ? 'Product is active and visible' : 'Product is inactive and hidden')
                                    ->columnSpan([
                                        'sm' => 3,
                                        'md' => 1,
                                    ]),
                            ]),

                        TextEntry::make('name')
                            ->label('Product Name')
                            ->icon('heroicon-m-tag')
                            ->iconColor('gray')
                            ->size(TextSize::Large)
                            ->weight('semibold')
                            ->columnSpanFull(),

                        TextEntry::make('description')
                            ->label('Description')
                            ->iconColor('gray')
                            ->placeholder('No description provided')
                            ->html()
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->columnSpanFull(),

                // Pricing Information Section
                Section::make('Pricing Information')
                    ->icon('heroicon-o-currency-dollar')
                    ->description('Product pricing details')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('price')
                                    ->label('Unit Price')
                                    ->money('IDR')
                                    ->icon('heroicon-m-banknotes')
                                    ->iconColor('success')
                                    ->size(TextSize::Large)
                                    ->weight('bold')
                                    ->color('success')
                                    ->columnSpan([
                                        'sm' => 2,
                                        'md' => 1,
                                    ]),

                                TextEntry::make('price_breakdown')
                                    ->label('Price Per Unit')
                                    ->state(function ($record) {
                                        $price = number_format($record->price, 0, ',', '.');
                                        $unit = $record->unit ?? 'pcs';

                                        return new HtmlString("
                                        <div class='p-3 bg-success-50 dark:bg-success-900/20 border border-success-200 dark:border-success-800 rounded-lg'>
                                            <div class='text-2xl font-bold text-success-700 dark:text-success-300'>Rp {$price}</div>
                                            <div class='text-sm text-success-600 dark:text-success-400 mt-1'>per {$unit}</div>
                                        </div>
                                    ");
                                    })
                                    ->columnSpan([
                                        'sm' => 2,
                                        'md' => 1,
                                    ]),
                            ]),
                    ])
                    ->collapsible()
                    ->columnSpanFull(),

                // Inventory Details Section
                Section::make('Inventory Details')
                    ->icon('heroicon-o-archive-box')
                    ->description('Stock levels and inventory tracking')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('stock_quantity')
                                    ->label('Current Stock')
                                    ->numeric()
                                    ->icon('heroicon-m-archive-box')
                                    ->badge()
                                    ->color(fn ($record) => match (true) {
                                        $record->stock_quantity <= 0 => 'danger',
                                        $record->stock_quantity <= $record->minimum_stock => 'warning',
                                        $record->stock_quantity <= ($record->minimum_stock * 2) => 'info',
                                        default => 'success',
                                    })
                                    ->formatStateUsing(fn ($state, $record) => $state.' '.($record->unit ?? 'pcs'))
                                    ->size(TextSize::Large)
                                    ->weight('bold')
                                    ->columnSpan([
                                        'sm' => 3,
                                        'md' => 1,
                                    ]),

                                TextEntry::make('minimum_stock')
                                    ->label('Minimum Stock Level')
                                    ->numeric()
                                    ->icon('heroicon-m-arrow-trending-down')
                                    ->iconColor('gray')
                                    ->formatStateUsing(fn ($state, $record) => $state.' '.($record->unit ?? 'pcs'))
                                    ->badge()
                                    ->color('gray')
                                    ->columnSpan([
                                        'sm' => 3,
                                        'md' => 1,
                                    ]),

                                TextEntry::make('stock_status')
                                    ->label('Stock Health')
                                    ->state(function ($record) {
                                        $stock = $record->stock_quantity;
                                        $minStock = $record->minimum_stock;
                                        $unit = $record->unit ?? 'pcs';

                                        if ($stock <= 0) {
                                            return new HtmlString("
                                            <div class='flex items-center gap-2 p-3 bg-danger-50 dark:bg-danger-900/20 border border-danger-200 dark:border-danger-800 rounded-lg'>
                                                <svg class='w-5 h-5 text-danger-600 dark:text-danger-400' fill='currentColor' viewBox='0 0 20 20'>
                                                    <path fill-rule='evenodd' d='M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z' clip-rule='evenodd'></path>
                                                </svg>
                                                <div>
                                                    <div class='font-semibold text-danger-700 dark:text-danger-300'>Out of Stock</div>
                                                    <div class='text-xs text-danger-600 dark:text-danger-400'>Immediate reorder required</div>
                                                </div>
                                            </div>
                                        ");
                                        }

                                        if ($stock <= $minStock) {
                                            $percentage = round(($stock / $minStock) * 100);

                                            return new HtmlString("
                                            <div class='flex items-center gap-2 p-3 bg-warning-50 dark:bg-warning-900/20 border border-warning-200 dark:border-warning-800 rounded-lg'>
                                                <svg class='w-5 h-5 text-warning-600 dark:text-warning-400' fill='currentColor' viewBox='0 0 20 20'>
                                                    <path fill-rule='evenodd' d='M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z' clip-rule='evenodd'></path>
                                                </svg>
                                                <div>
                                                    <div class='font-semibold text-warning-700 dark:text-warning-300'>Low Stock</div>
                                                    <div class='text-xs text-warning-600 dark:text-warning-400'>{$percentage}% of minimum level</div>
                                                </div>
                                            </div>
                                        ");
                                        }

                                        if ($stock <= ($minStock * 2)) {
                                            return new HtmlString("
                                            <div class='flex items-center gap-2 p-3 bg-info-50 dark:bg-info-900/20 border border-info-200 dark:border-info-800 rounded-lg'>
                                                <svg class='w-5 h-5 text-info-600 dark:text-info-400' fill='currentColor' viewBox='0 0 20 20'>
                                                    <path fill-rule='evenodd' d='M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z' clip-rule='evenodd'></path>
                                                </svg>
                                                <div>
                                                    <div class='font-semibold text-info-700 dark:text-info-300'>Moderate Stock</div>
                                                    <div class='text-xs text-info-600 dark:text-info-400'>Monitor closely</div>
                                                </div>
                                            </div>
                                        ");
                                        }

                                        return new HtmlString("
                                        <div class='flex items-center gap-2 p-3 bg-success-50 dark:bg-success-900/20 border border-success-200 dark:border-success-800 rounded-lg'>
                                            <svg class='w-5 h-5 text-success-600 dark:text-success-400' fill='currentColor' viewBox='0 0 20 20'>
                                                <path fill-rule='evenodd' d='M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z' clip-rule='evenodd'></path>
                                            </svg>
                                            <div>
                                                <div class='font-semibold text-success-700 dark:text-success-300'>Good Stock</div>
                                                <div class='text-xs text-success-600 dark:text-success-400'>Stock level healthy</div>
                                            </div>
                                        </div>
                                    ");
                                    })
                                    ->columnSpan([
                                        'sm' => 3,
                                        'md' => 1,
                                    ]),
                            ]),
                    ])
                    ->collapsible()
                    ->columnSpanFull(),

                // Stock Analytics Section
                Section::make('Stock Analytics')
                    ->icon('heroicon-o-chart-bar')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('total_ordered')
                                    ->label('Total Ordered')
                                    ->formatStateUsing(fn ($record) => $record->getTotalOrdered()),

                                TextEntry::make('total_received')
                                    ->label('Total Received')
                                    ->formatStateUsing(fn ($record) => $record->getTotalReceived())
                                    ->color('success'),

                                TextEntry::make('acceptance_rate')
                                    ->label('Acceptance Rate')
                                    ->formatStateUsing(fn ($record) => $record->getAcceptanceRate().'%')
                                    ->badge()
                                    ->color(fn ($record) => match (true) {
                                        $record->getAcceptanceRate() >= 95 => 'success',
                                        $record->getAcceptanceRate() >= 80 => 'warning',
                                        default => 'danger',
                                    }),
                            ]),

                        Grid::make(3)
                            ->schema([
                                TextEntry::make('pending_quantity')
                                    ->label('On Order')
                                    ->formatStateUsing(fn ($record) => $record->getPendingQuantity())
                                    ->badge()
                                    ->color('info'),

                                TextEntry::make('projected_stock')
                                    ->label('Projected Stock')
                                    ->formatStateUsing(fn ($record) => $record->getProjectedStock())
                                    ->hint('Current + Pending'),

                                TextEntry::make('stock_value')
                                    ->label('Inventory Value')
                                    ->formatStateUsing(fn ($record) => $record->formatted_stock_value)
                                    ->color('success'),
                            ]),
                    ]),

                // Reorder Information Section
                Section::make('Reorder Information')
                    ->icon('heroicon-o-arrow-path')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('needs_reorder')
                                    ->label('Needs Reorder')
                                    ->formatStateUsing(fn ($record) => $record->isNeedsReorder() ? 'Yes' : 'No')
                                    ->badge()
                                    ->color(fn ($record) => $record->isNeedsReorder() ? 'danger' : 'success'),

                                TextEntry::make('suggested_quantity')
                                    ->label('Suggested Order Qty')
                                    ->formatStateUsing(fn ($record) => $record->suggestedReorderQuantity())
                                    ->visible(fn ($record) => $record->isNeedsReorder()),

                                TextEntry::make('urgency')
                                    ->label('Urgency')
                                    ->formatStateUsing(fn ($record) => ucfirst($record->reorderUrgency()))
                                    ->badge()
                                    ->color(fn ($record) => match ($record->reorderUrgency()) {
                                        'critical' => 'danger',
                                        'high' => 'warning',
                                        'medium' => 'info',
                                        'low' => 'success',
                                    })
                                    ->visible(fn ($record) => $record->isNeedsReorder()),
                            ]),
                    ])
                    ->visible(fn ($record) => $record->isNeedsReorder())
                    ->collapsed(),
                // Activity Timeline Section
                Section::make('Activity Timeline')
                    ->icon('heroicon-o-clock')
                    ->description('Product history and modifications')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label('Created')
                                    ->dateTime('M d, Y H:i')
                                    ->icon('heroicon-m-plus-circle')
                                    ->iconColor('success')
                                    ->since()
                                    ->tooltip(fn ($record) => 'Created on '.$record->created_at->format('F j, Y \a\t g:i A'))
                                    ->placeholder('Not available')
                                    ->columnSpan([
                                        'sm' => 3,
                                        'md' => 1,
                                    ]),

                                TextEntry::make('updated_at')
                                    ->label('Last Modified')
                                    ->dateTime('M d, Y H:i')
                                    ->icon('heroicon-m-arrow-path')
                                    ->iconColor('gray')
                                    ->since()
                                    ->tooltip(fn ($record) => 'Updated on '.$record->updated_at->format('F j, Y \a\t g:i A'))
                                    ->placeholder('Not available')
                                    ->columnSpan([
                                        'sm' => 3,
                                        'md' => 1,
                                    ]),

                                TextEntry::make('deleted_at')
                                    ->label('Deleted')
                                    ->dateTime('M d, Y H:i')
                                    ->icon('heroicon-m-trash')
                                    ->iconColor('danger')
                                    ->since()
                                    ->badge()
                                    ->color('danger')
                                    ->tooltip(fn ($record) => $record->deleted_at ? 'Deleted on '.$record->deleted_at->format('F j, Y \a\t g:i A') : null)
                                    ->visible(fn ($record) => $record->trashed())
                                    ->columnSpan([
                                        'sm' => 3,
                                        'md' => 1,
                                    ]),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed()
                    ->columnSpanFull(),
            ]);
    }
}
