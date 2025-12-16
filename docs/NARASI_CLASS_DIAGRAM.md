# BAB III (Lanjutan)
# PERANCANGAN SISTEM

## 3.5 Perancangan Class Diagram

### 3.5.1 Pengantar Class Diagram

Class diagram merupakan representasi struktural dari desain berorientasi objek sistem Warelink yang menggambarkan entitas-entitas utama, atribut-atributnya, operasi/method yang dimiliki, serta relasi antar class. Diagram ini menjadi blueprint teknis yang memetakan konsep dari domain model ke struktur kode aktual menggunakan Laravel Framework dengan Eloquent ORM sebagai implementation layer.

Perancangan class diagram dalam penelitian ini mengikuti prinsip **Domain-Driven Design (DDD)** dimana setiap class merepresentasikan entitas bisnis yang jelas dengan **Single Responsibility Principle**. Peneliti mengidentifikasi 9 class utama yang membentuk core domain model sistem, 3 enumeration untuk mendefinisikan state yang valid, serta berbagai relationship (one-to-many, one-to-one, many-to-one) yang menggambarkan dependency dan association antar entitas.

Class diagram dirancang dengan mempertimbangkan **data integrity**, **referential integrity**, dan **business rules enforcement** melalui constraint, validation, dan encapsulation logic dalam method. Setiap class dilengkapi dengan scope methods untuk query filtering, helper methods untuk business logic, dan relationship methods yang mengikuti konvensi Laravel Eloquent untuk memastikan maintainability dan testability.

---

### 3.5.2 Enumeration Classes

Sistem Warelink menggunakan empat enumeration sebagai value object untuk mendefinisikan state yang valid dan terbatas pada beberapa domain concept. Enumeration di-implement menggunakan PHP 8.1+ native enum dengan backing value berupa string untuk database persistence dan readability.

**UserRole Enumeration**

Enumeration ini mendefinisikan empat role pengguna dalam sistem: Admin, Accounting, Checker, dan Supplier. Role ini menjadi basis dari **Role-Based Access Control (RBAC)** yang menentukan authorization dan panel access setiap user. Implementation menggunakan backed enum yang disimpan sebagai string di database pada kolom `users.role`.

**PurchaseOrderStatus Enumeration**

Mendefinisikan lifecycle status Purchase Order dengan empat state: Pending (PO baru dibuat), Partial (sebagian barang sudah diterima), Completed (semua barang sudah diterima lengkap), dan Cancelled (PO dibatalkan). Status ini mengatur state transition dan menentukan operasi apa yang diizinkan pada PO (edit hanya pada status Pending, misalnya).

**ShipmentStatus Enumeration**

Mendefinisikan workflow pengiriman barang dengan empat tahap: Draft (DO dibuat supplier tapi belum dikirim), Shipped (barang dalam perjalanan), Arrived (barang sudah sampai gudang), dan Processed (shipment sudah diproses lengkap). Status ini men-trigger notification dan menentukan visibility Shipment untuk aktor yang berbeda.

**GoodsReceiptStatus Enumeration**

Mendefinisikan tahapan verifikasi penerimaan barang dengan tiga status: Pending (GR baru dibuat oleh Checker), Verified (sudah diverifikasi tapi belum final), dan Completed (final approval, trigger stock update). Status Completed menjadi gate untuk cascade update ke Product stock, PO status, dan Shipment status.

---

### 3.5.3 User Class

**Responsibility:** Merepresentasikan pengguna sistem dengan multi-role capability dan tenant-based access control untuk Supplier users.

**Atribut Utama:** User memiliki atribut identitas standar (id, name, email, phone), atribut autentikasi (password, email_verified_at), atribut authorization (role dengan tipe UserRole enum, is_active boolean flag), atribut tenant isolation (supplier_id yang nullable untuk link Supplier user ke Supplier entity), serta timestamp standar Laravel (created_at, updated_at).

