<?php

use App\Filament\Resources\Suppliers\Pages\CreateSupplier;
use App\Filament\Resources\Suppliers\Pages\EditSupplier;
use App\Filament\Resources\Suppliers\Pages\ListSuppliers;
use App\Filament\Resources\Suppliers\Pages\ViewSupplier;
use App\Models\Supplier;
use App\Models\User;
use Filament\Actions\Testing\TestAction;

use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create([
        'is_active' => true,
    ]));
});

describe('Menampilkan Daftar Supplier', function () {
    test('dapat menampilkan halaman daftar supplier', function () {
        livewire(ListSuppliers::class)
            ->assertSuccessful();
    });

    test('dapat menampilkan daftar supplier', function () {
        $suppliers = Supplier::factory()->count(5)->create();
        $trashedSupplier = Supplier::factory()->trashed()->count(4)->create();

        livewire(ListSuppliers::class)
            ->assertCanSeeTableRecords($suppliers)
            ->assertCanNotSeeTableRecords($trashedSupplier)
            ->assertCountTableRecords(5);
    });

    test('dapat mencari supplier', function () {
        $suppliers = Supplier::factory()->count(10)->create();
        $supplier = $suppliers->first();

        livewire(ListSuppliers::class)
            ->searchTable($supplier->name)
            ->assertCanSeeTableRecords([$supplier])
            ->assertCanNotSeeTableRecords($suppliers->except($supplier->id))
            ->searchTable($supplier->email)
            ->assertCanSeeTableRecords([$supplier])
            ->assertCanNotSeeTableRecords($suppliers->except($supplier->id));
    });

    test('dapat memfilter supplier yang dihapus', function () {
        $supplier = Supplier::factory()->count(3)->create();
        $trashedSupplier = Supplier::factory()->trashed()->count(5)->create();

        livewire(ListSuppliers::class)
            ->assertCanSeeTableRecords($supplier)
            ->assertCanNotSeeTableRecords($trashedSupplier)
            ->filterTable('trashed', 1)
            ->assertCanSeeTableRecords($trashedSupplier);
    });
});

describe('Membuat Supplier', function () {
    test('dapat membuat supplier baru', function () {
        $newSupplier = Supplier::factory()->make();

        livewire(CreateSupplier::class)
            ->fillForm([
                'name' => $newSupplier->name,
                'email' => $newSupplier->email,
                'phone' => $newSupplier->phone,
                'address' => $newSupplier->address,
                'city' => $newSupplier->city,
                'country' => $newSupplier->country,
                'tax_number' => $newSupplier->tax_number,
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('suppliers', [
            'name' => $newSupplier->name,
            'email' => $newSupplier->email,
            'is_active' => true,
        ]);
    });

    test('memvalidasi field yang wajib diisi saat membuat supplier', function () {
        livewire(CreateSupplier::class)
            ->fillForm([
                'name' => '',
                'is_active' => null,
            ])
            ->call('create')
            ->assertHasFormErrors(['name', 'is_active']);
    });
});

describe('Melihat & Mengedit Supplier', function () {
    test('dapat melihat detail supplier', function () {
        $supplier = Supplier::factory()->create();

        livewire(ViewSupplier::class, [
            'record' => $supplier->id,
        ])
            ->assertSuccessful()
            ->assertSee($supplier->name)
            ->assertSee($supplier->email);
    });

    test('dapat mengedit supplier', function () {
        $supplier = Supplier::factory()->create();
        $newData = Supplier::factory()->make();

        livewire(EditSupplier::class, [
            'record' => $supplier->id,
        ])
            ->fillForm([
                'name' => $newData->name,
                'email' => $newData->email,
                'phone' => $newData->phone,
                'address' => $newData->address,
                'city' => $newData->city,
                'country' => $newData->country,
                'tax_number' => $newData->tax_number,
                'is_active' => false,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        expect($supplier->refresh())
            ->name->toBe($newData->name)
            ->email->toBe($newData->email)
            ->is_active->toBeFalse();
    });
});

describe('Menghapus & Restore Supplier', function () {
    test('dapat menghapus supplier secara soft delete', function () {
        $supplier = Supplier::factory()->create();

        livewire(EditSupplier::class, [
            'record' => $supplier->id,
        ])
            ->callAction(TestAction::make('delete'))
            ->assertHasNoFormErrors();

        expect($supplier->refresh())
            ->deleted_at->not->toBeNull();
    });

    test('dapat menghapus supplier secara permanen', function () {
        $supplier = Supplier::factory()->trashed()->create();

        livewire(EditSupplier::class, [
            'record' => $supplier->id, ])
            ->callAction('forceDelete')
            ->assertHasNoFormErrors();

        $this->assertModelMissing($supplier);
    });

    test('dapat mengembalikan supplier yang dihapus', function () {
        $supplier = Supplier::factory()->trashed()->create();

        livewire(EditSupplier::class, [
            'record' => $supplier->id,
        ])
            ->callAction(TestAction::make('restore'))
            ->assertHasNoFormErrors();

        expect($supplier->refresh())
            ->deleted_at->toBeNull();
    });

    test('dapat menghapus banyak supplier sekaligus', function () {
        $suppliers = Supplier::factory()->count(3)->create();

        livewire(ListSuppliers::class)
            ->selectTableRecords($suppliers->pluck('id')->toArray())
            ->callAction(TestAction::make('delete')->table()->bulk())
            ->assertHasNoFormErrors();

        foreach ($suppliers as $supplier) {
            expect($supplier->refresh())
                ->deleted_at->not->toBeNull();
        }
    });
});
