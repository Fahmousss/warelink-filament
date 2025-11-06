# Activity Diagrams - Sistem Informasi Warelink

Dokumentasi ini berisi activity diagram untuk setiap use case dalam Sistem Informasi Warelink. Activity diagram menggambarkan alur proses bisnis yang sesuai dengan method-method yang ada di class diagram, namun ditampilkan dari perspektif implikasi/fungsi bisnis, bukan nama method teknis.

## Daftar Activity Diagrams

### 1. Master Data Management
**File:** `1-master-data-management.puml`
**Aktor:** Admin Gudang
**Deskripsi:** Mengelola data master sistem yang mencakup:
- **Manajemen Pengguna**: Menambah, mengedit, menghapus, dan melihat data user dengan role (Admin, Accounting, Checker, Supplier)
- **Manajemen Supplier**: Mengelola data supplier dengan generate kode otomatis
- **Manajemen Produk**: Mengelola produk dengan tracking stok dan pergerakan stok

**Proses Utama:**
- Generate kode supplier/produk otomatis
- Set status aktif/non-aktif
- Validasi data master
- Pencatatan pergerakan stok (stock movement logging)
- Filter berdasarkan berbagai kriteria (status, supplier, kondisi stok)

---

### 2. Purchase Order Management
**File:** `2-purchase-order-management.puml`
**Aktor:** Admin Gudang
**Deskripsi:** Mengelola Purchase Order (PO) dari pembuatan hingga pembatalan

**Proses Utama:**
- Generate nomor PO otomatis
- Input detail PO (supplier, tanggal, estimasi pengiriman)
- Tambah detail produk dengan perhitungan subtotal dan total amount otomatis
- Edit PO (hanya untuk status Pending)
- Lihat PO dengan filter berdasarkan status/supplier/tanggal
- Batalkan PO (hanya untuk status Pending)
- Update status otomatis berdasarkan penerimaan barang (Pending → Partial → Completed)

**Status PO:**
- **Pending**: PO baru dibuat
- **Partial**: Sebagian barang sudah diterima
- **Completed**: Semua barang sudah diterima
- **Cancelled**: PO dibatalkan

---

### 3. Delivery Order Management
**File:** `3-delivery-order-management.puml`
**Aktor:** Supplier
**Deskripsi:** Supplier mengelola pengiriman barang berdasarkan Purchase Order

**Proses Utama:**
- Lihat PO yang ditujukan ke supplier sendiri (filtered by supplier_id)
- Buat Shipment/Delivery Order dengan generate nomor otomatis
- Input nomor DO, tanggal pengiriman, estimasi kedatangan
- Upload scan dokumen DO
- Tambah detail produk yang akan dikirim (support partial shipment)
- Edit shipment (hanya untuk status Draft)
- Mark shipment as "Shipped" untuk notifikasi ke gudang

**Status Shipment:**
- **Draft**: Shipment dibuat tapi belum dikirim
- **Shipped**: Barang sudah dikirim supplier
- **Arrived**: Barang sudah tiba di gudang
- **Processed**: Penerimaan barang selesai diproses

---

### 4. Goods Receipt Creation
**File:** `4-goods-receipt-creation.puml`
**Aktor:** Checker
**Deskripsi:** Checker membuat penerimaan barang saat barang tiba dari supplier

**Proses Utama:**
- Terima notifikasi Delivery Order dengan status "Shipped"
- Lihat dan verifikasi dokumen DO
- Generate nomor GRN (Goods Receipt Number) otomatis
- Upload scan dokumen POD (Proof of Delivery)
- Pemeriksaan fisik barang satu per satu
- Input jumlah diterima vs jumlah ditolak
- Input alasan penolakan untuk barang yang ditolak (rusak, kurang, tidak sesuai, dll)
- Set status = Pending untuk menunggu verifikasi Admin
- Update status shipment = Arrived
- Kirim notifikasi ke Admin Gudang untuk verifikasi

**Alasan Penolakan:**
- Barang rusak
- Jumlah kurang
- Tidak sesuai spesifikasi
- Kadaluarsa
- dll

---

### 5. Goods Receipt Verification
**File:** `5-goods-receipt-verification.puml`
**Aktor:** Admin Gudang
**Deskripsi:** Admin Gudang memverifikasi dan menyelesaikan penerimaan barang

**Proses Utama:**
- Terima notifikasi Goods Receipt baru (status Pending)
- Verifikasi data penerimaan dengan dokumen fisik
- Mark as Verified jika data sesuai
- Selesaikan penerimaan (Completed) yang akan trigger:
  - Update stok produk (increase stock)
  - Catat pergerakan stok (stock movement)
  - Update jumlah diterima di Purchase Order Detail
  - Update status Purchase Order (Partial/Completed)
  - Update status Shipment = Processed
  - Kirim notifikasi ke Accounting
  - Kirim notifikasi ke Supplier jika ada penolakan
