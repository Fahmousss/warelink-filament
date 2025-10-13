<?php

namespace App\Filament\App\Resources\GoodsReceipts\Schemas;

use App\Enums\PurchaseOrderStatus;
use App\Models\Product;
use App\Models\PurchaseOrder;
use Filament\Actions\Action;
use Filament\Forms\Components as FormComponents;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class GoodsReceiptForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Components\Section::make('Receipt Information')
                    ->description('Basic information about this goods receipt')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Components\Grid::make(2)
                            ->schema([
                                FormComponents\Select::make('purchase_order_id')
                                    ->label('Purchase Order')
                                    ->relationship('purchaseOrder', 'po_number', fn (Builder $query) => $query
                                        ->whereIn('status', [PurchaseOrderStatus::PENDING, PurchaseOrderStatus::PARTIAL])
                                        ->with(['supplier', 'details.product'])
                                    )
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                        if ($state) {
                                            $po = PurchaseOrder::with('details.product')->find($state);
                                            if ($po) {
                                                // Pre-populate items from PO
                                                $items = [];
                                                foreach ($po->details as $detail) {
                                                    if ($detail->quantity_remaining > 0) {
                                                        $items[] = [
                                                            'product_id' => $detail->product_id,
                                                            'quantity_received' => $detail->quantity_remaining,
                                                            'quantity_accepted' => $detail->quantity_remaining,
                                                            'quantity_rejected' => 0,
                                                            '_product_name' => $detail->product->name,
                                                            '_product_code' => $detail->product->product_code,
                                                            '_unit' => $detail->product->unit,
                                                            '_quantity_ordered' => $detail->quantity_ordered,
                                                            '_quantity_already_received' => $detail->quantity_received,
                                                            '_quantity_remaining' => $detail->quantity_remaining,
                                                        ];
                                                    }
                                                }
                                                $set('details', $items);
                                            }
                                        }
                                    })
                                    ->prefixIcon('heroicon-m-shopping-cart')
                                    ->helperText('Select the purchase order for this receipt')
                                    ->columnSpan(1),

                                TextEntry::make('po_info')
                                    ->label('PO Details')
                                    ->state(function (Get $get) {
                                        $poId = $get('purchase_order_id');
                                        if (! $poId) {
                                            return 'Select a PO to see details';
                                        }

                                        $po = PurchaseOrder::with('supplier')->find($poId);
                                        if (! $po) {
                                            return '';
                                        }

                                        return new HtmlString('
                                            <div class="space-y-1 text-sm">
                                                <div><strong>Supplier:</strong> '.$po->supplier->name.'</div>
                                                <div><strong>Order Date:</strong> '.$po->order_date->format('M d, Y').'</div>
                                                <div><strong>Status:</strong> <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-800">'.$po->status->value.'</span></div>
                                            </div>
                                        ');
                                    })
                                    ->columnSpan(1),
                            ]),

                        Components\Grid::make(3)
                            ->schema([
                                FormComponents\TextInput::make('delivery_order_number')
                                    ->label('Delivery Order Number (DO)')
                                    ->required()
                                    ->maxLength(100)
                                    ->placeholder('DO-2025-001')
                                    ->prefixIcon('heroicon-m-document-text')
                                    ->helperText('Supplier\'s delivery order number')
                                    ->columnSpan(1),

                                FormComponents\DateTimePicker::make('receipt_date')
                                    ->label('Receipt Date & Time')
                                    ->required()
                                    ->default(now())
                                    ->native(false)
                                    ->prefixIcon('heroicon-m-calendar-days')
                                    ->helperText('When the goods were received')
                                    ->columnSpan(1),

                                FormComponents\Select::make('status')
                                    ->label('Status')
                                    ->options([
                                        'Pending' => 'Pending Verification',
                                        'Verified' => 'Verified',
                                        'Completed' => 'Completed',
                                    ])
                                    ->default('Pending')
                                    ->required()
                                    ->native(false)
                                    ->prefixIcon('heroicon-m-flag')
                                    ->helperText('Current receipt status')
                                    ->visible(fn ($record) => $record !== null)
                                    ->columnSpan(1),
                            ]),

                        FormComponents\Textarea::make('notes')
                            ->label('Notes')
                            ->rows(3)
                            ->placeholder('Additional notes about this receipt...')
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->columnSpanFull(),

                // Proof of Delivery Section
                Components\Section::make('Proof of Delivery (POD)')
                    ->description('Upload the signed delivery document')
                    ->icon('heroicon-o-camera')
                    ->schema([
                        FormComponents\FileUpload::make('pod_scan_path')
                            ->label('POD Document')
                            ->image()
                            ->imageEditor()
                            ->imageEditorAspectRatios([
                                '16:9',
                                '4:3',
                                '1:1',
                            ])
                            ->directory('pod-scans')
                            ->visibility('private')
                            ->maxSize(5120)
                            ->acceptedFileTypes(['image/*', 'application/pdf'])
                            ->helperText('Upload signed delivery document, photo, or PDF. Max 5MB.')
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed()
                    ->columnSpanFull(),

                // Receipt Details Section
                Components\Section::make('Received Items')
                    ->description('Verify quantities and condition of received products')
                    ->icon('heroicon-o-cube')
                    ->schema([
                        TextEntry::make('verification_info')
                            ->hiddenLabel()
                            ->state(new HtmlString('
                                <div class="flex items-start gap-3 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg mb-4">
                                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <div class="flex-1">
                                        <h4 class="text-sm font-semibold text-blue-900 dark:text-blue-100 mb-1">Verification Instructions</h4>
                                        <ul class="text-xs text-blue-800 dark:text-blue-200 space-y-1">
                                            <li>• <strong>Quantity Received:</strong> Total items delivered</li>
                                            <li>• <strong>Quantity Accepted:</strong> Items in good condition</li>
                                            <li>• <strong>Quantity Rejected:</strong> Damaged or incorrect items</li>
                                            <li>• Accepted + Rejected must equal Received quantity</li>
                                        </ul>
                                    </div>
                                </div>
                            ')),

                        FormComponents\Repeater::make('details')
                            ->relationship()
                            ->schema([
                                Components\Grid::make(8)
                                    ->schema([
                                        FormComponents\Select::make('product_id')
                                            ->label('Product')
                                            ->options(function (Get $get) {
                                                $poId = $get('../../purchase_order_id');
                                                if (! $poId) {
                                                    return [];
                                                }

                                                $po = PurchaseOrder::with('details.product')->find($poId);
                                                if (! $po) {
                                                    return [];
                                                }

                                                return $po->details->pluck('product.name', 'product_id');
                                            })
                                            ->required()
                                            ->searchable()
                                            ->reactive()
                                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                                if ($state) {
                                                    $product = Product::find($state);
                                                    if ($product) {
                                                        $set('_product_name', $product->name);
                                                        $set('_product_code', $product->product_code);
                                                        $set('_unit', $product->unit);
                                                    }

                                                    // Get PO detail info
                                                    $poId = $get('../../purchase_order_id');
                                                    if ($poId) {
                                                        $po = PurchaseOrder::with('details')->find($poId);
                                                        $detail = $po->details->where('product_id', $state)->first();
                                                        if ($detail) {
                                                            $set('_quantity_ordered', $detail->quantity_ordered);
                                                            $set('_quantity_already_received', $detail->quantity_received);
                                                            $set('_quantity_remaining', $detail->quantity_remaining);
                                                        }
                                                    }
                                                }
                                            })
                                            ->disabled(fn ($record) => $record !== null)
                                            ->columnSpan(3),

                                        TextEntry::make('product_code')
                                            ->label('Code')
                                            ->state(fn (Get $get) => $get('_product_code') ?? '-')
                                            ->columnSpan(2),

                                        TextEntry::make('po_info')
                                            ->label('PO Info')
                                            ->state(function (Get $get) {
                                                $ordered = $get('_quantity_ordered') ?? 0;
                                                $received = $get('_quantity_already_received') ?? 0;
                                                $remaining = $get('_quantity_remaining') ?? 0;

                                                return new HtmlString("
                                                    <div class='text-xs space-y-0.5'>
                                                        <div><strong>Ordered:</strong> {$ordered}</div>
                                                        <div><strong>Already:</strong> {$received}</div>
                                                        <div><strong>Remaining:</strong> <span class='font-bold text-amber-600'>{$remaining}</span></div>
                                                    </div>
                                                ");
                                            })
                                            ->columnSpan(2),

                                        FormComponents\TextInput::make('quantity_received')
                                            ->label('Received')
                                            ->required()
                                            ->numeric()
                                            ->minValue(1)
                                            ->default(1)
                                            ->reactive()
                                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                                // Auto-set accepted if not manually changed
                                                $rejected = $get('quantity_rejected') ?? 0;
                                                $set('quantity_accepted', $state - $rejected);
                                            })
                                            ->suffixIcon('heroicon-m-inbox-arrow-down')
                                            ->columnSpan(2),

                                        FormComponents\TextInput::make('quantity_accepted')
                                            ->label('Accepted')
                                            ->required()
                                            ->numeric()
                                            ->minValue(0)
                                            ->reactive()
                                            ->suffixIcon('heroicon-m-check-circle')
                                            ->extraInputAttributes(['class' => 'text-green-600 font-semibold'])
                                            ->columnSpan(1),

                                        FormComponents\TextInput::make('quantity_rejected')
                                            ->label('Rejected')
                                            ->numeric()
                                            ->default(0)
                                            ->minValue(0)
                                            ->reactive()
                                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                                $received = $get('quantity_received') ?? 0;
                                                $set('quantity_accepted', $received - $state);
                                            })
                                            ->suffixIcon('heroicon-m-x-circle')
                                            ->extraInputAttributes(['class' => 'text-red-600 font-semibold'])
                                            ->columnSpan(1),

                                        TextEntry::make('unit')
                                            ->label('Unit')
                                            ->state(fn (Get $get) => $get('_unit') ?? '-')
                                            ->columnSpan(1),
                                    ]),

                                FormComponents\TextInput::make('rejection_reason')
                                    ->label('Rejection Reason')
                                    ->placeholder('Explain why items were rejected...')
                                    ->visible(fn (Get $get) => ($get('quantity_rejected') ?? 0) > 0)
                                    ->required(fn (Get $get) => ($get('quantity_rejected') ?? 0) > 0)
                                    ->columnSpanFull(),

                                FormComponents\Textarea::make('notes')
                                    ->label('Item Notes')
                                    ->rows(2)
                                    ->placeholder('Special notes for this item...')
                                    ->columnSpanFull(),
                            ])
                            ->minItems(1)
                            ->defaultItems(0)
                            ->reorderable(false)
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['_product_name'] ?? 'New Item'
                            )
                            ->addActionLabel('Add Product')
                            ->deleteAction(
                                fn (Action $action) => $action
                                    ->requiresConfirmation()
                            )
                            ->columnSpanFull()
                            ->live(),

                        TextEntry::make('receipt_summary')
                            ->label('Receipt Summary')
                            ->state(function (Get $get) {
                                $details = collect($get('details') ?? []);
                                $totalReceived = $details->sum('quantity_received');
                                $totalAccepted = $details->sum('quantity_accepted');
                                $totalRejected = $details->sum('quantity_rejected');
                                $acceptanceRate = $totalReceived > 0 ? round(($totalAccepted / $totalReceived) * 100, 1) : 0;

                                return new HtmlString('
                                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                        <div class="p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                                            <div class="text-xs text-blue-600 dark:text-blue-400 font-medium uppercase">Total Received</div>
                                            <div class="text-2xl font-bold text-blue-900 dark:text-blue-100 mt-1">'.$totalReceived.'</div>
                                        </div>
                                        <div class="p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                                            <div class="text-xs text-green-600 dark:text-green-400 font-medium uppercase">Accepted</div>
                                            <div class="text-2xl font-bold text-green-900 dark:text-green-100 mt-1">'.$totalAccepted.'</div>
                                        </div>
                                        <div class="p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                                            <div class="text-xs text-red-600 dark:text-red-400 font-medium uppercase">Rejected</div>
                                            <div class="text-2xl font-bold text-red-900 dark:text-red-100 mt-1">'.$totalRejected.'</div>
                                        </div>
                                        <div class="p-4 bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800 rounded-lg">
                                            <div class="text-xs text-purple-600 dark:text-purple-400 font-medium uppercase">Acceptance Rate</div>
                                            <div class="text-2xl font-bold text-purple-900 dark:text-purple-100 mt-1">'.$acceptanceRate.'%</div>
                                        </div>
                                    </div>
                                ');
                            }),
                    ])
                    ->collapsible()
                    ->columnSpanFull(),
            ])
            ->columns(1);
    }
}
