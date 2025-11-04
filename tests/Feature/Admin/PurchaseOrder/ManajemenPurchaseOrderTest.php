<?php

use App\Enums\PurchaseOrderStatus;
use App\Enums\UserRole;
use App\Filament\Resources\PurchaseOrders\Pages\CreatePurchaseOrder;
use App\Filament\Resources\PurchaseOrders\Pages\EditPurchaseOrder;
use App\Filament\Resources\PurchaseOrders\Pages\ListPurchaseOrders;
use App\Filament\Resources\PurchaseOrders\Pages\ViewPurchaseOrder;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderDetail;
use App\Models\Supplier;
use App\Models\User;
use Filament\Forms\Components\Repeater;

use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->admin = User::factory()->create([
        'role' => UserRole::Admin,
        'is_active' => true,
    ]);
    $this->actingAs($this->admin);
});

describe('Manajemen Purchase Order', function () {
    test('admin dapat melihat daftar purchase order', function () {
        $supplier = Supplier::factory()->create();
        $purchaseOrders = PurchaseOrder::factory()
            ->for(
                $supplier,
            )
            ->create();

        $purchaseOrdersDetail = PurchaseOrderDetail::factory()
            ->for(
                Product::factory()
                    ->for(
                        $supplier,
                        'supplier'
                    ),
            )
            ->for(
                $purchaseOrders,
                'purchaseOrder'
            )
            ->create();

        livewire(ListPurchaseOrders::class)
            ->loadTable()
            ->assertCanSeeTableRecords([$purchaseOrdersDetail->purchaseOrder])
            ->assertOk()
            ->assertSuccessful();
    });

    test('admin dapat membuat purchase order baru', function () {
        $undoRepeaterFake = Repeater::fake();
        $supplier = Supplier::factory()->create();
        $products = Product::factory(2)->create([
            'supplier_id' => $supplier->id,
        ]);

        livewire(CreatePurchaseOrder::class)
            ->fillForm([
                'supplier_id' => $supplier->id,
                'order_date' => now(),
                'expected_delivery_date' => now()->addDays(7),
                'notes' => 'Test notes',
                'details' => [
                    [
                        'product_id' => $products[0]->id,
                        'quantity_ordered' => 5,
                        'price' => 10000,
                    ],
                    [
                        'product_id' => $products[1]->id,
                        'quantity_ordered' => 3,
                        'price' => 15000,
                    ],
                ],
            ])
            ->assertSchemaStateSet([
                'details' => [
                    [
                        'product_id' => $products[0]->id,
                        'quantity_ordered' => 5,
                        'price' => 10000,
                        'subtotal' => 50000,
                        'notes' => null,
                        'product_code' => $products[0]->product_code,
                        'unit' => $products[0]->unit,
                    ],
                    [
                        'product_id' => $products[1]->id,
                        'quantity_ordered' => 3,
                        'price' => 15000,
                        'subtotal' => 45000,
                        'product_code' => $products[1]->product_code,
                        'unit' => $products[1]->unit,
                    ],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $undoRepeaterFake();

        $this->assertDatabaseHas('purchase_orders', [
            'supplier_id' => $supplier->id,
            'status' => 'Pending',
        ]);
    });

    test('admin dapat mengedit purchase order yang berstatus pending', function () {
        $undoRepeaterFake = Repeater::fake();
        $supplier = Supplier::factory()->create();
        $purchaseOrder = PurchaseOrder::factory()
            ->for(
                $supplier,
            )
            ->create();

        PurchaseOrderDetail::factory()
            ->for(
                Product::factory()
                    ->for(
                        $supplier,
                        'supplier'
                    ),
                'product'
            )
            ->for(
                $purchaseOrder,
                'purchaseOrder'
            )
            ->create();

        livewire(EditPurchaseOrder::class, [
            'record' => $purchaseOrder->id,
        ])
            ->fillForm([
                'notes' => 'Updated notes',
                'expected_delivery_date' => now()->addDays(7),

            ])

            ->call('save')
            ->assertHasNoFormErrors();

        $undoRepeaterFake();
        $purchaseOrder->refresh();

        expect($purchaseOrder->notes)->toBe('Updated notes');
    });

    test('admin dapat membatalkan purchase order', function () {
        $supplier = Supplier::factory()->create();
        $purchaseOrder = PurchaseOrder::factory()
            ->for(
                $supplier,
            )
            ->create();

        PurchaseOrderDetail::factory()
            ->for(
                Product::factory()
                    ->for(
                        $supplier,
                        'supplier'
                    ),
                'product'
            )
            ->for(
                $purchaseOrder,
                'purchaseOrder'
            )
            ->create();

        livewire(ViewPurchaseOrder::class, [
            'record' => $purchaseOrder->id,
        ])
            ->callAction('cancel')
            ->assertHasNoFormErrors();

        $purchaseOrder->refresh();

        expect($purchaseOrder->isCancelled())->toBeTrue();
    });

    test('purchase order yang sudah selesai tidak dapat diubah', function () {
        $undoRepeaterFake = Repeater::fake();
        $supplier = Supplier::factory()->create();
        $purchaseOrder = PurchaseOrder::factory()
            ->for(
                $supplier,
            )
            ->create([
                'status' => PurchaseOrderStatus::COMPLETED,
                'notes' => 'Original notes',
            ]);

        PurchaseOrderDetail::factory()
            ->for(
                Product::factory()
                    ->for(
                        $supplier,
                        'supplier'
                    ),
                'product'
            )
            ->for(
                $purchaseOrder,
                'purchaseOrder'
            )
            ->create();

        // Memverifikasi bahwa policy mencegah update
        expect($this->admin->can('update', $purchaseOrder))->toBeFalse();

        $undoRepeaterFake();
    });
});
