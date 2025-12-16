# BAB III (Lanjutan)
# PERANCANGAN SISTEM

## 3.3 Perancangan Use Case

### 3.3.1 Identifikasi Aktor

Berdasarkan analisis kebutuhan sistem yang telah dilakukan, peneliti mengidentifikasi empat aktor utama yang berinteraksi dengan Sistem Informasi Warelink. Setiap aktor memiliki peran, tanggung jawab, dan hak akses yang berbeda dalam sistem. Tabel 3.1 menunjukkan identifikasi aktor beserta deskripsinya.

**Tabel 3.1: Identifikasi Aktor Sistem Warelink**

| No | Aktor | Deskripsi | Panel Akses | Tanggung Jawab Utama |
|----|-------|-----------|-------------|----------------------|
| 1 | **Admin Gudang** | Pengguna internal dengan hak akses penuh untuk mengelola master data dan proses procurement | Admin Panel, App Panel | - Mengelola master data (User, Supplier, Produk)<br>- Membuat dan mengelola Purchase Order<br>- Verifikasi akhir penerimaan barang<br>- Monitoring dan koordinasi operasional gudang |
| 2 | **Checker** | Staff gudang yang bertanggung jawab untuk menerima dan memeriksa barang yang datang | App Panel | - Membuat catatan penerimaan barang (Goods Receipt)<br>- Memeriksa kesesuaian barang dengan Delivery Order<br>- Input data quantity received dan rejected<br>- Upload bukti Proof of Delivery (POD) |
| 3 | **Supplier** | Pihak eksternal yang memasok produk ke gudang | Supplier Panel | - Membuat dan mengelola Delivery Order (Shipment)<br>- Melihat Purchase Order yang ditujukan kepada mereka<br>- Melihat informasi produk yang relevan<br>- Komunikasi dengan internal team |
| 4 | **Accounting** | Staff keuangan yang bertanggung jawab untuk pelaporan dan analisis finansial | App Panel | - Membuat dan mengunduh laporan bulanan<br>- Monitoring transaksi procurement<br>- Analisis data keuangan dan stok<br>- Audit trail documentation |

**Karakteristik Multi-Panel Authentication:**

Sistem Warelink mengimplementasikan arsitektur multi-panel menggunakan Filament v4 yang memisahkan akses berdasarkan role pengguna:

- **Admin Panel** (`/admin`): Akses eksklusif untuk Admin Gudang dengan privilese penuh untuk master data management
- **App Panel** (`/app`): Akses untuk internal users (Admin Gudang, Checker, Accounting) dengan fungsionalitas operasional
- **Supplier Panel** (`/supplier`): Akses untuk eksternal users (Supplier) dengan tenant isolation untuk keamanan data

Pemisahan panel ini memastikan **separation of concerns** dan **least privilege principle** dalam access control, dimana setiap aktor hanya memiliki akses ke fungsionalitas yang sesuai dengan perannya.

---

### 3.3.2 Identifikasi Use Case

Berdasarkan analisis kebutuhan fungsional (F-01 hingga F-16), peneliti mengidentifikasi 8 use case utama yang merepresentasikan interaksi aktor dengan sistem. Gambar 3.1 menunjukkan Use Case Diagram lengkap untuk Sistem Informasi Warelink.

**Gambar 3.1: Use Case Diagram Sistem Informasi Warelink**

![Use Case Diagram](../use%20case.png)

Dari diagram di atas, teridentifikasi 8 use case utama dengan berbagai relationship (include dan extend) yang menunjukkan dependency dan optional flows antar use case. Tabel 3.2 menunjukkan daftar lengkap use case beserta aktor yang terlibat.

**Tabel 3.2: Daftar Use Case Sistem Warelink**

| ID Use Case | Nama Use Case | Aktor Utama | Aktor Sekunder | Prioritas | Status Implementasi |
|-------------|---------------|-------------|----------------|-----------|---------------------|
| **UC-01** | Mengelola Master Data | Admin Gudang | - | Critical | ✅ Implemented (100% coverage) |
| **UC-02** | Manajemen Purchase Order (PO) | Admin Gudang | Checker (view-only) | Critical | ✅ Implemented (100% coverage) |
| **UC-03** | Mengelola Delivery Order | Supplier | - | High | ✅ Implemented (100% coverage) |
| **UC-04** | Penerimaan Barang | Checker, Admin Gudang | - | Critical | ✅ Implemented (100% coverage) |
| **UC-05** | Autentikasi Multi-Panel | Semua Aktor | - | Critical | ✅ Implemented (100% coverage) |
| **UC-06** | Mengirim Pesan | Checker, Supplier | - | Medium | ⚠️ Partial (36% coverage) |
| **UC-07** | Membuat Laporan Bulanan | Accounting | - | High | ✅ Implemented (100% coverage) |
| **UC-08** | Melihat Produk (Supplier) | Supplier | - | Low | ❌ Not Implemented (0% coverage) |

**Keterangan:**
- ✅ Implemented: Use case telah diimplementasikan dan diuji secara komprehensif
- ⚠️ Partial: Use case telah diimplementasikan dengan coverage terbatas
- ❌ Not Implemented: Use case belum diimplementasikan

---

### 3.3.3 Pemetaan Kebutuhan Fungsional ke Use Case

Setiap kebutuhan fungsional yang telah diidentifikasi pada tahap analisis kebutuhan dipetakan ke use case yang sesuai untuk memastikan **traceability** dan **completeness** dari requirement coverage. Tabel 3.3 menunjukkan pemetaan sistematis antara kebutuhan fungsional dan use case.

**Tabel 3.3: Pemetaan Kebutuhan Fungsional ke Use Case**

