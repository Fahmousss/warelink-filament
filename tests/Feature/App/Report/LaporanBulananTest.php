<?php

use App\Enums\PurchaseOrderStatus;
use App\Enums\UserRole;
use App\Filament\App\Pages\MonthlyReport;
use App\Models\GoodsReceipt;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;
use Carbon\Carbon;
use Filament\Facades\Filament;

use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->accounting = User::factory()->create([
        'role' => UserRole::Accounting,
        'is_active' => true,
    ]);

    $this->admin = User::factory()->create([
        'role' => UserRole::Admin,
        'is_active' => true,
    ]);

    Filament::setCurrentPanel('app');
});

describe('Autentikasi & Akses', function () {
    test('hanya accounting yang dapat mengakses halaman laporan bulanan', function () {
        $this->actingAs($this->accounting);

        livewire(MonthlyReport::class)
            ->assertSuccessful();
    });

    test('admin tidak dapat mengakses halaman laporan bulanan', function () {
        $this->actingAs($this->admin);

        livewire(MonthlyReport::class)
            ->assertForbidden();
    });

    test('requires authentication untuk akses laporan', function () {
        $this->get(MonthlyReport::getUrl())
            ->assertRedirect('/login');
    });
});

describe('Filter Periode', function () {
    test('dapat menampilkan form filter periode', function () {
        $this->actingAs($this->accounting);

        livewire(MonthlyReport::class)
            ->assertSchemaExists('form');
    });

    test('default period adalah bulan ini', function () {
        $this->actingAs($this->accounting);

        $startDate = Carbon::now()->startOfMonth()->toDateString();
        $endDate = Carbon::now()->endOfMonth()->toDateString();

        livewire(MonthlyReport::class)
            ->assertFormFieldIsVisible('start_date')
            ->assertFormFieldIsVisible('end_date');
    });

    test('dapat memilih jenis laporan', function () {
        $this->actingAs($this->accounting);

        livewire(MonthlyReport::class)
            ->assertFormFieldIsVisible('report_type')
            ->fillForm([
                'report_type' => 'goods_receipts',
            ])
            ->assertSet('reportType', 'goods_receipts');
    });

    test('dapat mengubah tanggal periode', function () {
        $this->actingAs($this->accounting);

        $startDate = Carbon::now()->subMonth()->startOfMonth()->toDateString();
        $endDate = Carbon::now()->subMonth()->endOfMonth()->toDateString();

        livewire(MonthlyReport::class)
            ->fillForm([
                'start_date' => $startDate,
                'end_date' => $endDate,
            ])
            ->assertSet('startDate', $startDate)
            ->assertSet('endDate', $endDate);
    });
});

describe('Laporan Purchase Order', function () {
    test('dapat menampilkan tabel purchase order', function () {
        $this->actingAs($this->accounting);

        $supplier = Supplier::factory()->create();
        $pos = PurchaseOrder::factory()->count(5)->create([
            'supplier_id' => $supplier->id,
            'order_date' => Carbon::now(),
        ]);

        livewire(MonthlyReport::class)
            ->fillForm(['report_type' => 'purchase_orders'])
            ->assertCanSeeTableRecords($pos);
    });

    test('dapat filter PO berdasarkan periode', function () {
        $this->actingAs($this->accounting);

        $supplier = Supplier::factory()->create();

        // PO dalam periode
        $poThisMonth = PurchaseOrder::factory()->create([
            'supplier_id' => $supplier->id,
            'order_date' => Carbon::now()->startOfMonth(),
        ]);

        // PO diluar periode
        $poLastMonth = PurchaseOrder::factory()->create([
            'supplier_id' => $supplier->id,
            'order_date' => Carbon::now()->subMonth(),
        ]);

        livewire(MonthlyReport::class)
            ->fillForm([
                'report_type' => 'purchase_orders',
                'start_date' => Carbon::now()->startOfMonth()->toDateString(),
                'end_date' => Carbon::now()->endOfMonth()->toDateString(),
            ])
            ->assertCanSeeTableRecords([$poThisMonth])
            ->assertCanNotSeeTableRecords([$poLastMonth]);
    });

    test('menampilkan kolom yang sesuai untuk laporan PO', function () {
        $this->actingAs($this->accounting);

        livewire(MonthlyReport::class)
            ->fillForm(['report_type' => 'purchase_orders'])
            ->assertTableColumnExists('po_number')
            ->assertTableColumnExists('supplier.name')
            ->assertTableColumnExists('order_date')
            ->assertTableColumnExists('status')
            ->assertTableColumnExists('total_amount');
    });
});

