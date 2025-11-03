
<?php

use App\Enums\GoodsReceiptStatus;
use App\Enums\PurchaseOrderStatus;
use App\Enums\ShipmentStatus;
use App\Filament\App\Resources\GoodsReceipts\Pages\CreateGoodsReceipt;
use App\Filament\App\Resources\GoodsReceipts\Pages\EditGoodsReceipt;
use App\Filament\App\Resources\GoodsReceipts\Pages\ListGoodsReceipts;
use App\Filament\App\Resources\GoodsReceipts\Pages\ViewGoodsReceipt;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptDetail;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderDetail;
use App\Models\Shipment;
use App\Models\ShipmentDetail;
use App\Models\Supplier;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Forms\Components\Repeater;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

// Test Setup
beforeEach(function () {
    $this->checker = User::factory()->checker()->create();
    $this->admin = User::factory()->admin()->create();
    $this->supplier = Supplier::factory()->create();
    $this->supplierUser = User::factory()->supplier()->create();
});

// Group 1: Core Functionality & Workflow Tests
test('checker can create goods receipt from shipment (happy path)', function () {
    $undoRepeaterFaker = Repeater::fake();
    Filament::setCurrentPanel('app');
    actingAs($this->checker);

    $product = Product::factory(3)->for($this->supplier, 'supplier')->create()->toArray();
    $purchaseOrders = PurchaseOrder::factory()
        ->for(
            $this->supplier,
        )
        ->create();

    $purchaseOrdersDetail = PurchaseOrderDetail::factory()
        ->forEachSequence(
            ['product_id' => $product[0]['id']],
            ['product_id' => $product[1]['id']],
            ['product_id' => $product[2]['id']]
        )
        ->for(
            $purchaseOrders,
            'purchaseOrder'
        )
        ->create();

    $shipment = Shipment::factory()
        ->has(ShipmentDetail::factory()
            ->forEachSequence(
                ['product_id' => $product[0]['id']],
                ['product_id' => $product[1]['id']],
                ['product_id' => $product[2]['id']]
            ),
            'details')
        ->for($purchaseOrders, 'purchaseOrder')
        ->for($this->supplier, 'supplier')
        ->create([
            'status' => ShipmentStatus::ARRIVED,
        ]);

    livewire(CreateGoodsReceipt::class)
        ->fillForm([
            'shipment_id' => $shipment->id,
            'purchase_order_id' => $shipment->purchase_order_id,
            'receipt_date' => now(),
            'details' => $shipment->details->map(fn ($detail) => [
                'product_id' => $detail->product_id,
                'quantity_received' => $detail->quantity_shipped,
                'quantity_accepted' => $detail->quantity_shipped,
                'quantity_rejected' => 0,
            ])->toArray(),
        ])
        ->call('create')
        ->assertHasNoFormErrors();
    $undoRepeaterFaker();

    assertDatabaseHas(GoodsReceipt::class, [
        'shipment_id' => $shipment->id,
        'status' => GoodsReceiptStatus::PENDING,
    ]);
});

test('checker can create partial receipt', function () {
    Filament::setCurrentPanel('app');
    actingAs($this->checker);

    $product = Product::factory(3)->for($this->supplier, 'supplier')->create()->toArray();
    $purchaseOrders = PurchaseOrder::factory()
        ->for(
            $this->supplier,
        )
        ->create();

    $purchaseOrdersDetail = PurchaseOrderDetail::factory()
        ->forEachSequence(
            ['product_id' => $product[0]['id']],
            ['product_id' => $product[1]['id']],
            ['product_id' => $product[2]['id']]
        )
        ->for(
            $purchaseOrders,
            'purchaseOrder'
        )
        ->create();

    $shipment = Shipment::factory()
        ->has(ShipmentDetail::factory()
            ->forEachSequence(
                ['product_id' => $product[0]['id']],
                ['product_id' => $product[1]['id']],
                ['product_id' => $product[2]['id']]
            ),
            'details')
        ->for($purchaseOrders, 'purchaseOrder')
        ->for($this->supplier, 'supplier')
        ->create([
            'status' => ShipmentStatus::ARRIVED,
        ]);

    $detail = $shipment->details->first();

    livewire(CreateGoodsReceipt::class)
        ->fillForm([
            'shipment_id' => $shipment->id,
            'purchase_order_id' => $shipment->purchase_order_id,
            'delivery_order_number' => 'DO-TEST-002',
            'receipt_date' => now(),
            'details' => [
                [
                    'product_id' => $detail->product_id,
                    'quantity_received' => $detail->quantity_shipped,
                    'quantity_accepted' => $detail->quantity_shipped / 2,
                    'quantity_rejected' => 0,
                ],
            ],
        ])
        ->call('createAndComplete')
        ->assertHasNoFormErrors();

    // Verify PO status changes to Partial
    expect($shipment->purchaseOrder->fresh()->status)
        ->toBe(PurchaseOrderStatus::PARTIAL);
});

