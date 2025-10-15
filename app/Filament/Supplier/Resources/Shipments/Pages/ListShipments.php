<?php

namespace App\Filament\Supplier\Resources\Shipments\Pages;

use App\Enums\ShipmentStatus;
use App\Filament\Supplier\Resources\Shipments\ShipmentResource;
use App\Models\Shipment;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListShipments extends ListRecords
{
    protected static string $resource = ShipmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->icon('heroicon-m-plus')
                ->label('New Shipment (ASN)'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Shipments')
                ->icon('heroicon-m-queue-list')
                ->badge(Shipment::count()),

            'draft' => Tab::make('Draft')
                ->icon('heroicon-m-document')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', ShipmentStatus::DRAFT))
                ->badge(Shipment::where('status', ShipmentStatus::DRAFT)->count())
                ->badgeColor('gray'),

            'shipped' => Tab::make('Shipped')
                ->icon('heroicon-m-truck')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', ShipmentStatus::SHIPPED))
                ->badge(Shipment::where('status', ShipmentStatus::SHIPPED)->count())
                ->badgeColor('info'),

            'arrived' => Tab::make('Arrived')
                ->icon('heroicon-m-inbox-arrow-down')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', ShipmentStatus::ARRIVED))
                ->badge(Shipment::where('status', ShipmentStatus::ARRIVED)->count())
                ->badgeColor('warning'),

            'processed' => Tab::make('Processed')
                ->icon('heroicon-m-check-badge')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', ShipmentStatus::PROCESSED))
                ->badge(Shipment::where('status', ShipmentStatus::PROCESSED)->count())
                ->badgeColor('success'),
        ];
    }
}
