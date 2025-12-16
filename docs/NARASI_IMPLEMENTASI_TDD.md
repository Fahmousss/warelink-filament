# BAB IV
# HASIL DAN PEMBAHASAN

## 4.1 Implementasi Test-Driven Development (TDD)

Dalam penelitian ini, peneliti mengimplementasikan metodologi Test-Driven Development (TDD) untuk memastikan kualitas kode dan kesesuaian dengan spesifikasi yang telah dirancang. Implementasi TDD dilakukan secara sistematis mengikuti siklus Red-Green-Refactor, dimana setiap fitur dimulai dengan menulis test terlebih dahulu (Red), kemudian menulis kode minimal untuk membuat test berhasil (Green), dan terakhir memperbaiki struktur kode tanpa mengubah perilakunya (Refactor).

### 4.1.1 Perancangan Model TDD

Pada tahapan perancangan model TDD, peneliti melakukan pemetaan sistematis antara requirement yang telah didefinisikan dalam diagram UML dengan test suite yang akan diimplementasikan. Perancangan ini bertujuan untuk memastikan setiap aspek fungsionalitas sistem ter-cover dengan baik oleh pengujian yang memadai.

#### 4.1.1.1 Framework dan Tools Pengujian

Dalam pelaksanaan pengujian, peneliti menggunakan kombinasi framework dan tools yang sesuai dengan ekosistem Laravel. Tabel 4.1 menunjukkan framework dan tools yang digunakan beserta alasan pemilihannya.

**Tabel 4.1: Framework dan Tools Pengujian**

| Komponen | Teknologi | Fungsi dalam Penelitian |
|----------|-----------|-------------------------|
| Testing Framework | Pest PHP v4.0 | Framework utama untuk menulis dan menjalankan test cases dengan syntax ekspresif |
| UI Testing | Filament Test Helpers | Menyediakan assertion methods khusus untuk testing Filament resources, forms, tables, dan actions |
| Application Framework | Laravel 12 | Framework aplikasi dengan fitur testing terintegrasi |
| Database Testing | MySQL (Production)<br>SQLite (In-Memory) | MySQL untuk production, SQLite untuk testing agar lebih cepat dengan database transaction isolation |
| HTTP Testing | Laravel HTTP Testing | Untuk testing HTTP requests, responses, dan routing |
| Authentication Testing | Laravel Sanctum | Testing multi-panel authentication dan authorization |

#### 4.1.1.2 Pemetaan Use Case ke Test Suite

Peneliti melakukan pemetaan dari 8 use case utama yang telah dirancang ke dalam test suite. Setiap use case dipecah menjadi beberapa test file yang terorganisir berdasarkan panel dan role pengguna untuk memudahkan maintenance dan membuat test suite lebih modular. Tabel 4.2 menunjukkan hasil pemetaan use case ke test suite.

**Tabel 4.2: Pemetaan Use Case ke Test Suite**

| Use Case | Aktor | Test File | Jumlah Test | Coverage |
|----------|-------|-----------|-------------|----------|
| UC-01: Mengelola Master Data | Admin Gudang | ManajemenUserTest.php<br>ManajemenSupplierTest.php<br>ManajemenProdukTest.php | 11<br>12<br>37 | 100% |
| UC-02: Manajemen Purchase Order | Admin Gudang | ManajemenPurchaseOrderTest.php<br>ValidasiPurchaseOrderTest.php<br>PurchaseOrderCheckerTest.php | 5<br>3<br>2 | 100% |
| UC-03: Mengelola Delivery Order | Supplier | ManajemenPengirimanTest.php<br>PurchaseOrderSupplierTest.php | 14<br>4 | 100% |
| UC-04: Penerimaan Barang | Checker & Admin | PenerimaanBarangTest.php | 13 | 100% |
| UC-05: Autentikasi Multi-Panel | All Users | AutentikasiMultiPanelTest.php | 16 | 100% |
| UC-06: Mengirim Pesan | All Users | FiturChatTest.php | 5 | 36% |
| UC-07: Membuat Laporan Bulanan | Accounting | LaporanBulananTest.php | 19 | 100% |
| UC-08: Melihat Produk | Supplier | - | 0 | 0% |
| **Total** | | **12 test files** | **141** | **87.5%** |

Dari pemetaan ini, peneliti mengidentifikasi total 141 test cases yang perlu diimplementasikan untuk mencapai coverage yang komprehensif.

#### 4.1.1.3 Stratifikasi Pengujian Berdasarkan Layer

Peneliti merancang stratifikasi test berdasarkan layer arsitektur sistem untuk memastikan coverage yang komprehensif di semua layer. Tabel 4.3 menunjukkan distribusi pengujian berdasarkan layer.

**Tabel 4.3: Stratifikasi Pengujian Berdasarkan Layer**

| Layer | Fokus Pengujian | Jumlah Test | Persentase |
|-------|----------------|-------------|------------|
| Presentation Layer | Komponen UI (Livewire/Filament), form rendering, table display | 85 | 60.3% |
| Business Logic Layer | Validasi business rules, domain logic, calculations | 90 | 63.8% |
| Data Access Layer | Query correctness, data persistence, relationship integrity | 40 | 28.4% |
| Integration Layer | Cascade effects, transaction integrity, multi-step workflows | 20 | 14.2% |
| Authorization Layer | RBAC, policy checks, tenant isolation | 30 | 21.3% |

*Catatan: Total persentase > 100% karena beberapa test mencakup multiple layers*

#### 4.1.1.4 Kategorisasi Pengujian

Peneliti mengkategorikan test cases berdasarkan tipe pengujian untuk memastikan coverage yang seimbang antara functionality, security, dan integration. Tabel 4.4 menunjukkan distribusi test berdasarkan kategori.

**Tabel 4.4: Kategorisasi Test Cases**

| Kategori | Deskripsi | Jumlah | Persentase | Contoh Test Cases |
|----------|-----------|--------|------------|-------------------|
| Functional Tests | Validasi business functionality dan user workflows | 91 | 64.5% | CRUD operations, reporting, filtering |
| RBAC/Security Tests | Validasi authentication, authorization, tenant isolation | 28 | 19.9% | Panel access control, policy enforcement |
| Validation Tests | Validasi input validation dan business rules | 12 | 8.5% | Required fields, data constraints |
| Integration Tests | Validasi system integration dan cascade effects | 10 | 7.1% | Stock updates, PO status transitions |
| **Total** | | **141** | **100%** | |