test('checker can handle rejected items', function () {
    Filament::setCurrentPanel('app');
    actingAs($this->checker);

    $product = Product::factory(3)->for($this->supplier, 'supplier')->create()->toArray();
    $purchaseOrders = PurchaseOrder::factory()
        ->for(
            $this->supplier,
        )
        ->create();

    $purchaseOrdersDetail = PurchaseOrderDetail::factory()
        ->forEachSequence(
            ['product_id' => $product[0]['id']],
            ['product_id' => $product[1]['id']],
            ['product_id' => $product[2]['id']]
        )
        ->for(
            $purchaseOrders,
            'purchaseOrder'
        )
        ->create();

    $shipment = Shipment::factory()
        ->has(ShipmentDetail::factory()
            ->forEachSequence(
                ['product_id' => $product[0]['id']],
                ['product_id' => $product[1]['id']],
                ['product_id' => $product[2]['id']]
            ),
            'details')
        ->for($purchaseOrders, 'purchaseOrder')
        ->for($this->supplier, 'supplier')
        ->create([
            'status' => ShipmentStatus::ARRIVED,
        ]);

    $detail = $shipment->details->first();

    livewire(CreateGoodsReceipt::class)
        ->fillForm([
            'shipment_id' => $shipment->id,
            'purchase_order_id' => $shipment->purchase_order_id,
            'delivery_order_number' => 'DO-TEST-003',
            'receipt_date' => now(),
            'details' => [
                [
                    'product_id' => $detail->product_id,
                    'quantity_received' => $detail->quantity_shipped,
                    'quantity_accepted' => $detail->quantity_shipped - 2,
                    'quantity_rejected' => 2,
                    'rejection_reason' => 'Items damaged during transit',
                ],
            ],
        ])
        ->call('createAndComplete')
        ->assertHasNoFormErrors();

    assertDatabaseHas('goods_receipt_details', [
        'quantity_rejected' => 2,
        'rejection_reason' => 'Items damaged during transit',
    ]);
    // Verify PO status changes to Partial
    expect($shipment->purchaseOrder->fresh()->status)
        ->toBe(PurchaseOrderStatus::PARTIAL);
});

