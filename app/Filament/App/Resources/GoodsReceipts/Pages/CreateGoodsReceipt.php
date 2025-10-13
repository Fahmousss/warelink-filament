<?php

namespace App\Filament\App\Resources\GoodsReceipts\Pages;

use App\Filament\App\Resources\GoodsReceipts\GoodsReceiptResource;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateGoodsReceipt extends CreateRecord
{
    protected static string $resource = GoodsReceiptResource::class;

    protected static bool $canCreateAnother = false;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set the receiver as current user
        $data['received_by'] = auth()->id();

        return $data;
    }

    protected function getFormActions(): array
    {
        return [
            Action::make()
                ->label('Create & Verify')
                ->action('createAndVerify'),
            $this->getCreateFormAction()
                ->label('Create as Pending')
                ->action('create'),
            ...(static::canCreateAnother() ? [$this->getCreateAnotherFormAction()] : []),
            $this->getCancelFormAction(),
        ];
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Goods Receipt Created')
            ->body("GRN {$this->record->grn_number} has been created successfully.")
            ->send();
    }

    public function createAndVerify(): void
    {
        $this->create();

        if ($this->record) {
            $this->record->markAsVerified();

            Notification::make()
                ->success()
                ->title('Receipt Verified')
                ->body('Goods receipt has been created and verified.')
                ->send();
        }
    }
}
