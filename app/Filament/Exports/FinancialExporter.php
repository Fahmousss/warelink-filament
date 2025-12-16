<?php

namespace App\Filament\Exports;

use App\Models\PurchaseOrder;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class FinancialExporter extends Exporter
{
    protected static ?string $model = PurchaseOrder::class;

    public static function getColumns(): array
    {
        return [
            \Filament\Actions\Exports\ExportColumn::make('po_number')->label('Nomor PO'),
            \Filament\Actions\Exports\ExportColumn::make('supplier.name')->label('Supplier'),
            \Filament\Actions\Exports\ExportColumn::make('order_date')->label('Tanggal Order'),
            \Filament\Actions\Exports\ExportColumn::make('status')->label('Status'),
            \Filament\Actions\Exports\ExportColumn::make('total_amount')->label('Total Amount'),

        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your financial export has completed and '.Number::format($export->successful_rows).' '.str('row')->plural($export->successful_rows).' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to export.';
        }

        return $body;
    }
}