| No | ID Requirement | Kebutuhan Fungsional | Use Case Terkait | Relasi | Keterangan |
|----|---------------|---------------------|------------------|--------|------------|
| 1 | **F-01** | Sistem harus menyediakan fungsionalitas untuk menambah, mengubah, dan menghapus data Pengguna | **UC-01** (Mengelola Master Data) | Direct | CRUD User diimplementasikan sebagai sub-use case "Manajemen Pengguna" dengan 11 test cases |
| 2 | **F-02** | Sistem harus menyediakan fungsionalitas untuk menambah, mengubah, dan menghapus data Supplier | **UC-01** (Mengelola Master Data) | Direct | CRUD Supplier diimplementasikan sebagai sub-use case "Manajemen Supplier" dengan 12 test cases dan fitur soft delete |
| 3 | **F-03** | Sistem harus menyediakan fungsionalitas untuk menambah, mengubah, dan menghapus data Produk | **UC-01** (Mengelola Master Data) | Direct | CRUD Produk diimplementasikan sebagai sub-use case "Manajemen Produk" dengan 37 test cases termasuk stock management dan analytics |
| 4 | **F-04** | Sistem harus memungkinkan Admin Gudang untuk membuat, melihat, dan mengelola dokumen Purchase Order (PO) secara digital | **UC-02** (Manajemen Purchase Order) | Direct | PO management dengan status workflow (Pending → Partial → Completed) dan business rules validation |
| 5 | **F-05** | Sistem harus memungkinkan Admin Gudang untuk melakukan verifikasi akhir dan persetujuan terhadap catatan penerimaan barang yang dibuat oleh Checker | **UC-04** (Penerimaan Barang) | Direct | Sub-flow "Menverifikasi & Menyelesaikan Penerimaan Barang" dalam UC-04, trigger cascade updates |
| 6 | **F-06** | Sistem harus memungkinkan Checker untuk membuat catatan penerimaan barang secara digital saat barang fisik tiba | **UC-04** (Penerimaan Barang) | Direct | Sub-flow "Membuat Penerimaan Barang" dengan upload POD dan input quantities |
| 7 | **F-07** | Fungsionalitas pembuatan catatan penerimaan barang harus mewajibkan Checker untuk memilih atau merujuk pada data Delivery Order yang sudah ada di sistem | **UC-04** (Penerimaan Barang) | Include | UC-04 **include** "Melihat Delivery Order" untuk memastikan GR dibuat dari Shipment yang valid |
| 8 | **F-08** | Sistem harus menampilkan notifikasi real-time kepada Checker ketika ada Delivery Order baru yang dikirim oleh Supplier | **UC-04** (Penerimaan Barang) | Include | UC-04 **include** "Menerima Notifikasi Delivery Order" menggunakan Laravel Reverb WebSocket broadcasting |
| 9 | **F-09** | Sistem harus menyediakan fungsionalitas pengiriman pesan (messaging) untuk Checker | **UC-06** (Mengirim Pesan) | Direct | Messaging functionality menggunakan Wirechat package (Partial implementation: 36% coverage) |
| 10 | **F-10** | Sistem harus memungkinkan Supplier untuk membuat dan mengirimkan data Delivery Order secara digital | **UC-03** (Mengelola Delivery Order) | Direct | Shipment CRUD dengan status workflow (Draft → Shipped → Arrived → Processed) dan tenant isolation |
| 11 | **F-11** | Sistem harus memungkinkan Supplier untuk melihat daftar dan detail Purchase Order yang ditujukan kepada mereka | **UC-03** (Mengelola Delivery Order) | Include | UC-03 **include** "Melihat Purchase Order" dengan tenant filtering (supplier hanya lihat PO milik sendiri) |
| 12 | **F-12** | Sistem harus memungkinkan Supplier untuk melihat daftar Produk yang relevan bagi mereka | **UC-08** (Melihat Produk Supplier) | Direct | Use case terpisah untuk product catalog view (Not yet implemented) |
| 13 | **F-13** | Sistem harus menyediakan fungsionalitas pengiriman pesan (messaging) untuk Supplier | **UC-06** (Mengirim Pesan) | Direct | Messaging functionality menggunakan Wirechat package (Partial implementation: 36% coverage) |
| 14 | **F-14** | Sistem harus menyediakan fungsionalitas untuk membuat dan mengunduh Laporan Bulanan | **UC-07** (Membuat Laporan Bulanan) | Direct | 4 jenis laporan: Purchase Order, Goods Receipt, Stock, Financial dengan period filtering |
| 15 | **F-15** | Sistem harus secara otomatis mengagregasi data transaksi barang masuk yang telah terverifikasi untuk digunakan dalam pembuatan Laporan Bulanan | **UC-07** (Membuat Laporan Bulanan) | Dependency | Laporan Goods Receipt dan Financial menggunakan data dari UC-04 (cascade update setelah GR completion) |
| 16 | **F-16** | Sistem harus secara otomatis memperbarui jumlah stok di database setelah Admin Gudang menyelesaikan verifikasi penerimaan barang | **UC-04** (Penerimaan Barang) | System Event | Automatic stock update triggered oleh GR completion (method `increaseStock()` di Product model) |

**Summary Pemetaan:**

**Tabel 3.4: Summary Coverage Kebutuhan Fungsional**

| Use Case | Jumlah Requirement Ter-cover | Requirement IDs | Coverage Status |
|----------|------------------------------|-----------------|-----------------|
| UC-01: Mengelola Master Data | 3 | F-01, F-02, F-03 | ✅ 100% Implemented |
| UC-02: Manajemen Purchase Order | 1 | F-04 | ✅ 100% Implemented |
| UC-03: Mengelola Delivery Order | 2 | F-10, F-11 | ✅ 100% Implemented |
| UC-04: Penerimaan Barang | 4 | F-05, F-06, F-07, F-08, F-16 | ✅ 100% Implemented |
| UC-06: Mengirim Pesan | 2 | F-09, F-13 | ⚠️ 36% Implemented |
| UC-07: Membuat Laporan Bulanan | 2 | F-14, F-15 | ✅ 100% Implemented |
| UC-08: Melihat Produk Supplier | 1 | F-12 | ❌ 0% Implemented |
| **Total** | **16** | **F-01 hingga F-16** | **87.5% Implemented** |

---

### 3.3.4 Deskripsi Detail Use Case

#### 3.3.4.1 UC-01: Mengelola Master Data

**Tabel 3.5: Spesifikasi UC-01 - Mengelola Master Data**

