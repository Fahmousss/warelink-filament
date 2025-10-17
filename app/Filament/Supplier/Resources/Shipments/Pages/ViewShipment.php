<?php

namespace App\Filament\Supplier\Resources\Shipments\Pages;

use App\Filament\Supplier\Resources\Shipments\ShipmentResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewShipment extends ViewRecord
{
    protected static string $resource = ShipmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('mark_shipped')
                ->label('Mark as Shipped')
                ->icon('heroicon-m-truck')
                ->color('info')
                ->requiresConfirmation()
                ->modalHeading('Mark Shipment as Shipped')
                ->modalDescription('Confirm that this shipment has been dispatched and is in transit.')
                ->action(function (): void {
                    $this->record->markAsShipped();

                    Notification::make()
                        ->success()
                        ->title('Shipment Dispatched')
                        ->body('Shipment is now in transit.')
                        ->send();
                })
                ->visible(fn () => $this->record->isDraft() && auth()->user()->can('update', $this->record)),

            Action::make('print')
                ->label('Print POD')
                ->icon('heroicon-m-printer')
                ->color('primary')
                ->url(fn () => route('download-pod', ['grn' => $this->record->goodsReceipt]))
                ->openUrlInNewTab()
                ->visible(fn () => (auth()->user()->can('downloadPOD', $this->record->goodsReceipt))),

            Action::make('mark_arrived')
                ->label('Mark as Arrived')
                ->icon('heroicon-m-inbox-arrow-down')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Mark Shipment as Arrived')
                ->modalDescription('Confirm that this shipment has arrived at the warehouse.')
                ->action(function (): void {
                    $this->record->markAsArrived();

                    Notification::make()
                        ->success()
                        ->title('Shipment Arrived')
                        ->body('Shipment has arrived at warehouse.')
                        ->send();
                })
                ->visible(fn () => $this->record->isShipped() && ! $this->record->isArrived() && auth()->user()->can('update', $this->record)),

            Action::make('create_goods_receipt')
                ->label('Create Goods Receipt')
                ->icon('heroicon-m-clipboard-document-check')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Create Goods Receipt')
                ->modalDescription('Create a goods receipt from this shipment to process the arrival.')
                ->url(fn () => route('filament.app.resources.goods-receipts.create', [
                    'shipment_id' => $this->record->id,
                    'purchase_order_id' => $this->record->purchase_order_id,
                ]))
                ->visible(fn () => $this->record->isArrived() && ! $this->record->isProcessed() && auth()->user()->can('create', \App\Models\GoodsReceipt::class)),

            EditAction::make()
                ->visible(fn () => $this->record->isDraft()),

            DeleteAction::make()
                ->visible(fn () => $this->record->isDraft()),
        ];
    }
}
