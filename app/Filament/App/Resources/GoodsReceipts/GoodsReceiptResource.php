<?php

namespace App\Filament\App\Resources\GoodsReceipts;

use App\Enums\GoodsReceiptStatus;
use App\Filament\App\Resources\GoodsReceipts\Pages\CreateGoodsReceipt;
use App\Filament\App\Resources\GoodsReceipts\Pages\EditGoodsReceipt;
use App\Filament\App\Resources\GoodsReceipts\Pages\ListGoodsReceipts;
use App\Filament\App\Resources\GoodsReceipts\Pages\ViewGoodsReceipt;
use App\Filament\App\Resources\GoodsReceipts\Schemas\GoodsReceiptForm;
use App\Filament\App\Resources\GoodsReceipts\Schemas\GoodsReceiptInfolist;
use App\Filament\App\Resources\GoodsReceipts\Tables\GoodsReceiptsTable;
use App\Models\GoodsReceipt;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class GoodsReceiptResource extends Resource
{
    protected static ?string $model = GoodsReceipt::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInboxArrowDown;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::InboxArrowDown;

    protected static string|UnitEnum|null $navigationGroup = 'Procurement';

    protected static ?string $recordTitleAttribute = 'grn_number';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', GoodsReceiptStatus::PENDING)->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Schema $schema): Schema
    {
        return GoodsReceiptForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return GoodsReceiptInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GoodsReceiptsTable::configure($table);
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
            'index' => ListGoodsReceipts::route('/'),
            'create' => CreateGoodsReceipt::route('/create'),
            'view' => ViewGoodsReceipt::route('/{record}'),
            'edit' => EditGoodsReceipt::route('/{record}/edit'),
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
