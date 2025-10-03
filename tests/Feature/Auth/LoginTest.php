<?php

// tests/Feature/Filament/Auth/LoginTest.php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->supplier = User::factory()->create([
        'username' => 'supplier1',
        'password' => Hash::make('password123'),
        'role' => 'Supplier',
        'status' => 'Active',
    ]);

    $this->admin = User::factory()->create([
        'username' => 'admin1',
        'password' => Hash::make('admin123'),
        'role' => 'Admin',
        'status' => 'Active',
    ]);
});

describe('Authentication - Login', function () {
    it('dapat login dengan username dan password yang valid', function () {
        livewire(\Filament\Http\Livewire\Auth\Login::class)
            ->fillForm([
                'username' => 'supplier1',
                'password' => 'password123',
            ])
            ->call('authenticate')
            ->assertRedirect(route('filament.admin.pages.dashboard'));

        expect(auth()->check())->toBeTrue();
    });

    it('gagal login dengan password yang salah', function () {
        livewire(\Filament\Http\Livewire\Auth\Login::class)
            ->fillForm([
                'username' => 'supplier1',
                'password' => 'wrongpassword',
            ])
            ->call('authenticate')
            ->assertHasErrors(['username']);

        expect(auth()->check())->toBeFalse();
    });

    it('menolak akses jika role tidak sesuai', function () {
        actingAs($this->supplier);

        // Supplier mencoba akses halaman admin
        livewire(\App\Filament\Resources\UserResource\Pages\ListUsers::class)
            ->assertForbidden();
    });

    it('password tersimpan dengan hashing yang benar', function () {
        $user = User::factory()->create([
            'password' => Hash::make('testpassword'),
        ]);

        expect(Hash::check('testpassword', $user->password))->toBeTrue()
            ->and(Hash::check('wrongpassword', $user->password))->toBeFalse();
    });

    it('user inactive tidak dapat login', function () {
        $inactiveUser = User::factory()->create([
            'username' => 'inactive_user',
            'password' => Hash::make('password123'),
            'status' => 'Inactive',
        ]);

        livewire(\Filament\Http\Livewire\Auth\Login::class)
            ->fillForm([
                'username' => 'inactive_user',
                'password' => 'password123',
            ])
            ->call('authenticate')
            ->assertHasErrors(['username']);
    });
});