| Atribut | Deskripsi |
|---------|-----------|
| **ID Use Case** | UC-01 |
| **Nama Use Case** | Mengelola Master Data |
| **Aktor Utama** | Admin Gudang |
| **Aktor Sekunder** | - |
| **Deskripsi** | Use case ini memungkinkan Admin Gudang untuk mengelola data master yang menjadi fondasi sistem, mencakup manajemen User, Supplier, dan Produk dengan operasi CRUD lengkap |
| **Precondition** | - Admin Gudang telah login ke sistem<br>- Admin Gudang memiliki akses ke Admin Panel |
| **Postcondition** | - Data master (User/Supplier/Produk) berhasil ditambahkan, diubah, atau dihapus<br>- Perubahan data tercatat dalam audit log |
| **Trigger** | Admin Gudang mengakses menu "Master Data" pada Admin Panel |
| **Requirements** | F-01 (Manajemen User), F-02 (Manajemen Supplier), F-03 (Manajemen Produk) |
| **Diagram Terkait** | Activity: `1-master-data-management.puml`<br>Sequence: `ManageUser`, `ManageSupplier`, `ManageProduct` |
| **Test Coverage** | 60 test cases (11 User + 12 Supplier + 37 Produk) dengan 100% coverage |

**Main Flow (Happy Path):**

1. Admin Gudang login ke sistem dan mengakses Admin Panel
2. Sistem menampilkan menu "Master Data" dengan pilihan: User, Supplier, Produk
3. Admin Gudang memilih jenis data yang akan dikelola
4. Sistem menampilkan halaman list data dengan tabel interaktif (search, filter, pagination)
5. Admin Gudang dapat melakukan:
   - **Create**: Klik "New" → Isi form → Validasi → Simpan
   - **Read/View**: Browse tabel atau detail view
   - **Update**: Klik "Edit" → Ubah data → Validasi → Simpan
   - **Delete**: Klik "Delete" → Konfirmasi → Hapus (hard/soft delete tergantung entitas)
6. Sistem menampilkan notifikasi success/error
7. Use case selesai

**Alternative Flow:**

- **AF-01: Validasi Gagal**
  - Jika input tidak valid (e.g., email duplicate, harga negatif), sistem menampilkan validation error
  - Admin Gudang memperbaiki input dan submit ulang

- **AF-02: Hapus User Aktif (Business Rule)**
  - Jika Admin Gudang mencoba menghapus user dengan `is_active = true`
  - Sistem block aksi dan tampilkan error: "User aktif tidak dapat dihapus"

- **AF-03: Auto-Generate Kode Supplier**
  - Saat create Supplier baru, sistem auto-generate `code` dengan format `SUP-{5-digit}`
  - Kode unique dan sequential

**Business Rules:**

1. **User**: Email harus unique, password minimal 8 karakter, user aktif tidak dapat dihapus
2. **Supplier**: Kode supplier auto-generated, soft delete digunakan untuk preserve referential integrity
3. **Produk**: Harga harus ≥ 0, stock tidak boleh negatif, memiliki reorder calculation logic

**Sub-Use Cases:**

UC-01 terdiri dari 3 sub-use case yang di-extend:

- **UC-01a: Manajemen Pengguna** (<<extend>>)
  - CRUD User dengan 4 role: Admin, Checker, Accounting, Supplier
  - Field: name, email, password, role, is_active
  - 11 test cases: viewing, search, create, update, toggle status, delete scenarios

- **UC-01b: Manajemen Supplier** (<<extend>>)
  - CRUD Supplier dengan soft delete functionality
  - Field: code (auto), name, email, phone, address, deleted_at
  - 12 test cases: CRUD operations, soft delete, restore, filtering

- **UC-01c: Manajemen Produk** (<<extend>>)
  - CRUD Produk dengan stock management dan analytics
  - Field: code, name, description, price, stock_quantity, minimum_stock, reorder_point, supplier_id
  - 37 test cases: CRUD, validation, stock operations, status detection, analytics calculations

---

#### 3.3.4.2 UC-02: Manajemen Purchase Order (PO)

**Tabel 3.6: Spesifikasi UC-02 - Manajemen Purchase Order**

| Atribut | Deskripsi |
|---------|-----------|
| **ID Use Case** | UC-02 |
| **Nama Use Case** | Manajemen Purchase Order (PO) |
| **Aktor Utama** | Admin Gudang |
| **Aktor Sekunder** | Checker (read-only access) |
| **Deskripsi** | Use case ini memungkinkan Admin Gudang untuk membuat, melihat, dan mengelola Purchase Order sebagai dokumen permintaan pembelian barang dari Supplier. PO memiliki status workflow dan business rules untuk maintain data integrity |
| **Precondition** | - Admin Gudang/Checker telah login<br>- Master data Supplier dan Produk sudah tersedia |
| **Postcondition** | - Purchase Order berhasil dibuat/diubah dengan nomor PO unique<br>- Status PO sesuai dengan workflow (Pending/Partial/Completed) |
| **Trigger** | Admin Gudang mengakses menu "Purchase Orders" pada App Panel |
| **Requirements** | F-04 (PO Management) |
| **Diagram Terkait** | Activity: `2-purchase-order-management.puml`<br>Sequence: `CreatePurchaseOrder`, `UpdatePurchaseOrder` |
| **Test Coverage** | 10 test cases (5 CRUD + 3 Business Rules + 2 RBAC) dengan 100% coverage |

**Main Flow (Happy Path):**

1. Admin Gudang mengakses halaman "Purchase Orders"
2. Sistem menampilkan list PO dengan filter (status, tanggal, supplier)
3. Admin Gudang klik "New Purchase Order"
4. Sistem auto-generate nomor PO dengan format `PO-YYYYMMDD-XXXX`
5. Admin Gudang:
   - Pilih Supplier dari dropdown
   - Set tanggal order
   - Add Products: untuk setiap produk, pilih product → input quantity → sistem auto-calculate total (qty × price)
6. Admin Gudang review total amount dan submit
7. Sistem validasi data dan simpan PO dengan status = "Pending"
8. Sistem tampilkan success notification
9. Use case selesai

**Alternative Flow:**

- **AF-01: Edit PO (Conditional)**
  - Admin dapat edit PO **hanya jika** status = "Pending"
  - Decision point: "Status != Pending?" → Block edit, tampilkan 403 Forbidden

- **AF-02: Update Status Otomatis (System-Driven)**
  - Status PO updated otomatis berdasarkan Goods Receipt:
    - Pending → Partial: Jika 0% < received < 100%
    - Pending/Partial → Completed: Jika received = 100%
  - Perubahan status di-trigger oleh UC-04 (GR completion)

