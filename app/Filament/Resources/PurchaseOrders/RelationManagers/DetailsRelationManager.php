<?php

namespace App\Filament\Resources\PurchaseOrders\RelationManagers;

use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;

class DetailsRelationManager extends RelationManager
{
    protected static string $relationship = 'details';

    protected static ?string $title = 'Order Details';

    protected static string|BackedEnum|null $icon = Heroicon::OutlinedCube;

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.name')
                    ->label('Product')
                    ->searchable()
                    ->icon('heroicon-m-cube')
                    ->weight('semibold')
                    ->description(fn ($record) => "Code: {$record->product->product_code}"),

                TextColumn::make('quantity_ordered')
                    ->label('Ordered')
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn ($state, $record) => "{$state} {$record->product->unit}"),

                TextColumn::make('quantity_received')
                    ->label('Received')
                    ->badge()
                    ->color(fn ($state, $record) => $state >= $record->quantity_ordered ? 'success' :
                        ($state > 0 ? 'warning' : 'gray')
                    )
                    ->formatStateUsing(fn ($state, $record) => "{$state} {$record->product->unit}"),

                TextColumn::make('quantity_remaining')
                    ->label('Remaining')
                    ->badge()
                    ->color(fn ($state) => $state > 0 ? 'danger' : 'success')
                    ->formatStateUsing(fn ($state, $record) => "{$state} {$record->product->unit}"),

                TextColumn::make('price')
                    ->label('Unit Price')
                    ->money('IDR'),

                TextColumn::make('subtotal')
                    ->label('Subtotal')
                    ->money('IDR')
                    ->weight('bold')
                    ->color('success'),
            ])
            ->filters([
                Filter::make('not_fully_received')
                    ->label('Not Fully Received')
                    ->query(fn ($query) => $query->whereColumn('quantity_received', '<', 'quantity_ordered')),
            ])
            // ->headerActions([
            //     CreateAction::make()
            //         ->visible(fn ($livewire) => ! $livewire->ownerRecord->isCompleted() && ! $livewire->ownerRecord->isCancelled()),
            // ])
            ->recordActions([
                ViewAction::make(),
                // EditAction::make()
                //     ->visible(fn ($livewire) => ! $livewire->ownerRecord->isCompleted() && ! $livewire->ownerRecord->isCancelled()),
                // DeleteAction::make()
                //     ->visible(fn ($livewire) => ! $livewire->ownerRecord->isCompleted() && ! $livewire->ownerRecord->isCancelled()),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn ($livewire) => ! $livewire->ownerRecord->isCompleted() && ! $livewire->ownerRecord->isCancelled()),
                ]),
            ])
            ->emptyStateHeading('No items added')
            ->emptyStateDescription('Add products to this purchase order.')
            ->emptyStateIcon('heroicon-o-cube');

    }
}