**Relationship:** User memiliki relationship **one-to-many** dengan Supplier melalui method `supplier()` yang return BelongsTo untuk Supplier users, dan **one-to-many** dengan GoodsReceipt sebagai receiver/creator. User juga memiliki relationship dengan Filament Panel dan Collection (interface dari Filament untuk multi-tenancy).

**Key Methods:** Method `scopeSupplier()` adalah query scope untuk filtering user berdasarkan supplier tertentu. Method checker seperti `isActive()`, `isAdmin()`, `isAccounting()`, `isSupplier()` digunakan untuk authorization check. Method `canAccessTenant()`, `getTenants()`, dan `canAccessPanel()` mengimplementasikan Filament multi-tenancy contract untuk tenant isolation. Method `canCreateChats()` dan `canCreateGroups()` menentukan messaging permission.

**Business Rules:** User dengan role Supplier wajib memiliki supplier_id (not null), user aktif (is_active = true) diperlukan untuk login, dan email harus unique across seluruh users. User implement Laravel Authenticatable contract untuk authentication dan Filament HasTenant contract untuk multi-panel access.

---

### 3.5.4 Supplier Class

**Responsibility:** Merepresentasikan entitas supplier sebagai pihak eksternal yang memasok produk ke gudang, dengan soft delete capability untuk preserve data history.

**Atribut Utama:** Supplier memiliki identitas bisnis berupa code (unique, auto-generated format SUP-xxxxx), name, contact information (email, phone, address, city, country), tax_number untuk compliance, is_active flag untuk enable/disable supplier, dan timestamp termasuk deleted_at untuk soft delete implementation.

**Relationship:** Supplier memiliki **one-to-many** relationship dengan PurchaseOrder melalui method `purchaseOrders()`, dengan Shipment melalui `shipments()`, dengan Product melalui `products()`, dan dengan User melalui `users()` untuk Supplier panel users. Semua relationship menggunakan HasMany yang indicate ownership.

**Key Methods:** Method `isActive()` untuk check active status. Query scope `active()` untuk filtering hanya supplier aktif. Private method `generateSupplierCode()` untuk auto-generate kode supplier dengan format yang konsisten dan unique menggunakan incrementing number dengan prefix SUP.

**Business Rules:** Kode supplier auto-generated saat creation dan immutable, email dan tax_number harus unique jika diisi, soft delete digunakan untuk prevent data loss ketika supplier dihapus (referential integrity dengan PO dan Product yang sudah exist), dan supplier tidak aktif tidak bisa dipilih untuk PO baru tetapi data historical tetap accessible.

---

### 3.5.5 Product Class

**Responsibility:** Merepresentasikan produk/barang yang disupply oleh supplier dengan comprehensive stock management logic dan analytics capabilities.

**Atribut Utama:** Product memiliki product_code (unique, auto-generated based on supplier code), name, description, unit (satuan seperti PCS, BOX, CARTON), stock_quantity (current stock level), minimum_stock (threshold untuk reorder alert), supplier_id (foreign key ke Supplier), is_active flag, dan soft delete capability (deleted_at).

**Relationship:** Product memiliki **one-to-many** relationship dengan PurchaseOrderDetail, ShipmentDetail, dan GoodsReceiptDetail yang menunjukkan bahwa satu produk bisa muncul di banyak transaksi. Product juga memiliki **many-to-one** relationship dengan Supplier melalui `supplier()` BelongsTo.

**Key Methods:** Class ini memiliki rich set of query scopes untuk filtering: `active()`, `lowStock()`, `outOfStock()`, `goodStock()`, `needsReorder()`, dan `bySupplier()`. Method untuk stock operations include `increaseStock()` (dipanggil saat GR completion), `decreaseStock()` (untuk stock adjustment), `setStock()` (set absolute value), `hasSufficientStock()`, dan `reserveStock()`. Analytics methods meliputi `getTotalReordered()`, `getTotalReceived()`, `getTotalRejected()`, `getAcceptanceRate()` (percentage produk diterima vs rejected), `getPendingQuantity()`, `getProjectedStock()`, dan `getSummary()` yang return comprehensive product statistics. Helper methods `isNeedsReorder()`, `suggestedReorderQuantity()`, `reorderUrgency()` mendukung intelligent reorder recommendation. Private method `logStockMovement()` mencatat semua pergerakan stock untuk audit trail, dan `generateProductCode()` untuk auto-generate kode produk.