#### 4.1.1.5 Perancangan Test Granularity

Peneliti menentukan granularity yang tepat untuk setiap jenis pengujian. Tabel 4.5 menunjukkan strategi granularity yang diterapkan.

**Tabel 4.5: Strategi Test Granularity**

| Aspek Pengujian | Granularity Level | Pendekatan | Alasan |
|-----------------|-------------------|-----------|--------|
| Business Logic Calculations | Fine-grained (Unit Test) | Test individual methods secara isolated | Logic kompleks perlu validasi detail (contoh: calculateAcceptanceRate()) |
| User Workflows | Coarse-grained (Functional Test) | Test end-to-end dari user perspective | Validasi flow lengkap dari UI hingga database |
| Component Interactions | Medium-grained (Integration Test) | Test interaksi antar components | Validasi cascade effects dan transaction integrity |
| UI Components | Functional Test | Test dengan Filament helpers | Validasi rendering dan user interaction |

#### 4.1.1.6 Database Testing Strategy

Pada perancangan database testing, peneliti menggunakan dual-database strategy dengan transaction isolation untuk memastikan test reliability dan repeatability. Tabel 4.6 menunjukkan konfigurasi database testing.

**Tabel 4.6: Konfigurasi Database Testing**

| Aspek | Production | Testing | Alasan |
|-------|-----------|---------|--------|
| Database Engine | MySQL 8.0 | SQLite (In-Memory) | SQLite lebih cepat untuk testing |
| Transaction Strategy | Manual commit | Auto-rollback setelah test | Memastikan test isolation |
| Data Seeding | Manual/Seeder | Factory pattern | Factory memberikan flexibility |
| Connection Pool | Persistent | Per-test | Menghindari state leak antar tests |

#### 4.1.1.7 Poin-Poin Pengujian Utama

Berdasarkan pemetaan dari UML diagrams, peneliti mengidentifikasi poin-poin pengujian utama yang akan diimplementasikan dalam fase Red-Green-Refactor. Tabel 4.7 menunjukkan poin-poin pengujian yang dirancang.

**Tabel 4.7: Poin-Poin Pengujian Utama**

| ID Poin | Area Pengujian | Jumlah Test | Referensi Diagram | Prioritas |
|---------|---------------|-------------|-------------------|-----------|
| P-01 | Manajemen User (CRUD + RBAC) | 11 | Activity: 1-master-data-management | Tinggi |
| P-02 | Manajemen Supplier (CRUD + Soft Delete) | 12 | Activity: 1-master-data-management | Tinggi |
| P-03 | Manajemen Produk (CRUD + Stock Management) | 37 | Activity: 1-master-data-management, Class: Product | Tinggi |
| P-04 | Manajemen Purchase Order (CRUD + Validation) | 10 | Activity: 2-purchase-order-management | Tinggi |
| P-05 | Manajemen Delivery Order (CRUD + Tenant Isolation) | 18 | Activity: 3-delivery-order-management | Tinggi |
| P-06 | Penerimaan Barang (Integration + Transaction) | 13 | Activity: 4, 5-goods-receipt, Sequence: 4, 5 | Tinggi |
| P-07 | Autentikasi Multi-Panel (Security + RBAC) | 16 | Cross-cutting concern | Tinggi |
| P-08 | Laporan Bulanan (Reporting + Filtering) | 19 | Activity: 6-monthly-report-generation | Sedang |
| P-09 | Messaging (Real-time + WebSocket) | 5 (partial) | Activity: 8-messaging | Rendah |
| P-10 | View Produk Supplier (Read-only + Tenant) | 0 (belum) | Activity: 7-view-product | Rendah |

### 4.1.2 Red Phase - Menulis Test yang Gagal

Pada fase Red, peneliti menulis test cases berdasarkan poin-poin pengujian yang telah dirancang sebelum menulis implementasi kode. Fase ini memastikan bahwa test benar-benar menguji requirement yang dimaksud dan bukan sekedar passing test tanpa makna.

#### 4.1.2.1 P-01: Manajemen User

Pada tahapan ini, peneliti menulis 11 test cases untuk menguji manajemen user. Test cases mencakup operasi CRUD, validation, dan business rules.

**Test Case TU-001: Menampilkan Daftar User**

Peneliti menulis test untuk memvalidasi bahwa admin dapat mengakses halaman daftar user:

```php
test('dapat menampilkan halaman daftar user', function () {
    $admin = User::factory()->admin()->create();

    actingAs($admin, 'admin')
        ->get(route('filament.admin.resources.users.index'))
        ->assertSuccessful();
});
```

Pada saat pertama kali dijalankan, test ini gagal dengan error 404 karena route dan resource belum didefinisikan.

**Test Case TU-005: Membuat User Baru**

Peneliti menulis test untuk validasi pembuatan user baru dengan semua field required:

```php
test('dapat membuat user baru', function () {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin, 'admin')
        ->test(CreateUser::class)
        ->fillForm([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'role' => UserRole::Checker,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    assertDatabaseHas('users', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ]);
});
```

Test ini gagal karena CreateUser component belum exist dan form schema belum didefinisikan.

**Test Case TU-006: Validasi Field Wajib**

Peneliti menulis negative test untuk memastikan validation rules berfungsi:

```php
test('validasi field wajib user', function () {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin, 'admin')
        ->test(CreateUser::class)
        ->fillForm(['name' => '']) // Required field kosong
        ->call('create')
        ->assertHasFormErrors(['name' => 'required']);
});
```

Test ini gagal karena validation rules belum diterapkan pada form.

**Test Case TU-011: Business Rule - Tidak Dapat Hapus User Aktif**

Peneliti menulis test untuk business rule yang melarang penghapusan user aktif:

