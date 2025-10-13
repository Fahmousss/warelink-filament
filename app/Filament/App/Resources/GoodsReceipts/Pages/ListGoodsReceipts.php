<?php

namespace App\Filament\App\Resources\GoodsReceipts\Pages;

use App\Filament\App\Resources\GoodsReceipts\GoodsReceiptResource;
use App\Models\GoodsReceipt;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListGoodsReceipts extends ListRecords
{
    protected static string $resource = GoodsReceiptResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->icon('heroicon-m-plus')
                ->label('New Goods Receipt'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Receipts')
                ->icon('heroicon-m-queue-list')
                ->badge(GoodsReceipt::count()),

            'pending' => Tab::make('Pending')
                ->icon('heroicon-m-clock')
                ->modifyQueryUsing(fn (Builder $query) => $query->pending())
                ->badge(GoodsReceipt::pending()->count())
                ->badgeColor('warning'),

            'verified' => Tab::make('Verified')
                ->icon('heroicon-m-clipboard-document-check')
                ->modifyQueryUsing(fn (Builder $query) => $query->verified())
                ->badge(GoodsReceipt::verified()->count())
                ->badgeColor('info'),

            'completed' => Tab::make('Completed')
                ->icon('heroicon-m-check-badge')
                ->modifyQueryUsing(fn (Builder $query) => $query->completed())
                ->badge(GoodsReceipt::completed()->count())
                ->badgeColor('success'),
        ];
    }
}
