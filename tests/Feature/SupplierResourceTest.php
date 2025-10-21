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

it('can render supplier list page', function () {
    livewire(ListSuppliers::class)
        ->assertSuccessful();
});

it('can list suppliers', function () {
    $suppliers = Supplier::factory()->count(5)->create();
    $trashedSupplier = Supplier::factory()->trashed()->count(4)->create();

    livewire(ListSuppliers::class)
        ->assertCanSeeTableRecords($suppliers)
        ->assertCanNotSeeTableRecords($trashedSupplier)
        ->assertCountTableRecords(5);
});

it('can search suppliers', function () {
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

it('can create supplier', function () {
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

it('validates required fields when creating supplier', function () {
    livewire(CreateSupplier::class)
        ->fillForm([
            'name' => '',
            'is_active' => null,
        ])
        ->call('create')
        ->assertHasFormErrors(['name', 'is_active']);
});

it('can view supplier', function () {
    $supplier = Supplier::factory()->create();

    livewire(ViewSupplier::class, [
        'record' => $supplier->id,
    ])
        ->assertSuccessful()
        ->assertSee($supplier->name)
        ->assertSee($supplier->email);
});

it('can edit supplier', function () {
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

it('can soft delete supplier', function () {
    $supplier = Supplier::factory()->create();

    livewire(EditSupplier::class, [
        'record' => $supplier->id,
    ])
        ->callAction(TestAction::make('delete'))
        ->assertHasNoFormErrors();

    expect($supplier->refresh())
        ->deleted_at->not->toBeNull();
});

it('can force delete supplier', function () {
    $supplier = Supplier::factory()->trashed()->create();

    livewire(EditSupplier::class, [
        'record' => $supplier->id, ])
        ->callAction('forceDelete')
        ->assertHasNoFormErrors();

    $this->assertModelMissing($supplier);
});

it('can restore soft deleted supplier', function () {
    $supplier = Supplier::factory()->trashed()->create();

    livewire(EditSupplier::class, [
        'record' => $supplier->id,
    ])
        ->callAction(TestAction::make('restore'))
        ->assertHasNoFormErrors();

    expect($supplier->refresh())
        ->deleted_at->toBeNull();
});

it('can bulk delete suppliers', function () {
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

it('can filter trashed suppliers', function () {
    $supplier = Supplier::factory()->count(3)->create();
    $trashedSupplier = Supplier::factory()->trashed()->count(5)->create();

    livewire(ListSuppliers::class)
        ->assertCanSeeTableRecords($supplier)
        ->assertCanNotSeeTableRecords($trashedSupplier)
        ->filterTable('trashed', 1)
        ->assertCanSeeTableRecords($trashedSupplier);
});