- **AF-03: View-Only untuk Checker**
  - Checker dapat view PO list dan detail
  - Checker **tidak dapat** create, edit, atau delete PO
  - Policy enforcement: `PurchaseOrderPolicy::update()` dan `delete()` return false untuk Checker role

**Business Rules:**

1. **Status Workflow**:
   - Initial status: Pending
   - Transition: Pending → Partial (sebagian diterima) → Completed (semua diterima)
   - Status hanya dapat berubah via Goods Receipt completion (bukan manual edit)

2. **Edit Restriction**:
   - PO dengan status Partial/Completed tidak dapat di-edit
   - Rasionalisasi: Preserve data integrity dan audit trail

3. **Delete Restriction**:
   - PO yang sudah diproses (status != Pending) tidak dapat dihapus
   - Rasionalisasi: Prevent cascading data loss

4. **Calculation**:
   - Total amount = Σ (quantity × unit_price) untuk semua PO details
   - Auto-calculated, tidak dapat di-override manual

**Relationship dengan Use Case Lain:**

- **UC-02 → UC-04** (Dependency): Status PO di-update berdasarkan Goods Receipt (cascade update)
- **UC-02 → UC-03** (Reference): Supplier membuat Shipment berdasarkan PO
- **UC-02 → UC-07** (Data Source): PO data digunakan dalam laporan Purchase Order dan Financial

---

#### 3.3.4.3 UC-03: Mengelola Delivery Order

**Tabel 3.7: Spesifikasi UC-03 - Mengelola Delivery Order**

| Atribut | Deskripsi |
|---------|-----------|
| **ID Use Case** | UC-03 |
| **Nama Use Case** | Mengelola Delivery Order (Shipment) |
| **Aktor Utama** | Supplier |
| **Aktor Sekunder** | - |
| **Deskripsi** | Use case ini memungkinkan Supplier untuk membuat dan mengelola Delivery Order (Shipment) sebagai dokumen pengiriman barang ke gudang. Shipment memiliki status workflow dan tenant isolation untuk keamanan data |
| **Precondition** | - Supplier telah login ke Supplier Panel<br>- Purchase Order dari gudang sudah tersedia |
| **Postcondition** | - Shipment berhasil dibuat dengan status = "Draft"<br>- Setelah di-mark as shipped, status = "Shipped" dan trigger notifikasi ke Checker |
| **Trigger** | Supplier mengakses menu "Shipments" pada Supplier Panel |
| **Requirements** | F-10 (Create Delivery Order), F-11 (View PO) |
| **Diagram Terkait** | Activity: `3-delivery-order-management.puml`<br>Sequence: `CreateShipment`, `MarkAsShipped` |
| **Test Coverage** | 18 test cases (14 Shipment CRUD + 4 PO View) dengan 100% coverage termasuk tenant isolation |

**Main Flow (Happy Path):**

1. Supplier login ke Supplier Panel
2. Supplier mengakses halaman "Shipments"
3. Sistem menampilkan list Shipment milik Supplier tersebut (tenant filtering otomatis)
4. Supplier klik "New Shipment"
5. Supplier:
   - Pilih Purchase Order dari dropdown (hanya PO yang ditujukan ke Supplier ini)
   - Set shipping date
   - Add Products dari PO yang dipilih (auto-populate dari PO details)
   - Input quantity untuk setiap product
6. Supplier submit
7. Sistem validasi dan simpan Shipment dengan status = "Draft"
8. Supplier dapat edit Shipment selama status = "Draft"
9. Ketika siap dikirim, Supplier klik "Mark as Shipped"
10. Sistem:
    - Update status Shipment = "Shipped"
    - Trigger notifikasi real-time ke Checker (via Laravel Reverb WebSocket)
    - Block edit/delete operations (shipment sudah dikirim, immutable)
11. Use case selesai

**Sub-Use Case (Include):**

**UC-03a: Melihat Purchase Order** (<<include>>)
- Supplier dapat view list PO yang ditujukan kepada mereka
- **Tenant Isolation**: Query otomatis filter berdasarkan `purchase_orders.supplier_id = current_supplier_id`
- Supplier **tidak dapat** melihat PO dari supplier lain (security critical)
- View-only access (tidak ada edit/delete)

**Alternative Flow:**

- **AF-01: State Transition - Mark as Shipped**
  - Saat Supplier mark shipment as shipped:
    - Status: Draft → Shipped
    - Edit button hilang dari UI
    - Delete action disabled
    - Shipment menjadi immutable

- **AF-02: Automatic State Transitions (System-Driven)**
  - Shipped → Arrived: Otomatis saat Checker create Goods Receipt
  - Arrived → Processed: Otomatis saat Admin complete Goods Receipt
  - Transitions ini di-trigger oleh UC-04

- **AF-03: Tenant Isolation Enforcement**
  - Supplier A mencoba akses shipment milik Supplier B
  - Sistem return 404 Not Found (tidak expose existence untuk security)
  - Policy: `ShipmentPolicy::view()` check `shipment->supplier_id == auth()->user()->supplier_id`

**Business Rules:**

1. **Tenant Isolation**:
   - Supplier hanya dapat view/edit/delete shipment milik mereka sendiri
   - Auto-filtering di query level dan policy level
   - 404 response untuk unauthorized access (prevent information leakage)

2. **Status Workflow**:
   - Draft → Shipped (manual, via "Mark as Shipped" button)
   - Shipped → Arrived (automatic, via GR creation)
   - Arrived → Processed (automatic, via GR completion)
   - Transition kembali (reverse) tidak diperbolehkan

3. **Edit/Delete Restrictions**:
   - Shipment dengan status != "Draft" tidak dapat di-edit atau di-delete
   - Rasionalisasi: Once shipped, shipment represents physical goods in transit, immutable

4. **Notification**:
   - Saat shipment di-mark as shipped, sistem broadcast event `ShipmentShipped`
   - Checker menerima real-time notification via WebSocket

**Relationship dengan Use Case Lain:**

- **UC-03 → UC-02** (Reference): Shipment dibuat berdasarkan Purchase Order
- **UC-03 → UC-04** (Trigger): Shipment yang shipped menjadi basis untuk Goods Receipt creation
- **UC-03 include UC-03a** (Melihat PO): Mandatory untuk select PO saat create shipment

---

#### 3.3.4.4 UC-04: Penerimaan Barang

**Tabel 3.8: Spesifikasi UC-04 - Penerimaan Barang**