test('system completes PO when all items received', function () {
    Filament::setCurrentPanel('app');
    actingAs($this->checker);

    $product = Product::factory(3)->for($this->supplier, 'supplier')->create()->toArray();
    $purchaseOrders = PurchaseOrder::factory()
        ->for(
            $this->supplier,
        )
        ->create();

    $purchaseOrdersDetail = PurchaseOrderDetail::factory()
        ->forEachSequence(
            ['product_id' => $product[0]['id']],
            ['product_id' => $product[1]['id']],
            ['product_id' => $product[2]['id']]
        )
        ->for(
            $purchaseOrders,
            'purchaseOrder'
        )
        ->create()
        ->toArray();

    $shipment = Shipment::factory()
        ->has(ShipmentDetail::factory()
            ->forEachSequence(
                [
                    'product_id' => $product[0]['id'],
                    'quantity_shipped' => $purchaseOrdersDetail[0]['quantity_ordered'],
                ],
                [
                    'product_id' => $product[1]['id'],
                    'quantity_shipped' => $purchaseOrdersDetail[1]['quantity_ordered'],
                ],
                [
                    'product_id' => $product[2]['id'],
                    'quantity_shipped' => $purchaseOrdersDetail[2]['quantity_ordered'],
                ],
            ),
            'details')
        ->for($purchaseOrders, 'purchaseOrder')
        ->for($this->supplier, 'supplier')
        ->create([
            'status' => ShipmentStatus::ARRIVED,
        ]);

    // Create GR for all items
    $gr = GoodsReceipt::factory()
        ->for($shipment)
        ->for($purchaseOrders)
        ->has(GoodsReceiptDetail::factory()
            ->forEachSequence(
                [
                    'product_id' => $product[0]['id'],
                    'quantity_received' => $purchaseOrdersDetail[0]['quantity_ordered'],
                    'quantity_accepted' => $purchaseOrdersDetail[0]['quantity_ordered'],
                    'quantity_rejected' => 0,
                ],
                [
                    'product_id' => $product[1]['id'],
                    'quantity_received' => $purchaseOrdersDetail[1]['quantity_ordered'],
                    'quantity_accepted' => $purchaseOrdersDetail[1]['quantity_ordered'],
                    'quantity_rejected' => 0,
                ],
                [
                    'product_id' => $product[2]['id'],
                    'quantity_received' => $purchaseOrdersDetail[2]['quantity_ordered'],
                    'quantity_accepted' => $purchaseOrdersDetail[2]['quantity_ordered'],
                    'quantity_rejected' => 0,
                ],
            ),
            'details')
        ->create();

    // Verify and complete the GR
    livewire(ViewGoodsReceipt::class, [
        'record' => $gr->id,
    ])
        ->callAction('verify')
        ->assertHasNoFormErrors()
        ->callAction('complete')
        ->assertHasNoFormErrors();

    // Check PO status
    expect($purchaseOrders->fresh()->status)
        ->toBe(PurchaseOrderStatus::COMPLETED);
});

test('checker can view completed goods receipt details', function () {
    Filament::setCurrentPanel('app');
    actingAs($this->checker);

    $product = Product::factory(3)->for($this->supplier, 'supplier')->create()->toArray();
    $purchaseOrders = PurchaseOrder::factory()
        ->for(
            $this->supplier,
        )
        ->create();

    $purchaseOrdersDetail = PurchaseOrderDetail::factory()
        ->forEachSequence(
            ['product_id' => $product[0]['id']],
            ['product_id' => $product[1]['id']],
            ['product_id' => $product[2]['id']]
        )
        ->for(
            $purchaseOrders,
            'purchaseOrder'
        )
        ->create()
        ->toArray();

    $shipment = Shipment::factory()
        ->has(ShipmentDetail::factory()
            ->forEachSequence(
                [
                    'product_id' => $product[0]['id'],
                    'quantity_shipped' => $purchaseOrdersDetail[0]['quantity_ordered'],
                ],
                [
                    'product_id' => $product[1]['id'],
                    'quantity_shipped' => $purchaseOrdersDetail[1]['quantity_ordered'],
                ],
                [
                    'product_id' => $product[2]['id'],
                    'quantity_shipped' => $purchaseOrdersDetail[2]['quantity_ordered'],
                ],
            ),
            'details')
        ->for($purchaseOrders, 'purchaseOrder')
        ->for($this->supplier, 'supplier')
        ->create([
            'status' => ShipmentStatus::ARRIVED,
        ]);

    // Create GR for all items
    $gr = GoodsReceipt::factory()
        ->for($shipment)
        ->for($purchaseOrders)
        ->has(GoodsReceiptDetail::factory()
            ->forEachSequence(
                [
                    'product_id' => $product[0]['id'],
                    'quantity_received' => $purchaseOrdersDetail[0]['quantity_ordered'],
                    'quantity_accepted' => $purchaseOrdersDetail[0]['quantity_ordered'],
                    'quantity_rejected' => 0,
                ],
                [
                    'product_id' => $product[1]['id'],
                    'quantity_received' => $purchaseOrdersDetail[1]['quantity_ordered'],
                    'quantity_accepted' => $purchaseOrdersDetail[1]['quantity_ordered'],
                    'quantity_rejected' => 0,
                ],
                [
                    'product_id' => $product[2]['id'],
                    'quantity_received' => $purchaseOrdersDetail[2]['quantity_ordered'],
                    'quantity_accepted' => $purchaseOrdersDetail[2]['quantity_ordered'],
                    'quantity_rejected' => 0,
                ],
            ),
            'details')
        ->create([
            'status' => GoodsReceiptStatus::COMPLETED,
        ]);

    livewire(ViewGoodsReceipt::class, [
        'record' => $gr->id,
    ])
        ->assertSchemaStateSet([
            'grn_number' => $gr->grn_number,
            'delivery_order_number' => $gr->delivery_order_number,
            'status' => $gr->status,
            'purchaseOrder.po_number' => $gr->purchaseOrder->po_number,
            'receipt_date' => $gr->receipt_date,
            'receiver.name' => $gr->receiver->name,
            'notes' => $gr->notes,
            'pod_scan_path' => $gr->pod_scan_path,
        ]);
});

