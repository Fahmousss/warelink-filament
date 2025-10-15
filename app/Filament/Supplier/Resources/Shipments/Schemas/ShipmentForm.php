<?php

namespace App\Filament\Supplier\Resources\Shipments\Schemas;

use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Schema;

class ShipmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Wizard::make()
                    ->columnSpanFull()
                    ->persistStepInQueryString(),
            ]);
    }
}
