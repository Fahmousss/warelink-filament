<?php

use App\Enums\UserRole;
use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\User;
use Filament\Actions\Testing\TestAction;

use function Pest\Livewire\livewire;

beforeEach(function () {
    // Create and login as admin user
    $this->actingAs(User::factory()->create([
        'role' => UserRole::Admin,
    ]));
});

it('can render user list page', function () {
    livewire(ListUsers::class)
        ->assertSuccessful();
});

it('can list users', function () {
    $users = User::factory()->count(5)->create();

    livewire(ListUsers::class)
        ->loadTable()
        ->assertOk()
        ->assertCanSeeTableRecords($users);
});

it('can search users by name and email', function () {
    $users = User::factory()->count(10)->create();
    $searchUser = $users->first();

    livewire(ListUsers::class)
        ->loadTable()
        ->searchTable($searchUser->name)
        ->assertCanSeeTableRecords([$searchUser])
        ->assertCanNotSeeTableRecords($users->except($searchUser->id))
        ->searchTable($searchUser->email)
        ->assertCanSeeTableRecords([$searchUser])
        ->assertCanNotSeeTableRecords($users->except($searchUser->id));
});

it('can filter users by role', function () {
    $adminUser = User::factory()->create(['role' => UserRole::Admin]);
    $supplierUser = User::factory()->create(['role' => UserRole::Supplier]);

    livewire(ListUsers::class)
        ->loadTable()
        ->filterTable('role', UserRole::Supplier->value)
        ->assertCanSeeTableRecords([$supplierUser])
        ->assertCanNotSeeTableRecords([$adminUser]);
});

it('can create new user', function () {
    $newUser = User::factory()->make();

    livewire(CreateUser::class)
        ->fillForm([
            'name' => $newUser->name,
            'email' => $newUser->email,
            'password' => 'password',
            'role' => UserRole::Checker,
            'is_active' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('users', [
        'name' => $newUser->name,
        'email' => $newUser->email,
        'role' => UserRole::Checker,
        'is_active' => true,
    ]);
});

it('validates required fields when creating user', function () {
    livewire(CreateUser::class)
        ->fillForm([
            'name' => '',
            'email' => '',
            'password' => '',
            'role' => null,
        ])
        ->call('create')
        ->assertHasFormErrors(['name', 'email', 'password', 'role']);
});

it('can edit existing user', function () {
    $user = User::factory()->create();
    $newData = User::factory()->make();

    livewire(EditUser::class, [
        'record' => $user->id,
    ])
        ->fillForm([
            'name' => $newData->name,
            'email' => $newData->email,
            'role' => UserRole::Accounting,
            'is_active' => false,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $user->refresh();

    expect($user)
        ->name->toBe($newData->name)
        ->email->toBe($newData->email)
        ->role->toBe(UserRole::Accounting)
        ->is_active->toBeFalse();
});

it('can toggle user active status', function () {
    $user = User::factory()->create(['is_active' => true]);

    livewire(ListUsers::class)
        ->callAction(TestAction::make('toggle_status')->table($user))
        ->assertNotified();

    expect($user->refresh())
        ->is_active->toBeFalse();
});

it('can delete inactive user', function () {
    $user = User::factory()->create([
        'is_active' => false,
        'role' => UserRole::Checker,
    ]);

    livewire(ListUsers::class)
        ->callAction(TestAction::make('delete')->table($user))
        ->assertNotified();

    $this->assertModelMissing($user);
});

it('can bulk delete inactive users', function () {
    $users = User::factory()->count(3)->create([
        'is_active' => false,
        'role' => UserRole::Checker,
    ]);

    livewire(ListUsers::class)
        ->selectTableRecords($users->pluck('id')->toArray())
        ->callAction(TestAction::make('delete')->table()->bulk())
        ->assertNotified();

    foreach ($users as $user) {
        $this->assertModelMissing($user);
    }
});

it('can bulk toggle user status', function () {
    $users = User::factory()->count(3)->create(['is_active' => true]);

    livewire(ListUsers::class)
        ->selectTableRecords($users->pluck('id')->toArray())
        ->callAction(TestAction::make('deactivate')->table()->bulk())
        ->assertNotified();

    foreach ($users as $user) {
        expect($user->refresh())
            ->is_active->toBeFalse();
    }
});