| Atribut | Deskripsi |
|---------|-----------|
| **ID Use Case** | UC-04 |
| **Nama Use Case** | Penerimaan Barang (Goods Receipt) |
| **Aktor Utama** | Checker (create), Admin Gudang (verify & complete) |
| **Aktor Sekunder** | - |
| **Deskripsi** | Use case paling kompleks yang melibatkan cascade updates ke multiple tables. Checker membuat catatan penerimaan barang saat barang fisik tiba, kemudian Admin Gudang melakukan verifikasi akhir yang trigger stock update, PO status update, dan shipment status update secara atomic |
| **Precondition** | - Shipment dengan status "Shipped" sudah tersedia<br>- Checker/Admin telah login ke App Panel |
| **Postcondition** | - Goods Receipt berhasil dibuat dengan status "Pending"<br>- Setelah di-complete oleh Admin: stock updated, PO status updated, shipment status = "Processed" |
| **Trigger** | Checker menerima notifikasi bahwa ada shipment baru (from UC-03), atau navigate ke shipment list |
| **Requirements** | F-06 (Create GR), F-07 (Reference DO), F-08 (Notifikasi), F-05 (Verify GR), F-16 (Auto Stock Update) |
| **Diagram Terkait** | Activity: `4-goods-receipt-creation.puml`, `5-goods-receipt-verification.puml`<br>Sequence: `CreateGoodsReceipt`, `CompleteGoodsReceipt` |
| **Test Coverage** | 13 test cases termasuk integration tests untuk cascade updates dan transaction integrity |

**Main Flow (Happy Path - Part 1: Checker Creates GR):**

1. Checker login dan navigate ke "Shipments" page
2. Sistem tampilkan list shipment dengan status "Shipped" (siap diterima)
3. Checker pilih shipment dan klik "Buat Penerimaan Barang"
4. Sistem:
   - Auto-generate GRN (Goods Receipt Number) dengan format `GRN-YYYYMMDD-XXXX`
   - Auto-populate data dari Shipment (supplier, PO reference, products)
   - Update Shipment status: Shipped → Arrived
5. Checker untuk setiap product:
   - Upload POD (Proof of Delivery) - dokumen bukti penerimaan
   - Input `qty_ordered` (dari PO)
   - Input `qty_received` (actual diterima dalam kondisi baik)
   - Input `qty_rejected` (ditolak karena rusak/tidak sesuai)
   - Input `rejection_reason` (jika ada rejected)
6. Checker submit form
7. Sistem validasi data dan simpan GR dengan status = "Pending"
8. Sistem tampilkan success notification
9. **GR menunggu verifikasi Admin** (belum update stock)

**Main Flow (Part 2: Admin Verifies & Completes GR):**

10. Admin Gudang navigate ke "Goods Receipts" page
11. Sistem tampilkan list GR dengan status "Pending"
12. Admin pilih GR untuk di-review
13. Admin verifikasi data:
    - Check POD yang di-upload Checker
    - Validasi quantities
    - Review rejection reasons (jika ada)
14. Admin klik "Selesaikan Penerimaan"
15. Sistem tampilkan konfirmasi modal
16. Admin confirm
17. **Sistem execute cascade updates dalam DATABASE TRANSACTION** (atomic):
    - Update GR status: Pending → Completed
    - Update Product stock: `stock_quantity += qty_received` (via `increaseStock()` method)
    - Create StockMovement record (audit trail)
    - Update PO Detail: `quantity_received += qty_received`
    - Update PO status via `updateStatus()` logic:
      - Jika total received < total ordered (0% < % < 100%) → status = "Partial"
      - Jika total received = total ordered (100%) → status = "Completed"
    - Update Shipment status: Arrived → Processed
18. Jika **ANY step fails**, rollback ALL changes (transaction integrity)
19. Sistem tampilkan success notification
20. Use case selesai

**Sub-Use Case (Include):**

**UC-04a: Melihat Delivery Order** (<<include>>)
- Checker harus pilih Shipment sebagai referensi untuk create GR
- Mandatory relationship

**UC-04b: Menerima Notifikasi Delivery Order** (<<include>>)
- Real-time notification via WebSocket saat Supplier mark shipment as shipped
- Event: `ShipmentShipped` broadcast ke channel `warehouse.notifications`
- Checker menerima notifikasi dengan info: shipment number, supplier name, expected arrival

**Alternative Flow:**

- **AF-01: Partial Receipt**
  - Jika `qty_received < qty_ordered`
  - Barang diterima sebagian (sisanya akan datang di shipment berikutnya)
  - PO status akan = "Partial" setelah GR completion

- **AF-02: Full Rejection**
  - Jika `qty_received = 0` dan `qty_rejected = qty_ordered`
  - Semua barang ditolak (rusak/tidak sesuai)
  - PO status tetap "Pending" (belum ada yang diterima)
  - Supplier perlu kirim shipment replacement

- **AF-03: Transaction Rollback**
  - Saat Admin complete GR, jika ANY database operation fails (e.g., product_id tidak exist)
  - Sistem rollback ALL changes untuk maintain consistency
  - Error message ditampilkan ke Admin
  - GR status tetap "Pending", Admin dapat re-try setelah fix issue

**Business Rules:**

1. **Cascade Update Atomicity**:
   - Semua updates (GR, Product stock, PO, Shipment) happen dalam **satu transaction**
   - All-or-nothing: Jika salah satu gagal, semua di-rollback
   - Critical untuk data consistency

2. **Stock Update Formula**:
   - `new_stock = current_stock + qty_received`
   - Hanya qty_received yang di-count (qty_rejected tidak masuk stok)

3. **PO Status Logic** (method `updateStatus()` di PurchaseOrder model):
   ```
   percentage_received = (total_qty_received / total_qty_ordered) × 100%

   if percentage_received = 0%        → status = "Pending"
   if 0% < percentage_received < 100% → status = "Partial"
   if percentage_received = 100%      → status = "Completed"
   ```

4. **GR Immutability**:
   - Setelah status = "Completed", GR tidak dapat di-edit atau di-delete
   - Rasionalisasi: GR adalah legal document untuk audit

5. **POD Requirement**:
   - Upload POD adalah **mandatory** untuk create GR
   - POD serve sebagai bukti legal penerimaan barang

**Relationship dengan Use Case Lain:**

