<?php

use App\Enums\UserRole;
use App\Filament\Resources\PurchaseOrders\Pages\CreatePurchaseOrder;
use App\Filament\Resources\PurchaseOrders\Pages\EditPurchaseOrder;
use App\Filament\Supplier\Resources\Shipments\Pages\ListShipments;
use App\Models\PurchaseOrder;
use App\Models\Shipment;
use App\Models\Supplier;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;

use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->checker = User::factory()->create([
        'role' => UserRole::Checker,
    ]);
});

test('checker can view shipment but cannot modify purchase order', function () {
    $this->actingAs($this->checker);

    Filament::setCurrentPanel(Filament::getPanel('app'));

    $purchaseOrders = PurchaseOrder::factory()
        ->for(Supplier::factory(), 'supplier')->create();

    $shipments = Shipment::factory()->for($purchaseOrders, 'purchaseOrder')->for(Supplier::factory(), 'supplier')->create();
    // Can view list
    livewire(ListShipments::class)
        ->assertCanSeeTableRecords([$shipments])
        ->assertActionHidden(TestAction::make('edit')->table($purchaseOrders))
        ->assertActionHidden(TestAction::make('delete')->table($purchaseOrders));

    // Cannot access edit page
    livewire(EditPurchaseOrder::class, [
        'record' => $purchaseOrders->id,
    ])
        ->assertForbidden();
});

test('checker cannot create purchase orders', function () {
    $this->actingAs($this->checker);

    Filament::setCurrentPanel(Filament::getPanel('app'));

    livewire(CreatePurchaseOrder::class)
        ->assertForbidden();
});
