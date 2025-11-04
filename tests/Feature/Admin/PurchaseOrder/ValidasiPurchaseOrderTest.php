<?php

use App\Enums\UserRole;
use App\Filament\Resources\PurchaseOrders\Pages\CreatePurchaseOrder;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;

use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => UserRole::Admin]);
    $this->actingAs($this->admin);
});

describe('Validasi Purchase Order', function () {
    test('tidak dapat menyimpan purchase order dengan form kosong', function () {
        livewire(CreatePurchaseOrder::class)
            ->fillForm([])
            ->call('create')
            ->assertHasFormErrors([
                'supplier_id' => 'required',
                'order_date' => 'required',
                'expected_delivery_date' => 'required',
            ]);

    });

    test('tidak dapat menyimpan purchase order dengan kuantitas yang tidak valid', function () {
        $supplier = Supplier::factory()->create();
        $product = Product::factory()->create([
            'supplier_id' => $supplier->id,
        ]);

        livewire(CreatePurchaseOrder::class)
            ->fillForm([
                'supplier_id' => $supplier->id,
                'order_date' => now(),
                'details' => [
                    [
                        'product_id' => $product->id,
                        'quantity_ordered' => 0,
                        'price' => 10000,
                    ],
                ],
            ])
            ->call('create')
            ->assertHasFormErrors(['details.0.quantity_ordered']);

        livewire(CreatePurchaseOrder::class)
            ->fillForm([
                'supplier_id' => $supplier->id,
                'order_date' => now(),
                'details' => [
                    [
                        'product_id' => $product->id,
                        'quantity_ordered' => -1,
                        'price' => 10000,
                    ],
                ],
            ])
            ->call('create')
            ->assertHasFormErrors(['details.0.quantity_ordered']);
    });

    test('tidak dapat menambahkan produk duplikat dalam purchase order', function () {
        $supplier = Supplier::factory()->create();
        $product = Product::factory()->create([
            'supplier_id' => $supplier->id,
        ]);

        livewire(CreatePurchaseOrder::class)
            ->fillForm([
                'supplier_id' => $supplier->id,
                'order_date' => now(),
                'details' => [
                    [
                        'product_id' => $product->id,
                        'quantity_ordered' => 5,
                        'price' => 10000,
                    ],
                    [
                        'product_id' => $product->id,
                        'quantity_ordered' => 3,
                        'price' => 10000,
                    ],
                ],
            ])
            ->call('create')
            ->assertHasFormErrors(['details.1.product_id']);
    });
});