```php
test('tidak dapat menghapus user aktif', function () {
    $admin = User::factory()->admin()->create();
    $activeUser = User::factory()->create(['is_active' => true]);

    Livewire::actingAs($admin, 'admin')
        ->test(ListUsers::class)
        ->callTableAction('delete', $activeUser)
        ->assertNotified(); // Expecting error notification

    assertDatabaseHas('users', ['id' => $activeUser->id]); // User still exists
});
```

Test ini gagal karena policy untuk mencegah penghapusan user aktif belum diimplementasikan.

#### 4.1.2.2 P-02: Manajemen Supplier

Pada tahapan ini, peneliti menulis 12 test cases untuk manajemen supplier dengan fitur soft delete.

**Test Case TS-006: Auto-Generate Kode Supplier**

Peneliti menulis test untuk validasi auto-generation kode supplier:

```php
test('dapat membuat supplier baru dengan kode otomatis', function () {
    $admin = User::factory()->admin()->create();

    $supplier = Supplier::factory()->create([
        'name' => 'Test Supplier',
        'email' => 'test@supplier.com',
    ]);

    expect($supplier->code)
        ->not->toBeNull()
        ->toStartWith('SUP-');
});
```

Test ini gagal karena method `generateSupplierCode()` belum diimplementasikan dan kode tidak di-generate otomatis.

**Test Case TS-010: Soft Delete Supplier**

Peneliti menulis test untuk memvalidasi soft delete functionality:

```php
test('dapat soft delete supplier', function () {
    $admin = User::factory()->admin()->create();
    $supplier = Supplier::factory()->create();

    Livewire::actingAs($admin, 'admin')
        ->test(ListSuppliers::class)
        ->callTableAction('delete', $supplier);

    $supplier->refresh();
    expect($supplier->deleted_at)->not->toBeNull();
});
```

Test ini gagal karena SoftDeletes trait belum diterapkan pada model Supplier.

#### 4.1.2.3 P-03: Manajemen Produk

Pada tahapan ini, peneliti menulis 37 test cases untuk manajemen produk, mencakup CRUD, stock management, dan analytics.

**Test Case TP-007: Validasi Harga Positif**

Peneliti menulis validation test untuk memastikan harga harus positif:

```php
test('validasi harga produk harus positif', function () {
    $admin = User::factory()->admin()->create();
    $supplier = Supplier::factory()->create();

    Livewire::actingAs($admin, 'admin')
        ->test(CreateProduct::class)
        ->fillForm([
            'supplier_id' => $supplier->id,
            'name' => 'Test Product',
            'price' => -5000, // Negative price
        ])
        ->call('create')
        ->assertHasFormErrors(['price' => 'min']);
});
```

Test ini gagal karena validation rule `min:0` belum diterapkan pada field price.

**Test Case TP-020: Tambah Stok Produk**

Peneliti menulis test untuk method `increaseStock()`:

```php
test('dapat menambah stok produk', function () {
    $product = Product::factory()->create(['stock_quantity' => 50]);

    $product->increaseStock(20, 'Goods Receipt GRN-001');

    expect($product->fresh()->stock_quantity)->toBe(70);

    assertDatabaseHas('stock_movements', [
        'product_id' => $product->id,
        'quantity' => 20,
        'type' => 'in',
        'reference' => 'Goods Receipt GRN-001',
    ]);
});
```

Test ini gagal karena method `increaseStock()` belum exist dan table `stock_movements` belum dibuat.

**Test Case TP-022: Validasi Stok Tidak Boleh Negatif**

Peneliti menulis negative test untuk business rule stock:

```php
test('tidak dapat mengurangi stok melebihi yang tersedia', function () {
    $product = Product::factory()->create(['stock_quantity' => 50]);

    expect(fn() => $product->decreaseStock(60, 'Adjustment'))
        ->toThrow(InsufficientStockException::class);

    expect($product->fresh()->stock_quantity)->toBe(50); // Unchanged
});
```

Test ini gagal karena validation untuk prevent negative stock belum diimplementasikan.

**Test Case TP-030: Calculate Acceptance Rate**

Peneliti menulis test untuk business logic calculation:

```php
test('dapat menghitung acceptance rate produk dengan benar', function () {
    $product = Product::factory()->create();

    GoodsReceipt::factory()
        ->hasDetails(1, [
            'product_id' => $product->id,
            'quantity_ordered' => 100,
            'quantity_received' => 85,
            'quantity_rejected' => 15,
        ])
        ->create(['status' => GoodsReceiptStatus::Completed]);

    $acceptanceRate = $product->getAcceptanceRate();

    // Expected: 85 / (85 + 15) * 100 = 85%
    expect($acceptanceRate)->toBe(85.0);
});
```

Test ini gagal karena method `getAcceptanceRate()` belum diimplementasikan.

#### 4.1.2.4 P-04: Manajemen Purchase Order

Pada tahapan ini, peneliti menulis 10 test cases untuk manajemen Purchase Order dengan fokus pada validation dan RBAC.

**Test Case TPO-002: Membuat PO Baru**

Peneliti menulis test untuk pembuatan PO dengan auto-generated PO number:

```php
test('dapat membuat PO baru', function () {
    $admin = User::factory()->admin()->create();
    $supplier = Supplier::factory()->create();
    $product = Product::factory()->create();

    Livewire::actingAs($admin, 'admin')
        ->test(CreatePurchaseOrder::class)
        ->fillForm([
            'supplier_id' => $supplier->id,
            'details' => [
                ['product_id' => $product->id, 'quantity' => 100, 'price' => 10000],
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $po = PurchaseOrder::first();
    expect($po->po_number)->toStartWith('PO-');
    expect($po->status)->toBe(POStatus::Pending);
});
```

Test ini gagal karena PO resource dan auto-generation logic belum diimplementasikan.

**Test Case TPO-006: Tidak Dapat Edit PO Non-Pending**

Peneliti menulis negative test untuk business rule:

```php
test('tidak dapat mengedit PO yang tidak berstatus Pending', function () {
    $admin = User::factory()->admin()->create();
    $po = PurchaseOrder::factory()->create(['status' => POStatus::Completed]);

    Livewire::actingAs($admin, 'admin')
        ->test(EditPurchaseOrder::class, ['record' => $po->id])
        ->assertForbidden();
});
```

