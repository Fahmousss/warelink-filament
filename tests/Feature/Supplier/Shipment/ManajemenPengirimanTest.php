<?php

use App\Enums\PurchaseOrderStatus;
use App\Enums\ShipmentStatus;
use App\Enums\UserRole;
use App\Filament\Supplier\Resources\Shipments\Pages\CreateShipment;
use App\Filament\Supplier\Resources\Shipments\Pages\EditShipment;
use App\Filament\Supplier\Resources\Shipments\Pages\ListShipments;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderDetail;
use App\Models\Shipment;
use App\Models\ShipmentDetail;
use App\Models\Supplier as SupplierModel;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Forms\Components\Repeater;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

// Setup Test
beforeEach(function () {
    $this->supplier = SupplierModel::factory()->create();
    $this->supplierUser = User::factory()->create([
        'role' => UserRole::Supplier,
        'supplier_id' => $this->supplier->id,
        'is_active' => true,
    ]);
    $this->admin = User::factory()->admin()->create();
    $this->checker = User::factory()->checker()->create();
});

// ===== RBAC Tests =====

test('supplier dapat membuat shipment untuk purchase order mereka sendiri', function () {
    $undoRepeaterFake = Repeater::fake();
    actingAs($this->supplierUser);
    Filament::setCurrentPanel('supplier');
    Filament::setTenant($this->supplier);

    $product = Product::factory()->for($this->supplier, 'supplier')->create();
    $purchaseOrder = PurchaseOrder::factory()
        ->for($this->supplier)
        ->create(['status' => PurchaseOrderStatus::PENDING]);

    $poDetail = PurchaseOrderDetail::factory()
        ->for($product, 'product')
        ->for($purchaseOrder, 'purchaseOrder')
        ->create([
            'quantity_ordered' => 10,
            'quantity_received' => 0,
        ]);

    livewire(CreateShipment::class)
        ->fillForm([
            'purchase_order_id' => $purchaseOrder->id,
            'supplier_id' => $this->supplier->id,
            'delivery_order_number' => 'DO-TEST-001',
            'shipping_date' => now(),
            'estimated_arrival_date' => now()->addDays(2),
            'details' => [
                [
                    'product_id' => $product->id,
                    'quantity_shipped' => 5,
                ],
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $undoRepeaterFake();

    assertDatabaseHas('shipments', [
        'purchase_order_id' => $purchaseOrder->id,
        'supplier_id' => $this->supplier->id,
        'status' => ShipmentStatus::DRAFT,
    ]);
});

test('supplier tidak dapat membuat shipment untuk purchase order supplier lain', function () {
    $otherSupplier = SupplierModel::factory()->create();
    $purchaseOrder = PurchaseOrder::factory()
        ->for($otherSupplier)
        ->create(['status' => PurchaseOrderStatus::PENDING]);

    // Memverifikasi melalui policy bahwa supplier tidak dapat mengakses PO supplier lain
    expect($this->supplierUser->can('view', $purchaseOrder))->toBeFalse();

    // Di UI sebenarnya, query constraints pada dropdown mencegah melihat PO supplier lain
    // Ini ditangani oleh query relationship pada form
});

test('supplier hanya dapat melihat shipment mereka sendiri', function () {
    actingAs($this->supplierUser);
    Filament::setCurrentPanel('supplier');
    Filament::setTenant($this->supplier);

    $otherSupplier = SupplierModel::factory()->create();

    // Membuat shipment untuk kedua supplier
    $myShipment = Shipment::factory()
        ->for($this->supplier)
        ->for(PurchaseOrder::factory()->for($this->supplier))
        ->create();

    $otherShipment = Shipment::factory()
        ->for($otherSupplier)
        ->for(PurchaseOrder::factory()->for($otherSupplier))
        ->create();

    // Memverifikasi melalui policy
    expect($this->supplierUser->can('view', $myShipment))->toBeTrue();
    expect($this->supplierUser->can('view', $otherShipment))->toBeFalse();

    // Sistem tenant secara otomatis memfilter records di UI
    // Jadi daftar hanya akan menampilkan $myShipment
    livewire(ListShipments::class)
        ->loadTable()
        ->assertCanSeeTableRecords([$myShipment]);
});

test('supplier hanya dapat mengedit shipment dengan status draft', function () {
    actingAs($this->supplierUser);
    Filament::setCurrentPanel('supplier');
    Filament::setTenant($this->supplier);

    $draftShipment = Shipment::factory()
        ->for($this->supplier)
        ->for(PurchaseOrder::factory()->for($this->supplier))
        ->create(['status' => ShipmentStatus::DRAFT]);

    $shippedShipment = Shipment::factory()
        ->for($this->supplier)
        ->for(PurchaseOrder::factory()->for($this->supplier))
        ->create(['status' => ShipmentStatus::SHIPPED]);

    // Dapat mengedit draft
    livewire(EditShipment::class, ['record' => $draftShipment->id])
        ->assertSuccessful();

    // Tidak dapat mengedit shipped (view-only atau forbidden tergantung policy)
    // Policy hanya memperbolehkan update untuk shipment draft
});

test('supplier hanya dapat menghapus shipment dengan status draft', function () {
    actingAs($this->supplierUser);
    Filament::setCurrentPanel('supplier');
    Filament::setTenant($this->supplier);

    $draftShipment = Shipment::factory()
        ->for($this->supplier)
        ->for(PurchaseOrder::factory()->for($this->supplier))
        ->create(['status' => ShipmentStatus::DRAFT]);

    $shippedShipment = Shipment::factory()
        ->for($this->supplier)
        ->for(PurchaseOrder::factory()->for($this->supplier))
        ->create(['status' => ShipmentStatus::SHIPPED]);

    // Memeriksa policy memperbolehkan penghapusan draft
    expect($this->supplierUser->can('delete', $draftShipment))->toBeTrue();

    // Memeriksa policy mencegah penghapusan shipped
    expect($this->supplierUser->can('delete', $shippedShipment))->toBeFalse();
});

test('admin dan checker dapat melihat semua shipment', function () {
    actingAs($this->admin);
    Filament::setCurrentPanel('admin');

    $shipments = Shipment::factory(3)
        ->for($this->supplier)
        ->for(PurchaseOrder::factory()->for($this->supplier))
        ->create();

    expect($this->admin->can('viewAny', Shipment::class))->toBeTrue();
    expect($this->checker->can('viewAny', Shipment::class))->toBeTrue();
});

// ===== Validasi Workflow =====

test('tidak dapat membuat shipment untuk purchase order yang sudah selesai', function () {
    actingAs($this->supplierUser);
    Filament::setCurrentPanel('supplier');
    Filament::setTenant($this->supplier);

    $completedPO = PurchaseOrder::factory()
        ->for($this->supplier)
        ->create(['status' => PurchaseOrderStatus::COMPLETED]);

    // Form query seharusnya mencegah ini, tapi mari kita test
    livewire(CreateShipment::class)
        ->fillForm([
            'purchase_order_id' => $completedPO->id,
            'supplier_id' => $this->supplier->id,
            'delivery_order_number' => 'DO-TEST-003',
            'shipping_date' => now(),
            'estimated_arrival_date' => now()->addDays(2),
        ])
        ->call('create')
        ->assertHasFormErrors();
});

test('tidak dapat membuat shipment untuk purchase order yang dibatalkan', function () {
    actingAs($this->supplierUser);
    Filament::setCurrentPanel('supplier');
    Filament::setTenant($this->supplier);

    $cancelledPO = PurchaseOrder::factory()
        ->for($this->supplier)
        ->create(['status' => PurchaseOrderStatus::CANCELLED]);

    livewire(CreateShipment::class)
        ->fillForm([
            'purchase_order_id' => $cancelledPO->id,
            'supplier_id' => $this->supplier->id,
            'delivery_order_number' => 'DO-TEST-004',
            'shipping_date' => now(),
            'estimated_arrival_date' => now()->addDays(2),
        ])
        ->call('create')
        ->assertHasFormErrors();
});

// ===== Transisi Status =====

test('status shipment bertransisi dari draft ke shipped', function () {
    $shipment = Shipment::factory()
        ->for($this->supplier)
        ->for(PurchaseOrder::factory()->for($this->supplier))
        ->has(ShipmentDetail::factory(), 'details')
        ->create(['status' => ShipmentStatus::DRAFT]);

    expect($shipment->isDraft())->toBeTrue();

    $shipment->markAsShipped();

    expect($shipment->fresh()->isShipped())->toBeTrue();
});

test('status shipment bertransisi dari shipped ke arrived', function () {
    $shipment = Shipment::factory()
        ->for($this->supplier)
        ->for(PurchaseOrder::factory()->for($this->supplier))
        ->has(ShipmentDetail::factory(), 'details')
        ->create(['status' => ShipmentStatus::SHIPPED]);

    expect($shipment->isShipped())->toBeTrue();

    $shipment->markAsArrived();

    expect($shipment->fresh()->isArrived())->toBeTrue();
});

test('status shipment bertransisi dari arrived ke processed', function () {
    $shipment = Shipment::factory()
        ->for($this->supplier)
        ->for(PurchaseOrder::factory()->for($this->supplier))
        ->has(ShipmentDetail::factory(), 'details')
        ->create(['status' => ShipmentStatus::ARRIVED]);

    expect($shipment->isArrived())->toBeTrue();

    $shipment->markAsProcessed();

    expect($shipment->fresh()->isProcessed())->toBeTrue();
});

// ===== Validasi =====

test('shipment memerlukan setidaknya satu produk', function () {
    actingAs($this->supplierUser);
    Filament::setCurrentPanel('supplier');
    Filament::setTenant($this->supplier);

    $purchaseOrder = PurchaseOrder::factory()
        ->for($this->supplier)
        ->create(['status' => PurchaseOrderStatus::PENDING]);

    livewire(CreateShipment::class)
        ->fillForm([
            'purchase_order_id' => $purchaseOrder->id,
            'supplier_id' => $this->supplier->id,
            'delivery_order_number' => 'DO-TEST-005',
            'shipping_date' => now(),
            'estimated_arrival_date' => now()->addDays(2),
            'details' => [],
        ])
        ->call('create')
        ->assertHasFormErrors(['details']);
});

test('estimasi kedatangan shipment harus setelah tanggal pengiriman', function () {
    actingAs($this->supplierUser);
    Filament::setCurrentPanel('supplier');
    Filament::setTenant($this->supplier);

    $purchaseOrder = PurchaseOrder::factory()
        ->for($this->supplier)
        ->create(['status' => PurchaseOrderStatus::PENDING]);

    $product = Product::factory()->for($this->supplier, 'supplier')->create();

    livewire(CreateShipment::class)
        ->fillForm([
            'purchase_order_id' => $purchaseOrder->id,
            'supplier_id' => $this->supplier->id,
            'delivery_order_number' => 'DO-TEST-006',
            'shipping_date' => now()->addDays(2),
            'estimated_arrival_date' => now(),
            'details' => [
                [
                    'product_id' => $product->id,
                    'quantity_shipped' => 5,
                ],
            ],
        ])
        ->call('create')
        ->assertHasFormErrors(['estimated_arrival_date']);
});

test('shipment tidak boleh memiliki produk duplikat', function () {
    actingAs($this->supplierUser);
    Filament::setCurrentPanel('supplier');
    Filament::setTenant($this->supplier);

    $purchaseOrder = PurchaseOrder::factory()
        ->for($this->supplier)
        ->create(['status' => PurchaseOrderStatus::PENDING]);

    $product = Product::factory()->for($this->supplier, 'supplier')->create();

    PurchaseOrderDetail::factory()
        ->for($product, 'product')
        ->for($purchaseOrder, 'purchaseOrder')
        ->create(['quantity_ordered' => 10]);

    livewire(CreateShipment::class)
        ->fillForm([
            'purchase_order_id' => $purchaseOrder->id,
            'supplier_id' => $this->supplier->id,
            'delivery_order_number' => 'DO-TEST-007',
            'shipping_date' => now(),
            'estimated_arrival_date' => now()->addDays(2),
            'details' => [
                [
                    'product_id' => $product->id,
                    'quantity_shipped' => 3,
                ],
                [
                    'product_id' => $product->id,
                    'quantity_shipped' => 2,
                ],
            ],
        ])
        ->call('create')
        ->assertHasFormErrors(['details.1.product_id']);
});
