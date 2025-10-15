<?php

namespace App\Filament\Supplier\Resources\Shipments;

use App\Enums\ShipmentStatus;
use App\Filament\Supplier\Resources\Shipments\Pages\CreateShipment;
use App\Filament\Supplier\Resources\Shipments\Pages\EditShipment;
use App\Filament\Supplier\Resources\Shipments\Pages\ListShipments;
use App\Filament\Supplier\Resources\Shipments\Pages\ViewShipment;
use App\Filament\Supplier\Resources\Shipments\Schemas\ShipmentForm;
use App\Filament\Supplier\Resources\Shipments\Schemas\ShipmentInfolist;
use App\Filament\Supplier\Resources\Shipments\Tables\ShipmentsTable;
use App\Models\Shipment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class ShipmentResource extends Resource
{
    protected static ?string $model = Shipment::class;

    protected static ?string $navigationLabel = 'Shipments (ASN)';

    protected static string|UnitEnum|null $navigationGroup = 'Procurement';

    protected static ?int $navigationSort = 2;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTruck;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::Truck;

    protected static ?string $recordTitleAttribute = 'shipment_number';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', ShipmentStatus::SHIPPED)->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'info';
    }

    public static function form(Schema $schema): Schema
    {
        return ShipmentForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ShipmentInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ShipmentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListShipments::route('/'),
            'create' => CreateShipment::route('/create'),
            'view' => ViewShipment::route('/{record}'),
            'edit' => EditShipment::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