// Group 2: Access Control & Security Tests
test('admin can view all goods receipts', function () {
    Filament::setCurrentPanel('admin');
    actingAs($this->admin);

    $product = Product::factory(3)->for($this->supplier, 'supplier')->create()->toArray();
    $purchaseOrders = PurchaseOrder::factory()
        ->for(
            $this->supplier,
        )
        ->create();

    $purchaseOrdersDetail = PurchaseOrderDetail::factory()
        ->forEachSequence(
            ['product_id' => $product[0]['id']],
            ['product_id' => $product[1]['id']],
            ['product_id' => $product[2]['id']]
        )
        ->for(
            $purchaseOrders,
            'purchaseOrder'
        )
        ->create()
        ->toArray();

    $shipment = Shipment::factory()
        ->has(ShipmentDetail::factory()
            ->forEachSequence(
                [
                    'product_id' => $product[0]['id'],
                    'quantity_shipped' => $purchaseOrdersDetail[0]['quantity_ordered'],
                ],
                [
                    'product_id' => $product[1]['id'],
                    'quantity_shipped' => $purchaseOrdersDetail[1]['quantity_ordered'],
                ],
                [
                    'product_id' => $product[2]['id'],
                    'quantity_shipped' => $purchaseOrdersDetail[2]['quantity_ordered'],
                ],
            ),
            'details')
        ->for($purchaseOrders, 'purchaseOrder')
        ->for($this->supplier, 'supplier')
        ->create([
            'status' => ShipmentStatus::ARRIVED,
        ]);

    // Create GR for all items
    $gr = GoodsReceipt::factory()
        ->for($shipment)
        ->for($purchaseOrders)
        ->has(GoodsReceiptDetail::factory()
            ->forEachSequence(
                [
                    'product_id' => $product[0]['id'],
                    'quantity_received' => $purchaseOrdersDetail[0]['quantity_ordered'],
                    'quantity_accepted' => $purchaseOrdersDetail[0]['quantity_ordered'],
                    'quantity_rejected' => 0,
                ],
                [
                    'product_id' => $product[1]['id'],
                    'quantity_received' => $purchaseOrdersDetail[1]['quantity_ordered'],
                    'quantity_accepted' => $purchaseOrdersDetail[1]['quantity_ordered'],
                    'quantity_rejected' => 0,
                ],
                [
                    'product_id' => $product[2]['id'],
                    'quantity_received' => $purchaseOrdersDetail[2]['quantity_ordered'],
                    'quantity_accepted' => $purchaseOrdersDetail[2]['quantity_ordered'],
                    'quantity_rejected' => 0,
                ],
            ),
            'details')
        ->create([
            'status' => GoodsReceiptStatus::COMPLETED,
        ]);

    livewire(ListGoodsReceipts::class)
        ->loadTable()
        ->assertCanSeeTableRecords([$gr]);
});