**Business Rules:** Stock quantity tidak boleh negatif (enforced by validation dan stock operations), setiap perubahan stock harus disertai reason dan dicatat dalam stock movement log untuk traceability, produk hanya bisa di-soft delete jika tidak ada pending transactions, dan minimum_stock menentukan kapan sistem raise reorder alert.

---

### 3.5.6 PurchaseOrder dan PurchaseOrderDetail Classes

**Responsibility PurchaseOrder:** Merepresentasikan dokumen Purchase Order sebagai formal request untuk pembelian barang dari supplier dengan status workflow management.

**Atribut PurchaseOrder:** PO memiliki po_number (unique, auto-generated format PO-YYYYMMDD-XXXX), supplier_id, order_date, expected_delivery_date, status (tipe PurchaseOrderStatus enum), total_amount (calculated field), notes, dan soft delete capability. Total amount adalah agregasi dari semua subtotal di PurchaseOrderDetail.

**Relationship PurchaseOrder:** PO memiliki **one-to-many** dengan PurchaseOrderDetail melalui `details()`, dengan Shipment melalui `shipments()` (satu PO bisa dipenuhi oleh multiple shipments/partial delivery), dan dengan GoodsReceipt melalui `goodsReceipts()`. PO juga memiliki **many-to-one** dengan Supplier melalui `supplier()`.

**Key Methods PurchaseOrder:** Query scopes `pending()`, `partial()`, `completed()`, `cancelled()` untuk filtering berdasarkan status. Checker methods `isPending()`, `isPartial()`, `isCompleted()`, `isCancelled()`. Method `calculateTotalAmount()` untuk sum semua subtotal dari details. Method `updateStatus()` dipanggil setelah GR completion untuk auto-update status berdasarkan percentage received. Method `markAsCancelled()` untuk cancel PO dengan validation. Private method `generatePONumber()` untuk auto-generate nomor PO dengan timestamp-based format.

**Responsibility PurchaseOrderDetail:** Merepresentasikan line item dalam Purchase Order, menyimpan detail produk yang dipesan dengan quantity dan price information.

**Atribut PurchaseOrderDetail:** Detail memiliki purchase_order_id (foreign key), product_id (foreign key), quantity_ordered, quantity_received (updated saat GR completion), price (unit price dari produk saat PO dibuat), subtotal (calculated: quantity_ordered × price), dan notes untuk catatan spesifik item.

**Relationship PurchaseOrderDetail:** Detail memiliki **many-to-one** dengan PurchaseOrder melalui `purchaseOrder()` dan dengan Product melalui `product()`, membentuk many-to-many relationship between PO and Product dengan additional attributes (quantity, price).

**Business Rules:** Subtotal auto-calculated dan immutable, quantity_ordered harus > 0, quantity_received di-update saat GR completion dan tidak boleh exceed quantity_ordered, price di-snapshot saat PO creation untuk prevent price fluctuation impact, dan detail tidak bisa diubah jika PO status bukan Pending.

---

### 3.5.7 Shipment dan ShipmentDetail Classes

**Responsibility Shipment:** Merepresentasikan Delivery Order (DO) atau pengiriman barang dari supplier ke gudang sebagai realisasi dari Purchase Order.

**Atribut Shipment:** Shipment memiliki shipment_number (unique, auto-generated), purchase_order_id (reference ke PO), supplier_id, delivery_order_number (nomor DO dari supplier), shipping_date, estimated_arrival_date, status (tipe ShipmentStatus enum), do_scan_path (path file scan DO yang diupload supplier), notes, dan soft delete capability.

