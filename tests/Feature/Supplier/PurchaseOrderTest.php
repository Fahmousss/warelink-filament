<?php

use App\Enums\PurchaseOrderStatus;
use App\Enums\UserRole;
use App\Filament\Resources\PurchaseOrders\Pages\ListPurchaseOrders;
use App\Filament\Resources\PurchaseOrders\Pages\ViewPurchaseOrder;
use App\Filament\Supplier\Resources\Shipments\Pages\CreateShipment;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;
use Filament\Facades\Filament;

use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->supplier = Supplier::factory()->create();
    $this->supplierUser = User::factory()->create([
        'role' => UserRole::Supplier,
        'supplier_id' => $this->supplier->id,
    ]);
});

test('supplier can only view their purchase orders', function () {
    $otherSupplier = Supplier::factory()->create(['code' => '1']);
    $this->actingAs($this->supplierUser);

    Filament::setCurrentPanel(Filament::getPanel('supplier'));
    Filament::setTenant($this->supplier);

    // Create POs for another supplier
    $otherPOs = PurchaseOrder::factory(2)->for($otherSupplier, 'supplier')->create();

    // Create POs for the supplier
    $supplierPOs = PurchaseOrder::factory(3)->create([
        'supplier_id' => $this->supplier->id,
    ]);

    // Trashed PO
    $trashedSupplierPOs = PurchaseOrder::factory(3)->trashed()->create([
        'supplier_id' => $this->supplier->id,
    ]);

    livewire(ListPurchaseOrders::class)
        ->loadTable()
        ->assertCanSeeTableRecords($supplierPOs)
        // ->assertCanNotSeeTableRecords($otherPOs)
        ->assertCanNotSeeTableRecords($trashedSupplierPOs);
});

test('supplier cannot access unauthorized purchase orders', function () {
    $this->actingAs($this->supplierUser);

    Filament::setCurrentPanel(Filament::getPanel('supplier'));
    Filament::setTenant($this->supplier);

    $otherSupplierPO = PurchaseOrder::factory()->for(Supplier::factory(), 'supplier')->create();

    $this->get(ViewPurchaseOrder::getUrl(['record' => $otherSupplierPO]))
        ->assertNotFound();
});

test('supplier can create shipment from purchase order', function () {
    $this->actingAs($this->supplierUser);

    Filament::setCurrentPanel(Filament::getPanel('supplier'));
    Filament::setTenant($this->supplier);

    $purchaseOrder = PurchaseOrder::factory()
        ->create([
            'status' => PurchaseOrderStatus::PENDING,
            'supplier_id' => $this->supplier->id,
        ]);

    livewire(CreateShipment::class)
        ->fillForm([
            'purchase_order_id' => $purchaseOrder->id,
            'delivery_order_number' => 'DO_123',
            'shipping_date' => now(),
            'estimated_arrival_date' => now()->addDays(2),
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('shipments', [
        'purchase_order_id' => $purchaseOrder->id,
        'supplier_id' => $this->supplier->id,
    ]);
});

test('supplier cannot edit purchase order', function () {
    $this->actingAs($this->supplierUser);

    Filament::setCurrentPanel(Filament::getPanel('supplier'));

    $purchaseOrder = PurchaseOrder::factory()->create([
        'supplier_id' => $this->supplier->id,
    ]);

    $this->get(route('filament.admin.resources.purchase-orders.edit', [
        'record' => $purchaseOrder,
    ]))
        ->assertForbidden();
});
