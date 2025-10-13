<?php

namespace App\Filament\App\Resources\GoodsReceipts\Pages;

use App\Filament\App\Resources\GoodsReceipts\GoodsReceiptResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewGoodsReceipt extends ViewRecord
{
    protected static string $resource = GoodsReceiptResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