- **UC-04 include UC-03** (Melihat Delivery Order): Mandatory untuk create GR
- **UC-04 include UC-04b** (Notifikasi): Real-time alert untuk Checker
- **UC-04 → UC-02** (Update): Cascade update PO status
- **UC-04 → UC-03** (Update): Cascade update Shipment status
- **UC-04 → UC-01** (Update): Cascade update Product stock
- **UC-04 → UC-07** (Data Source): GR data digunakan dalam laporan Goods Receipt dan Financial

---

#### 3.3.4.5 UC-05: Autentikasi Multi-Panel

**Tabel 3.9: Spesifikasi UC-05 - Autentikasi Multi-Panel**

| Atribut | Deskripsi |
|---------|-----------|
| **ID Use Case** | UC-05 |
| **Nama Use Case** | Autentikasi Multi-Panel |
| **Aktor Utama** | Semua Aktor (Admin Gudang, Checker, Accounting, Supplier) |
| **Aktor Sekunder** | - |
| **Deskripsi** | Cross-cutting concern yang memastikan setiap user hanya dapat login dan mengakses panel yang sesuai dengan role mereka. Menggunakan Filament v4 multi-panel architecture dengan strict authorization enforcement |
| **Precondition** | User memiliki akun aktif dalam sistem (`is_active = true`) |
| **Postcondition** | User berhasil login ke panel yang sesuai dan diarahkan ke dashboard |
| **Trigger** | User mengakses URL panel (e.g., `/admin`, `/app`, `/supplier`) |
| **Requirements** | Cross-cutting (semua use case memerlukan authentication) |
| **Diagram Terkait** | Sequence: `MultiPanelAuthentication` |
| **Test Coverage** | 16 test cases dengan exhaustive matrix testing (role × panel combinations) |

**Access Control Matrix:**

**Tabel 3.10: Access Control Matrix per Role**

| User Role | Admin Panel (`/admin`) | App Panel (`/app`) | Supplier Panel (`/supplier`) |
|-----------|----------------------|-------------------|---------------------------|
| **Admin Gudang** | ✅ Allow | ✅ Allow | ❌ Deny (403 Forbidden) |
| **Checker** | ❌ Deny (403 Forbidden) | ✅ Allow | ❌ Deny (403 Forbidden) |
| **Accounting** | ❌ Deny (403 Forbidden) | ✅ Allow | ❌ Deny (403 Forbidden) |
| **Supplier** | ❌ Deny (403 Forbidden) | ❌ Deny (403 Forbidden) | ✅ Allow |
| **Inactive User** | ❌ Deny (Login Failed) | ❌ Deny (Login Failed) | ❌ Deny (Login Failed) |

**Main Flow:**

1. User navigate ke panel URL (e.g., `admin.warelink.test/admin`)
2. Sistem check apakah user sudah authenticated
3. Jika belum, redirect ke login page panel tersebut
4. User input credentials (email + password)
5. Sistem validasi credentials:
   - Check email exist
   - Verify password (hashed with bcrypt)
   - Check `is_active = true`
6. Sistem check authorization via `canAccessPanel()` method:
   - Admin Panel: `$user->role === UserRole::Admin`
   - App Panel: `in_array($user->role, [UserRole::Admin, UserRole::Checker, UserRole::Accounting])`
   - Supplier Panel: `$user->role === UserRole::Supplier`
7. Jika authorized, create session dan redirect ke dashboard
8. Jika not authorized, tampilkan 403 Forbidden page
9. Use case selesai

**Alternative Flow:**

- **AF-01: Invalid Credentials**
  - Jika email/password salah, tampilkan error "Invalid credentials"
  - User dapat retry

- **AF-02: Inactive Account**
  - Jika `is_active = false`, block login dengan message "Account inactive"
  - Contact admin untuk aktivasi

- **AF-03: Unauthorized Panel Access**
  - Jika user authenticated tapi try access panel yang tidak sesuai role
  - Return 403 Forbidden page
  - Log unauthorized access attempt untuk security audit

**Business Rules:**

1. **Least Privilege Principle**:
   - Setiap role hanya dapat akses panel yang minimal necessary untuk pekerjaannya

2. **Multi-Panel Separation**:
   - Admin Gudang dapat akses 2 panel (Admin + App) untuk flexibility
   - Internal users (Checker, Accounting) hanya akses App Panel
   - External users (Supplier) hanya akses Supplier Panel dengan strict tenant isolation

3. **Session Management**:
   - Session timeout: 120 minutes inactivity
   - Concurrent sessions diperbolehkan (same user bisa login di multiple devices)

4. **Password Policy**:
   - Minimum 8 characters
   - Hashed dengan bcrypt (cost factor: 12)

---

#### 3.3.4.6 UC-06: Mengirim Pesan

**Tabel 3.11: Spesifikasi UC-06 - Mengirim Pesan**

| Atribut | Deskripsi |
|---------|-----------|
| **ID Use Case** | UC-06 |
| **Nama Use Case** | Mengirim Pesan (Messaging) |
| **Aktor Utama** | Checker, Supplier |
| **Aktor Sekunder** | Admin Gudang (dapat berpartisipasi dalam chat) |
| **Deskripsi** | Use case ini menyediakan fungsionalitas messaging real-time menggunakan Wirechat package untuk komunikasi antara internal team (Checker) dan external stakeholders (Supplier) |
| **Precondition** | User telah login ke panel masing-masing (App Panel untuk Checker, Supplier Panel untuk Supplier) |
| **Postcondition** | Pesan berhasil terkirim dan diterima secara real-time oleh recipient |
| **Trigger** | User mengakses menu "Chat" atau "Messages" |
| **Requirements** | F-09 (Messaging untuk Checker), F-13 (Messaging untuk Supplier) |
| **Diagram Terkait** | Activity: `8-messaging.puml`<br>Sequence: `SendMessage`, `ReceiveMessage` |
| **Test Coverage** | ⚠️ 5 test cases (36% coverage) - Partial implementation, 9 additional tests planned |

**Current Implementation (Basic Functionality):**

1. User navigate ke Chat page
2. Sistem load Wirechat Livewire component
3. User dapat:
   - View existing chats/conversations
   - Select conversation untuk view messages
   - Send text message
4. Message tersimpan di database
5. Sistem broadcast message via Laravel Reverb WebSocket
6. Recipient menerima message real-time (jika online)