**Relationship Shipment:** Shipment memiliki **many-to-one** dengan PurchaseOrder melalui `purchaseOrder()` (multiple shipments untuk satu PO dalam partial delivery scenario), dengan Supplier melalui `supplier()`, **one-to-one** dengan GoodsReceipt melalui `goodsReceipts()` (HasOne, karena satu shipment menghasilkan satu GR), dan **one-to-many** dengan ShipmentDetail melalui `details()`.

**Key Methods Shipment:** Status checker methods `isDraft()`, `isShipped()`, `isArrived()`, `isProcessed()`. State transition methods `markAsShipped()` (dipanggil supplier saat confirm pengiriman, trigger notifikasi ke Checker), `markAsProcessed()` (dipanggil system saat GR completed), dan `markAsCancelled()` untuk cancel shipment. Private method `generateShipmentNumber()` untuk auto-generate nomor shipment unique.

**Responsibility ShipmentDetail:** Merepresentasikan line item dalam Shipment, menyimpan detail produk yang dikirim dengan quantity actual.

**Atribut ShipmentDetail:** Detail memiliki shipment_id, product_id, quantity_shipped (quantity actual yang dikirim supplier), dan notes untuk catatan kondisi atau informasi tambahan per item.

**Relationship ShipmentDetail:** Detail memiliki **many-to-one** dengan Shipment melalui `shipment()` dan dengan Product melalui `product()`.

**Business Rules:** Shipment hanya bisa dibuat/diedit oleh supplier yang sesuai (tenant isolation), quantity_shipped dalam ShipmentDetail tidak boleh exceed quantity_ordered dalam PO terkait, status Draft indicate belum dikirim dan masih editable, status Shipped trigger notification ke Checker dan Admin Gudang, dan satu Shipment hanya bisa memiliki satu GoodsReceipt (one-to-one constraint).

---

### 3.5.8 GoodsReceipt dan GoodsReceiptDetail Classes

**Responsibility GoodsReceipt:** Merepresentasikan catatan penerimaan barang yang dibuat Checker saat barang fisik tiba, dengan verifikasi workflow dan cascade update capability.

**Atribut GoodsReceipt:** GR memiliki grn_number (Goods Receipt Number, unique, auto-generated format GRN-YYYYMMDD-XXXX), purchase_order_id (reference), shipment_id (reference), delivery_order_number (copied from Shipment), receipt_date, received_by (foreign key ke User sebagai Checker yang menerima), status (tipe GoodsReceiptStatus enum), pod_scan_path (path file Proof of Delivery yang diupload Checker), notes, dan soft delete capability.

**Relationship GoodsReceipt:** GR memiliki **many-to-one** dengan Shipment melalui `shipment()` (one-to-one dari sisi Shipment), dengan PurchaseOrder melalui `purchaseOrder()`, dengan User melalui `receiver()` untuk track siapa yang membuat GR, dan **one-to-many** dengan GoodsReceiptDetail melalui `details()`.

**Key Methods GoodsReceipt:** Query scopes `pending()`, `verified()`, `completed()` untuk filtering. Status checker methods `isPending()`, `isVerified()`, `isCompleted()`. State transition methods `markAsVerified()` (optional intermediate state) dan `markAsCompleted()` yang merupakan critical method karena men-trigger cascade updates: update Product stock via `increaseStock()`, update PurchaseOrder status, update Shipment status ke Processed, dan kirim notification ke Accounting. Private method `generateGRNNumber()` untuk auto-generate nomor GRN.

**Responsibility GoodsReceiptDetail:** Merepresentasikan line item dalam Goods Receipt dengan acceptance/rejection tracking.

**Atribut GoodsReceiptDetail:** Detail memiliki goods_receipt_id, product_id, quantity_received (yang dikirim), quantity_accepted (yang diterima dalam kondisi baik), quantity_rejected (yang ditolak karena rusak/tidak sesuai), rejection_reason (mandatory jika ada rejected), dan notes untuk catatan detail per item.

