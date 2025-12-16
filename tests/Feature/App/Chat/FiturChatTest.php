<?php

use App\Enums\UserRole;
// use App\Filament\App\Pages\Chats;
use App\Models\Supplier;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Livewire\SimpleUserMenu;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function () {
    // Membuat test user
    $this->user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
        'role' => UserRole::Checker,
    ]);

    // Membuat supplier dan mengasosiasikan dengan user
    $this->supplier = User::factory()->supplier()->for(Supplier::factory(), 'supplier')->create(['is_active' => true]);

    Filament::setCurrentPanel('app');
});

describe('Fitur Chat', function () {
    test('dapat menampilkan halaman chat', function () {
        // Autentikasi sebagai test user
        actingAs($this->user);

        // Akses halaman chat
        $response = $this->get('/chats');
        $response->assertStatus(200);
    });

    test('memerlukan autentikasi untuk mengakses halaman chat', function () {
        // Test tanpa autentikasi
        $response = $this->get('/chats');
        $response->assertRedirect('/login');
    });

    test('dapat memuat komponen wirechat', function () {
        actingAs($this->user);

        // Test bahwa Livewire component dapat dimuat
        livewire('wirechat')
            ->assertSuccessful();
    });

    test('menampilkan halaman chat di user menu', function () {
        actingAs($this->user);

        livewire(SimpleUserMenu::class)
            ->assertSee('Messages');
    });

    test('dapat mengakses chat panel provider', function () {
        $this->actingAs($this->user);

        // Test bahwa chat panel dapat diakses
        $response = $this->get('/chats');
        $response->assertSuccessful();
    });
});