test('supplier can view processed shipment status', function () {
    actingAs($this->supplierUser);
    Filament::setCurrentPanel('supplier');
    Filament::setTenant($this->supplier);

    $product = Product::factory(3)->for($this->supplier, 'supplier')->create()->toArray();
    $purchaseOrders = PurchaseOrder::factory()
        ->for(
            $this->supplier,
        )
        ->create();

    $purchaseOrdersDetail = PurchaseOrderDetail::factory()
        ->forEachSequence(
            ['product_id' => $product[0]['id']],
            ['product_id' => $product[1]['id']],
            ['product_id' => $product[2]['id']]
        )
        ->for(
            $purchaseOrders,
            'purchaseOrder'
        )
        ->create()
        ->toArray();

    $shipment = Shipment::factory()
        ->has(ShipmentDetail::factory()
            ->forEachSequence(
                [
                    'product_id' => $product[0]['id'],
                    'quantity_shipped' => $purchaseOrdersDetail[0]['quantity_ordered'],
                ],
                [
                    'product_id' => $product[1]['id'],
                    'quantity_shipped' => $purchaseOrdersDetail[1]['quantity_ordered'],
                ],
                [
                    'product_id' => $product[2]['id'],
                    'quantity_shipped' => $purchaseOrdersDetail[2]['quantity_ordered'],
                ],
            ),
            'details')
        ->for($purchaseOrders, 'purchaseOrder')
        ->for($this->supplier, 'supplier')
        ->create([
            'status' => ShipmentStatus::ARRIVED,
        ]);

    // Create GR for all items
    $gr = GoodsReceipt::factory()
        ->for($shipment)
        ->for($purchaseOrders)
        ->has(GoodsReceiptDetail::factory()
            ->forEachSequence(
                [
                    'product_id' => $product[0]['id'],
                    'quantity_received' => $purchaseOrdersDetail[0]['quantity_ordered'],
                    'quantity_accepted' => $purchaseOrdersDetail[0]['quantity_ordered'],
                    'quantity_rejected' => 0,
                ],
                [
                    'product_id' => $product[1]['id'],
                    'quantity_received' => $purchaseOrdersDetail[1]['quantity_ordered'],
                    'quantity_accepted' => $purchaseOrdersDetail[1]['quantity_ordered'],
                    'quantity_rejected' => 0,
                ],
                [
                    'product_id' => $product[2]['id'],
                    'quantity_received' => $purchaseOrdersDetail[2]['quantity_ordered'],
                    'quantity_accepted' => $purchaseOrdersDetail[2]['quantity_ordered'],
                    'quantity_rejected' => 0,
                ],
            ),
            'details')
        ->create([
            'status' => GoodsReceiptStatus::COMPLETED,
        ]);
    $gr->shipment->markAsProcessed();

    expect($shipment->fresh()->status)->toBe(ShipmentStatus::PROCESSED);
});

test('checker cannot edit completed goods receipt', function () {
    Filament::setCurrentPanel('app');
    actingAs($this->checker);

    $product = Product::factory(3)->for($this->supplier, 'supplier')->create()->toArray();
    $purchaseOrders = PurchaseOrder::factory()
        ->for(
            $this->supplier,
        )
        ->create();

    $purchaseOrdersDetail = PurchaseOrderDetail::factory()
        ->forEachSequence(
            ['product_id' => $product[0]['id']],
            ['product_id' => $product[1]['id']],
            ['product_id' => $product[2]['id']]
        )
        ->for(
            $purchaseOrders,
            'purchaseOrder'
        )
        ->create()
        ->toArray();

    $shipment = Shipment::factory()
        ->has(ShipmentDetail::factory()
            ->forEachSequence(
                [
                    'product_id' => $product[0]['id'],
                    'quantity_shipped' => $purchaseOrdersDetail[0]['quantity_ordered'],
                ],
                [
                    'product_id' => $product[1]['id'],
                    'quantity_shipped' => $purchaseOrdersDetail[1]['quantity_ordered'],
                ],
                [
                    'product_id' => $product[2]['id'],
                    'quantity_shipped' => $purchaseOrdersDetail[2]['quantity_ordered'],
                ],
            ),
            'details')
        ->for($purchaseOrders, 'purchaseOrder')
        ->for($this->supplier, 'supplier')
        ->create([
            'status' => ShipmentStatus::ARRIVED,
        ]);

    // Create GR for all items
    $gr = GoodsReceipt::factory()
        ->for($shipment)
        ->for($purchaseOrders)
        ->has(GoodsReceiptDetail::factory()
            ->forEachSequence(
                [
                    'product_id' => $product[0]['id'],
                    'quantity_received' => $purchaseOrdersDetail[0]['quantity_ordered'],
                    'quantity_accepted' => $purchaseOrdersDetail[0]['quantity_ordered'],
                    'quantity_rejected' => 0,
                ],
                [
                    'product_id' => $product[1]['id'],
                    'quantity_received' => $purchaseOrdersDetail[1]['quantity_ordered'],
                    'quantity_accepted' => $purchaseOrdersDetail[1]['quantity_ordered'],
                    'quantity_rejected' => 0,
                ],
                [
                    'product_id' => $product[2]['id'],
                    'quantity_received' => $purchaseOrdersDetail[2]['quantity_ordered'],
                    'quantity_accepted' => $purchaseOrdersDetail[2]['quantity_ordered'],
                    'quantity_rejected' => 0,
                ],
            ),
            'details')
        ->create([
            'status' => GoodsReceiptStatus::COMPLETED,
        ]);

    livewire(EditGoodsReceipt::class, [
        'record' => $gr->id,
    ])
        ->assertForbidden();
});

