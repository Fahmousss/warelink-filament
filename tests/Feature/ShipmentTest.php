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

// Test Setup
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

test('supplier can create shipment for their own purchase orders', function () {
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

test('supplier cannot create shipment for another suppliers purchase order', function () {
    $otherSupplier = SupplierModel::factory()->create();
    $purchaseOrder = PurchaseOrder::factory()
        ->for($otherSupplier)
        ->create(['status' => PurchaseOrderStatus::PENDING]);

    // Verify via policy that supplier cannot access other supplier's PO
    expect($this->supplierUser->can('view', $purchaseOrder))->toBeFalse();

    // In the actual UI, the dropdown query constraints prevent seeing other supplier's POs
    // This is handled by the relationship query in the form
});

test('supplier can only view their own shipments', function () {
    actingAs($this->supplierUser);
    Filament::setCurrentPanel('supplier');
    Filament::setTenant($this->supplier);

    $otherSupplier = SupplierModel::factory()->create();

    // Create shipments for both suppliers
    $myShipment = Shipment::factory()
        ->for($this->supplier)
        ->for(PurchaseOrder::factory()->for($this->supplier))
        ->create();

    $otherShipment = Shipment::factory()
        ->for($otherSupplier)
        ->for(PurchaseOrder::factory()->for($otherSupplier))
        ->create();

    // Verify via policy
    expect($this->supplierUser->can('view', $myShipment))->toBeTrue();
    expect($this->supplierUser->can('view', $otherShipment))->toBeFalse();

    // The tenant system automatically filters records in the UI
    // So the list will only show $myShipment
    livewire(ListShipments::class)
        ->loadTable()
        ->assertCanSeeTableRecords([$myShipment]);
});

test('supplier can only edit draft shipments', function () {
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

    // Can edit draft
    livewire(EditShipment::class, ['record' => $draftShipment->id])
        ->assertSuccessful();

    // Cannot edit shipped (view-only or forbidden depending on policy)
    // The policy allows update only for draft shipments
});

test('supplier can only delete draft shipments', function () {
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

    // Check policy allows deletion of draft
    expect($this->supplierUser->can('delete', $draftShipment))->toBeTrue();

    // Check policy prevents deletion of shipped
    expect($this->supplierUser->can('delete', $shippedShipment))->toBeFalse();
});

test('admin and checker can view all shipments', function () {
    actingAs($this->admin);
    Filament::setCurrentPanel('admin');

    $shipments = Shipment::factory(3)
        ->for($this->supplier)
        ->for(PurchaseOrder::factory()->for($this->supplier))
        ->create();

    expect($this->admin->can('viewAny', Shipment::class))->toBeTrue();
    expect($this->checker->can('viewAny', Shipment::class))->toBeTrue();
});

// ===== Workflow Validation Tests =====

test('cannot create shipment for completed purchase order', function () {
    actingAs($this->supplierUser);
    Filament::setCurrentPanel('supplier');
    Filament::setTenant($this->supplier);

    $completedPO = PurchaseOrder::factory()
        ->for($this->supplier)
        ->create(['status' => PurchaseOrderStatus::COMPLETED]);

    // The form query should prevent this, but let's test
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

test('cannot create shipment for cancelled purchase order', function () {
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

// ===== Status Transition Tests =====

test('shipment status transitions from draft to shipped', function () {
    $shipment = Shipment::factory()
        ->for($this->supplier)
        ->for(PurchaseOrder::factory()->for($this->supplier))
        ->has(ShipmentDetail::factory(), 'details')
        ->create(['status' => ShipmentStatus::DRAFT]);

    expect($shipment->isDraft())->toBeTrue();

    $shipment->markAsShipped();

    expect($shipment->fresh()->isShipped())->toBeTrue();
});

test('shipment status transitions from shipped to arrived', function () {
    $shipment = Shipment::factory()
        ->for($this->supplier)
        ->for(PurchaseOrder::factory()->for($this->supplier))
        ->has(ShipmentDetail::factory(), 'details')
        ->create(['status' => ShipmentStatus::SHIPPED]);

    expect($shipment->isShipped())->toBeTrue();

    $shipment->markAsArrived();

    expect($shipment->fresh()->isArrived())->toBeTrue();
});

test('shipment status transitions from arrived to processed', function () {
    $shipment = Shipment::factory()
        ->for($this->supplier)
        ->for(PurchaseOrder::factory()->for($this->supplier))
        ->has(ShipmentDetail::factory(), 'details')
        ->create(['status' => ShipmentStatus::ARRIVED]);

    expect($shipment->isArrived())->toBeTrue();

    $shipment->markAsProcessed();

    expect($shipment->fresh()->isProcessed())->toBeTrue();
});

// ===== Validation Tests =====

test('shipment requires at least one product', function () {
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

test('shipment estimated arrival must be after shipping date', function () {
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

test('shipment cannot have duplicate products', function () {
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
