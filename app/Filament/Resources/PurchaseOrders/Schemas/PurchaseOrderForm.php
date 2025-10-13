<?php

namespace App\Filament\Resources\PurchaseOrders\Schemas;

use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Schema;

class PurchaseOrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Wizard::make()
                    ->columnSpanFull()
                    ->persistStepInQueryString(),
            ])
            ->columns(1);
    }
}
