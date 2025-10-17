<?php

namespace App\Filament\App\Resources\GoodsReceipts\Schemas;

use App\Enums\GoodsReceiptStatus;
use Filament\Infolists;
use Filament\Schemas\Components;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontFamily;
use Filament\Support\Enums\TextSize;

class GoodsReceiptInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Status Banner
                Infolists\Components\TextEntry::make('status_banner')
                    ->hiddenLabel()
                    ->columnSpanFull()
                    ->state(fn ($record) => new \Illuminate\Support\HtmlString(
                        match ($record->status) {
                            GoodsReceiptStatus::PENDING => '
                                    <div class="flex items-center gap-3 p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg">
                                        <svg class="w-6 h-6 text-amber-600 dark:text-amber-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                        </svg>
                                        <div class="flex-1">
                                            <h3 class="font-semibold text-amber-900 dark:text-amber-100">Pending Verification</h3>
                                            <p class="text-sm text-amber-800 dark:text-amber-200">Receipt awaiting verification and approval</p>
                                        </div>
                                    </div>
                                ',
                            GoodsReceiptStatus::VERIFIED => '
                                    <div class="flex items-center gap-3 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                        <div class="flex-1">
                                            <h3 class="font-semibold text-blue-900 dark:text-blue-100">Verified</h3>
                                            <p class="text-sm text-blue-800 dark:text-blue-200">Items verified and ready to complete</p>
                                        </div>
                                    </div>
                                ',
                            GoodsReceiptStatus::COMPLETED => '
                                    <div class="flex items-center gap-3 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                                        <svg class="w-6 h-6 text-green-600 dark:text-green-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                        <div class="flex-1">
                                            <h3 class="font-semibold text-green-900 dark:text-green-100">Receipt Completed</h3>
                                            <p class="text-sm text-green-800 dark:text-green-200">Stock has been updated successfully</p>
                                        </div>
                                    </div>
                                ',
                        }
                    )),

                // Receipt Overview
                Components\Section::make('Goods Receipt Details')
                    ->icon('heroicon-o-document-text')
                    ->description('Receipt identification and basic information')
                    ->schema([
                        Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('grn_number')
                                    ->label('GRN Number')
                                    ->icon('heroicon-m-hashtag')
                                    ->iconColor('primary')
                                    ->copyable()
                                    ->copyMessage('Copied!')
                                    ->fontFamily(FontFamily::Mono)
                                    ->size(TextSize::Large)
                                    ->weight('bold')
                                    ->columnSpan(1),

                                Infolists\Components\TextEntry::make('delivery_order_number')
                                    ->label('DO Number')
                                    ->icon('heroicon-m-document-text')
                                    ->iconColor('gray')
                                    ->fontFamily(FontFamily::Mono)
                                    ->size(TextSize::Large)
                                    ->weight('semibold')
                                    ->columnSpan(1),

                                Infolists\Components\TextEntry::make('status')
                                    ->badge()

                                    ->size(TextSize::Large)
                                    ->columnSpan(1),
                            ]),

                        Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('purchaseOrder.po_number')
                                    ->label('Purchase Order')
                                    ->icon('heroicon-m-shopping-cart')
                                    ->iconColor('primary')
                                    ->size(TextSize::Large)
                                    ->weight('semibold')
                                    ->hint(fn ($record) => "Supplier: {$record->purchaseOrder->supplier->name}")
                                    ->columnSpan(1),

                                Infolists\Components\TextEntry::make('receipt_date')
                                    ->label('Receipt Date')
                                    ->dateTime('F j, Y H:i')
                                    ->icon('heroicon-m-calendar-days')
                                    ->iconColor('gray')
                                    ->hint(fn ($record) => $record->receipt_date->diffForHumans())
                                    ->columnSpan(1),

                                Infolists\Components\TextEntry::make('receiver.name')
                                    ->label('Received By')
                                    ->icon('heroicon-m-user')
                                    ->iconColor('gray')
                                    ->size(TextSize::Large)
                                    ->weight('semibold')
                                    ->hint(fn ($record) => $record->receiver->email)
                                    ->columnSpan(1),
                            ]),

                        Infolists\Components\TextEntry::make('notes')
                            ->label('Notes')
                            ->placeholder('No notes')
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->columnSpanFull(),

                // Proof of Delivery
                Components\Section::make('Proof of Delivery')
                    ->icon('heroicon-o-camera')
                    ->description('Signed delivery document')
                    ->schema([
                        Infolists\Components\ImageEntry::make('pod_scan_path')
                            ->label('POD Document')
                            ->disk('private')
                            ->visibility('private')
                            ->imageHeight(400)
                            ->columnSpanFull(),
                    ])
                    ->visible(fn ($record) => $record->pod_scan_path)
                    ->collapsible()
                    ->columnSpanFull(),

                // Received Items
                Components\Section::make('Received Items')
                    ->icon('heroicon-o-cube')
                    ->description('Products and quantities received')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('details')
                            ->schema([
                                Components\Grid::make(7)
                                    ->schema([
                                        Infolists\Components\TextEntry::make('product.name')
                                            ->label('Product')
                                            ->icon('heroicon-m-cube')
                                            ->weight('semibold')
                                            ->hint(fn ($record) => "Code: {$record->product->product_code}")
                                            ->columnSpan(2),

                                        Infolists\Components\TextEntry::make('quantity_received')
                                            ->label('Received')
                                            ->badge()
                                            ->color('info')
                                            ->formatStateUsing(fn ($state, $record) => "{$state} {$record->product->unit}")
                                            ->columnSpan(1),

                                        Infolists\Components\TextEntry::make('quantity_accepted')
                                            ->label('Accepted')
                                            ->badge()
                                            ->color('success')
                                            ->formatStateUsing(fn ($state, $record) => "{$state} {$record->product->unit}")
                                            ->columnSpan(1),

                                        Infolists\Components\TextEntry::make('quantity_rejected')
                                            ->label('Rejected')
                                            ->badge()
                                            ->color(fn ($state) => $state > 0 ? 'danger' : 'gray')
                                            ->formatStateUsing(fn ($state, $record) => "{$state} {$record->product->unit}")
                                            ->columnSpan(1),

                                        Infolists\Components\TextEntry::make('acceptance_rate')
                                            ->label('Acceptance')
                                            ->badge()
                                            ->color(fn ($state) => match (true) {
                                                $state >= 95 => 'success',
                                                $state >= 80 => 'warning',
                                                default => 'danger',
                                            })
                                            ->formatStateUsing(fn ($state) => "{$state}%")
                                            ->columnSpan(1),

                                        Infolists\Components\IconEntry::make('has_rejections')
                                            ->label('Issues')
                                            ->boolean()
                                            ->trueIcon('heroicon-o-exclamation-triangle')
                                            ->falseIcon('heroicon-o-check-circle')
                                            ->trueColor('danger')
                                            ->falseColor('success')
                                            ->columnSpan(1),
                                    ]),

                                Infolists\Components\TextEntry::make('rejection_reason')
                                    ->label('Rejection Reason')
                                    ->color('danger')
                                    ->icon('heroicon-m-exclamation-triangle')
                                    ->visible(fn ($record) => $record->has_rejections)
                                    ->columnSpanFull(),

                                Infolists\Components\TextEntry::make('notes')
                                    ->label('Notes')
                                    ->placeholder('No notes')
                                    ->columnSpanFull(),
                            ])
                            ->columnSpanFull(),

                        Infolists\Components\TextEntry::make('receipt_summary')
                            ->label('Receipt Summary')
                            ->state(function ($record) {
                                $totalReceived = $record->total_quantity_received;
                                $totalAccepted = $record->total_quantity_accepted;
                                $totalRejected = $record->total_quantity_rejected;
                                $acceptanceRate = $totalReceived > 0 ? round(($totalAccepted / $totalReceived) * 100, 1) : 0;

                                return new \Illuminate\Support\HtmlString('
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

                // Audit Information
                Components\Section::make('Audit Information')
                    ->icon('heroicon-o-clock')
                    ->description('Record history and timestamps')
                    ->schema([
                        Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('created_at')
                                    ->label('Created')
                                    ->dateTime('M d, Y H:i')
                                    ->icon('heroicon-m-plus-circle')
                                    ->iconColor('success')
                                    ->since()
                                    ->tooltip(fn ($record) => $record->created_at->format('F j, Y \a\t g:i A')),

                                Infolists\Components\TextEntry::make('updated_at')
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
