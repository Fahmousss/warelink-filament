<?php

namespace App\Filament\Resources\PurchaseOrders\Pages;

use App\Filament\Resources\PurchaseOrders\PurchaseOrderResource;
use App\Filament\Supplier\Resources\Shipments\ShipmentResource;
use App\Models\Shipment;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Gate;

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

            Action::make('print')
                ->label('Print Purchase Order')
                ->icon('heroicon-m-printer')
                ->color('primary')
                ->url(fn () => route('print-po', ['record' => $this->record]))
                ->openUrlInNewTab(),

            Action::make('create_delivery_order')
                ->label('Create Delivery Order')
                ->icon('heroicon-m-truck')
                ->color('success')
                ->url(fn () => ShipmentResource::getUrl('create', [
                    'purchase_order_id' => $this->record->id,
                ]))
                ->visible(fn () => ! $this->record->isCompleted() && ! $this->record->isCancelled() && auth()->user()->can('create', Shipment::class)),

            Action::make('cancel')
                ->label('Cancel Order')
                ->icon('heroicon-m-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Cancel Purchase Order')
                ->modalDescription('Are you sure you want to cancel this purchase order?')
                ->action(function () {
                    Gate::authorize('update', $this->record);
                    $this->record->markAsCancelled();

                    Notification::make()
                        ->danger()
                        ->title('Order Cancelled')
                        ->body('Purchase order has been cancelled.')
                        ->send();
                })
                ->visible(fn () => ! $this->record->isCompleted() && ! $this->record->isCancelled() && auth()->user()->can('update', $this->record)),

            EditAction::make()
                ->visible(fn () => ! $this->record->isCompleted() && ! $this->record->isCancelled()),

            DeleteAction::make()
                ->visible(fn () => $this->record->isPending()),
        ];
    }
}