**Planned Features (Not Yet Tested - 64% Gap):**

- **Real-time Delivery**: WebSocket broadcasting dengan Laravel Reverb
- **Read Receipts**: Track kapan message dibaca
- **Unread Counter**: Badge notification untuk unread messages
- **Private vs Group Chat**: Support untuk 1-on-1 dan group conversations
- **Permission Checks**: `canCreateChats()`, `canCreateGroups()` policies
- **Typing Indicators**: Show "X is typing..." indicator
- **File Attachments**: Upload dan share files dalam chat

**Status Implementasi:**

**Tabel 3.12: Status Implementasi UC-06**

| Feature | Status | Test Coverage | Keterangan |
|---------|--------|---------------|------------|
| Basic Chat Access | ✅ Implemented | ✅ Tested | User dapat akses chat page |
| Component Loading | ✅ Implemented | ✅ Tested | Wirechat component load correctly |
| Authentication Check | ✅ Implemented | ✅ Tested | Hanya authenticated user dapat akses |
| UI Integration | ✅ Implemented | ✅ Tested | Chat integrated dalam panel layout |
| Send Text Message | ✅ Implemented | ⚠️ Partially Tested | Basic functionality working |
| Real-time Delivery | ⚠️ Partial | ❌ Not Tested | WebSocket integration perlu testing |
| Read Receipts | ❌ Not Implemented | ❌ Not Tested | Planned untuk next sprint |
| Unread Counter | ❌ Not Implemented | ❌ Not Tested | Planned untuk next sprint |
| Group Chats | ❌ Not Implemented | ❌ Not Tested | Future enhancement |

**Recommendation:**

Use case ini merupakan **technical debt** yang perlu di-address untuk meningkatkan coverage dari 36% ke target 90%+. Prioritas medium karena messaging bukan core business process, tapi tetap penting untuk komunikasi operasional.

---

#### 3.3.4.7 UC-07: Membuat Laporan Bulanan

**Tabel 3.13: Spesifikasi UC-07 - Membuat Laporan Bulanan**

| Atribut | Deskripsi |
|---------|-----------|
| **ID Use Case** | UC-07 |
| **Nama Use Case** | Membuat Laporan Bulanan |
| **Aktor Utama** | Accounting |
| **Aktor Sekunder** | - |
| **Deskripsi** | Use case ini menyediakan fungsionalitas comprehensive reporting dengan 4 jenis laporan berbeda, period filtering, dan dynamic table switching untuk analisis data procurement dan inventory |
| **Precondition** | - Accounting telah login ke App Panel<br>- Data transaksi (PO, GR, Stock) tersedia dalam sistem |
| **Postcondition** | Laporan berhasil di-generate dan ditampilkan sesuai dengan period dan report type yang dipilih |
| **Trigger** | Accounting mengakses menu "Monthly Report" pada App Panel |
| **Requirements** | F-14 (Generate Laporan), F-15 (Auto-aggregate data) |
| **Diagram Terkait** | Activity: `6-monthly-report-generation.puml`<br>Sequence: `GenerateReport` |
| **Test Coverage** | 19 test cases (100% coverage) mencakup 4 report types dengan various scenarios |

**Jenis Laporan (4 Report Types):**

**Tabel 3.14: Jenis Laporan yang Tersedia**

| Report Type | Deskripsi | Columns | Filter | Business Logic |
|-------------|-----------|---------|--------|----------------|
| **Purchase Orders** | Laporan transaksi PO dalam periode tertentu | po_number, supplier, order_date, status, total_amount, notes | Date range (start_date, end_date) | Aggregate total_amount per periode |
| **Goods Receipts** | Laporan penerimaan barang yang terverifikasi | grn_number, shipment_number, supplier, receipt_date, status, qty_received, qty_rejected | Date range (start_date, end_date) | Hanya GR dengan status "Completed" |
| **Stock** | Laporan inventory current stock levels | product_code, product_name, supplier, stock_quantity, minimum_stock, status | **No date filter** (snapshot saat ini) | Sort by stock_quantity ASC (low stock first) |
| **Financial** | Laporan analisis keuangan PO vs actual received | po_number, supplier, total_amount (ordered), received_value (actual), outstanding (sisa) | Status filter (Partial, Completed) | outstanding = total_amount - received_value |

**Main Flow:**

1. Accounting login dan navigate ke "Monthly Report" page
2. Sistem tampilkan default view dengan report type "Purchase Orders" dan period "Current Month"
3. Accounting dapat customize:
   - **Select Report Type**: Dropdown (PO / GR / Stock / Financial)
   - **Set Period**: Date picker untuk start_date dan end_date (kecuali Stock report)
4. Accounting klik "Generate" atau "Filter"
5. Sistem:
   - Validasi input (e.g., end_date >= start_date)
   - Query database sesuai report type dan filters
   - Aggregate data (e.g., calculate totals, outstanding amounts)
   - Render table dengan columns sesuai report type
6. Sistem tampilkan hasil dalam interactive table (dengan sort, pagination)
7. Accounting dapat:
   - View detail records
   - Change period dan re-generate
   - Switch report type (dynamic table content changes)
8. Use case selesai

**Alternative Flow:**

- **AF-01: Invalid Date Range**
  - Jika end_date < start_date, tampilkan validation error
  - User correct input dan re-submit

- **AF-02: No Data Found**
  - Jika query return 0 records, tampilkan empty state dengan message
  - "No records found for the selected period"

- **AF-03: Export Functionality (Future Enhancement)**
  - **Not yet implemented** (identified dalam Beta Testing sebagai P1 priority)
  - User request: Export to Excel (.xlsx) dan PDF untuk archival
  - Planned untuk post-deployment sprint

**Business Rules:**

1. **Period Filtering**:
   - Default period: Current month (1st to last day)
   - User dapat custom select any date range
   - Stock report **tidak memiliki** period filter (always show current snapshot)

2. **Report Type Switching**:
   - Dynamic table: Columns dan data source berubah sesuai selected report type
   - Seamless switching tanpa page reload (Livewire reactive)

3. **Stock Report Sorting**:
   - Default sort: `stock_quantity` ASC
   - Rasionalisasi: Produk dengan stock rendah appear first untuk facilitate reorder decision

