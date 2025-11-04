<?php

use App\Enums\UserRole;
use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\User;
use Filament\Actions\Testing\TestAction;

use function Pest\Livewire\livewire;

beforeEach(function () {
    // Membuat dan login sebagai admin user
    $this->actingAs(User::factory()->create([
        'role' => UserRole::Admin,
    ]));
});

describe('Menampilkan Daftar User', function () {
    test('dapat menampilkan halaman daftar user', function () {
        livewire(ListUsers::class)
            ->assertSuccessful();
    });

    test('dapat menampilkan daftar user', function () {
        $users = User::factory()->count(5)->create();

        livewire(ListUsers::class)
            ->loadTable()
            ->assertOk()
            ->assertCanSeeTableRecords($users);
    });

    test('dapat mencari user berdasarkan nama dan email', function () {
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

    test('dapat memfilter user berdasarkan role', function () {
        $adminUser = User::factory()->create(['role' => UserRole::Admin]);
        $supplierUser = User::factory()->create(['role' => UserRole::Supplier]);

        livewire(ListUsers::class)
            ->loadTable()
            ->filterTable('role', UserRole::Supplier->value)
            ->assertCanSeeTableRecords([$supplierUser])
            ->assertCanNotSeeTableRecords([$adminUser]);
    });
});

describe('Membuat User', function () {
    test('dapat membuat user baru', function () {
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

    test('memvalidasi field yang wajib diisi saat membuat user', function () {
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
});

describe('Mengedit User', function () {
    test('dapat mengedit user yang sudah ada', function () {
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

    test('dapat mengubah status aktif user', function () {
        $user = User::factory()->create(['is_active' => true]);

        livewire(ListUsers::class)
            ->callAction(TestAction::make('toggle_status')->table($user))
            ->assertNotified();

        expect($user->refresh())
            ->is_active->toBeFalse();
    });
});

describe('Menghapus User', function () {
    test('dapat menghapus user yang tidak aktif', function () {
        $user = User::factory()->create([
            'is_active' => false,
            'role' => UserRole::Checker,
        ]);

        livewire(ListUsers::class)
            ->callAction(TestAction::make('delete')->table($user))
            ->assertNotified();

        $this->assertModelMissing($user);
    });

    test('dapat menghapus banyak user yang tidak aktif sekaligus', function () {
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

    test('dapat mengubah status banyak user sekaligus', function () {
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
});
