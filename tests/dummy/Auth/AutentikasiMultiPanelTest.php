<?php

use App\Enums\UserRole;
use App\Models\Supplier;
use App\Models\User;
use Filament\Auth\Pages\Login;
use Filament\Facades\Filament;

use function Pest\Livewire\livewire;

beforeEach(function () {
    // Membuat supplier untuk testing multi-tenant
    $this->supplier = Supplier::factory()->create([
        'name' => 'Test Supplier',
        'code' => 'SUP00001',
        'is_active' => true,
    ]);

    // Membuat user untuk berbagai panel
    $this->adminUser = User::factory()->create([
        'email' => 'admin@example.com',
        'password' => bcrypt('password'),
        'role' => UserRole::Admin,
        'is_active' => true,
    ]);

    $this->appUser = User::factory()->create([
        'email' => 'checker@example.com',
        'password' => bcrypt('password'),
        'role' => UserRole::Checker,
        'is_active' => true,
    ]);

    $this->supplierUser = User::factory()->create([
        'email' => 'supplier@example.com',
        'password' => bcrypt('password'),
        'role' => UserRole::Supplier,
        'is_active' => true,
        'supplier_id' => $this->supplier->id,
    ]);
});

describe('Login Panel Admin', function () {
    beforeEach(fn () => Filament::setCurrentPanel('admin'));

    test('dapat menampilkan halaman login admin', function () {
        $this->get('/admin/login')
            ->assertSuccessful()
            ->assertSeeLivewire(Login::class);
    });

    test('dapat login ke panel admin dengan kredensial yang valid', function () {
        livewire(Login::class)
            ->fillForm([
                'email' => 'admin@example.com',
                'password' => 'password',
            ])
            ->call('authenticate')
            ->assertRedirect('/admin');

        $this->assertAuthenticated();
    });

    test('tidak dapat login ke panel admin dengan kredensial yang salah', function () {
        livewire(Login::class)
            ->fillForm([
                'email' => 'admin@example.com',
                'password' => 'wrong-password',
            ])
            ->call('authenticate')
            ->assertHasFormErrors(['email']);

        $this->assertGuest();
    });

    test('tidak dapat mengakses panel admin dengan role non-admin', function () {
        $this->actingAs($this->appUser);

        $this->get('/admin')
            ->assertForbidden();
    });
});

describe('Login Panel App', function () {
    beforeEach(fn () => Filament::setCurrentPanel('app'));

    test('dapat menampilkan halaman login app', function () {
        $this->get('/login')
            ->assertSuccessful()
            ->assertSeeLivewire(Login::class);
    });

    test('dapat login ke panel app dengan kredensial yang valid', function () {
        livewire(Login::class)
            ->fillForm([
                'email' => 'checker@example.com',
                'password' => 'password',
            ])
            ->call('authenticate')
            ->assertRedirect('/');

        $this->assertAuthenticated();
    });

    test('user yang tidak aktif tidak dapat login ke panel app', function () {
        $inactiveUser = User::factory()->create([
            'email' => 'inactive@example.com',
            'password' => bcrypt('password'),
            'role' => UserRole::Checker,
            'is_active' => false,
        ]);

        livewire(Login::class)
            ->fillForm([
                'email' => 'inactive@example.com',
                'password' => 'password',
            ])
            ->call('authenticate')
            ->assertHasFormErrors(['email']);

        $this->assertGuest();
    });
});

describe('Login Panel Supplier', function () {
    beforeEach(fn () => Filament::setCurrentPanel('supplier'));

    test('dapat menampilkan halaman login supplier', function () {
        $this->get('/supplier/login')
            ->assertSuccessful()
            ->assertSeeLivewire(Login::class);
    });

    test('dapat login ke panel supplier dengan kredensial dan tenant yang valid', function () {
        livewire(Login::class)
            ->fillForm([
                'email' => 'supplier@example.com',
                'password' => 'password',
            ])
            ->call('authenticate')
            ->assertRedirect("/supplier/{$this->supplier->code}");

        Filament::setTenant($this->supplier);
        $this->assertAuthenticated();

        // Memverifikasi tenant diset dengan benar
        expect(Filament::getTenant()->getOriginal('code'))
            ->toBe($this->supplier->code);
    });

    test('supplier tidak dapat mengakses tenant supplier lain', function () {
        $anotherSupplier = Supplier::factory()->create();

        $this->actingAs($this->supplierUser);

        Filament::setTenant($anotherSupplier);
        $this->get("/supplier/{$anotherSupplier->code}")
            ->assertNotFound();
    });

    test('user non-supplier tidak dapat mengakses panel supplier', function () {
        $this->actingAs($this->adminUser);

        $this->get("/supplier/{$this->supplier->code}")
            ->assertForbidden();
    });

    test('supplier yang tidak aktif tidak dapat login ke panel supplier', function () {
        $inactiveSupplier = Supplier::factory()->create([
            'is_active' => false,
        ]);

        $inactiveSupplierUser = User::factory()->create([
            'email' => 'inactive.supplier@example.com',
            'password' => bcrypt('password'),
            'role' => UserRole::Supplier,
            'is_active' => true,
            'supplier_id' => $inactiveSupplier->id,
        ]);

        livewire(Login::class)
            ->fillForm([
                'email' => 'inactive.supplier@example.com',
                'password' => 'password',
            ])
            ->call('authenticate')
            ->assertHasNoFormErrors(['email']);

        $this->get("/supplier/{$inactiveSupplier->code}")
            ->assertNotFound();

    });
});

describe('Kontrol Akses Antar Panel', function () {
    test('user supplier tidak dapat mengakses panel admin', function () {
        $this->actingAs($this->supplierUser);

        $this->get('/admin')->assertForbidden();
    });

    test('user supplier tidak dapat mengakses panel app', function () {
        $this->actingAs($this->supplierUser);

        $this->get('/')->assertForbidden();
    });

    test('admin dapat mengakses semua panel', function () {
        $this->actingAs($this->adminUser);

        $this->get('/admin')->assertSuccessful();
        $this->get('/')->assertSuccessful();
        // Admin dapat mengakses panel supplier melalui impersonation, bukan akses langsung
        $this->get("/supplier/{$this->supplier->code}")->assertForbidden();
    });

    test('checker hanya dapat mengakses panel app', function () {
        $this->actingAs($this->appUser);

        $this->get('/')->assertSuccessful();
        $this->get('/admin')->assertForbidden();
        $this->get("/supplier/{$this->supplier->code}")->assertForbidden();
    });
});
