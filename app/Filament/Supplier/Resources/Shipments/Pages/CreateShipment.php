<?php

namespace App\Filament\Supplier\Resources\Shipments\Pages;

use App\Filament\Supplier\Resources\Shipments\ShipmentResource;
use App\Models\PurchaseOrder;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\CreateRecord\Concerns\HasWizard;
use Filament\Schemas\Components;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Wizard\Step;
use Illuminate\Support\HtmlString;

class CreateShipment extends CreateRecord
{
    use HasWizard;

    protected static string $resource = ShipmentResource::class;

    protected static bool $canCreateAnother = false;

    public function mount(): void
    {
        $pid = request()->query('purchase_order_id');
        $supplier = PurchaseOrder::find($pid)?->supplier->id;

        $this->form->fill([
            'purchase_order_id' => $pid,
            'supplier_id' => $supplier,
        ]);
    }

    protected function getSteps(): array
    {
        return [
            Step::make('Shipment Details')
                ->icon('heroicon-m-document-text')
                ->description('Enter the basic shipment information')
                ->schema([
                    Components\Section::make('Advance Shipping Notice')
                        ->description('Create an ASN to notify the warehouse of incoming delivery')
                        ->icon('heroicon-o-bell-alert')
                        ->schema([
                            Components\Grid::make(2)
                                ->schema([
                                    Select::make('purchase_order_id')
                                        ->label('Purchase Order')
                                        ->relationship(
                                            'purchaseOrder',
                                            'po_number',
                                            fn ($query) => $query->whereIn('status', ['Pending', 'Partial'])
                                                ->with(['supplier', 'details.product'])
                                        )
                                        ->required()
                                        ->searchable()
                                        ->preload()
                                        ->live()
                                        ->reactive()
                                        ->afterStateUpdated(function ($state, Set $set): void {
                                            if ($state) {
                                                $po = PurchaseOrder::with('supplier')->find($state);
                                                if ($po) {
                                                    $set('supplier_id', $po->supplier_id);
                                                }
                                            }
                                        })
                                        ->prefixIcon('heroicon-m-shopping-cart')
                                        ->helperText('Select the PO for this shipment')
                                        ->columnSpan(1),

                                    Select::make('supplier_id')
                                        ->label('Supplier')
                                        ->relationship('supplier', 'name')
                                        ->required()
                                        ->disabled()
                                        ->dehydrated()
                                        ->reactive()
                                        ->prefixIcon('heroicon-m-building-storefront')
                                        ->helperText('Auto-filled from selected PO')
                                        ->columnSpan(1),
                                ]),

                            Components\Grid::make(3)
                                ->schema([
                                    TextInput::make('delivery_order_number')
                                        ->label('Delivery Order Number (DO)')
                                        ->required()
                                        ->maxLength(100)
                                        ->placeholder('DO-2025-001')
                                        ->prefixIcon('heroicon-m-document-text')
                                        ->helperText('Supplier\'s DO number')
                                        ->columnSpan(1),

                                    DatePicker::make('shipping_date')
                                        ->label('Shipping Date')
                                        ->required()
                                        ->default(now())
                                        ->native(false)
                                        ->prefixIcon('heroicon-m-calendar-days')
                                        ->helperText('Date when shipment departs')
                                        ->columnSpan(1),

                                    DatePicker::make('estimated_arrival_date')
                                        ->label('Estimated Arrival')
                                        ->native(false)
                                        ->prefixIcon('heroicon-m-clock')
                                        ->helperText('Expected arrival at warehouse')
                                        ->columnSpan(1),
                                ]),

                            Textarea::make('notes')
                                ->label('Notes')
                                ->rows(3)
                                ->placeholder('Additional shipment notes...')
                                ->columnSpanFull(),

                            FileUpload::make('do_scan_path')
                                ->label('Delivery Order Scan')
                                ->image()
                                ->imageEditor()
                                ->imageEditorAspectRatios([
                                    '16:9',
                                    '4:3',
                                    '1:1',
                                ])
                                ->directory('delivery-orders')
                                ->disk('public')
                                ->visibility('public')
                                ->maxSize(5120)
                                ->acceptedFileTypes(['image/*', 'application/pdf'])
                                ->helperText('Upload scanned DO document. Max 5MB.')
                                ->columnSpanFull(),
                        ])
                        ->collapsible()
                        ->columnSpanFull(),
                ]),

            // Step 2: Items to Ship
            Components\Wizard\Step::make('Items to Ship')
                ->icon('heroicon-m-cube')
                ->description('Add products being shipped')
                ->schema([
                    Components\Section::make('Shipped Products')
                        ->description('Select products from the PO and specify quantities')
                        ->icon('heroicon-o-shopping-bag')
                        ->schema([
                            TextEntry::make('shipping_info')
                                ->hiddenLabel()
                                ->state(new HtmlString('
                                            <div class="flex items-start gap-3 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg mb-4">
                                                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                <div class="flex-1">
                                                    <h4 class="text-sm font-semibold text-blue-900 dark:text-blue-100 mb-1">Shipping Instructions</h4>
                                                    <ul class="text-xs text-blue-800 dark:text-blue-200 space-y-1">
                                                        <li>• Select products from the Purchase Order</li>
                                                        <li>• Enter the quantity being shipped for each product</li>
                                                        <li>• You can ship partial quantities if needed</li>
                                                    </ul>
                                                </div>
                                            </div>
                                        ')),

                            Repeater::make('details')
                                ->relationship()
                                ->schema([
                                    Components\Grid::make(12)
                                        ->schema([
                                            Select::make('product_id')
                                                ->label('Product')
                                                ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                                ->options(function (Get $get): array {
                                                    $poId = $get('../../purchase_order_id');
                                                    if (! $poId) {
                                                        return [];
                                                    }

                                                    $po = PurchaseOrder::with('details.product')->find($poId);
                                                    if (! $po) {
                                                        return [];
                                                    }

                                                    return $po->details
                                                        ->where('quantity_remaining', '>', 0)
                                                        ->pluck('product.name', 'product_id')
                                                        ->toArray();
                                                })
                                                ->required()
                                                ->searchable()
                                                ->reactive()
                                                ->afterStateUpdated(function ($state, Set $set, Get $get): void {
                                                    if ($state) {
                                                        $product = \App\Models\Product::find($state);
                                                        if ($product) {
                                                            $set('_product_code', $product->product_code);
                                                            $set('_unit', $product->unit);
                                                        }

                                                        // Get PO detail info
                                                        $poId = $get('../../purchase_order_id');
                                                        if ($poId) {
                                                            $po = PurchaseOrder::with('details')->find($poId);
                                                            $detail = $po?->details->where('product_id', $state)->first();
                                                            if ($detail) {
                                                                $set('_quantity_ordered', $detail->quantity_ordered);
                                                                $set('_quantity_remaining', $detail->quantity_remaining);
                                                            }
                                                        }
                                                    }
                                                })
                                                ->disabled(fn ($record) => $record !== null)
                                                ->columnSpan(4),

                                            TextEntry::make('product_code')
                                                ->label('Code')
                                                ->state(fn (Get $get): string => $get('_product_code') ?? '-')
                                                ->columnSpan(2),

                                            TextEntry::make('po_info')
                                                ->label('PO Info')
                                                ->state(function (Get $get): HtmlString {
                                                    $ordered = $get('_quantity_ordered') ?? 0;
                                                    $remaining = $get('_quantity_remaining') ?? 0;

                                                    return new HtmlString("
                                                                <div class='text-xs space-y-0.5'>
                                                                    <div><strong>Ordered:</strong> {$ordered}</div>
                                                                    <div><strong>Remaining:</strong> <span class='font-bold text-amber-600'>{$remaining}</span></div>
                                                                </div>
                                                            ");
                                                })
                                                ->columnSpan(3),

                                            TextInput::make('quantity_shipped')
                                                ->label('Qty Shipping')
                                                ->required()
                                                ->numeric()
                                                ->minValue(1)
                                                ->default(1)
                                                ->suffixIcon('heroicon-m-truck')
                                                ->columnSpan(2),

                                            TextEntry::make('unit')
                                                ->label('Unit')
                                                ->state(fn (Get $get): string => $get('_unit') ?? '-')
                                                ->columnSpan(1),
                                        ]),

                                    TextEntry::make('notes')
                                        ->label('Item Notes')
                                        ->placeholder('Special notes for this item...')
                                        ->columnSpanFull(),
                                ])
                                ->minItems(1)
                                ->defaultItems(0)
                                ->reorderable(false)
                                ->collapsible()
                                ->itemLabel(fn (array $state): ?string => \App\Models\Product::find($state['product_id'] ?? null)?->name ?? 'New Item'
                                )
                                ->addActionLabel('Add Product')
                                ->deleteAction(
                                    fn (Action $action) => $action
                                        ->requiresConfirmation()
                                )
                                ->columnSpanFull()
                                ->live(),

                            TextEntry::make('shipment_summary')
                                ->label('Shipment Summary')
                                ->state(function (Get $get): HtmlString {
                                    $details = collect($get('details') ?? []);
                                    $totalQty = $details->sum('quantity_shipped');
                                    $totalItems = $details->count();

                                    return new HtmlString('
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                    <div class="p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                                                        <div class="text-xs text-blue-600 dark:text-blue-400 font-medium uppercase">Total Items</div>
                                                        <div class="text-2xl font-bold text-blue-900 dark:text-blue-100 mt-1">'.$totalItems.'</div>
                                                    </div>
                                                    <div class="p-4 bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800 rounded-lg">
                                                        <div class="text-xs text-purple-600 dark:text-purple-400 font-medium uppercase">Total Quantity</div>
                                                        <div class="text-2xl font-bold text-purple-900 dark:text-purple-100 mt-1">'.number_format($totalQty).'</div>
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

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->title('Shipment (ASN) Created')
            ->body("Shipment {$this->record->shipment_number} has been created successfully.")
            ->success();
    }
}
