<?php

namespace App\Filament\Resources\PurchaseOrders\Pages;

use App\Filament\Resources\PurchaseOrders\PurchaseOrderResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewPurchaseOrder extends ViewRecord
{
    protected static string $resource = PurchaseOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Action::make('create_goods_receipt')
            //     ->label('Create Goods Receipt')
            //     ->icon('heroicon-m-inbox-arrow-down')
            //     ->color('success')
            //     ->url(fn () => route('filament.app.resources.goods-receipts.create', [
            //         'purchase_order_id' => $this->record->id,
            //     ]))
            //     ->visible(fn () => ! $this->record->isCompleted() && ! $this->record->isCancelled()),

            Action::make('cancel')
                ->label('Cancel Order')
                ->icon('heroicon-m-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Cancel Purchase Order')
                ->modalDescription('Are you sure you want to cancel this purchase order?')
                ->action(function () {
                    $this->record->markAsCancelled();

                    Notification::make()
                        ->danger()
                        ->title('Order Cancelled')
                        ->body('Purchase order has been cancelled.')
                        ->send();
                })
                ->visible(fn () => ! $this->record->isCompleted() && ! $this->record->isCancelled()),

            EditAction::make()
                ->visible(fn () => ! $this->record->isCompleted() && ! $this->record->isCancelled()),

            DeleteAction::make()
                ->visible(fn () => $this->record->isPending()),
        ];
    }
}
