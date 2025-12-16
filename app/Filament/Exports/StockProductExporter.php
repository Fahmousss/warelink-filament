<?php

namespace App\Filament\Exports;

use App\Models\Product;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class StockProductExporter extends Exporter
{
    protected static ?string $model = Product::class;

    public static function getColumns(): array
    {
        return [
            \Filament\Actions\Exports\ExportColumn::make('product_code')->label('Kode Produk'),
            \Filament\Actions\Exports\ExportColumn::make('name')->label('Nama Produk'),
            \Filament\Actions\Exports\ExportColumn::make('supplier.name')->label('Supplier'),
            \Filament\Actions\Exports\ExportColumn::make('stock_quantity')->label('Stok Saat Ini'),
            \Filament\Actions\Exports\ExportColumn::make('minimum_stock')->label('Stok Minimum'),
            \Filament\Actions\Exports\ExportColumn::make('unit')->label('Satuan'),
            \Filament\Actions\Exports\ExportColumn::make('price')->label('Harga'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your stock product export has completed and '.Number::format($export->successful_rows).' '.str('row')->plural($export->successful_rows).' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to export.';
        }

        return $body;
    }
}