4. **Financial Report Logic**:
   - Hanya PO dengan status "Partial" atau "Completed" yang muncul
   - Outstanding calculation: Total ordered - Total received value
   - Membantu identify PO dengan pending deliveries

**Authorization:**

- **RBAC**: Hanya Accounting role yang dapat akses Monthly Report page
- Policy: `MonthlyReportPolicy::viewAny()` return true hanya untuk Accounting
- Non-Accounting users mendapat 403 Forbidden jika attempt access

**Relationship dengan Use Case Lain:**

- **UC-07 ← UC-02** (Data Source): PO data untuk Purchase Order Report dan Financial Report
- **UC-07 ← UC-04** (Data Source): GR data untuk Goods Receipt Report, aggregate untuk F-15
- **UC-07 ← UC-01** (Data Source): Product dan Supplier data untuk Stock Report

---

#### 3.3.4.8 UC-08: Melihat Produk (Supplier)

**Tabel 3.15: Spesifikasi UC-08 - Melihat Produk Supplier**

| Atribut | Deskripsi |
|---------|-----------|
| **ID Use Case** | UC-08 |
| **Nama Use Case** | Melihat Produk (Supplier View) |
| **Aktor Utama** | Supplier |
| **Aktor Sekunder** | - |
| **Deskripsi** | Use case ini menyediakan read-only product catalog view untuk Supplier, memungkinkan mereka untuk melihat daftar produk yang relevan dengan tenant isolation |
| **Precondition** | Supplier telah login ke Supplier Panel |
| **Postcondition** | Supplier dapat view product list dengan filtering dan statistics |
| **Trigger** | Supplier mengakses menu "Products" pada Supplier Panel |
| **Requirements** | F-12 (View Product untuk Supplier) |
| **Diagram Terkait** | Activity: `7-view-product.puml` (sudah dirancang) |
| **Test Coverage** | ❌ 0% - **Not Yet Implemented** |

**Planned Features:**

1. **Product List View**:
   - Tampilkan list products yang associated dengan Supplier tersebut
   - Tenant filtering: `products.supplier_id = current_supplier_id`
   - Read-only (no create/edit/delete actions untuk Supplier)

2. **Filtering & Search**:
   - Search by product code atau name
   - Filter by stock status (In Stock / Low Stock / Out of Stock)
   - Sort by various columns

3. **Product Statistics**:
   - Total products from this supplier
   - Average stock level
   - Products requiring reorder

4. **Detail View**:
   - View detailed product information
   - See current stock level
   - View pending orders for this product

**Status:**

❌ **Not Implemented** - Planned untuk future sprint

**Recommendation:**

Prioritas Low-Medium. Implementasi estimated 8-12 test cases untuk coverage yang adequate.

---

### 3.3.5 Relationship Antar Use Case

**Gambar 3.2: Use Case Relationship Diagram**

```
UC-01 (Master Data)
  │
  ├─→ UC-02 (PO Management)
  │     │
  │     └─→ UC-03 (Delivery Order)
  │           │
  │           └─→ UC-04 (Goods Receipt)
  │                 │
  │                 ├─→ UC-02 (update PO status)
  │                 ├─→ UC-03 (update Shipment status)
  │                 ├─→ UC-01 (update Product stock)
  │                 └─→ UC-07 (data source)
  │
  ├─→ UC-07 (Monthly Report)
  │
  └─→ UC-08 (View Product Supplier)

UC-05 (Multi-Panel Auth) ──→ ALL USE CASES (cross-cutting)

UC-06 (Messaging) ←──→ UC-03, UC-04 (supporting communication)
```

**Tabel 3.16: Include & Extend Relationships**

| Use Case | Relationship Type | Related Use Case | Deskripsi |
|----------|------------------|------------------|-----------|
| UC-01 | **extend** | UC-01a (Manajemen User) | Optional sub-use case |
| UC-01 | **extend** | UC-01b (Manajemen Supplier) | Optional sub-use case |
| UC-01 | **extend** | UC-01c (Manajemen Produk) | Optional sub-use case |
| UC-04 | **include** | UC-03 (Melihat Delivery Order) | Mandatory - GR harus reference Shipment |
| UC-04 | **include** | UC-04b (Menerima Notifikasi DO) | Mandatory - Real-time notification |
| UC-03 | **include** | UC-03a (Melihat PO) | Mandatory - Shipment harus reference PO |

**Dependency & Data Flow:**

1. **Master Data Foundation**: UC-01 provides foundational data (User, Supplier, Product) untuk semua use case lain
2. **Procurement Flow**: UC-02 → UC-03 → UC-04 merepresentasikan linear workflow procurement process
3. **Cascade Updates**: UC-04 (GR completion) trigger updates ke UC-02 (PO status), UC-03 (Shipment status), dan UC-01 (Product stock)
4. **Reporting Aggregation**: UC-07 aggregate data dari UC-02 (PO), UC-04 (GR), dan UC-01 (Product) sesuai F-15

---

### 3.3.6 Kesimpulan Perancangan Use Case

Perancangan use case untuk Sistem Informasi Warelink telah menghasilkan **8 use case utama** yang comprehensively cover **16 kebutuhan fungsional** (F-01 hingga F-16) dengan **100% requirement traceability**.

**Key Achievements:**

1. **Complete Requirement Coverage**: Semua 16 kebutuhan fungsional ter-map ke use case dengan jelas
2. **Systematic Organization**: Use case terorganisir berdasarkan aktor dan business process flow
3. **Multi-Panel Architecture**: Separation of concerns via 3 panels (Admin, App, Supplier) dengan strict RBAC
4. **Implementation Progress**: 87.5% use case telah diimplementasikan dan tested

**Implementation Status Summary:**

- ✅ **6 Use Cases (75%)**: Full implementation dengan 100% test coverage
- ⚠️ **1 Use Case (12.5%)**: Partial implementation dengan 36% coverage (UC-06 Messaging)
- ❌ **1 Use Case (12.5%)**: Not yet implemented (UC-08 View Product Supplier)

Perancangan use case ini menjadi **foundation** untuk perancangan diagram UML detail (Activity, Sequence, Class Diagram) dan **test case design** yang akan dijelaskan pada sub-bab berikutnya, memastikan bahwa sistem yang dibangun benar-benar memenuhi kebutuhan stakeholders dengan quality assurance yang kuat melalui Test-Driven Development approach.