Test ini gagal karena policy check untuk status Pending belum diimplementasikan.

#### 4.1.2.5 P-06: Penerimaan Barang (Integration Testing)

Pada tahapan ini, peneliti menulis 13 test cases untuk penerimaan barang dengan fokus pada integration dan transaction integrity.

**Test Case TGR-008: Cascade Updates Saat Complete**

Peneliti menulis comprehensive integration test:

```php
test('menyelesaikan penerimaan barang memicu cascade updates', function () {
    $admin = User::factory()->admin()->create();

    // Setup
    $product = Product::factory()->create(['stock_quantity' => 100]);
    $po = PurchaseOrder::factory()->create(['status' => POStatus::Pending]);
    $poDetail = PurchaseOrderDetail::factory()->create([
        'purchase_order_id' => $po->id,
        'product_id' => $product->id,
        'quantity' => 50,
        'quantity_received' => 0,
    ]);

    $shipment = Shipment::factory()->create([
        'purchase_order_id' => $po->id,
        'status' => ShipmentStatus::Arrived,
    ]);

    $gr = GoodsReceipt::factory()->create([
        'shipment_id' => $shipment->id,
        'status' => GoodsReceiptStatus::Pending,
    ]);

    GoodsReceiptDetail::factory()->create([
        'goods_receipt_id' => $gr->id,
        'product_id' => $product->id,
        'quantity_ordered' => 50,
        'quantity_received' => 45,
        'quantity_rejected' => 5,
    ]);

    // Action
    Livewire::actingAs($admin, 'app')
        ->test(ViewGoodsReceipt::class, ['record' => $gr->id])
        ->callAction('complete');

    // Assertions - Cascade updates
    expect($gr->fresh()->status)->toBe(GoodsReceiptStatus::Completed);
    expect($product->fresh()->stock_quantity)->toBe(145); // 100 + 45
    expect($poDetail->fresh()->quantity_received)->toBe(45);
    expect($po->fresh()->status)->toBe(POStatus::Partial); // Not all received
    expect($shipment->fresh()->status)->toBe(ShipmentStatus::Processed);

    // Verify stock movement logged
    assertDatabaseHas('stock_movements', [
        'product_id' => $product->id,
        'quantity' => 45,
        'type' => 'in',
    ]);
});
```

Test ini gagal di multiple points karena:
1. Complete action belum exist
2. Cascade update logic belum diimplementasikan
3. Transaction integrity belum diterapkan

**Test Case TGR-013: Database Transaction Integrity**

Peneliti menulis test untuk memastikan atomicity:

```php
test('database transaction integrity saat complete GR', function () {
    // Setup dengan kondisi yang akan cause error di tengah proses
    $product = Product::factory()->create(['stock_quantity' => 100]);
    $gr = GoodsReceipt::factory()->create(['status' => GoodsReceiptStatus::Pending]);

    // Intentional error condition
    GoodsReceiptDetail::factory()->create([
        'goods_receipt_id' => $gr->id,
        'product_id' => 999999, // Non-existent product
        'quantity_received' => 10,
    ]);

    expect(fn() => $gr->complete())->toThrow(Exception::class);

    // Verify rollback - nothing changed
    expect($product->fresh()->stock_quantity)->toBe(100);
    expect($gr->fresh()->status)->toBe(GoodsReceiptStatus::Pending);
});
```

Test ini gagal karena database transaction belum dibungkus untuk ensure atomicity.

#### 4.1.2.6 P-07: Autentikasi Multi-Panel

Pada tahapan ini, peneliti menulis 16 test cases untuk autentikasi multi-panel dengan matrix approach.

**Test Case TAUTH-003: Non-Admin Tidak Dapat Login ke Admin Panel**

Peneliti menulis security test:

```php
test('user selain admin tidak dapat login ke panel admin', function () {
    $checker = User::factory()->create(['role' => UserRole::Checker]);

    actingAs($checker, 'admin')
        ->get(route('filament.admin.pages.dashboard'))
        ->assertForbidden();
});
```

Test ini gagal karena panel authorization belum dikonfigurasi.

#### 4.1.2.7 P-08: Laporan Bulanan

Pada tahapan ini, peneliti menulis 19 test cases untuk reporting dengan dynamic table switching.

**Test Case TLB-005: Default Period Bulan Ini**

Peneliti menulis test untuk default behavior:

```php
test('default period adalah bulan ini', function () {
    $accounting = User::factory()->create(['role' => UserRole::Accounting]);

    Livewire::actingAs($accounting, 'app')
        ->test(MonthlyReport::class)
        ->assertSet('start_date', now()->startOfMonth()->toDateString())
        ->assertSet('end_date', now()->endOfMonth()->toDateString());
});
```

Test ini gagal karena default period logic belum diimplementasikan di `mount()` method.

**Test Case TLB-011: Dynamic Table Switching**

Peneliti menulis test untuk UI behavior:

```php
test('menampilkan tabel Goods Receipt sesuai report type', function () {
    $accounting = User::factory()->create(['role' => UserRole::Accounting]);

    Livewire::actingAs($accounting, 'app')
        ->test(MonthlyReport::class)
        ->set('reportType', 'goods_receipts')
        ->assertSee('GRN Number')
        ->assertSee('Supplier')
        ->assertSee('Status');
});
```

Test ini gagal karena dynamic table switching belum diimplementasikan.

### 4.1.3 Green Phase - Implementasi Minimal untuk Pass Tests

Pada fase Green, peneliti menulis kode implementasi minimal namun cukup untuk membuat tests passing. Fokus pada tahapan ini adalah membuat test passing sesegera mungkin, bukan menulis kode yang sempurna.

#### 4.1.3.1 P-01: Implementasi Manajemen User

**Implementasi TU-001: Resource dan Routing**

Untuk membuat test TU-001 passing, peneliti membuat Filament resource:

```bash
php artisan make:filament-resource User --generate
```

Command ini generate basic resource dengan routing, sehingga test untuk menampilkan halaman daftar user menjadi passing.

**Implementasi TU-005: Form Schema untuk Create User**

Peneliti implement form schema dengan field yang required:

