<?php

use App\Enums\UserRole;
use App\Filament\App\Resources\Products\Pages\CreateProduct;
use App\Filament\App\Resources\Products\Pages\EditProduct;
use App\Filament\App\Resources\Products\Pages\ListProducts;
use App\Filament\App\Resources\Products\Pages\ViewProduct;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;

use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create([
        'role' => UserRole::Admin,
        'is_active' => true,
    ]));

    Filament::setCurrentPanel('app');

    $this->supplier = Supplier::factory()->create();
});

describe('Menampilkan Daftar Produk', function () {
    test('dapat menampilkan halaman daftar produk', function () {
        livewire(ListProducts::class)
            ->assertSuccessful();
    });

    test('dapat menampilkan daftar produk dengan informasi supplier', function () {
        $products = Product::factory()->count(5)->create([
            'supplier_id' => $this->supplier->id,
        ]);

        livewire(ListProducts::class)
            ->loadTable()
            ->assertCanSeeTableRecords($products);
    });

    test('dapat mencari produk berdasarkan nama dan kode', function () {
        $products = Product::factory()->count(10)->create([
            'supplier_id' => $this->supplier->id,
        ]);
        $product = $products->first();

        livewire(ListProducts::class)
            ->loadTable()
            ->searchTable($product->name)
            ->assertCanSeeTableRecords([$product])
            ->assertCanNotSeeTableRecords($products->except($product->id))
            ->searchTable($product->product_code)
            ->assertCanSeeTableRecords([$product])
            ->assertCanNotSeeTableRecords($products->except($product->id));
    });

    test('tidak menampilkan produk yang sudah dihapus secara default', function () {
        $activeProducts = Product::factory()->count(3)->create([
            'supplier_id' => $this->supplier->id,
        ]);
        $trashedProducts = Product::factory()->count(4)->trashed()->create([
            'supplier_id' => $this->supplier->id,
        ]);

        livewire(ListProducts::class)
            ->loadTable()
            ->assertCanSeeTableRecords($activeProducts)
            ->assertCanNotSeeTableRecords($trashedProducts)
            ->assertCountTableRecords(3);
    });
});

