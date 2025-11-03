<?php

namespace App\Filament\App\Resources\Products\Widgets;

use App\Filament\App\Resources\Products\Pages\ViewProduct;
use App\Models\Product;
use Filament\Actions\Action;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class LowStockProducts extends TableWidget
{
    protected static ?string $heading = 'Low & Out of Stock Products';

    protected static ?int $sort = 2;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::query()
                    ->active()
                    ->lowStock()
                    ->orWhere('stock_quantity', 0)
                    ->latest()
                    ->limit(5)
            )
            ->columns([
                Split::make([

                    Stack::make([
                        TextColumn::make('product_code')
                            ->icon(Heroicon::Hashtag)
                            ->iconColor(Color::Gray)
                            ->label('SKU')
                            ->fontFamily('mono')
                            ->weight('bold'),
                        TextColumn::make('name')
                            ->icon(Heroicon::Cube)
                            ->iconColor(Color::Blue),
                    ]),
                    TextColumn::make('stock_quantity')
                        ->label('Current Stock')
                        ->description(fn ($record) => 'Min stock: '.$record->minimum_stock)
                        ->badge()
                        ->color(fn ($record) => $record->is_low_stock ? 'warning' : 'danger')
                        ->icon(fn ($record) => $record->is_low_stock ? Heroicon::ExclamationTriangle : Heroicon::XCircle),
                ]),
            ])
            ->recordActions([
                Action::make('view')
                    ->url(fn (Product $record): string => ViewProduct::getUrl(['record' => $record]))
                    ->icon('heroicon-m-eye'),
            ])
            ->paginated(false);
    }
}