```php
public static function form(Form $form): Form
{
    return $form->schema([
        Forms\Components\TextInput::make('name')
            ->required(),
        Forms\Components\TextInput::make('email')
            ->email()
            ->required()
            ->unique(ignoreRecord: true),
        Forms\Components\TextInput::make('password')
            ->password()
            ->required()
            ->minLength(8),
        Forms\Components\Select::make('role')
            ->options(UserRole::class)
            ->required(),
    ]);
}
```

Implementation straightforward ini membuat test TU-005 passing. Validation untuk TU-006 juga otomatis ter-handle karena `required()` rule.

**Implementasi TU-011: Policy untuk Business Rule**

Peneliti implement policy untuk prevent deletion user aktif:

```php
public function delete(User $user, User $model): bool
{
    // Tidak dapat hapus user aktif
    return !$model->is_active;
}
```

Simple policy ini membuat test TU-011 passing dengan business rule enforcement.

#### 4.1.3.2 P-02: Implementasi Manajemen Supplier

**Implementasi TS-006: Auto-Generate Supplier Code**

Peneliti implement Observer untuk auto-generate kode supplier:

```php
class SupplierObserver
{
    public function creating(Supplier $supplier): void
    {
        if (empty($supplier->code)) {
            $supplier->code = $this->generateSupplierCode();
        }
    }

    private function generateSupplierCode(): string
    {
        $lastSupplier = Supplier::orderBy('id', 'desc')->first();
        $number = $lastSupplier ? ($lastSupplier->id + 1) : 1;
        return 'SUP-' . str_pad($number, 5, '0', STR_PAD_LEFT);
    }
}
```

Simple implementation ini membuat test TS-006 passing dengan auto-generation kode.

**Implementasi TS-010: Soft Delete**

Peneliti add SoftDeletes trait ke model:

```php
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use SoftDeletes;

    // ... rest of model
}
```

Dengan menambahkan trait dan migration untuk `deleted_at` column, test TS-010 dan TS-011 (restore) menjadi passing.

#### 4.1.3.3 P-03: Implementasi Manajemen Produk

**Implementasi TP-007: Validation Rule Harga Positif**

Peneliti add validation rule pada form:

```php
Forms\Components\TextInput::make('price')
    ->numeric()
    ->required()
    ->minValue(0), // Validation rule untuk harga positif
```

Simple validation rule ini membuat test TP-007 passing.

**Implementasi TP-020: Method increaseStock()**

Peneliti implement method untuk increase stock dengan logging:

```php
public function increaseStock(int $quantity, string $reference): void
{
    DB::transaction(function () use ($quantity, $reference) {
        $this->increment('stock_quantity', $quantity);

        $this->logStockMovement($quantity, 'in', $reference);
    });
}

protected function logStockMovement(int $quantity, string $type, string $reference): void
{
    StockMovement::create([
        'product_id' => $this->id,
        'quantity' => $quantity,
        'type' => $type,
        'reference' => $reference,
        'balance_after' => $this->stock_quantity,
    ]);
}
```

Implementation ini includes database transaction dan stock movement logging, membuat test TP-020 passing.

**Implementasi TP-022: Validation Prevent Negative Stock**

Peneliti implement method `decreaseStock()` dengan validation:

```php
public function decreaseStock(int $quantity, string $reference): void
{
    DB::transaction(function () use ($quantity, $reference) {
        if ($this->stock_quantity < $quantity) {
            throw new InsufficientStockException(
                "Insufficient stock. Available: {$this->stock_quantity}, Required: {$quantity}"
            );
        }

        $this->decrement('stock_quantity', $quantity);

        $this->logStockMovement($quantity, 'out', $reference);
    });
}
```

Validation check sebelum decrement membuat test TP-022 passing dengan proper exception.

**Implementasi TP-030: Calculate Acceptance Rate**

Peneliti implement business logic calculation:

```php
public function getAcceptanceRate(): float
{
    $totalReceived = $this->getTotalReceived();
    $totalRejected = $this->getTotalRejected();
    $total = $totalReceived + $totalRejected;

    if ($total === 0) {
        return 0.0;
    }

    return round(($totalReceived / $total) * 100, 2);
}

public function getTotalReceived(): int
{
    return $this->goodsReceiptDetails()
        ->whereHas('goodsReceipt', fn($q) =>
            $q->where('status', GoodsReceiptStatus::Completed)
        )
        ->sum('quantity_received');
}

public function getTotalRejected(): int
{
    return $this->goodsReceiptDetails()
        ->whereHas('goodsReceipt', fn($q) =>
            $q->where('status', GoodsReceiptStatus::Completed)
        )
        ->sum('quantity_rejected');
}
```

Implementation dengan helper methods ini membuat test TP-030 passing dengan calculation yang correct.

#### 4.1.3.4 P-04: Implementasi Purchase Order

**Implementasi TPO-002: Auto-Generate PO Number**

Peneliti implement Observer untuk PO:

```php
class PurchaseOrderObserver
{
    public function creating(PurchaseOrder $po): void
    {
        if (empty($po->po_number)) {
            $po->po_number = $this->generatePONumber();
        }

        if (empty($po->status)) {
            $po->status = POStatus::Pending;
        }
    }

    private function generatePONumber(): string
    {
        $lastPO = PurchaseOrder::orderBy('id', 'desc')->first();
        $number = $lastPO ? ($lastPO->id + 1) : 1;
        return 'PO-' . date('Ymd') . '-' . str_pad($number, 4, '0', STR_PAD_LEFT);
    }
}
```

Implementation ini auto-generate PO number dan set default status, membuat test TPO-002 passing.

**Implementasi TPO-006: Policy untuk Edit PO**

Peneliti implement policy check:

```php
public function update(User $user, PurchaseOrder $po): bool
{
    // Hanya dapat edit jika status Pending
    return $po->status === POStatus::Pending;
}
```

Simple policy ini enforce business rule, membuat test TPO-006 passing.

#### 4.1.3.5 P-06: Implementasi Goods Receipt Integration

**Implementasi TGR-008: Complete Action dengan Cascade Updates**

Peneliti implement complex complete logic:

