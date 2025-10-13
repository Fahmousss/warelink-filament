<?php

namespace App\Filament\Resources\PurchaseOrders\Tables;

use App\Enums\PurchaseOrderStatus;
use Carbon\Carbon;
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
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PurchaseOrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('po_number')
                    ->label('PO Number')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-m-hashtag')
                    ->iconColor('primary')
                    ->copyable()
                    ->copyMessage('PO number copied!')
                    ->fontFamily(FontFamily::Mono)
                    ->weight('semibold')
                    ->description(fn ($record) => $record->supplier->name)
                    ->toggleable(),

                TextColumn::make('supplier.name')
                    ->label('Supplier')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-m-building-storefront')
                    ->iconColor('gray')
                    ->description(fn ($record) => $record->supplier->code)
                    ->toggleable()
                    ->visibleFrom('md'),

                TextColumn::make('order_date')
                    ->label('Order Date')
                    ->date('M d, Y')
                    ->sortable()
                    ->icon('heroicon-m-calendar-days')
                    ->iconColor('gray')
                    ->description(fn ($record) => $record->order_date->diffForHumans())
                    ->tooltip(fn ($record) => $record->order_date->format('F j, Y'))
                    ->toggleable(),

                TextColumn::make('status')
                    ->badge()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('IDR')
                    ->sortable()
                    ->icon('heroicon-m-banknotes')
                    ->iconColor('success')
                    ->weight('bold')
                    ->alignEnd()
                    ->toggleable(),

                TextColumn::make('details_count')
                    ->label('Items')
                    ->counts('details')
                    ->badge()
                    ->color('info')
                    ->icon('heroicon-m-cube')
                    ->tooltip(fn ($state) => "{$state} product(s)")
                    ->alignCenter()
                    ->toggleable()
                    ->visibleFrom('lg'),

                TextColumn::make('goods_receipts_count')
                    ->label('GRs')
                    ->counts('goodsReceipts')
                    ->badge()
                    ->color('primary')
                    ->icon('heroicon-m-inbox-arrow-down')
                    ->tooltip(fn ($state) => "{$state} goods receipt(s)")
                    ->alignCenter()
                    ->toggleable()
                    ->visibleFrom('xl'),

                TextColumn::make('expected_delivery_date')
                    ->label('Expected')
                    ->date('M d, Y')
                    ->placeholder('-')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                TrashedFilter::make(),
                SelectFilter::make('status')
                    ->options(PurchaseOrderStatus::class)
                    ->multiple()
                    ->indicator('Status'),

                SelectFilter::make('supplier')
                    ->relationship('supplier', 'name')
                    ->searchable()
                    ->preload()
                    ->indicator('Supplier'),

                Filter::make('order_date')
                    ->schema([
                        DatePicker::make('from')
                            ->label('Order from'),
                        DatePicker::make('until')
                            ->label('Order until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('order_date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('order_date', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['from'] ?? null) {
                            $indicators[] = Indicator::make('Order from '.Carbon::parse($data['from'])->toFormattedDateString())
                                ->removeField('from');
                        }
                        if ($data['until'] ?? null) {
                            $indicators[] = Indicator::make('Order until '.Carbon::parse($data['until'])->toFormattedDateString())
                                ->removeField('until');
                        }

                        return $indicators;
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

                    EditAction::make()
                        ->icon('heroicon-m-pencil-square')
                        ->color('warning')
                        ->visible(fn ($record) => ! $record->isCompleted() && ! $record->isCancelled()),

                    Action::make('cancel')
                        ->label('Cancel Order')
                        ->icon('heroicon-m-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Cancel Purchase Order')
                        ->modalDescription('Are you sure you want to cancel this purchase order? This action cannot be undone.')
                        ->action(fn ($record) => $record->markAsCancelled())
                        ->successNotificationTitle('Purchase order cancelled')
                        ->visible(fn ($record) => ! $record->isCompleted() && ! $record->isCancelled()),

                    DeleteAction::make()
                        ->icon('heroicon-m-trash')
                        ->visible(fn ($record) => $record->isPending()),

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
            ->emptyStateHeading('No purchase orders yet')
            ->emptyStateDescription('Create your first purchase order to start procurement.')
            ->emptyStateIcon('heroicon-o-shopping-cart')
            ->emptyStateActions([
                CreateAction::make()
                    ->label('Create Purchase Order')
                    ->icon('heroicon-m-plus')
                    ->button(),
            ])
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->extremePaginationLinks()
            ->deferLoading();
    }
}
