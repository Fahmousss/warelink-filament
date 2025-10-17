<?php

namespace App\Filament\Supplier\Resources\Shipments\Tables;

use App\Enums\ShipmentStatus;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Support\Enums\FontFamily;
use Filament\Support\Enums\Size;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ShipmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('shipment_number')
                    ->label('ASN Number')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-m-hashtag')
                    ->iconColor('primary')
                    ->copyable()
                    ->copyMessage('ASN number copied!')
                    ->fontFamily(FontFamily::Mono)
                    ->weight('semibold')
                    ->description(fn ($record) => $record->delivery_order_number)
                    ->toggleable(),

                TextColumn::make('purchaseOrder.po_number')
                    ->label('PO Number')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-m-shopping-cart')
                    ->iconColor('gray')
                    ->description(fn ($record) => $record->supplier->name)
                    ->toggleable()
                    ->visibleFrom('md'),

                TextColumn::make('supplier.name')
                    ->label('Supplier')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-m-building-storefront')
                    ->iconColor('gray')
                    ->toggleable()
                    ->visibleFrom('lg'),

                TextColumn::make('status')
                    ->badge()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('shipping_date')
                    ->label('Shipping Date')
                    ->date('M d, Y')
                    ->sortable()
                    ->icon('heroicon-m-calendar-days')
                    ->iconColor('gray')
                    ->description(fn ($record) => $record->shipping_date->diffForHumans())
                    ->tooltip(fn ($record) => $record->shipping_date->format('F j, Y'))
                    ->toggleable(),

                TextColumn::make('estimated_arrival_date')
                    ->label('Est. Arrival')
                    ->date('M d, Y')
                    ->placeholder('-')
                    ->sortable()
                    ->icon('heroicon-m-clock')
                    ->iconColor('gray')
                    ->toggleable()
                    ->visibleFrom('xl'),

                TextColumn::make('details_count')
                    ->label('Items')
                    ->counts('details')
                    ->badge()
                    ->color('info')
                    ->icon('heroicon-m-cube')
                    ->tooltip(fn ($state) => "{$state} product(s)")
                    ->alignCenter()
                    ->toggleable(),

                IconColumn::make('do_scan_path')
                    ->label('DO Scan')
                    ->boolean()
                    ->trueIcon('heroicon-o-document-check')
                    ->falseIcon('heroicon-o-document')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->tooltip(fn ($state) => $state ? 'DO uploaded' : 'No DO')
                    ->alignCenter()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('supplier_id')
                    ->label('Supplier')
                    ->relationship('supplier', 'name')
                    ->searchable()
                    ->preload()
                    ->multiple()
                    ->indicator('Supplier'),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options(ShipmentStatus::class)
                    ->multiple()
                    ->indicator('Status'),

                Filter::make('shipping_date')
                    ->schema([
                        DatePicker::make('from')
                            ->label('Shipping from'),
                        DatePicker::make('until')
                            ->label('Shipping until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('shipping_date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('shipping_date', '<=', $date),
                            );
                    }),

                TrashedFilter::make(),
            ])
            ->filtersLayout(FiltersLayout::Dropdown)
            ->persistFiltersInSession()
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->icon('heroicon-m-eye')
                        ->color('info'),

                    Action::make('mark_shipped')
                        ->label('Mark as Shipped')
                        ->icon('heroicon-m-truck')
                        ->color('info')
                        ->requiresConfirmation()
                        ->modalHeading('Mark Shipment as Shipped')
                        ->modalDescription('Confirm that this shipment has been dispatched.')
                        ->action(fn ($record) => $record->markAsShipped())
                        ->successNotificationTitle('Shipment marked as shipped')
                        ->visible(fn ($record) => $record->isDraft()),

                    Action::make('mark_arrived')
                        ->label('Mark as Arrived')
                        ->icon('heroicon-m-inbox-arrow-down')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('Mark Shipment as Arrived')
                        ->modalDescription('Confirm that this shipment has arrived at the warehouse.')
                        ->action(fn ($record) => $record->markAsArrived())
                        ->successNotificationTitle('Shipment marked as arrived')
                        ->visible(fn ($record) => $record->isShipped()),

                    Action::make('print')
                        ->label('Print POD')
                        ->icon('heroicon-m-printer')
                        ->color('primary')
                        ->url(fn ($record) => route('download-pod', ['grn' => $record->goodsReceipt]))
                        ->openUrlInNewTab()
                        ->visible(fn ($record) => (auth()->user()->can('downloadPOD', $record->goodsReceipt))),

                    Action::make('create_goods_receipt')
                        ->label('Create Goods Receipt')
                        ->icon('heroicon-m-clipboard-document-check')
                        ->color('success')
                        ->url(fn ($record) => route('filament.app.resources.goods-receipts.create', [
                            'shipment_id' => $record->id,
                            'purchase_order_id' => $record->purchase_order_id,
                        ]))
                        ->visible(fn ($record) => $record->isArrived() && ! $record->isProcessed() && auth()->user()->can('create', \App\Models\GoodsReceipt::class)),

                    EditAction::make()
                        ->icon('heroicon-m-pencil-square')
                        ->color('warning')
                        ->visible(fn ($record) => $record->isDraft()),

                    DeleteAction::make()
                        ->icon('heroicon-m-trash')
                        ->visible(fn ($record) => $record->isDraft()),

                    RestoreAction::make()
                        ->icon('heroicon-m-arrow-uturn-left')
                        ->color('success'),

                    ForceDeleteAction::make()
                        ->icon('heroicon-m-trash')
                        ->color('danger'),
                ])
                    ->label('Actions')
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->size(Size::Small)
                    ->color('gray')
                    ->button(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No shipments yet')
            ->emptyStateDescription('Create advance shipping notices (ASN) to notify warehouse of incoming deliveries.')
            ->emptyStateIcon('heroicon-o-truck')
            ->emptyStateActions([
                CreateAction::make()
                    ->label('Create Shipment (ASN)')
                    ->icon('heroicon-m-plus')
                    ->button(),
            ])
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->extremePaginationLinks();

    }
}