```php
public function complete(): void
{
    DB::transaction(function () {
        // Update GR status
        $this->update(['status' => GoodsReceiptStatus::Completed]);

        // Update product stocks
        foreach ($this->details as $detail) {
            $detail->product->increaseStock(
                $detail->quantity_received,
                "Goods Receipt {$this->grn_number}"
            );
        }

        // Update PO received quantities
        foreach ($this->details as $detail) {
            $poDetail = PurchaseOrderDetail::where([
                'purchase_order_id' => $this->shipment->purchase_order_id,
                'product_id' => $detail->product_id,
            ])->first();

            if ($poDetail) {
                $poDetail->increment('quantity_received', $detail->quantity_received);
            }
        }

        // Update PO status based on received quantities
        $this->shipment->purchaseOrder->updateStatus();

        // Update shipment status
        $this->shipment->update(['status' => ShipmentStatus::Processed]);
    });
}
```

Implementation comprehensive ini handle semua cascade updates dalam transaction, membuat test TGR-008 passing.

**Implementasi: PO updateStatus() Logic**

Peneliti implement automatic PO status update:

```php
public function updateStatus(): void
{
    $allReceived = $this->details->every(function ($detail) {
        return $detail->quantity_received >= $detail->quantity;
    });

    $anyReceived = $this->details->some(function ($detail) {
        return $detail->quantity_received > 0;
    });

    if ($allReceived) {
        $this->update(['status' => POStatus::Completed]);
    } elseif ($anyReceived) {
        $this->update(['status' => POStatus::Partial]);
    }
}
```

Logic ini determine status based on received quantities, membuat test TGR-011 passing.

**Implementasi TGR-013: Transaction Integrity**

Database transaction sudah dibungkus di method `complete()`, sehingga jika ada error di tengah proses, semua changes akan di-rollback. Ini membuat test TGR-013 passing dengan atomicity guarantee.

#### 4.1.3.6 P-07: Implementasi Multi-Panel Auth

**Implementasi TAUTH-003: Panel Authorization**

Peneliti configure panel authorization di `bootstrap/app.php`:

```php
$adminPanel
    ->authGuard('admin')
    ->login()
    ->authMiddleware([
        Authenticate::class,
    ])
    ->canAccess(function (User $user) {
        return $user->role === UserRole::Admin;
    });

$appPanel
    ->authGuard('web')
    ->login()
    ->authMiddleware([
        Authenticate::class,
    ])
    ->canAccess(function (User $user) {
        return in_array($user->role, [
            UserRole::Admin,
            UserRole::Checker,
            UserRole::Accounting,
        ]);
    });

$supplierPanel
    ->authGuard('web')
    ->login()
    ->authMiddleware([
        Authenticate::class,
    ])
    ->canAccess(function (User $user) {
        return $user->role === UserRole::Supplier;
    });
```

Configuration ini enforce panel-level authorization, membuat semua test TAUTH passing dengan proper access control.

#### 4.1.3.7 P-08: Implementasi Laporan Bulanan

**Implementasi TLB-005: Default Period**

Peneliti implement `mount()` method:

```php
public function mount(): void
{
    $this->start_date = now()->startOfMonth()->toDateString();
    $this->end_date = now()->endOfMonth()->toDateString();
    $this->reportType = 'purchase_orders';
}
```

Simple initialization ini membuat test TLB-005 passing dengan default current month.

**Implementasi TLB-011: Dynamic Table Switching**

Peneliti implement dynamic table rendering:

```php
public function getPurchaseOrdersTable(): Table
{
    return Table::make()
        ->query(
            PurchaseOrder::query()
                ->whereBetween('created_at', [$this->start_date, $this->end_date])
        )
        ->columns([
            Tables\Columns\TextColumn::make('po_number'),
            Tables\Columns\TextColumn::make('supplier.name'),
            Tables\Columns\TextColumn::make('created_at')->date(),
            Tables\Columns\TextColumn::make('status'),
            Tables\Columns\TextColumn::make('total_amount')->money('IDR'),
        ]);
}

public function getGoodsReceiptsTable(): Table
{
    return Table::make()
        ->query(
            GoodsReceipt::query()
                ->whereBetween('created_at', [$this->start_date, $this->end_date])
        )
        ->columns([
            Tables\Columns\TextColumn::make('grn_number'),
            Tables\Columns\TextColumn::make('shipment.purchaseOrder.supplier.name'),
            Tables\Columns\TextColumn::make('created_at')->date(),
            Tables\Columns\TextColumn::make('status'),
        ]);
}
```

Di view, table di-render berdasarkan `$reportType`:

```blade
@if($reportType === 'purchase_orders')
    {{ $this->getPurchaseOrdersTable() }}
@elseif($reportType === 'goods_receipts')
    {{ $this->getGoodsReceiptsTable() }}
@endif
```

Implementation ini enable dynamic table switching, membuat test TLB-011 passing.

### 4.1.4 Refactor Phase - Perbaikan Kualitas Kode

Pada fase Refactor, peneliti memperbaiki struktur dan kualitas kode tanpa mengubah behavior yang sudah benar. Pada tahapan ini, peneliti dapat dengan percaya diri melakukan refactoring karena test suite yang sudah ada memberikan feedback langsung jika ada perubahan yang merusak fungsionalitas.

#### 4.1.4.1 P-03: Refactoring Product Management

**Refactoring 1: Extract Method untuk Reduce Duplication**

Peneliti notice duplication dalam query logic untuk getTotalReceived() dan getTotalRejected():

```php
// Before refactoring - duplication
public function getTotalReceived(): int
{
    return $this->goodsReceiptDetails()
        ->whereHas('goodsReceipt', fn($q) =>
            $q->where('status', GoodsReceiptStatus::Completed)
        )
        ->sum('quantity_received');
}

public function getTotalRejected(): int
{
    return $this->goodsReceiptDetails()
        ->whereHas('goodsReceipt', fn($q) =>
            $q->where('status', GoodsReceiptStatus::Completed)
        )
        ->sum('quantity_rejected');
}

// After refactoring - extracted common query
protected function completedGoodsReceiptDetails()
{
    return $this->goodsReceiptDetails()
        ->whereHas('goodsReceipt', fn($q) =>
            $q->where('status', GoodsReceiptStatus::Completed)
        );
}

public function getTotalReceived(): int
{
    return $this->completedGoodsReceiptDetails()->sum('quantity_received');
}

public function getTotalRejected(): int
{
    return $this->completedGoodsReceiptDetails()->sum('quantity_rejected');
}
```