// Group 3: Validation & Error Handling Tests
test('system prevents over-receipt of PO quantities', function () {
    Filament::setCurrentPanel('app');
    actingAs($this->checker);

    $product = Product::factory(3)->for($this->supplier, 'supplier')->create()->toArray();
    $purchaseOrders = PurchaseOrder::factory()
        ->for(
            $this->supplier,
        )
        ->create();

    $purchaseOrdersDetail = PurchaseOrderDetail::factory()
        ->forEachSequence(
            [
                'product_id' => $product[0]['id'],
                'quantity_ordered' => 15,
                'quantity_received' => 0,
            ],
        )
        ->for(
            $purchaseOrders,
            'purchaseOrder'
        )
        ->create();

    $shipment = Shipment::factory()
        ->has(ShipmentDetail::factory()
            ->forEachSequence(
                [
                    'product_id' => $product[0]['id'],
                    'quantity_shipped' => 30,
                ],
            ),
            'details')
        ->for($purchaseOrders, 'purchaseOrder')
        ->for($this->supplier, 'supplier')
        ->create([
            'status' => ShipmentStatus::ARRIVED,
        ]);

    $detail = $shipment->details->first();

    livewire(CreateGoodsReceipt::class)
        ->fillForm([
            'shipment_id' => $shipment->id,
            'purchase_order_id' => $shipment->purchase_order_id,
            'receipt_date' => now(),
            'details' => [
                [
                    'product_id' => $detail->product_id,
                    'quantity_received' => $detail->quantity_shipped + 10,
                    'quantity_accepted' => $detail->quantity_shipped + 10,
                    'quantity_rejected' => 0,
                ],
            ],
        ])
        ->call('createAndComplete')
        ->assertHasFormErrors(['details.0.quantity_received']);
});

test('system validates quantity inputs', function () {
    Filament::setCurrentPanel('app');
    actingAs($this->checker);

    $product = Product::factory(3)->for($this->supplier, 'supplier')->create()->toArray();
    $purchaseOrders = PurchaseOrder::factory()
        ->for(
            $this->supplier,
        )
        ->create();

    $purchaseOrdersDetail = PurchaseOrderDetail::factory()
        ->forEachSequence(
            [
                'product_id' => $product[0]['id'],
                'quantity_ordered' => 15,
                'quantity_received' => 0,
            ],
        )
        ->for(
            $purchaseOrders,
            'purchaseOrder'
        )
        ->create();

    $shipment = Shipment::factory()
        ->has(ShipmentDetail::factory()
            ->forEachSequence(
                [
                    'product_id' => $product[0]['id'],
                    'quantity_shipped' => 30,
                ],
            ),
            'details')
        ->for($purchaseOrders, 'purchaseOrder')
        ->for($this->supplier, 'supplier')
        ->create([
            'status' => ShipmentStatus::ARRIVED,
        ]);

    $detail = $shipment->details->first();

    livewire(CreateGoodsReceipt::class)
        ->fillForm([
            'shipment_id' => $shipment->id,
            'purchase_order_id' => $shipment->purchase_order_id,
            'receipt_date' => now(),
            'details' => [
                [
                    'product_id' => $detail->product_id,
                    'quantity_received' => -5,
                    'quantity_accepted' => 'abc',
                    'quantity_rejected' => 0,
                ],
            ],
        ])
        ->call('createAndComplete')
        ->assertHasFormErrors(['details.0.quantity_received', 'details.0.quantity_accepted']);
});

