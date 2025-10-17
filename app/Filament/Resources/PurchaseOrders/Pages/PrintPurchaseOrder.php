<?php

namespace App\Filament\Resources\PurchaseOrders\Pages;

use App\Filament\Resources\PurchaseOrders\PurchaseOrderResource;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;

class PrintPurchaseOrder extends Page
{
    use InteractsWithRecord;

    protected static string $resource = PurchaseOrderResource::class;

    protected string $view = 'filament.resources.purchase-orders.pages.print-purchase-order';

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }
}