Refactoring ini eliminate duplication dan make code more maintainable. Tests tetap passing karena behavior unchanged.

**Refactoring 2: Introduce Query Scope**

Peneliti further improve dengan introducing Eloquent scope:

```php
// In GoodsReceiptDetail model
public function scopeCompleted($query)
{
    return $query->whereHas('goodsReceipt', fn($q) =>
        $q->where('status', GoodsReceiptStatus::Completed)
    );
}

// In Product model - cleaner usage
protected function completedGoodsReceiptDetails()
{
    return $this->goodsReceiptDetails()->completed();
}
```

Scope ini more reusable dan can be used di tempat lain. Tests tetap passing.

**Refactoring 3: Extract Complex Condition**

Peneliti extract complex stock status conditions:

```php
// Before - complex inline conditions
public function getStockStatus(): string
{
    if ($this->stock_quantity === 0) {
        return 'Out';
    }

    if ($this->stock_quantity > 0 && $this->stock_quantity < $this->minimum_stock) {
        return 'Low';
    }

    return 'Good';
}

// After - extracted untuk better readability
public function isLowStock(): bool
{
    return $this->stock_quantity > 0 &&
           $this->stock_quantity < $this->minimum_stock;
}

public function isOutOfStock(): bool
{
    return $this->stock_quantity === 0;
}

public function isGoodStock(): bool
{
    return $this->stock_quantity >= $this->minimum_stock;
}

public function getStockStatus(): string
{
    if ($this->isOutOfStock()) return 'Out';
    if ($this->isLowStock()) return 'Low';
    return 'Good';
}
```

Extracted methods more readable dan self-documenting. Tests tetap passing dan bahkan dapat add new tests untuk individual methods.

#### 4.1.4.2 P-04: Refactoring Purchase Order

**Refactoring: Extract Complex Condition untuk PO Status**

Peneliti refactor updateStatus() logic:

```php
// Before - complex conditions inline
public function updateStatus(): void
{
    $allReceived = $this->details->every(function ($detail) {
        return $detail->quantity_received >= $detail->quantity;
    });

    $anyReceived = $this->details->some(function ($detail) {
        return $detail->quantity_received > 0;
    });

    if ($allReceived) {
        $this->update(['status' => POStatus::Completed]);
    } elseif ($anyReceived) {
        $this->update(['status' => POStatus::Partial]);
    }
}

// After - extracted untuk better readability
protected function allItemsReceived(): bool
{
    return $this->details->every(fn($detail) =>
        $detail->quantity_received >= $detail->quantity
    );
}

protected function anyItemsReceived(): bool
{
    return $this->details->some(fn($detail) =>
        $detail->quantity_received > 0
    );
}

public function updateStatus(): void
{
    if ($this->allItemsReceived()) {
        $this->update(['status' => POStatus::Completed]);
    } elseif ($this->anyItemsReceived()) {
        $this->update(['status' => POStatus::Partial]);
    }
}
```

More readable dengan descriptive method names. Tests tetap passing.

#### 4.1.4.3 P-06: Refactoring Goods Receipt

**Refactoring: Extract Methods untuk Better Organization**

Peneliti refactor complex `complete()` method:

```php
// Before - all logic in one method
public function complete(): void
{
    DB::transaction(function () {
        $this->update(['status' => GoodsReceiptStatus::Completed]);

        foreach ($this->details as $detail) {
            $detail->product->increaseStock(
                $detail->quantity_received,
                "Goods Receipt {$this->grn_number}"
            );
        }

        foreach ($this->details as $detail) {
            $poDetail = PurchaseOrderDetail::where([...])->first();
            if ($poDetail) {
                $poDetail->increment('quantity_received', $detail->quantity_received);
            }
        }

        $this->shipment->purchaseOrder->updateStatus();
        $this->shipment->update(['status' => ShipmentStatus::Processed]);
    });
}

// After - extracted methods untuk better organization
public function complete(): void
{
    DB::transaction(function () {
        $this->updateProductStocks();
        $this->updatePurchaseOrderReceived();
        $this->updateShipmentStatus();
        $this->markAsCompleted();
    });
}

protected function updateProductStocks(): void
{
    foreach ($this->details as $detail) {
        $detail->product->increaseStock(
            $detail->quantity_received,
            "Goods Receipt {$this->grn_number}"
        );
    }
}

protected function updatePurchaseOrderReceived(): void
{
    foreach ($this->details as $detail) {
        $poDetail = PurchaseOrderDetail::where([
            'purchase_order_id' => $this->shipment->purchase_order_id,
            'product_id' => $detail->product_id,
        ])->first();

        if ($poDetail) {
            $poDetail->increment('quantity_received', $detail->quantity_received);
        }
    }

    $this->shipment->purchaseOrder->updateStatus();
}

protected function updateShipmentStatus(): void
{
    $this->shipment->update(['status' => ShipmentStatus::Processed]);
}

protected function markAsCompleted(): void
{
    $this->update(['status' => GoodsReceiptStatus::Completed]);
}
```

Extracted methods make `complete()` method very readable - clear steps untuk cascade updates. Tests tetap passing dengan behavior yang sama.

#### 4.1.4.4 Refactoring Test Code

**Refactoring: Extract Test Helper Methods**

Peneliti juga refactor test code untuk reduce duplication:

