<?php

namespace App\Filament\Resources\PurchaseOrders\Pages;

use App\Enums\PurchaseOrderStatus;
use App\Filament\Resources\PurchaseOrders\PurchaseOrderResource;
use App\Models\Product;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\CreateRecord\Concerns\HasWizard;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Support\Enums\FontFamily;
use Filament\Support\Enums\TextSize;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class CreatePurchaseOrder extends CreateRecord
{
    use HasWizard;

    protected static string $resource = PurchaseOrderResource::class;

    protected static bool $canCreateAnother = false;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Calculate total amount
        $total = 0;
        if (isset($data['details'])) {
            foreach ($data['details'] as $detail) {
                $total += $detail['subtotal'];
            }
        }
        $data['total_amount'] = $total;

        return $data;
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Purchase Order Created')
            ->body("PO {$this->record->po_number} has been created successfully.")
            ->send()
            ->sendToDatabase($this->record->supplier->user);
    }

    protected function getSteps(): array
    {
        return [
            // Step 1: Header Information
            Step::make('Order Information')
                ->icon('heroicon-m-document-text')
                ->schema([
                    Section::make('Purchase Order Details')
                        ->description('Enter the basic information for this purchase order')
                        ->icon('heroicon-o-clipboard-document-list')
                        ->schema([
                            Grid::make(2)
                                ->schema([
                                    TextInput::make('po_number')
                                        ->label('PO Number')
                                        ->disabled()
                                        ->dehydrated(false)
                                        ->placeholder('Auto-generated')
                                        ->prefixIcon('heroicon-m-hashtag')
                                        ->helperText('Will be generated automatically')
                                        ->visible(fn ($record) => $record === null)
                                        ->columnSpan(1),

                                    Select::make('supplier_id')
                                        ->label('Supplier')
                                        ->relationship('supplier', 'name')
                                        ->required()
                                        ->searchable()
                                        ->preload()
                                        ->live()
                                        ->prefixIcon('heroicon-m-building-storefront')
                                        ->helperText('Select the supplier for this purchase order')
                                        ->columnSpan(1),
                                ]),

                            Grid::make(2)
                                ->schema([
                                    DatePicker::make('order_date')
                                        ->label('Order Date')
                                        ->required()
                                        ->default(now())
                                        ->native(false)
                                        ->prefixIcon('heroicon-m-calendar-days')
                                        ->helperText('Date when the order is placed')
                                        ->columnSpan(1),

                                    DatePicker::make('expected_delivery_date')
                                        ->label('Expected Delivery')
                                        ->native(false)
                                        ->after('order_date')
                                        ->prefixIcon('heroicon-m-truck')
                                        ->helperText('Expected date of delivery')
                                        ->columnSpan(1),
                                ]),

                            Select::make('status')
                                ->label('Status')
                                ->options(PurchaseOrderStatus::class)
                                ->default('Pending')
                                ->required()
                                ->native(false)
                                ->prefixIcon('heroicon-m-flag')
                                ->helperText('Current order status')
                                ->visible(fn ($record) => $record !== null),

                            Textarea::make('notes')
                                ->label('Notes')
                                ->rows(3)
                                ->placeholder('Additional notes or special instructions...')
                                ->columnSpanFull(),
                        ])
                        ->collapsible()
                        ->columnSpanFull(),
                ]),

            // Step 2: Order Details
            Step::make('Products')
                ->icon('heroicon-m-cube')
                ->schema([
                    Section::make('Order Items')
                        ->description('Add products and quantities to this purchase order')
                        ->icon('heroicon-o-shopping-bag')
                        ->schema([
                            Repeater::make('details')
                                ->relationship()
                                ->schema([
                                    Grid::make(4)
                                        ->schema([
                                            Select::make('product_id')
                                                ->label('Product')
                                                ->relationship('product', 'name', function (Builder $query, Get $get) {
                                                    $supplierId = $get('../../supplier_id');
                                                    if (! $supplierId) {
                                                        return $query->whereRaw('1 = 0');
                                                    }
                                                    $query->bySupplier($supplierId)->active();
                                                })
                                                ->getOptionLabelFromRecordUsing(fn ($record) => $record->name.' - '.
                                                $record->stock_status_label.'
                                                ('.$record->stock_quantity.' '.$record->unit.')'
                                                )
                                                ->required()
                                                ->searchable()
                                                ->preload()
                                                ->live(onBlur: true)
                                                ->reactive()
                                                ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                                ->afterStateUpdated(function ($state, Set $set) {
                                                    if ($state) {
                                                        $product = Product::find($state);
                                                        if ($product) {
                                                            $set('product_code', $product->product_code);
                                                            $set('price', $product->price);
                                                            $set('unit', $product->unit);
                                                        }
                                                    }
                                                })
                                                ->columnSpan(4),
                                            TextEntry::make('stock_warning')
                                                ->state(function (Get $get) {
                                                    $productId = $get('product_id');
                                                    if (! $productId) {
                                                        return null;
                                                    }

                                                    $product = Product::find($productId);
                                                    if ($product->is_out_of_stock) {
                                                        return new HtmlString('
                <div class="text-danger-600">
                    ⚠️ This product is out of stock!
                </div>
            ');
                                                    }

                                                    return null;
                                                })
                                                ->visible(fn (Get $get) => $get('product_id') && Product::find($get('product_id'))->is_out_of_stock
                                                ),

                                            TextEntry::make('_product_code')
                                                ->label('Code')
                                                ->fontFamily(FontFamily::Mono)
                                                ->size(TextSize::Large)
                                                ->state(fn (Get $get) => $get('product_code') ?? '-')
                                                ->reactive()
                                                ->columnSpan(2),

                                            TextInput::make('quantity_ordered')
                                                ->label('Quantity')
                                                ->required()
                                                ->numeric()
                                                ->minValue(1)
                                                ->default(0)
                                                ->reactive()
                                                ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                                    $price = $get('price') ?? 0;
                                                    $set('subtotal', $state * $price);
                                                })
                                                ->columnSpan(2),

                                            TextEntry::make('_unit')
                                                ->label('Unit')
                                                ->state(fn (Get $get) => $get('unit') ?? '-')
                                                ->fontFamily(FontFamily::Mono)
                                                ->columnSpan(2),

                                            TextInput::make('price')
                                                ->label('Unit Price')
                                                ->required()
                                                ->numeric()
                                                ->live()
                                                ->prefix('Rp')
                                                ->reactive()
                                                ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                                    $quantity = $get('quantity_ordered') ?? 0;
                                                    $set('subtotal', $quantity * $state);
                                                })
                                                ->columnSpan(2),

                                            TextInput::make('subtotal')
                                                ->label('Subtotal')
                                                ->disabled()
                                                ->numeric()
                                                ->dehydrated()
                                                ->prefix('Rp')
                                                ->columnSpan(4),
                                        ]),

                                    Textarea::make('notes')
                                        ->label('Item Notes')
                                        ->rows(2)
                                        ->placeholder('Special notes for this item...')
                                        ->columnSpanFull(),
                                ])
                                ->minItems(1)
                                ->defaultItems(1)
                                ->reorderable()
                                ->collapsible()
                                ->cloneable()
                                ->itemLabel(fn (array $state): ?string => Product::find($state['product_id'] ?? null)?->name ?? 'New Item'
                                )
                                ->addActionLabel('Add Product')
                                ->deleteAction(
                                    fn (Action $action) => $action
                                        ->requiresConfirmation()
                                )
                                ->columnSpanFull()
                                ->live(),

                            TextEntry::make('total_summary')
                                ->label('Order Summary')
                                ->state(function (Get $get) {
                                    $details = collect($get('details') ?? []);
                                    $total = $details->sum('subtotal');
                                    $items = $details->count();
                                    $totalQty = $details->sum('quantity_ordered');

                                    return new HtmlString('
                                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                                    <div class="p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                                                        <div class="text-xs text-blue-600 dark:text-blue-400 font-medium uppercase">Total Items</div>
                                                        <div class="text-2xl font-bold text-blue-900 dark:text-blue-100 mt-1">'.$items.'</div>
                                                    </div>
                                                    <div class="p-4 bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800 rounded-lg">
                                                        <div class="text-xs text-purple-600 dark:text-purple-400 font-medium uppercase">Total Quantity</div>
                                                        <div class="text-2xl font-bold text-purple-900 dark:text-purple-100 mt-1">'.number_format($totalQty).'</div>
                                                    </div>
                                                    <div class="p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                                                        <div class="text-xs text-green-600 dark:text-green-400 font-medium uppercase">Total Amount</div>
                                                        <div class="text-2xl font-bold text-green-900 dark:text-green-100 mt-1">Rp '.number_format($total, 0, ',', '.').'</div>
                                                    </div>
                                                </div>
                                            ');
                                }),
                        ])
                        ->collapsible()
                        ->columnSpanFull(),
                ]),
        ];
    }
}
