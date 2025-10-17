<?php

namespace App\Filament\App\Resources\GoodsReceipts\Tables;

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
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class GoodsReceiptsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('grn_number')
                    ->label('GRN Number')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-m-hashtag')
                    ->iconColor('primary')
                    ->copyable()
                    ->copyMessage('GRN copied!')
                    ->fontFamily(FontFamily::Mono)
                    ->weight('semibold')
                    ->description(fn ($record) => $record->purchaseOrder->po_number)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('purchaseOrder.po_number')
                    ->label('Purchase Order')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-m-shopping-cart')
                    ->iconColor('gray')
                    ->description(fn ($record) => $record->purchaseOrder->supplier->name)
                    ->toggleable()
                    ->visibleFrom('md'),

                Tables\Columns\TextColumn::make('delivery_order_number')
                    ->label('DO Number')
                    ->searchable()
                    ->icon('heroicon-m-document-text')
                    ->iconColor('gray')
                    ->fontFamily(FontFamily::Mono)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('receipt_date')
                    ->label('Receipt Date')
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->icon('heroicon-m-calendar-days')
                    ->iconColor('gray')
                    ->description(fn ($record) => $record->receipt_date->diffForHumans())
                    ->tooltip(fn ($record) => $record->receipt_date->format('F j, Y \a\t g:i A'))
                    ->toggleable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('receiver.name')
                    ->label('Received By')
                    ->searchable()
                    ->icon('heroicon-m-user')
                    ->iconColor('gray')
                    ->toggleable()
                    ->visibleFrom('lg'),

                Tables\Columns\IconColumn::make('pod_scan_path')
                    ->label('POD')
                    ->boolean()
                    ->trueIcon('heroicon-o-document-check')
                    ->falseIcon('heroicon-o-document')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->tooltip(fn ($state) => $state ? 'POD uploaded' : 'No POD')
                    ->alignCenter()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('details_count')
                    ->label('Items')
                    ->counts('details')
                    ->badge()
                    ->color('info')
                    ->icon('heroicon-m-cube')
                    ->tooltip(fn ($state) => "{$state} product(s)")
                    ->alignCenter()
                    ->toggleable()
                    ->visibleFrom('xl'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'Pending' => 'Pending',
                        'Verified' => 'Verified',
                        'Completed' => 'Completed',
                    ])
                    ->multiple()
                    ->indicator('Status'),

                Tables\Filters\SelectFilter::make('purchase_order')
                    ->relationship('purchaseOrder', 'po_number')
                    ->searchable()
                    ->preload()
                    ->indicator('Purchase Order'),

                Tables\Filters\Filter::make('has_pod')
                    ->label('Has POD')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('pod_scan_path'))
                    ->toggle(),

                Tables\Filters\Filter::make('receipt_date')
                    ->schema([
                        DatePicker::make('from')
                            ->label('Receipt from'),
                        DatePicker::make('until')
                            ->label('Receipt until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('receipt_date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('receipt_date', '<=', $date),
                            );
                    }),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->filtersLayout(Tables\Enums\FiltersLayout::Dropdown)
            ->persistFiltersInSession()
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->icon('heroicon-m-eye')
                        ->color('info'),

                    Action::make('verify')
                        ->label('Verify')
                        ->icon('heroicon-m-clipboard-document-check')
                        ->color('info')
                        ->requiresConfirmation()
                        ->modalHeading('Verify Goods Receipt')
                        ->modalDescription('Confirm that all items have been verified.')
                        ->action(fn ($record) => $record->markAsVerified())
                        ->successNotificationTitle('Receipt verified')
                        ->visible(fn ($record) => auth()->user()->can('verify', $record)),

                    Action::make('complete')
                        ->label('Complete')
                        ->icon('heroicon-m-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Complete Goods Receipt')
                        ->modalDescription('This will update product stock and mark the receipt as completed.')
                        ->action(fn ($record) => $record->markAsCompleted())
                        ->successNotificationTitle('Receipt completed and stock updated')
                        ->visible(fn ($record) => auth()->user()->can('complete', $record)),

                    EditAction::make()
                        ->icon('heroicon-m-pencil-square')
                        ->color('warning')
                        ->visible(fn ($record) => auth()->user()->can('update', $record) && ! $record->isCompleted()),

                    DeleteAction::make()
                        ->icon('heroicon-m-trash')
                        ->visible(fn ($record) => auth()->user()->can('delete', $record) && ! $record->isCompleted()),

                    RestoreAction::make()
                        ->icon('heroicon-m-arrow-uturn-left')
                        ->color('success'),

                    ForceDeleteAction::make()
                        ->icon('heroicon-m-trash')
                        ->color('danger')
                        ->visible(fn ($record) => auth()->user()->can('forceDelete', $record)),
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
            ->emptyStateHeading('No goods receipts yet')
            ->emptyStateDescription('Create a goods receipt when products arrive from suppliers.')
            ->emptyStateIcon('heroicon-o-inbox-arrow-down')
            ->emptyStateActions([
                CreateAction::make()
                    ->label('Create Goods Receipt')
                    ->icon('heroicon-m-plus')
                    ->button(),
            ])
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->extremePaginationLinks()
            ->deferLoading();
    }
}