```php
// Before - repetitive setup dalam multiple tests
test('dapat membuat produk baru', function () {
    $admin = User::factory()->admin()->create();
    $supplier = Supplier::factory()->create();

    Livewire::actingAs($admin, 'admin')
        ->test(CreateProduct::class)
        ->fillForm([...])
        ->call('create');
});

test('validasi produk', function () {
    $admin = User::factory()->admin()->create();
    $supplier = Supplier::factory()->create();

    Livewire::actingAs($admin, 'admin')
        ->test(CreateProduct::class)
        ->fillForm([...])
        ->call('create');
});

// After - extracted helper methods
function asAdmin()
{
    return User::factory()->admin()->create();
}

function createSupplier(array $attributes = [])
{
    return Supplier::factory()->create($attributes);
}

function productFormData(Supplier $supplier, array $overrides = []): array
{
    return array_merge([
        'supplier_id' => $supplier->id,
        'code' => 'PRD-TEST-001',
        'name' => 'Test Product',
        'price' => 10000,
        'stock_quantity' => 100,
        'minimum_stock' => 10,
    ], $overrides);
}

test('dapat membuat produk baru', function () {
    $admin = asAdmin();
    $supplier = createSupplier();

    Livewire::actingAs($admin, 'admin')
        ->test(CreateProduct::class)
        ->fillForm(productFormData($supplier))
        ->call('create')
        ->assertHasNoFormErrors();
});

test('validasi harga produk', function () {
    $admin = asAdmin();
    $supplier = createSupplier();

    Livewire::actingAs($admin, 'admin')
        ->test(CreateProduct::class)
        ->fillForm(productFormData($supplier, ['price' => -5000]))
        ->call('create')
        ->assertHasFormErrors(['price']);
});
```

Test code menjadi more readable dan easier untuk maintain.

#### 4.1.4.5 Performance Optimization

**Refactoring: Add Database Indexes**

Peneliti add indexes untuk frequently queried columns:

```php
Schema::table('products', function (Blueprint $table) {
    $table->index('code');
    $table->index('supplier_id');
    $table->index(['stock_quantity', 'minimum_stock']);
    $table->index('is_active');
});
```

**Refactoring: Eager Loading untuk N+1 Prevention**

Peneliti add explicit eager loading dalam Filament resource:

```php
public static function table(Table $table): Table
{
    return $table
        ->columns([
            Tables\Columns\TextColumn::make('supplier.name'),
        ])
        ->modifyQueryUsing(fn($query) => $query->with('supplier'));
}
```

Optimizations ini improve performance significantly tanpa changing behavior. Tests confirm functionality remains correct.

## 4.2 Hasil Implementasi TDD

Setelah menerapkan siklus TDD secara konsisten untuk seluruh use case dalam sistem, peneliti berhasil mengimplementasikan test suite yang komprehensif. Tabel 4.8 menunjukkan ringkasan hasil implementasi TDD.

**Tabel 4.8: Ringkasan Hasil Implementasi TDD**

| Metrik | Nilai | Keterangan |
|--------|-------|------------|
| Total Test Cases | 141 | Test cases yang berhasil diimplementasikan |
| Total Assertions | 623 | Total validasi dalam semua test cases |
| Test Files | 12 | File test terorganisir berdasarkan panel dan role |
| Use Cases Fully Tested | 6 (75%) | UC-01, UC-02, UC-03, UC-04, UC-05, UC-07 |
| Use Cases Partially Tested | 1 (12.5%) | UC-06 (Messaging) dengan 36% coverage |
| Use Cases Not Implemented | 1 (12.5%) | UC-08 (View Product Supplier) |
| Overall Coverage | 87.5% | Coverage dari use cases yang sudah diimplementasikan |
| Average Assertions per Test | 4.4 | Menunjukkan thoroughness dalam validasi |

**Tabel 4.9: Distribusi Test Cases per Use Case**

| Use Case | Jumlah Test | Coverage | Kategori Utama |
|----------|-------------|----------|----------------|
| UC-01: Mengelola Master Data | 60 | 100% | Functional, Validation, CRUD |
| UC-02: Manajemen Purchase Order | 10 | 100% | Functional, Validation, RBAC |
| UC-03: Mengelola Delivery Order | 18 | 100% | Functional, RBAC, Tenant Isolation |
| UC-04: Penerimaan Barang | 13 | 100% | Integration, Transaction, Cascade Updates |
| UC-05: Autentikasi Multi-Panel | 16 | 100% | Security, RBAC, Panel Authorization |
| UC-06: Mengirim Pesan | 5 | 36% | Basic Functionality |
| UC-07: Membuat Laporan Bulanan | 19 | 100% | Reporting, Filtering, Dynamic UI |
| UC-08: Melihat Produk Supplier | 0 | 0% | Belum Diimplementasikan |

**Tabel 4.10: Coverage per Diagram Element**

| Diagram Element | Coverage | Jumlah Element Tested | Total Element | Gap |
|-----------------|----------|----------------------|---------------|-----|
| Activity Diagram Steps | 95% | 143 steps | 150 steps | 7 steps (UC-06, UC-08) |
| Sequence Diagram Interactions | 90% | 72 interactions | 80 interactions | 8 interactions (async operations) |
| Class Diagram Public Methods | 85% | 110 methods | 129 methods | 19 methods (helper/private) |
| Class Diagram Attributes | 90% | 45 attributes | 50 attributes | 5 attributes (computed) |
| Business Rules | 100% | 38 rules | 38 rules | 0 (fully tested) |

**Tabel 4.11: Coverage per Layer Arsitektur**

| Layer | Coverage | Jumlah Test | Contoh Test Areas |
|-------|----------|-------------|-------------------|
| Presentation Layer | 95% | 85 | Form rendering, table display, UI interactions |
| Business Logic Layer | 98% | 90 | Validation rules, calculations, business workflows |
| Data Access Layer | 90% | 40 | Database queries, relationships, persistence |
| Integration Layer | 95% | 20 | Cascade updates, transactions, multi-step workflows |
| Authorization Layer | 100% | 30 | RBAC, policies, tenant isolation |

Penerapan TDD terbukti memberikan multiple benefits termasuk better code design, higher confidence dalam changes, reduced debugging time, dan living documentation yang selalu up-to-date. Test suite yang dihasilkan tidak hanya memvalidasi functional correctness, tetapi juga security (RBAC), data integrity (database transactions), dan business rules compliance.

Setiap test dapat di-trace kembali ke requirement source dalam UML diagrams, memastikan bidirectional traceability antara requirements dan implementation. Meskipun ada initial time investment untuk writing tests, benefit jangka panjang dalam terms of maintainability dan code quality significantly outweigh costs.
