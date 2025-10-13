<?php

namespace App\Filament\App\Resources\Products\Tables;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
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
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Support\Enums\FontFamily;
use Filament\Support\Enums\Size;
use Filament\Support\Enums\TextSize;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                // Product Identity with visual hierarchy
                TextColumn::make('product_code')
                    ->label('SKU')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-m-hashtag')
                    ->iconColor('gray')
                    ->copyable()
                    ->copyMessage('Product code copied!')
                    ->copyMessageDuration(1500)
                    ->fontFamily(FontFamily::Mono)
                    ->weight('semibold')
                    ->description(fn ($record) => $record->name)
                    ->size(TextSize::Small)
                    ->toggleable()
                    ->visibleFrom('md'),

                TextColumn::make('name')
                    ->label('Product')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-m-cube')
                    ->iconColor('primary')
                    ->weight('semibold')
                    ->wrap()
                    ->limit(40)
                    ->tooltip(fn ($state) => strlen($state) > 40 ? $state : null)
                    ->description(fn ($record) => $record->product_code ? "SKU: {$record->product_code}" : null)
                    ->toggleable(),

                // Unit with badge styling
                TextColumn::make('unit')
                    ->badge()
                    ->color('gray')
                    ->icon('heroicon-m-cube-transparent')
                    ->sortable()
                    ->alignCenter()
                    ->toggleable()
                    ->visibleFrom('lg'),

                // Price with color coding
                TextColumn::make('price')
                    ->money('IDR')
                    ->sortable()
                    ->icon('heroicon-m-currency-dollar')
                    ->iconColor('success')
                    ->weight('semibold')
                    ->size(TextSize::Medium)
                    ->alignEnd()
                    ->toggleable(),

                // Stock with visual indicators
                TextColumn::make('stock_quantity')
                    ->label('Stock')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color(fn ($record) => match (true) {
                        $record->stock_quantity <= 0 => 'danger',
                        $record->stock_quantity <= $record->minimum_stock => 'warning',
                        $record->stock_quantity <= ($record->minimum_stock * 2) => 'info',
                        default => 'success',
                    })
                    ->icon(fn ($record) => match (true) {
                        $record->stock_quantity <= 0 => 'heroicon-m-x-circle',
                        $record->stock_quantity <= $record->minimum_stock => 'heroicon-m-exclamation-triangle',
                        default => 'heroicon-m-check-circle',
                    })
                    ->formatStateUsing(fn ($state, $record) => $state.' '.($record->unit ?? 'pcs'))
                    ->description(fn ($record) => $record->stock_quantity <= $record->minimum_stock
                        ? "Min: {$record->minimum_stock} {$record->unit}"
                        : null)
                    ->tooltip(fn ($record) => match (true) {
                        $record->stock_quantity <= 0 => 'Out of stock!',
                        $record->stock_quantity <= $record->minimum_stock => 'Low stock - reorder needed',
                        $record->stock_quantity <= ($record->minimum_stock * 2) => 'Stock level moderate',
                        default => 'Stock level good',
                    })
                    ->alignCenter()
                    ->toggleable(),

                // Minimum stock reference
                TextColumn::make('minimum_stock')
                    ->label('Min Stock')
                    ->numeric()
                    ->sortable()
                    ->icon('heroicon-m-arrow-trending-down')
                    ->iconColor('gray')
                    ->formatStateUsing(fn ($state, $record) => $state.' '.($record->unit ?? 'pcs'))
                    ->alignCenter()
                    ->toggleable()
                    ->visibleFrom('xl'),

                // Active status with clear indicators
                IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->sortable()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->tooltip(fn ($state) => $state ? 'Active product' : 'Inactive product')
                    ->alignCenter()
                    ->toggleable(),

                // Timestamps
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->since()
                    ->icon('heroicon-m-calendar-days')
                    ->iconColor('gray')
                    ->tooltip(fn ($record) => $record->created_at->format('F j, Y \a\t g:i A'))
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->since()
                    ->icon('heroicon-m-arrow-path')
                    ->iconColor('gray')
                    ->tooltip(fn ($record) => $record->updated_at->format('F j, Y \a\t g:i A'))
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('deleted_at')
                    ->label('Deleted')
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->since()
                    ->icon('heroicon-m-trash')
                    ->iconColor('danger')
                    ->badge()
                    ->color('danger')
                    ->tooltip(fn ($record) => $record->deleted_at ? 'Deleted on '.$record->deleted_at->format('F j, Y \a\t g:i A') : null)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                // Stock level filters
                SelectFilter::make('stock_status')
                    ->label('Stock Level')
                    ->options([
                        'out_of_stock' => 'Out of Stock',
                        'low_stock' => 'Low Stock',
                        'in_stock' => 'In Stock',
                        'overstocked' => 'Well Stocked',
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query->when(
                            $data['value'],
                            function (Builder $query, $value) {
                                switch ($value) {
                                    case 'out_of_stock':
                                        return $query->where('stock_quantity', '<=', 0);
                                    case 'low_stock':
                                        return $query->whereColumn('stock_quantity', '<=', 'minimum_stock')
                                            ->where('stock_quantity', '>', 0);
                                    case 'in_stock':
                                        return $query->whereColumn('stock_quantity', '>', 'minimum_stock')
                                            ->whereRaw('stock_quantity <= (minimum_stock * 2)');
                                    case 'overstocked':
                                        return $query->whereRaw('stock_quantity > (minimum_stock * 2)');
                                }
                            }
                        );
                    })
                    ->indicator('Stock Level'),

                // Active status filter
                TernaryFilter::make('is_active')
                    ->label('Product Status')
                    ->placeholder('All products')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only')
                    ->indicator('Status'),

                // Price range filter
                Filter::make('price_range')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('price_from')
                                    ->label('Price from')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->placeholder('0'),
                                TextInput::make('price_to')
                                    ->label('Price to')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->placeholder('999,999,999'),
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['price_from'],
                                fn (Builder $query, $price): Builder => $query->where('price', '>=', $price),
                            )
                            ->when(
                                $data['price_to'],
                                fn (Builder $query, $price): Builder => $query->where('price', '<=', $price),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['price_from'] ?? null) {
                            $indicators[] = Indicator::make('Price from Rp '.number_format($data['price_from'], 0, ',', '.'))
                                ->removeField('price_from');
                        }
                        if ($data['price_to'] ?? null) {
                            $indicators[] = Indicator::make('Price to Rp '.number_format($data['price_to'], 0, ',', '.'))
                                ->removeField('price_to');
                        }

                        return $indicators;
                    }),

                // Trashed filter
                TrashedFilter::make()
                    ->label('Deleted Products')
                    ->placeholder('Without trashed')
                    ->trueLabel('With trashed')
                    ->falseLabel('Only trashed')
                    ->native(false),
            ])
            ->filtersLayout(FiltersLayout::Dropdown)
            ->persistFiltersInSession()
            ->filtersTriggerAction(
                fn (Action $action) => $action
                    ->button()
                    ->label('Filters')
                    ->icon('heroicon-m-funnel')
                    ->color('gray')
                    ->badge(fn ($state) => count(array_filter($state ?? [])) ?: null)
            )
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->icon('heroicon-m-eye')
                        ->color('info'),
                    EditAction::make()
                        ->icon('heroicon-m-pencil-square')
                        ->color('warning'),
                    Action::make('toggle_status')
                        ->label(fn ($record) => $record->is_active ? 'Deactivate' : 'Activate')
                        ->icon(fn ($record) => $record->is_active ? 'heroicon-m-x-circle' : 'heroicon-m-check-circle')
                        ->color(fn ($record) => $record->is_active ? 'danger' : 'success')
                        ->requiresConfirmation()
                        ->modalHeading(fn ($record) => $record->is_active ? 'Deactivate Product' : 'Activate Product')
                        ->modalDescription(fn ($record) => $record->is_active
                            ? 'This product will be hidden from listings and cannot be sold.'
                            : 'This product will be visible in listings and available for sale.')
                        ->action(fn ($record) => $record->update(['is_active' => ! $record->is_active]))
                        ->successNotificationTitle('Product status updated')
                        ->visible(fn ($record) => ! $record->trashed()),
                    Action::make('duplicate')
                        ->label('Duplicate')
                        ->icon('heroicon-m-document-duplicate')
                        ->color('gray')
                        ->requiresConfirmation()
                        ->modalHeading('Duplicate Product')
                        ->modalDescription('This will create a copy of this product with a new product code.')
                        ->action(function ($record) {
                            $newProduct = $record->replicate();
                            $newProduct->product_code = $record->product_code.'-COPY';
                            $newProduct->name = $record->name.' (Copy)';
                            $newProduct->save();
                        })
                        ->successNotificationTitle('Product duplicated successfully')
                        ->visible(fn ($record) => ! $record->trashed()),
                    DeleteAction::make()
                        ->icon('heroicon-m-trash')
                        ->visible(fn ($record) => ! $record->trashed()),
                    RestoreAction::make()
                        ->icon('heroicon-m-arrow-uturn-left')
                        ->color('success')
                        ->visible(fn ($record) => $record->trashed()),
                    ForceDeleteAction::make()
                        ->icon('heroicon-m-trash')
                        ->color('danger')
                        ->visible(fn ($record) => $record->trashed()),
                ])
                    ->label('Actions')
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->size(Size::Small)
                    ->color('gray')
                    ->button(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('activate')
                        ->label('Activate selected')
                        ->icon('heroicon-m-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->action(fn (Collection $records) => $records->each->update(['is_active' => true]))
                        ->successNotificationTitle('Products activated successfully'),

                    BulkAction::make('deactivate')
                        ->label('Deactivate selected')
                        ->icon('heroicon-m-x-circle')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->action(fn (Collection $records) => $records->each->update(['is_active' => false]))
                        ->successNotificationTitle('Products deactivated successfully'),

                    BulkAction::make('adjust_stock')
                        ->label('Adjust stock')
                        ->icon('heroicon-m-adjustments-horizontal')
                        ->color('info')
                        ->schema([
                            Select::make('operation')
                                ->label('Operation')
                                ->options([
                                    'add' => 'Add to stock',
                                    'subtract' => 'Subtract from stock',
                                    'set' => 'Set stock to',
                                ])
                                ->required()
                                ->native(false),
                            TextInput::make('quantity')
                                ->label('Quantity')
                                ->numeric()
                                ->required()
                                ->minValue(0),
                        ])
                        ->action(function (Collection $records, array $data) {
                            $records->each(function ($record) use ($data) {
                                match ($data['operation']) {
                                    'add' => $record->increment('stock_quantity', $data['quantity']),
                                    'subtract' => $record->decrement('stock_quantity', $data['quantity']),
                                    'set' => $record->update(['stock_quantity' => $data['quantity']]),
                                };
                            });
                        })
                        ->deselectRecordsAfterCompletion()
                        ->successNotificationTitle('Stock adjusted successfully'),

                    // ExportBulkAction::make()
                    //         ->label('Export selected')
                    //         ->icon('heroicon-m-arrow-down-tray')
                    //         ->color('gray'),

                    DeleteBulkAction::make()
                        ->icon('heroicon-m-trash')
                        ->deselectRecordsAfterCompletion(),

                    ForceDeleteBulkAction::make()
                        ->icon('heroicon-m-trash')
                        ->color('danger')
                        ->deselectRecordsAfterCompletion(),

                    RestoreBulkAction::make()
                        ->icon('heroicon-m-arrow-uturn-left')
                        ->color('success')
                        ->deselectRecordsAfterCompletion(),
                ])
                    ->label('Bulk actions')
                    ->icon('heroicon-m-chevron-down'),
            ])
            ->emptyStateHeading('No products yet')
            ->emptyStateDescription('Get started by adding your first product to the inventory.')
            ->emptyStateIcon('heroicon-o-cube')
            ->emptyStateActions([
                CreateAction::make()
                    ->label('Add first product')
                    ->icon('heroicon-m-plus')
                    ->button(),
            ])
            ->striped()
            ->paginated([10, 25, 50, 100, 'all'])
            ->defaultPaginationPageOption(25)
            ->extremePaginationLinks()
            ->deferLoading()
            ->persistSearchInSession()
            ->persistColumnSearchesInSession();
    }
}
