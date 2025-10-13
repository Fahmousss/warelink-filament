<?php

namespace App\Filament\Resources\PurchaseOrders\Pages;

use App\Filament\Resources\PurchaseOrders\PurchaseOrderResource;
use App\Models\PurchaseOrder;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListPurchaseOrders extends ListRecords
{
    protected static string $resource = PurchaseOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->icon('heroicon-m-plus')
                ->label('New Purchase Order'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Orders')
                ->icon('heroicon-m-queue-list')
                ->badge(PurchaseOrder::count()),

            'pending' => Tab::make('Pending')
                ->icon('heroicon-m-clock')
                ->modifyQueryUsing(fn (Builder $query) => $query->pending())
                ->badge(PurchaseOrder::pending()->count())
                ->badgeColor('warning'),

            'partial' => Tab::make('Partial')
                ->icon('heroicon-m-arrow-path')
                ->modifyQueryUsing(fn (Builder $query) => $query->partial())
                ->badge(PurchaseOrder::partial()->count())
                ->badgeColor('info'),

            'completed' => Tab::make('Completed')
                ->icon('heroicon-m-check-circle')
                ->modifyQueryUsing(fn (Builder $query) => $query->completed())
                ->badge(PurchaseOrder::completed()->count())
                ->badgeColor('success'),

            'cancelled' => Tab::make('Cancelled')
                ->icon('heroicon-m-x-circle')
                ->modifyQueryUsing(fn (Builder $query) => $query->cancelled())
                ->badge(PurchaseOrder::cancelled()->count())
                ->badgeColor('danger'),
        ];
    }
}
