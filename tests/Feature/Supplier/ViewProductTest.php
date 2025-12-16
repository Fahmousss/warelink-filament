<?php

use App\Enums\UserRole;
use App\Filament\App\Resources\Products\Pages\ListProducts;
use App\Filament\App\Resources\Products\Pages\ViewProduct;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Filament\Facades\Filament;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->supplier = Supplier::factory()->create();

    $this->supplierUser = User::factory()->create([
        'role' => UserRole::Supplier,
        'supplier_id' => $this->supplier->id,
        'is_active' => true,
    ]);
});

test('supplier hanya dapat melihat daftar produk mereka sendiri', function () {
    $otherSupplier = Supplier::factory()->create();

    // Products for other supplier
    $otherProducts = Product::factory()->count(3)->for($otherSupplier, 'supplier')->create();

    // Products for current supplier
    $supplierProducts = Product::factory()->count(4)->create([
        'supplier_id' => $this->supplier->id,
    ]);

    actingAs($this->supplierUser);

    Filament::setCurrentPanel(Filament::getPanel('supplier'));
    Filament::setTenant($this->supplier);

    livewire(ListProducts::class)
        ->loadTable()
        ->assertCanSeeTableRecords($supplierProducts);
});

test('supplier dapat melihat detail produk milik mereka', function () {
    $product = Product::factory()->create([
        'supplier_id' => $this->supplier->id,
        'stock_quantity' => 25,
        'minimum_stock' => 10,
    ]);

    actingAs($this->supplierUser);

    Filament::setCurrentPanel(Filament::getPanel('supplier'));
    Filament::setTenant($this->supplier);

    livewire(ViewProduct::class, [
        'record' => $product->id,
    ])
        ->assertSuccessful()
        ->assertSee($product->name)
        ->assertSee($product->product_code)
        ->assertSee((string) $product->stock_quantity);
});

test('supplier tidak dapat mengakses produk supplier lain', function () {
    $otherProduct = Product::factory()->for(Supplier::factory(), 'supplier')->create();

    actingAs($this->supplierUser);

    Filament::setCurrentPanel(Filament::getPanel('supplier'));
    Filament::setTenant($this->supplier);

    livewire(ViewProduct::class, ['record' => $otherProduct->id])
        ->assertForbidden();
});
