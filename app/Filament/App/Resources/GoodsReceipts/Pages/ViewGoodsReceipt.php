<?php

namespace App\Filament\App\Resources\GoodsReceipts\Pages;

use App\Filament\App\Resources\GoodsReceipts\GoodsReceiptResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewGoodsReceipt extends ViewRecord
{
    protected static string $resource = GoodsReceiptResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('verify')
                ->label('Verify Receipt')
                ->icon('heroicon-m-clipboard-document-check')
                ->color('info')
                ->requiresConfirmation()
                ->modalHeading('Verify Goods Receipt')
                ->modalDescription('Confirm that all items have been checked and verified.')
                ->action(function () {
                    $this->record->markAsVerified();

                    Notification::make()
                        ->success()
                        ->title('Receipt Verified')
                        ->body('Goods receipt has been verified successfully.')
                        ->send();
                })
                ->visible(fn () => auth()->user()->can('verify', $this->record)),

            Action::make('complete')
                ->label('Complete Receipt')
                ->icon('heroicon-m-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Complete Goods Receipt')
                ->modalDescription('This will update product stock for all accepted items and mark the receipt as completed. This action cannot be undone.')
                ->modalSubmitActionLabel('Complete & Update Stock')
                ->action(function () {
                    try {
                        $this->record->markAsCompleted();

                        Notification::make()
                            ->success()
                            ->title('Receipt Completed')
                            ->body('Stock has been updated successfully.')
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->danger()
                            ->title('Error')
                            ->body($e->getMessage())
                            ->send();
                    }
                })
                ->visible(fn () => auth()->user()->can('complete', $this->record)),

            Action::make('download_pod')
                ->label('Download POD')
                ->icon('heroicon-m-arrow-down-tray')
                ->color('gray')
                ->url(fn () => route('download-pod', ['grn' => $this->record->id]))
                ->openUrlInNewTab()
                ->visible(fn () => auth()->user()->can('downloadPOD', $this->record)),

            EditAction::make()
                ->visible(fn () => ! $this->record->isCompleted()),

            DeleteAction::make()
                ->visible(fn () => $this->record->isPending()),
        ];
    }
}