- Kembalikan ke Checker jika data tidak akurat

**Alur Status:**
Pending → Verified → Completed

**Efek Setelah Completed:**
1. Stok produk terupdate otomatis
2. Purchase Order status terupdate
3. Shipment status = Processed
4. Pergerakan stok tercatat
5. Notifikasi terkirim ke pihak terkait

---

### 6. Monthly Report Generation
**File:** `6-monthly-report-generation.puml`
**Aktor:** Accounting
**Deskripsi:** Generate laporan bulanan untuk berbagai jenis data

**Jenis Laporan:**

#### a. Laporan Purchase Order
- Total PO per status
- Total amount per status dan keseluruhan
- Rata-rata nilai PO
- Top suppliers berdasarkan nilai transaksi
- Grafik trend PO per minggu
- Distribusi per supplier

#### b. Laporan Penerimaan Barang
- Total penerimaan
- Total produk diterima vs ditolak
- Acceptance rate (%)
- Produk dengan tingkat penolakan tertinggi
- Supplier performance (terbaik dan terburuk)
- Grafik trend penerimaan dan acceptance rate

#### c. Laporan Stok Produk
- Total produk aktif
- Produk dengan stok rendah/habis
- Produk perlu reorder
- Pergerakan stok (masuk/keluar)
- Projected stock berdasarkan PO pending
- Top moving products
- Reorder urgency chart

#### d. Laporan Keuangan
- Total pengeluaran dari PO completed
- Outstanding amount dari PO Partial dan Pending
- Pengeluaran per supplier dan kategori
- Rata-rata nilai transaksi
- Top spending suppliers
- Spending trend

**Fitur Laporan:**
- Filter tambahan (supplier, produk, status, range tanggal)
- Export ke berbagai format (PDF, Excel, CSV)
- Print laporan
- Grafik dan chart visualisasi
- KPI dashboard

---

### 7. View Product
**File:** `7-view-product.puml`
**Aktor:** Supplier
**Deskripsi:** Supplier melihat detail produk mereka sendiri

**Proses Utama:**
- Tampilkan produk milik supplier (auto-filter by supplier_id)
- Filter berdasarkan:
  - Status stok (rendah, habis, baik, perlu reorder)
  - Status aktif/non-aktif
  - Pencarian keyword
- Lihat detail lengkap produk:
  - Informasi dasar
  - Informasi stok (current, minimum, projected, status)
  - Statistik (total ordered, received, rejected, acceptance rate)
  - Riwayat transaksi (PO, Shipment, GR)
  - Riwayat pergerakan stok
  - Grafik trend
- Export data produk

**Informasi Stok Detail:**
- Jumlah stok saat ini
- Minimum stok
- Projected stock (including pending PO)
- Status (Low/Out/Good)
- Perlu reorder
- Suggested reorder quantity
- Reorder urgency level

**Keterbatasan:**
- Supplier hanya dapat melihat, tidak bisa edit/hapus produk

---

### 8. Messaging
**File:** `8-messaging.puml`
**Aktor:** Semua User (Admin Gudang, Accounting, Checker, Supplier)
**Deskripsi:** Sistem komunikasi real-time antar user menggunakan Wirechat dan Laravel Reverb

**Proses Utama:**
- Buat chat baru (Private/Group)
- Validasi permission (canCreateChats, canCreateGroups)
- Filter user yang bisa diajak chat berdasarkan role
- Kirim pesan (teks, file, atau kombinasi)
- Real-time message delivery via WebSocket (Laravel Reverb)
- Push notification untuk recipient offline
- Read receipts
- Auto-scroll ke pesan terbaru
- Unread counter

**Permission:**
- Supplier hanya bisa chat dengan Admin Gudang, Checker, dan Accounting (jika ada akses)
- Tidak semua user bisa membuat group chat (based on canCreateGroups property)

**Fitur Messaging:**
- Private chat 1-on-1
- Group chat dengan multiple participants
- File attachment (dokumen, gambar, PDF)
- Read receipts (centang biru)
- Real-time updates
- Push notifications
- Notification sound

**Teknologi:**
- Wirechat package
- Laravel Reverb untuk WebSocket
- Broadcasting untuk real-time communication

---

## Relasi Antar Activity Diagram