**Relationship GoodsReceiptDetail:** Detail memiliki **many-to-one** dengan GoodsReceipt melalui `goodsReceipt()` dan dengan Product melalui `product()`.

**Business Rules:** Constraint penting: quantity_received = quantity_accepted + quantity_rejected (enforced by validation), quantity_accepted adalah nilai yang akan ditambahkan ke Product.stock_quantity saat GR completion, rejection_reason wajib diisi jika quantity_rejected > 0, GR status Completed men-trigger cascade update (atomic transaction), update ke PurchaseOrderDetail.quantity_received, dan GR hanya bisa di-complete oleh Admin Gudang (policy enforcement).

---

### 3.5.9 Relationship Diagram Analysis

Class diagram Warelink menunjukkan struktur relational database yang complex dengan beberapa pola relationship yang krusial untuk business logic:

**Master-Detail Pattern:** Terlihat pada PurchaseOrder-PurchaseOrderDetail, Shipment-ShipmentDetail, dan GoodsReceipt-GoodsReceiptDetail dimana master entity (header) memiliki one-to-many relationship dengan detail entity (line items). Pattern ini memungkinkan representasi dokumen bisnis multi-item dengan efficient normalization.

**Transactional Chain Pattern:** Alur bisnis procurement membentuk chain: PurchaseOrder → Shipment → GoodsReceipt dimana setiap entity di-link melalui foreign key. PurchaseOrder dapat memiliki multiple Shipments (partial delivery), setiap Shipment memiliki exactly one GoodsReceipt (one-to-one), dan GoodsReceipt reference kembali ke PurchaseOrder untuk aggregation purpose. Pattern ini memungkinkan tracking lifecycle lengkap dari order hingga stock.

**Tenant Isolation Pattern:** User dengan role Supplier memiliki supplier_id yang meng-link mereka ke specific Supplier entity. Relationship ini digunakan untuk tenant filtering dimana Supplier users hanya bisa access data (PO, Product, Shipment) yang terkait dengan supplier mereka sendiri, implementing **multi-tenancy** untuk data security.

**Soft Delete Preservation:** Supplier, Product, PurchaseOrder, Shipment, dan GoodsReceipt mengimplementasikan soft delete (deleted_at timestamp) untuk preserve referential integrity dan historical data. Ini penting karena menghapus Supplier, misalnya, akan break foreign key reference di semua PO historical.

**Aggregate Root Pattern:** Product bertindak sebagai aggregate root untuk stock management, encapsulating semua logic stock operations dan analytics dalam class tersebut. External entities tidak directly modify stock_quantity, melainkan melalui method seperti `increaseStock()` yang ensure business rules dan logging.

---

### 3.5.10 Kesimpulan Perancangan Class Diagram

Class diagram yang dirancang merepresentasikan domain model lengkap dari Sistem Informasi Warelink dengan 9 core entities, 4 enumerations, dan comprehensive relationship mapping. Desain mengikuti prinsip **object-oriented programming** dengan encapsulation, inheritance (melalui Laravel Model base class dan Eloquent traits), dan polymorphism (melalui interface implementation untuk Filament contracts).

Setiap class dirancang dengan **high cohesion** dimana atribut dan method terkait dikelompokkan dalam satu class, dan **low coupling** dimana dependency antar class diminimalkan dan dikelola melalui explicit relationship. Implementation menggunakan Laravel Eloquent ORM memastikan bahwa class diagram ini bukan hanya conceptual model tetapi juga technical blueprint yang directly mappable ke database schema dan application code.

Perancangan class diagram ini menjadi foundation untuk implementation layer, dimana setiap class akan diimplementasikan sebagai Eloquent Model di directory `app/Models/`, relationship di-define melalui Eloquent relationship methods, enumerations sebagai PHP enum di `app/Enums/`, dan business logic di-test melalui unit tests yang memvalidasi behavior setiap method. Validitas desain ini telah dibuktikan melalui test coverage tinggi: 60 test cases untuk master data classes, 10 untuk PurchaseOrder, dan comprehensive integration tests untuk transactional chain.
