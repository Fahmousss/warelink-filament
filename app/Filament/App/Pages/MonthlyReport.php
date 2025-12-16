<?php

namespace App\Filament\App\Pages;

use App\Enums\PurchaseOrderStatus;
use App\Enums\UserRole;
use App\Models\GoodsReceipt;
use App\Models\Product;
use App\Models\PurchaseOrder;
use BackedEnum;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;

class MonthlyReport extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-chart-bar';

    protected string $view = 'filament.app.pages.monthly-report';

    protected static ?string $navigationLabel = 'Laporan Bulanan';

    protected static ?string $title = 'Laporan Bulanan';

    protected static ?int $navigationSort = 5;

    public ?string $reportType = 'purchase_orders';

    public ?array $data = [];

    public ?string $startDate = null;

    public ?string $endDate = null;

    public function mount(): void
    {
        // Set default period to current month
        $this->startDate = Carbon::now()->startOfMonth()->toDateString();
        $this->endDate = Carbon::now()->endOfMonth()->toDateString();

        $this->form->fill([
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
            'report_type' => $this->reportType,
        ]);
    }

    public static function canAccess(): bool
    {
        return Auth::user()?->role === UserRole::Accounting;
    }

    public function getTitle(): string|Htmlable
    {
        return 'Laporan Bulanan';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Filter Periode')
                    ->description('Pilih periode laporan yang ingin ditampilkan')
                    ->schema([
                        Select::make('report_type')
                            ->native(false)
                            ->label('Jenis Laporan')
                            ->options([
                                'purchase_orders' => 'Laporan Purchase Order',
                                'goods_receipts' => 'Laporan Penerimaan Barang',
                                'stock' => 'Laporan Stok Produk',
                                'financial' => 'Laporan Keuangan',
                            ])
                            ->default('purchase_orders')
                            ->live()
                            ->afterStateUpdated(fn ($state) => $this->reportType = $state),

                        DatePicker::make('start_date')
                            ->native(false)
                            ->label('Tanggal Mulai')
                            ->default(Carbon::now()->startOfMonth())
                            ->maxDate(now())
                            ->live()
                            ->afterStateUpdated(fn ($state) => $this->startDate = $state),

                        DatePicker::make('end_date')
                            ->native(false)
                            ->label('Tanggal Akhir')
                            ->default(Carbon::now()->endOfMonth())
                            ->maxDate(now())
                            ->after('start_date')
                            ->live()
                            ->afterStateUpdated(fn ($state) => $this->endDate = $state),
                    ])
                    ->columns(3),
            ])
            ->statePath('data');
    }

    public function table(Table $table): Table
    {
        return match ($this->reportType) {
            'goods_receipts' => $this->goodsReceiptsTable($table),
            'stock' => $this->stockTable($table),
            'financial' => $this->financialTable($table),
            default => $this->purchaseOrdersTable($table),
        };
    }

    protected function purchaseOrdersTable(Table $table): Table
    {
        return $table
            ->query(
                PurchaseOrder::query()
                    ->with(['supplier', 'details.product'])
                    ->when($this->startDate && $this->endDate, function ($query) {
                        return $query->whereBetween('order_date', [
                            Carbon::parse($this->startDate),
                            Carbon::parse($this->endDate),
                        ]);
                    })
                    ->orderBy('order_date', 'desc')
            )
            ->columns([
                TextColumn::make('po_number')
                    ->label('Nomor PO')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('supplier.name')
                    ->label('Supplier')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('order_date')
                    ->label('Tanggal Order')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('expected_delivery_date')
                    ->label('Estimasi Kirim')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->sortable(),

                TextColumn::make('total_amount')
                    ->label('Total Amount')
                    ->money('IDR')
                    ->sortable()
                    ->summarize(Sum::make()->money('IDR')->label('Total')),

                TextColumn::make('details_count')
                    ->label('Jumlah Item')
                    ->counts('details')
                    ->sortable(),
            ])
            ->defaultPaginationPageOption(25)
            ->striped();
    }

    protected function goodsReceiptsTable(Table $table): Table
    {
        return $table
            ->query(
                GoodsReceipt::query()
                    ->with(['purchaseOrder.supplier', 'shipment', 'receiver', 'details.product'])
                    ->when($this->startDate && $this->endDate, function ($query) {
                        return $query->whereBetween('receipt_date', [
                            Carbon::parse($this->startDate),
                            Carbon::parse($this->endDate),
                        ]);
                    })
                    ->orderBy('receipt_date', 'desc')
            )
            ->columns([
                TextColumn::make('grn_number')
                    ->label('Nomor GRN')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('purchaseOrder.po_number')
                    ->label('Nomor PO')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('purchaseOrder.supplier.name')
                    ->label('Supplier')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('receipt_date')
                    ->label('Tanggal Terima')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('receiver.name')
                    ->label('Penerima')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->sortable(),

                TextColumn::make('total_accepted')
                    ->label('Total Diterima')
                    ->state(function (GoodsReceipt $record): int {
                        return $record->details->sum('quantity_accepted');
                    })
                    ->numeric()
                    ->sortable(),

                TextColumn::make('total_rejected')
                    ->label('Total Ditolak')
                    ->state(function (GoodsReceipt $record): int {
                        return $record->details->sum('quantity_rejected');
                    })
                    ->numeric()
                    ->sortable(),

                TextColumn::make('acceptance_rate')
                    ->label('Acceptance Rate')
                    ->state(function (GoodsReceipt $record): string {
                        $accepted = $record->details->sum('quantity_accepted');
                        $total = $accepted + $record->details->sum('quantity_rejected');

                        if ($total === 0) {
                            return '0%';
                        }

                        return number_format(($accepted / $total) * 100, 2).'%';
                    })
                    ->sortable(),
            ])
            ->defaultPaginationPageOption(25)
            ->striped();
    }

    protected function stockTable(Table $table): Table
    {
        return $table
            ->query(
                Product::query()
                    ->with(['supplier'])
                    ->orderBy('stock_quantity', 'asc')
            )
            ->columns([
                TextColumn::make('product_code')
                    ->label('Kode Produk')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Nama Produk')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('supplier.name')
                    ->label('Supplier')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('stock_quantity')
                    ->label('Stok Saat Ini')
                    ->numeric()
                    ->sortable()
                    ->color(fn (Product $record): string => match (true) {
                        $record->stock_quantity === 0 => 'danger',
                        $record->stock_quantity < $record->minimum_stock => 'warning',
                        default => 'success',
                    }),

                TextColumn::make('minimum_stock')
                    ->label('Stok Minimum')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('stock_status')
                    ->label('Status Stok')
                    ->state(function (Product $record): string {
                        if ($record->stock_quantity === 0) {
                            return 'Habis';
                        }
                        if ($record->stock_quantity < $record->minimum_stock) {
                            return 'Rendah';
                        }

                        return 'Baik';
                    })
                    ->badge()
                    ->color(fn (Product $record): string => match (true) {
                        $record->stock_quantity === 0 => 'danger',
                        $record->stock_quantity < $record->minimum_stock => 'warning',
                        default => 'success',
                    })
                    ->sortable(),

                TextColumn::make('stock_quantity')
                    ->label('Nilai Stok')
                    ->state(function (Product $record): float {
                        return $record->stock_quantity * $record->price;
                    })
                    ->money('IDR')
                    ->sortable()
                    ->summarize(Sum::make()->money('IDR')->label('Total Nilai')),

                TextColumn::make('unit')
                    ->label('Satuan')
                    ->sortable(),
            ])
            ->defaultPaginationPageOption(25)
            ->striped();
    }

    protected function financialTable(Table $table): Table
    {
        return $table
            ->query(
                PurchaseOrder::query()
                    ->with(['supplier', 'details'])
                    ->whereIn('status', [
                        PurchaseOrderStatus::PARTIAL,
                        PurchaseOrderStatus::COMPLETED,
                    ])
                    ->when($this->startDate && $this->endDate, function ($query) {
                        return $query->whereBetween('order_date', [
                            Carbon::parse($this->startDate),
                            Carbon::parse($this->endDate),
                        ]);
                    })
                    ->orderBy('order_date', 'desc')
            )
            ->columns([
                TextColumn::make('po_number')
                    ->label('Nomor PO')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('supplier.name')
                    ->label('Supplier')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('order_date')
                    ->label('Tanggal Order')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->sortable(),

                TextColumn::make('total_amount')
                    ->label('Total Amount')
                    ->money('IDR')
                    ->sortable()
                    ->summarize(Sum::make()->money('IDR')->label('Total Pengeluaran')),

                TextColumn::make('total_amount')
                    ->label('Nilai Diterima')
                    ->state(function (PurchaseOrder $record): float {
                        $receivedValue = 0;
                        foreach ($record->details as $detail) {
                            $receivedValue += $detail->quantity_received * $detail->price;
                        }

                        return $receivedValue;
                    })
                    ->money('IDR')
                    ->sortable()
                    ->summarize(Sum::make()->money('IDR')->label('Total Diterima')),

                TextColumn::make('total_amount')
                    ->label('Outstanding')
                    ->state(function (PurchaseOrder $record): float {
                        $totalValue = 0;
                        $receivedValue = 0;

                        foreach ($record->details as $detail) {
                            $totalValue += $detail->quantity_ordered * $detail->price;
                            $receivedValue += $detail->quantity_received * $detail->price;
                        }

                        return $totalValue - $receivedValue;
                    })
                    ->money('IDR')
                    ->sortable()
                    ->summarize(Sum::make()->money('IDR')->label('Total Outstanding')),
            ])
            ->defaultPaginationPageOption(25)
            ->striped();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('refresh')
                ->label('Refresh Data')
                ->icon('heroicon-o-arrow-path')
                ->action(fn () => $this->dispatch('$refresh')),
        ];
    }
}