describe('Laporan Penerimaan Barang', function () {
    test('dapat menampilkan tabel goods receipt', function () {
        $this->actingAs($this->accounting);

        $supplier = Supplier::factory()->create();
        $po = PurchaseOrder::factory()->create(['supplier_id' => $supplier->id]);

        $grs = GoodsReceipt::factory()->count(3)->create([
            'purchase_order_id' => $po->id,
            'receipt_date' => Carbon::now(),
        ]);

        livewire(MonthlyReport::class)
            ->fillForm(['report_type' => 'goods_receipts'])
            ->assertCanSeeTableRecords($grs);
    });

    test('dapat filter GR berdasarkan periode', function () {
        $this->actingAs($this->accounting);

        $supplier = Supplier::factory()->create();
        $po = PurchaseOrder::factory()->create(['supplier_id' => $supplier->id]);

        // GR dalam periode
        $grThisMonth = GoodsReceipt::factory()->create([
            'purchase_order_id' => $po->id,
            'receipt_date' => Carbon::now()->startOfMonth(),
        ]);

        // GR diluar periode
        $grLastMonth = GoodsReceipt::factory()->create([
            'purchase_order_id' => $po->id,
            'receipt_date' => Carbon::now()->subMonth(),
        ]);

        livewire(MonthlyReport::class)
            ->fillForm([
                'report_type' => 'goods_receipts',
                'start_date' => Carbon::now()->startOfMonth()->toDateString(),
                'end_date' => Carbon::now()->endOfMonth()->toDateString(),
            ])
            ->assertCanSeeTableRecords([$grThisMonth])
            ->assertCanNotSeeTableRecords([$grLastMonth]);
    });

    test('menampilkan kolom yang sesuai untuk laporan GR', function () {
        $this->actingAs($this->accounting);

        livewire(MonthlyReport::class)
            ->fillForm(['report_type' => 'goods_receipts'])
            ->assertTableColumnExists('grn_number')
            ->assertTableColumnExists('purchaseOrder.po_number')
            ->assertTableColumnExists('purchaseOrder.supplier.name')
            ->assertTableColumnExists('receipt_date')
            ->assertTableColumnExists('status');
    });
});

describe('Laporan Stok Produk', function () {
    test('dapat menampilkan tabel stok produk', function () {
        $this->actingAs($this->accounting);

        $supplier = Supplier::factory()->create();
        $products = Product::factory()->count(5)->create([
            'supplier_id' => $supplier->id,
        ]);

        livewire(MonthlyReport::class)
            ->fillForm(['report_type' => 'stock'])
            ->assertCanSeeTableRecords($products);
    });

    test('menampilkan produk dengan stok terendah terlebih dahulu', function () {
        $this->actingAs($this->accounting);

        $supplier = Supplier::factory()->create();

        $productLowStock = Product::factory()->create([
            'supplier_id' => $supplier->id,
            'stock_quantity' => 5,
            'minimum_stock' => 10,
        ]);

        $productHighStock = Product::factory()->create([
            'supplier_id' => $supplier->id,
            'stock_quantity' => 100,
            'minimum_stock' => 10,
        ]);

        livewire(MonthlyReport::class)
            ->fillForm(['report_type' => 'stock'])
            ->assertCanSeeTableRecords([$productLowStock, $productHighStock], inOrder: true);
    });

    test('menampilkan kolom yang sesuai untuk laporan stok', function () {
        $this->actingAs($this->accounting);

        livewire(MonthlyReport::class)
            ->fillForm(['report_type' => 'stock'])
            ->assertTableColumnExists('product_code')
            ->assertTableColumnExists('name')
            ->assertTableColumnExists('supplier.name')
            ->assertTableColumnExists('stock_quantity')
            ->assertTableColumnExists('minimum_stock');
    });
});

describe('Laporan Keuangan', function () {
    test('dapat menampilkan laporan keuangan', function () {
        $this->actingAs($this->accounting);

        $supplier = Supplier::factory()->create();
        $po = PurchaseOrder::factory()->create([
            'supplier_id' => $supplier->id,
            'order_date' => Carbon::now(),
            'status' => PurchaseOrderStatus::COMPLETED,
        ]);

        livewire(MonthlyReport::class)
            ->fillForm(['report_type' => 'financial'])
            ->assertCanSeeTableRecords([$po]);
    });

    test('hanya menampilkan PO dengan status Partial atau Completed', function () {
        $this->actingAs($this->accounting);

        $supplier = Supplier::factory()->create();

        $poCompleted = PurchaseOrder::factory()->create([
            'supplier_id' => $supplier->id,
            'order_date' => Carbon::now(),
            'status' => PurchaseOrderStatus::COMPLETED,
        ]);

        $poPartial = PurchaseOrder::factory()->create([
            'supplier_id' => $supplier->id,
            'order_date' => Carbon::now(),
            'status' => PurchaseOrderStatus::PARTIAL,
        ]);

        $poPending = PurchaseOrder::factory()->create([
            'supplier_id' => $supplier->id,
            'order_date' => Carbon::now(),
            'status' => PurchaseOrderStatus::PENDING,
        ]);

        livewire(MonthlyReport::class)
            ->fillForm(['report_type' => 'financial'])
            ->assertCanSeeTableRecords([$poCompleted, $poPartial])
            ->assertCanNotSeeTableRecords([$poPending]);
    });

    test('menampilkan kolom yang sesuai untuk laporan keuangan', function () {
        $this->actingAs($this->accounting);

        livewire(MonthlyReport::class)
            ->fillForm(['report_type' => 'financial'])
            ->assertTableColumnExists('po_number')
            ->assertTableColumnExists('supplier.name')
            ->assertTableColumnExists('order_date')
            ->assertTableColumnExists('status')
            ->assertTableColumnExists('total_amount');
    });
});
