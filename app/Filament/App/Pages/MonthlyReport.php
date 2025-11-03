<?php

declare(strict_types=1);

namespace App\Filament\App\Pages;

use App\Enums\UserRole;
use App\Models\GoodsReceipt;
use App\Models\Product;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Icons\Heroicon;
// use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class MonthlyReport extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static string|UnitEnum|null $navigationGroup = 'Reports';

    protected static ?string $title = 'Monthly Report';

    protected string $view = 'filament.app.pages.monthly-report';

    public ?int $selectedMonth = null;

    public ?int $selectedYear = null;

    public Collection $valuationData;

    public Collection $receiptsData;

    public Collection $payablesData;

    public Collection $movementData;

    public function mount(): void
    {
        $this->selectedMonth = (int) now()->format('m');
        $this->selectedYear = (int) now()->format('Y');

        $this->valuationData = new Collection;
        $this->receiptsData = new Collection;
        $this->payablesData = new Collection;
        $this->movementData = new Collection;

        $this->generateReport();
    }

    public static function canAccess(): bool
    {
        return Auth::user()?->role === UserRole::Accounting;
    }

    public function generateReport(): void
    {
        $startDate = Carbon::createFromDate($this->selectedYear, $this->selectedMonth, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        // Generate Inventory Valuation Data
        $this->valuationData = Product::query()
            ->with('goodsReceiptDetails')
            ->get()
            ->map(function ($product) use ($endDate) {
                $endStock = $product->goodsReceiptDetails()
                    ->whereHas('goodsReceipt', function ($query) use ($endDate) {
                        $query->where('receipt_date', '<=', $endDate);
                    })
                    ->sum('quantity_accepted');

                return [
                    'product_code' => $product->product_code,
                    'product_name' => $product->name,
                    'end_stock' => $endStock,
                    'unit_price' => $product->price,
                    'total_value' => $endStock * $product->price,
                ];
            });

        // Generate Goods Receipts Data
        $this->receiptsData = GoodsReceipt::query()
            ->with(['shipment.supplier', 'details.product'])
            ->whereBetween('receipt_date', [$startDate, $endDate])
            ->get()
            ->map(function ($receipt) {
                return [
                    'grn_number' => $receipt->grn_number,
                    'supplier_name' => $receipt->shipment->supplier->name,
                    'receipt_date' => $receipt->receipt_date->format('Y-m-d'),
                    'total_items' => $receipt->details->sum('quantity_accepted'),
                    'total_value' => $receipt->details->sum(function ($detail) {
                        return $detail->quantity_accepted * $detail->product->price;
                    }),
                ];
            });

        // Generate Payables Data
        $this->payablesData = GoodsReceipt::query()
            ->with(['shipment.supplier', 'details.product'])
            ->whereBetween('receipt_date', [$startDate, $endDate])
            ->where('status', 'completed')
            ->get()
            ->groupBy('shipment.supplier.name')
            ->map(function ($receipts, $supplierName) {
                return [
                    'supplier_name' => $supplierName,
                    'total_receipts' => $receipts->count(),
                    'total_items' => $receipts->sum(function ($receipt) {
                        return $receipt->details->sum('quantity_accepted');
                    }),
                    'total_value' => $receipts->sum(function ($receipt) {
                        return $receipt->details->sum(function ($detail) {
                            return $detail->quantity_accepted * $detail->product->price;
                        });
                    }),
                ];
            })
            ->values();

        // Generate Stock Movement Data
        $this->movementData = Product::query()
            ->with(['goodsReceiptDetails.goodsReceipt'])
            ->get()
            ->map(function ($product) use ($startDate, $endDate) {
                $startStock = $product->goodsReceiptDetails()
                    ->whereHas('goodsReceipt', function ($query) use ($startDate) {
                        $query->where('receipt_date', '<', $startDate);
                    })
                    ->sum('quantity_accepted');

                $receivedStock = $product->goodsReceiptDetails()
                    ->whereHas('goodsReceipt', function ($query) use ($startDate, $endDate) {
                        $query->whereBetween('receipt_date', [$startDate, $endDate]);
                    })
                    ->sum('quantity_accepted');

                $endStock = $startStock + $receivedStock;

                return [
                    'product_code' => $product->product_code,
                    'product_name' => $product->name,
                    'opening_stock' => $startStock,
                    'stock_in' => $receivedStock,
                    'stock_out' => 0, // Placeholder for stock out calculation
                    'closing_stock' => $endStock,
                ];
            });
    }

    protected function getHeaderActions(): array
    {
        return [
            // Action::make('exportPdf')
            //     ->label('Export PDF')
            //     ->icon('heroicon-o-arrow-down-tray')
            //     ->iconPosition(IconPosition::After)
            //     ->action(function () {
            //         // PDF export logic here
            //     }),

            // Action::make('exportExcel')
            //     ->label('Export Excel')
            //     ->icon(Heroicon::OutlinedTableCells)
            //     ->iconPosition(IconPosition::After)
            //     ->action(function () {
            //         // Excel export logic using Laravel Excel here
            //     }),
        ];
    }
}
