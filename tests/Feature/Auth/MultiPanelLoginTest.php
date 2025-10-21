<?php

use App\Enums\UserRole;
use App\Models\Supplier;
use App\Models\User;
use Filament\Auth\Pages\Login;
use Filament\Facades\Filament;

// use Filament\Pages\Auth\Login;

use function Pest\Livewire\livewire;

beforeEach(function () {
    // Create supplier for multi-tenant testing
    $this->supplier = Supplier::factory()->create([
        'name' => 'Test Supplier',
        'code' => 'SUP00001',
        'is_active' => true,
    ]);

    // Create users for different panels
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

describe('Admin Panel Login', function () {
    beforeEach(fn () => Filament::setCurrentPanel('admin'));

    it('can render admin login page', function () {
        $this->get('/admin/login')
            ->assertSuccessful()
            ->assertSeeLivewire(Login::class);
    });

    it('can login to admin panel with valid credentials', function () {
        livewire(Login::class)
            ->fillForm([
                'email' => 'admin@example.com',
                'password' => 'password',
            ])
            ->call('authenticate')
            ->assertRedirect('/admin');

        $this->assertAuthenticated();
    });

    it('cannot login to admin panel with invalid credentials', function () {
        livewire(Login::class)
            ->fillForm([
                'email' => 'admin@example.com',
                'password' => 'wrong-password',
            ])
            ->call('authenticate')
            ->assertHasFormErrors(['email']);

        $this->assertGuest();
    });

    it('cannot access admin panel with non-admin role', function () {
        $this->actingAs($this->appUser);

        $this->get('/admin')
            ->assertForbidden();
    });
});

describe('App Panel Login', function () {
    beforeEach(fn () => Filament::setCurrentPanel('app'));

    it('can render app login page', function () {
        $this->get('/login')
            ->assertSuccessful()
            ->assertSeeLivewire(Login::class);
    });

    it('can login to app panel with valid credentials', function () {
        livewire(Login::class)
            ->fillForm([
                'email' => 'checker@example.com',
                'password' => 'password',
            ])
            ->call('authenticate')
            ->assertRedirect('/');

        $this->assertAuthenticated();
    });

    it('inactive users cannot login to app panel', function () {
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

describe('Supplier Panel Login', function () {
    beforeEach(fn () => Filament::setCurrentPanel('supplier'));

    it('can render supplier login page', function () {
        $this->get('/supplier/login')
            ->assertSuccessful()
            ->assertSeeLivewire(Login::class);
    });

    it('can login to supplier panel with valid credentials and tenant', function () {
        livewire(Login::class)
            ->fillForm([
                'email' => 'supplier@example.com',
                'password' => 'password',
            ])
            ->call('authenticate')
            ->assertRedirect("/supplier/{$this->supplier->code}");

        Filament::setTenant($this->supplier);
        $this->assertAuthenticated();

        // Verify tenant is set correctly
        expect(Filament::getTenant()->getOriginal('code'))
            ->toBe($this->supplier->code);
    });

    it('supplier cannot access another suppliers tenant', function () {
        $anotherSupplier = Supplier::factory()->create();

        $this->actingAs($this->supplierUser);

        Filament::setTenant($anotherSupplier);
        $this->get("/supplier/{$anotherSupplier->code}")
            ->assertNotFound();
    });

    it('non-supplier users cannot access supplier panel', function () {
        $this->actingAs($this->adminUser);

        $this->get("/supplier/{$this->supplier->code}")
            ->assertForbidden();
    });

    it('inactive supplier cannot login to supplier panel', function () {
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

describe('Cross Panel Access Control', function () {
    it('supplier user cannot access admin panel', function () {
        $this->actingAs($this->supplierUser);

        $this->get('/admin')->assertForbidden();
    });

    it('supplier user cannot access app panel', function () {
        $this->actingAs($this->supplierUser);

        $this->get('/')->assertForbidden();
    });

    it('admin can access all panels', function () {
        $this->actingAs($this->adminUser);

        $this->get('/admin')->assertSuccessful();
        $this->get('/')->assertSuccessful();
        // Admin can access supplier panel through impersonation, not direct access
        $this->get("/supplier/{$this->supplier->code}")->assertForbidden();
    });

    it('checker can only access app panel', function () {
        $this->actingAs($this->appUser);

        $this->get('/')->assertSuccessful();
        $this->get('/admin')->assertForbidden();
        $this->get("/supplier/{$this->supplier->code}")->assertForbidden();
    });
});
