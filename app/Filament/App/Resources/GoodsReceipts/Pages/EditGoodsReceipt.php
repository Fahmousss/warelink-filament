<?php

namespace App\Filament\App\Resources\GoodsReceipts\Pages;

use App\Filament\App\Resources\GoodsReceipts\GoodsReceiptResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditGoodsReceipt extends EditRecord
{
    protected static string $resource = GoodsReceiptResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make()
                ->visible(fn () => ! $this->record->isCompleted()),
        ];
    }

    protected function beforeSave(): void
    {
        // Only allow editing if not completed
        if ($this->record->isCompleted()) {
            $this->halt();

            Notification::make()
                ->danger()
                ->title('Cannot Edit')
                ->body('Completed receipts cannot be edited.')
                ->send();
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
}
