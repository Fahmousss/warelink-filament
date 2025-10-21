<?php

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
    $otherPOs = PurchaseOrder::factory(2)->create([
        'supplier_id' => $otherSupplier->id,
    ]);

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
        ->assertCanNotSeeTableRecords($otherPOs)
        ->assertCanNotSeeTableRecords($trashedSupplierPOs);
});

test('supplier cannot access unauthorized purchase orders', function () {
    $this->actingAs($this->supplierUser);

    Filament::setCurrentPanel(Filament::getPanel('supplier'));

    $otherSupplierPO = PurchaseOrder::factory()->create();

    $this->get(ViewPurchaseOrder::getUrl(['record' => $otherSupplierPO]))
        ->assertForbidden();
});

test('supplier can create shipment from purchase order', function () {
    $this->actingAs($this->supplierUser);

    Filament::setCurrentPanel(Filament::getPanel('supplier'));

    $purchaseOrder = PurchaseOrder::factory()
        ->pending()
        ->create([
            'supplier_id' => $this->supplier->id,
        ]);

    livewire(CreateShipment::class, [
        'purchase_order_id' => $purchaseOrder->id,
    ])
        ->fillForm([
            'delivery_date' => now()->addDays(2),
            'vehicle_number' => 'B 1234 ABC',
            'driver_name' => 'John Doe',
            'driver_phone' => '08123456789',
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