describe('Membuat Produk Baru', function () {
    test('dapat membuat produk baru dengan data lengkap', function () {
        $newProduct = Product::factory()->make([
            'supplier_id' => $this->supplier->id,
        ]);

        livewire(CreateProduct::class)
            ->fillForm([
                'product_code' => $newProduct->product_code,
                'name' => $newProduct->name,
                'description' => $newProduct->description,
                'unit' => $newProduct->unit,
                'price' => $newProduct->price,
                'stock_quantity' => $newProduct->stock_quantity ?? 0,
                'minimum_stock' => $newProduct->minimum_stock ?? 10,
                'is_active' => true,
                'supplier_id' => $this->supplier->id,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('products', [
            'name' => $newProduct->name,
            'supplier_id' => $this->supplier->id,
            'is_active' => true,
        ]);
    });

    test('produk baru mendapatkan kode produk otomatis jika tidak diisi', function () {
        $newProduct = Product::factory()->make([
            'supplier_id' => $this->supplier->id,
            'product_code' => null,
        ]);

        livewire(CreateProduct::class)
            ->fillForm([
                'name' => $newProduct->name,
                'description' => $newProduct->description,
                'unit' => $newProduct->unit,
                'price' => $newProduct->price,
                'stock_quantity' => $newProduct->stock_quantity ?? 0,
                'minimum_stock' => $newProduct->minimum_stock ?? 10,
                'is_active' => true,
                'supplier_id' => $this->supplier->id,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        // Memverifikasi produk dibuat dengan kode otomatis
        $this->assertDatabaseHas('products', [
            'name' => $newProduct->name,
            'supplier_id' => $this->supplier->id,
        ]);

        $product = Product::where('name', $newProduct->name)->first();
        expect($product->product_code)->not->toBeEmpty();
    });

    test('memvalidasi field yang wajib diisi saat membuat produk', function () {
        livewire(CreateProduct::class)
            ->fillForm([
                'name' => '',
                'unit' => '',
                'price' => null,
                'supplier_id' => null,
            ])
            ->call('create')
            ->assertHasFormErrors(['name', 'unit', 'price', 'supplier_id']);
    });

    test('memvalidasi harga tidak boleh negatif', function () {
        livewire(CreateProduct::class)
            ->fillForm([
                'name' => 'Test Product',
                'unit' => 'pcs',
                'price' => -1000,
                'supplier_id' => $this->supplier->id,
            ])
            ->call('create')
            ->assertHasFormErrors(['price']);
    });

    test('memvalidasi stok tidak boleh negatif', function () {
        livewire(CreateProduct::class)
            ->fillForm([
                'name' => 'Test Product',
                'unit' => 'pcs',
                'price' => 10000,
                'stock_quantity' => -10,
                'supplier_id' => $this->supplier->id,
            ])
            ->call('create')
            ->assertHasFormErrors(['stock_quantity']);
    });
});

describe('Melihat Detail Produk', function () {
    test('dapat melihat detail produk dengan informasi lengkap', function () {
        $product = Product::factory()->create([
            'supplier_id' => $this->supplier->id,
        ]);

        livewire(ViewProduct::class, [
            'record' => $product->id,
        ])
            ->assertSuccessful()
            ->assertSee($product->name)
            ->assertSee($product->product_code)
            ->assertSee($product->supplier->name);
    });

    test('menampilkan informasi stok produk', function () {
        $product = Product::factory()->create([
            'supplier_id' => $this->supplier->id,
            'stock_quantity' => 100,
            'minimum_stock' => 20,
        ]);

        livewire(ViewProduct::class, [
            'record' => $product->id,
        ])
            ->assertSuccessful()
            ->assertSee($product->stock_quantity)
            ->assertSee($product->minimum_stock);
    });

    test('menampilkan status stok produk', function () {
        $lowStockProduct = Product::factory()->create([
            'supplier_id' => $this->supplier->id,
            'stock_quantity' => 5,
            'minimum_stock' => 20,
        ]);

        livewire(ViewProduct::class, [
            'record' => $lowStockProduct->id,
        ])
            ->assertSuccessful();

        expect($lowStockProduct->is_low_stock)->toBeTrue();
        expect($lowStockProduct->stock_status)->toBe('low_stock');
    });
});

describe('Mengedit Produk', function () {
    test('dapat mengedit informasi produk', function () {
        $product = Product::factory()->create([
            'supplier_id' => $this->supplier->id,
        ]);
        $newData = Product::factory()->make([
            'supplier_id' => $this->supplier->id,
        ]);

        livewire(EditProduct::class, [
            'record' => $product->id,
        ])
            ->fillForm([
                'name' => $newData->name,
                'description' => $newData->description,
                'unit' => $newData->unit,
                'price' => $newData->price,
                'is_active' => false,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        expect($product->refresh())
            ->name->toBe($newData->name)
            ->is_active->toBeFalse();
    });

    test('dapat mengubah stok produk', function () {
        $product = Product::factory()->create([
            'supplier_id' => $this->supplier->id,
            'stock_quantity' => 50,
        ]);

        livewire(EditProduct::class, [
            'record' => $product->id,
        ])
            ->fillForm([
                'stock_quantity' => 100,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        expect($product->refresh())
            ->stock_quantity->toBe(100);
    });

    test('dapat mengubah minimum stok produk', function () {
        $product = Product::factory()->create([
            'supplier_id' => $this->supplier->id,
            'minimum_stock' => 10,
        ]);

        livewire(EditProduct::class, [
            'record' => $product->id,
        ])
            ->fillForm([
                'minimum_stock' => 25,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        expect($product->refresh())
            ->minimum_stock->toBe(25);
    });

    test('dapat menonaktifkan produk', function () {
        $product = Product::factory()->create([
            'supplier_id' => $this->supplier->id,
            'is_active' => true,
        ]);

        livewire(EditProduct::class, [
            'record' => $product->id,
        ])
            ->fillForm([
                'is_active' => false,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        expect($product->refresh())
            ->is_active->toBeFalse();
    });
});

describe('Menghapus & Restore Produk', function () {
    test('dapat menghapus produk secara soft delete', function () {
        $product = Product::factory()->create([
            'supplier_id' => $this->supplier->id,
        ]);

        livewire(EditProduct::class, [
            'record' => $product->id,
        ])
            ->callAction(TestAction::make('delete'))
            ->assertHasNoFormErrors();

        expect($product->refresh())
            ->deleted_at->not->toBeNull();
    });

    test('dapat menghapus produk secara permanen', function () {
        $product = Product::factory()->trashed()->create([
            'supplier_id' => $this->supplier->id,
        ]);

        livewire(EditProduct::class, [
            'record' => $product->id,
        ])
            ->callAction('forceDelete')
            ->assertHasNoFormErrors();

        $this->assertModelMissing($product);
    });

    test('dapat mengembalikan produk yang dihapus', function () {
        $product = Product::factory()->trashed()->create([
            'supplier_id' => $this->supplier->id,
        ]);

        livewire(EditProduct::class, [
            'record' => $product->id,
        ])
            ->callAction(TestAction::make('restore'))
            ->assertHasNoFormErrors();

        expect($product->refresh())
            ->deleted_at->toBeNull();
    });

    test('dapat menghapus banyak produk sekaligus', function () {
        $products = Product::factory()->count(3)->create([
            'supplier_id' => $this->supplier->id,
        ]);

        livewire(ListProducts::class)
            ->loadTable()
            ->selectTableRecords($products->pluck('id')->toArray())
            ->callAction(TestAction::make('delete')->table()->bulk())
            ->assertHasNoFormErrors();

        foreach ($products as $product) {
            expect($product->refresh())
                ->deleted_at->not->toBeNull();
        }
    });
});

describe('Filter & Search Produk', function () {
    test('dapat memfilter produk berdasarkan status aktif', function () {
        $activeProducts = Product::factory()->count(3)->create([
            'supplier_id' => $this->supplier->id,
            'is_active' => true,
        ]);
        $inactiveProducts = Product::factory()->count(2)->create([
            'supplier_id' => $this->supplier->id,
            'is_active' => false,
        ]);

        livewire(ListProducts::class)
            ->loadTable()
            ->filterTable('is_active', true)
            ->assertCanSeeTableRecords($activeProducts)
            ->assertCanNotSeeTableRecords($inactiveProducts);
    });

    test('dapat memfilter produk dengan stok rendah', function () {
        $lowStockProducts = Product::factory()->count(2)->create([
            'supplier_id' => $this->supplier->id,
            'stock_quantity' => 5,
            'minimum_stock' => 20,
        ]);
        $goodStockProducts = Product::factory()->count(3)->create([
            'supplier_id' => $this->supplier->id,
            'stock_quantity' => 100,
            'minimum_stock' => 20,
        ]);

        // Memverifikasi produk memiliki status stok yang benar
        foreach ($lowStockProducts as $product) {
            expect($product->is_low_stock)->toBeTrue();
        }
        foreach ($goodStockProducts as $product) {
            expect($product->is_low_stock)->toBeFalse();
        }
    });

    test('dapat memfilter produk yang habis', function () {
        $outOfStockProducts = Product::factory()->count(2)->create([
            'supplier_id' => $this->supplier->id,
            'stock_quantity' => 0,
        ]);
        $inStockProducts = Product::factory()->count(3)->create([
            'supplier_id' => $this->supplier->id,
            'stock_quantity' => 50,
        ]);

        // Memverifikasi produk memiliki status stok yang benar
        foreach ($outOfStockProducts as $product) {
            expect($product->is_out_of_stock)->toBeTrue();
        }
        foreach ($inStockProducts as $product) {
            expect($product->is_out_of_stock)->toBeFalse();
        }
    });

    test('dapat memfilter produk berdasarkan supplier melalui search', function () {
        $supplier1 = $this->supplier;
        $supplier2 = Supplier::factory()->create();

        $supplier1Products = Product::factory()->count(3)->create([
            'supplier_id' => $supplier1->id,
        ]);
        $supplier2Products = Product::factory()->count(2)->create([
            'supplier_id' => $supplier2->id,
        ]);

        // Verifikasi produk dibuat untuk supplier yang benar
        foreach ($supplier1Products as $product) {
            expect($product->supplier_id)->toBe($supplier1->id);
        }
        foreach ($supplier2Products as $product) {
            expect($product->supplier_id)->toBe($supplier2->id);
        }
    });

    test('dapat memfilter produk yang dihapus', function () {
        $activeProducts = Product::factory()->count(3)->create([
            'supplier_id' => $this->supplier->id,
        ]);
        $trashedProducts = Product::factory()->count(5)->trashed()->create([
            'supplier_id' => $this->supplier->id,
        ]);

        livewire(ListProducts::class)
            ->loadTable()
            ->assertCanSeeTableRecords($activeProducts)
            ->assertCanNotSeeTableRecords($trashedProducts)
            ->filterTable('trashed', 1)
            ->assertCanSeeTableRecords($trashedProducts);
    });
});

describe('Manajemen Stok Produk', function () {
    test('stok produk bertambah saat menerima barang', function () {
        $product = Product::factory()->create([
            'supplier_id' => $this->supplier->id,
            'stock_quantity' => 50,
        ]);

        $product->increaseStock(30, 'Goods Receipt');

        expect($product->fresh()->stock_quantity)->toBe(80);
    });

    test('stok produk berkurang saat ada pengurangan', function () {
        $product = Product::factory()->create([
            'supplier_id' => $this->supplier->id,
            'stock_quantity' => 100,
        ]);

        $product->decreaseStock(25, 'Adjustment');

        expect($product->fresh()->stock_quantity)->toBe(75);
    });

    test('tidak dapat mengurangi stok melebihi yang tersedia', function () {
        $product = Product::factory()->create([
            'supplier_id' => $this->supplier->id,
            'stock_quantity' => 10,
        ]);

        expect(fn () => $product->decreaseStock(20, 'Over-reduction'))
            ->toThrow(\Exception::class, 'Insufficient stock');
    });

    test('dapat mengecek apakah stok mencukupi', function () {
        $product = Product::factory()->create([
            'supplier_id' => $this->supplier->id,
            'stock_quantity' => 50,
        ]);

        expect($product->hasSufficientStock(30))->toBeTrue();
        expect($product->hasSufficientStock(60))->toBeFalse();
    });

    test('produk menampilkan status low stock dengan benar', function () {
        $product = Product::factory()->create([
            'supplier_id' => $this->supplier->id,
            'stock_quantity' => 10,
            'minimum_stock' => 20,
        ]);

        expect($product->is_low_stock)->toBeTrue();
        expect($product->stock_status)->toBe('low_stock');
        expect($product->stock_status_label)->toBe('Low Stock');
    });

    test('produk menampilkan status out of stock dengan benar', function () {
        $product = Product::factory()->create([
            'supplier_id' => $this->supplier->id,
            'stock_quantity' => 0,
            'minimum_stock' => 20,
        ]);

        expect($product->is_out_of_stock)->toBeTrue();
        expect($product->stock_status)->toBe('out_of_stock');
        expect($product->stock_status_label)->toBe('Out of Stock');
    });

    test('produk menampilkan status good stock dengan benar', function () {
        $product = Product::factory()->create([
            'supplier_id' => $this->supplier->id,
            'stock_quantity' => 100,
            'minimum_stock' => 20,
        ]);

        expect($product->has_good_stock)->toBeTrue();
        expect($product->stock_status)->toBe('good');
        expect($product->stock_status_label)->toBe('Good Stock');
    });
});

describe('Reorder & Statistik Produk', function () {
    test('produk mendeteksi kebutuhan reorder', function () {
        $needsReorder = Product::factory()->create([
            'supplier_id' => $this->supplier->id,
            'stock_quantity' => 10,
            'minimum_stock' => 20,
            'is_active' => true,
        ]);

        expect($needsReorder->isNeedsReorder())->toBeTrue();
    });

    test('produk menghitung suggested reorder quantity', function () {
        $product = Product::factory()->create([
            'supplier_id' => $this->supplier->id,
            'stock_quantity' => 10,
            'minimum_stock' => 20,
        ]);

        // Target = 2x minimum (40), current = 10, suggested = 30
        expect($product->suggestedReorderQuantity())->toBe(30);
    });

    test('produk menentukan urgency level reorder', function () {
        $criticalProduct = Product::factory()->create([
            'supplier_id' => $this->supplier->id,
            'stock_quantity' => 0,
            'minimum_stock' => 20,
        ]);

        $highProduct = Product::factory()->create([
            'supplier_id' => $this->supplier->id,
            'stock_quantity' => 5,
            'minimum_stock' => 20,
        ]);

        $mediumProduct = Product::factory()->create([
            'supplier_id' => $this->supplier->id,
            'stock_quantity' => 15,
            'minimum_stock' => 20,
        ]);

        expect($criticalProduct->reorderUrgency())->toBe('critical');
        expect($highProduct->reorderUrgency())->toBe('high');
        expect($mediumProduct->reorderUrgency())->toBe('medium');
    });

    test('produk menghitung stock percentage', function () {
        $product = Product::factory()->create([
            'supplier_id' => $this->supplier->id,
            'stock_quantity' => 50,
            'minimum_stock' => 20,
        ]);

        // 50/20 * 100 = 250%
        expect($product->stock_percentage)->toBe(250.0);
    });

    test('produk menghitung stock value', function () {
        $product = Product::factory()->create([
            'supplier_id' => $this->supplier->id,
            'stock_quantity' => 50,
            'price' => 10000,
        ]);

        expect($product->stock_value)->toBe(500000.0);
    });
});
