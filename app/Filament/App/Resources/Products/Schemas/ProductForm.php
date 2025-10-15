<?php

namespace App\Filament\App\Resources\Products\Schemas;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Product Information Section
                Section::make('Product Information')
                    ->description('Basic product details and identification')
                    ->icon('heroicon-o-cube')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('product_code')
                                    ->label('Product Code (SKU)')
                                    ->maxLength(50)
                                    ->dehydrated(false)
                                    ->disabled()
                                    ->visible(fn ($record) => $record === null)
                                    ->placeholder('Auto-generated')
                                    ->prefixIcon('heroicon-m-hashtag')
                                    ->autocomplete('off')
                                    ->helperText('Unique identifier for this product. Will be generated automatically')
                                    ->columnSpan([
                                        'sm' => 2,
                                        'md' => 1,
                                    ]),

                                Select::make('supplier_id')
                                    ->label('Supplier')
                                    ->relationship('supplier', 'name')
                                    ->required()
                                    ->native(false)
                                    ->preload()
                                    ->helperText('The supplier associated with this product')
                                    ->searchable()
                                    ->prefixIcon('heroicon-m-truck')
                                    ->getOptionLabelFromRecordUsing(fn (Model $record) => "{$record->name} ({$record->code}). Total Products: {$record->products->count()}")
                                    ->placeholder('Select a supplier')
                                    ->columnSpan([
                                        'sm' => 2,
                                        'md' => 1,
                                    ]),

                                Select::make('unit')
                                    ->label('Unit of Measurement')
                                    ->required()
                                    ->options([
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
                                    ])
                                    ->default('pcs')
                                    ->native(false)
                                    ->searchable()
                                    ->prefixIcon('heroicon-m-cube-transparent')
                                    ->helperText('How this product is measured')
                                    ->columnSpan([
                                        'sm' => 2,
                                        'md' => 1,
                                    ]),
                            ]),

                        TextInput::make('name')
                            ->label('Product Name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Enter the product name')
                            ->prefixIcon('heroicon-m-tag')
                            ->helperText('The display name for this product')
                            ->columnSpanFull(),

                        RichEditor::make('description')
                            ->label('Description')
                            ->placeholder('Detailed description of the product...')
                            ->helperText('Optional: Add detailed information about the product')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                                'link',
                                'undo',
                                'redo',
                            ])
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->persistCollapsed()
                    ->columnSpanFull(),

                // Pricing Section
                Section::make('Pricing')
                    ->description('Set the product price')
                    ->icon('heroicon-o-currency-dollar')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('price')
                                    ->label('Unit Price')
                                    ->required()
                                    ->numeric()
                                    ->minValue(0)
                                    ->prefix('Rp')
                                    ->placeholder('0')
                                    ->prefixIcon('heroicon-m-banknotes')
                                    ->helperText('Price per unit')
                                    // ->mask(RawJs::make('$money($input)'))
                                    // ->stripCharacters(',')
                                    ->reactive()
                                    ->live()
                                    ->columnSpan([
                                        'sm' => 3,
                                        'md' => 1,
                                    ]),

                                TextEntry::make('price_preview')
                                    ->label('Formatted Price')
                                    ->state(function (Get $get) {
                                        $price = $get('price');
                                        if (! $price) {
                                            return new HtmlString('<span class="text-gray-400">Rp 0</span>');
                                        }

                                        return new HtmlString('<span class="text-2xl font-bold text-success-600 dark:text-success-400">Rp '.number_format($price, 0, ',', '.').'</span>');
                                    })
                                    ->columnSpan([
                                        'sm' => 3,
                                        'md' => 2,
                                    ]),
                            ]),
                    ])
                    ->collapsible()
                    ->persistCollapsed()
                    ->columnSpanFull(),

                // Inventory Management Section
                Section::make('Inventory Management')
                    ->description('Stock levels and inventory tracking')
                    ->icon('heroicon-o-archive-box')
                    ->schema([
                        TextEntry::make('stock_info')
                            ->hiddenLabel()
                            ->state(new HtmlString('
                            <div class="flex items-start gap-3 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <div class="flex-1">
                                    <h4 class="text-sm font-semibold text-blue-900 dark:text-blue-100 mb-1">Stock Management Tips</h4>
                                    <ul class="text-xs text-blue-800 dark:text-blue-200 space-y-1">
                                        <li>• Set a minimum stock level to receive low stock warnings</li>
                                        <li>• The system will alert you when stock reaches the minimum level</li>
                                        <li>• Keep stock quantity updated for accurate inventory tracking</li>
                                    </ul>
                                </div>
                            </div>
                        ')),

                        Grid::make(3)
                            ->schema([
                                TextInput::make('stock_quantity')
                                    ->label('Current Stock')
                                    ->required()
                                    ->numeric()
                                    ->minValue(0)
                                    ->default(0)
                                    ->placeholder('0')
                                    ->prefixIcon('heroicon-m-archive-box')
                                    ->helperText('Available quantity in inventory')
                                    ->reactive()
                                    ->live()
                                    ->columnSpan([
                                        'sm' => 3,
                                        'md' => 1,
                                    ]),

                                TextInput::make('minimum_stock')
                                    ->label('Minimum Stock Level')
                                    ->required()
                                    ->numeric()
                                    ->minValue(0)
                                    ->default(0)
                                    ->placeholder('0')
                                    ->prefixIcon('heroicon-m-arrow-trending-down')
                                    ->helperText('Alert threshold for low stock')
                                    ->reactive()
                                    ->live()
                                    ->columnSpan([
                                        'sm' => 3,
                                        'md' => 1,
                                    ]),

                                TextEntry::make('stock_status')
                                    ->label('Stock Status')
                                    ->state(function (Get $get) {
                                        $stock = (int) $get('stock_quantity');
                                        $minStock = (int) $get('minimum_stock');
                                        $unit = $get('unit') ?? 'pcs';

                                        if ($stock <= 0) {
                                            return new HtmlString('
                                            <div class="flex items-center gap-2 px-3 py-2 bg-danger-50 dark:bg-danger-900/20 border border-danger-200 dark:border-danger-800 rounded-lg">
                                                <svg class="w-5 h-5 text-danger-600 dark:text-danger-400" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                                </svg>
                                                <div>
                                                    <div class="font-semibold text-danger-700 dark:text-danger-300">Out of Stock</div>
                                                    <div class="text-xs text-danger-600 dark:text-danger-400">Reorder immediately</div>
                                                </div>
                                            </div>
                                        ');
                                        }

                                        if ($stock <= $minStock) {
                                            return new HtmlString('
                                            <div class="flex items-center gap-2 px-3 py-2 bg-warning-50 dark:bg-warning-900/20 border border-warning-200 dark:border-warning-800 rounded-lg">
                                                <svg class="w-5 h-5 text-warning-600 dark:text-warning-400" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                                </svg>
                                                <div>
                                                    <div class="font-semibold text-warning-700 dark:text-warning-300">Low Stock</div>
                                                    <div class="text-xs text-warning-600 dark:text-warning-400">'.$stock.' '.$unit.' remaining</div>
                                                </div>
                                            </div>
                                        ');
                                        }

                                        return new HtmlString('
                                        <div class="flex items-center gap-2 px-3 py-2 bg-success-50 dark:bg-success-900/20 border border-success-200 dark:border-success-800 rounded-lg">
                                            <svg class="w-5 h-5 text-success-600 dark:text-success-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                            </svg>
                                            <div>
                                                <div class="font-semibold text-success-700 dark:text-success-300">In Stock</div>
                                                <div class="text-xs text-success-600 dark:text-success-400">'.$stock.' '.$unit.' available</div>
                                            </div>
                                        </div>
                                    ');
                                    })
                                    ->columnSpan([
                                        'sm' => 3,
                                        'md' => 1,
                                    ]),
                            ]),
                    ])
                    ->collapsible()
                    ->persistCollapsed()
                    ->columnSpanFull(),

                // Product Status Section
                Section::make('Product Status')
                    ->description('Control product visibility and availability')
                    ->icon('heroicon-o-eye')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Toggle::make('is_active')
                                    ->label('Active')
                                    ->onIcon('heroicon-m-check-circle')
                                    ->offIcon('heroicon-m-x-circle')
                                    ->onColor('success')
                                    ->offColor('danger')
                                    ->default(true)
                                    ->inline(false)
                                    ->live()
                                    ->helperText('Active products are visible to customers')
                                    ->columnSpan([
                                        'sm' => 2,
                                        'md' => 1,
                                    ]),

                                TextEntry::make('status_info')
                                    ->label('Visibility')
                                    ->state(function (Get $get) {
                                        $isActive = $get('is_active');

                                        if ($isActive) {
                                            return new HtmlString('
                                            <div class="flex items-center gap-2 text-success-600 dark:text-success-400">
                                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"></path>
                                                    <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"></path>
                                                </svg>
                                                <span class="font-medium">Visible to customers</span>
                                            </div>
                                        ');
                                        }

                                        return new HtmlString('
                                        <div class="flex items-center gap-2 text-danger-600 dark:text-danger-400">
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019.542 10C18.268 5.943 14.478 3 10 3a9.958 9.958 0 00-4.512 1.074l-1.78-1.781zm4.261 4.26l1.514 1.515a2.003 2.003 0 012.45 2.45l1.514 1.514a4 4 0 00-5.478-5.478z" clip-rule="evenodd"></path>
                                                <path d="M12.454 16.697L9.75 13.992a4 4 0 01-3.742-3.741L2.335 6.578A9.98 9.98 0 00.458 10c1.274 4.057 5.065 7 9.542 7 .847 0 1.669-.105 2.454-.303z"></path>
                                            </svg>
                                            <span class="font-medium">Hidden from customers</span>
                                        </div>
                                    ');
                                    })
                                    ->columnSpan([
                                        'sm' => 2,
                                        'md' => 1,
                                    ]),
                            ]),
                    ])
                    ->collapsible()
                    ->persistCollapsed()
                    ->columnSpanFull(),
            ])
            ->columns(1);
    }
}
