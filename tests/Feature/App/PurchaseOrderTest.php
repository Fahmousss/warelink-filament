<?php

use App\Filament\Resources\PurchaseOrders\Pages\ListPurchaseOrders;
use App\Models\PurchaseOrder;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;

use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->checker = User::factory()->checker()->create();
});

test('checker can view purchase orders but cannot modify them', function () {
    $this->actingAs($this->checker);

    Filament::setCurrentPanel(Filament::getPanel('app'));

    $purchaseOrders = PurchaseOrder::factory(3)->create();

    // Can view list
    livewire(ListPurchaseOrders::class)
        ->assertCanSeeTableRecords($purchaseOrders)
        ->assertActionHidden(TestAction::make('edit')->table($purchaseOrders))
        ->assertActionHidden(TestAction::make('delete')->table($purchaseOrders));

    // Cannot access edit page
    $this->get(route('filament.admin.resources.purchase-orders.edit', [
        'record' => $purchaseOrders->first(),
    ]))
        ->assertForbidden();
});

test('checker cannot create purchase orders', function () {
    $this->actingAs($this->checker);

    Filament::setCurrentPanel(Filament::getPanel('app'));

    $this->get(route('filament.admin.resources.purchase-orders.create'))
        ->assertForbidden();
});