```
1. Master Data Management
   ├─> Membuat data Supplier (digunakan di PO)
   ├─> Membuat data Produk (digunakan di PO)
   └─> Membuat data User (actor di semua use case)

2. Purchase Order Management
   ├─> Memicu Delivery Order (Supplier)
   └─> Direferensi di Goods Receipt

3. Delivery Order Management
   ├─> Memicu notifikasi ke Checker
   └─> Direferensi di Goods Receipt

4. Goods Receipt Creation
   ├─> Update status Shipment
   └─> Memicu verifikasi oleh Admin

5. Goods Receipt Verification
   ├─> Update stok produk (kembali ke Master Data)
   ├─> Update status PO (kembali ke PO Management)
   ├─> Memicu pembuatan laporan (Accounting)
   └─> Kirim notifikasi (Messaging)

6. Monthly Report Generation
   └─> Menggunakan data dari semua modul

7. View Product
   └─> Menampilkan data dari Master Data & transaksi

8. Messaging
   └─> Digunakan di semua use case untuk komunikasi
```

## Kesesuaian dengan Class Diagram

Setiap activity diagram dirancang untuk memenuhi method-method di class diagram:

### User Class
- `canAccessTenant()` - digunakan untuk filter data per supplier
- `isActive()` - validasi user aktif
- `isAdmin()`, `isAccounting()`, `isSupplier()` - role checking
- `canCreateChats()`, `canCreateGroups` - permission messaging

### Supplier Class
- `generateSupplierCode()` - di Master Data Management
- `isActive()` - filter supplier aktif
- Relasi `purchaseOrders()`, `shipments()`, `products()` - digunakan di semua diagram

### Product Class
- `increaseStock()`, `decreaseStock()` - di Goods Receipt Verification
- `logStockMovement()` - pencatatan pergerakan stok
- `isNeedsReorder()`, `suggestedReorderQuantity()` - di View Product
- `getAcceptanceRate()`, `getTotalReceived()`, `getTotalRejected()` - di Reports
- `generateProductCode()` - di Master Data Management

### PurchaseOrder Class
- `calculateTotalAmount()` - di PO Management
- `updateStatus()` - di Goods Receipt Verification
- `markAsCancelled()` - di PO Management
- `generatePONumber()` - di PO Management
- Scope queries: `pending()`, `partial()`, `completed()`, `cancelled()`

### Shipment Class
- `markAsShipped()` - di Delivery Order Management
- `markAsProcessed()` - di Goods Receipt Verification
- `generateShipmentNumber()` - di Delivery Order Management
- Status checks: `isDraft()`, `isShipped()`, `isArrived()`, `isProcessed()`

### GoodsReceipt Class
- `markAsVerified()` - di Goods Receipt Verification
- `markAsCompleted()` - di Goods Receipt Verification
- `generateGRNNumber()` - di Goods Receipt Creation
- Scope queries: `pending()`, `verified()`, `completed()`

## Cara Render Diagram

Activity diagram dibuat dalam format PlantUML. Untuk render diagram:

### Online
1. Buka [PlantUML Online Editor](http://www.plantuml.com/plantuml/uml/)
2. Copy-paste isi file `.puml`
3. Lihat hasilnya

### VS Code
1. Install extension: **PlantUML** by jebbs
2. Buka file `.puml`
3. Tekan `Alt+D` untuk preview

### Command Line
```bash
# Install PlantUML
sudo apt-get install plantuml

# Generate PNG
plantuml 1-master-data-management.puml

# Generate SVG
plantuml -tsvg 1-master-data-management.puml

# Generate semua diagram
plantuml *.puml
```

## Catatan Penting

1. **Tidak Menyebutkan Nama Method**: Activity diagram fokus pada implikasi bisnis, bukan nama method teknis
2. **Alur Lengkap**: Setiap diagram mencakup happy path, failure path, dan edge cases
3. **Notifikasi**: Sistem menggunakan notifikasi real-time untuk koordinasi antar role
4. **Status Management**: Setiap entity memiliki status yang terupdate otomatis berdasarkan alur proses
5. **Audit Trail**: Semua perubahan penting (stok, status) dicatat untuk audit
6. **Role-Based Access**: Setiap aktor hanya bisa akses fitur sesuai role mereka
7. **Real-time Updates**: Menggunakan Laravel Reverb untuk WebSocket communication

## Perubahan dari Use Case Diagram

Activity diagram ini diperluas dari use case diagram dengan menambahkan:
- Detail alur proses yang lebih lengkap
- Decision points dan conditional flows
- Swimlanes untuk memisahkan actor dan system
- Integration points antar use case
- Error handling dan validation flows
- Notification flows
- Status transitions

---

**Dibuat:** 2025-11-06
**Sistem:** Warelink - Warehouse Management System
**Versi:** 1.0