test('system requires mandatory fields', function () {
    Filament::setCurrentPanel('app');
    actingAs($this->checker);

    livewire(CreateGoodsReceipt::class)
        ->fillForm([])
        ->call('create')
        ->assertHasFormErrors([
            'purchase_order_id',
            'delivery_order_number',
            'receipt_date',
            'details',
        ]);
});

// Group 4: Integration & System Impact Tests
test('product stock increases after goods receipt completion', function () {
    Filament::setCurrentPanel('app');
    actingAs($this->checker);

    $products = Product::factory()->for($this->supplier, 'supplier')
        ->create([
            'stock_quantity' => 10,
        ]);

    $product = $products->toArray();
    $purchaseOrders = PurchaseOrder::factory()
        ->for(
            $this->supplier,
        )
        ->create();

    $purchaseOrdersDetail = PurchaseOrderDetail::factory()
        ->forEachSequence(
            [
                'product_id' => $product['id'],
                'quantity_ordered' => 15,
                'quantity_received' => 0,
            ],
        )
        ->for(
            $purchaseOrders,
            'purchaseOrder'
        )
        ->create();

    $shipment = Shipment::factory()
        ->has(ShipmentDetail::factory()
            ->forEachSequence(
                [
                    'product_id' => $product['id'],
                    'quantity_shipped' => 15,
                ],
            ),
            'details')
        ->for($purchaseOrders, 'purchaseOrder')
        ->for($this->supplier, 'supplier')
        ->create([
            'status' => ShipmentStatus::ARRIVED,
        ]);

    // Create GR for all items
    $gr = GoodsReceipt::factory()
        ->for($shipment)
        ->for($purchaseOrders)
        ->has(GoodsReceiptDetail::factory()
            ->forEachSequence(
                [
                    'product_id' => $product['id'],
                    'quantity_received' => $purchaseOrdersDetail[0]['quantity_ordered'],
                    'quantity_accepted' => $purchaseOrdersDetail[0]['quantity_ordered'],
                    'quantity_rejected' => 0,
                ],
            ),
            'details')
        ->create([
            'status' => GoodsReceiptStatus::PENDING,
        ]);

    livewire(ViewGoodsReceipt::class, [
        'record' => $gr->id,
    ])
        ->callAction('verify')
        ->callAction('complete');

    // Check stock increased
    expect($products->fresh()->stock_quantity)->toBe(25);
});

test('shipment status changes to processed after goods receipt completion', function () {
    Filament::setCurrentPanel('app');
    actingAs($this->checker);
    $products = Product::factory()->for($this->supplier, 'supplier')
        ->create([
            'stock_quantity' => 10,
        ]);

    $product = $products->toArray();
    $purchaseOrders = PurchaseOrder::factory()
        ->for(
            $this->supplier,
        )
        ->create();

    $purchaseOrdersDetail = PurchaseOrderDetail::factory()
        ->forEachSequence(
            [
                'product_id' => $product['id'],
                'quantity_ordered' => 15,
                'quantity_received' => 0,
            ],
        )
        ->for(
            $purchaseOrders,
            'purchaseOrder'
        )
        ->create();

    $shipment = Shipment::factory()
        ->has(ShipmentDetail::factory()
            ->forEachSequence(
                [
                    'product_id' => $product['id'],
                    'quantity_shipped' => 15,
                ],
            ),
            'details')
        ->for($purchaseOrders, 'purchaseOrder')
        ->for($this->supplier, 'supplier')
        ->create([
            'status' => ShipmentStatus::ARRIVED,
        ]);

    // Create GR for all items
    $gr = GoodsReceipt::factory()
        ->for($shipment)
        ->for($purchaseOrders)
        ->has(GoodsReceiptDetail::factory()
            ->forEachSequence(
                [
                    'product_id' => $product['id'],
                    'quantity_received' => $purchaseOrdersDetail[0]['quantity_ordered'],
                    'quantity_accepted' => $purchaseOrdersDetail[0]['quantity_ordered'],
                    'quantity_rejected' => 0,
                ],
            ),
            'details')
        ->create([
            'status' => GoodsReceiptStatus::PENDING,
        ]);

    livewire(ViewGoodsReceipt::class, [
        'record' => $gr->id,
    ])
        ->callAction('verify')
        ->callAction('complete');

    // Check shipment status
    expect($shipment->fresh()->status)->toBe(ShipmentStatus::PROCESSED);
});
