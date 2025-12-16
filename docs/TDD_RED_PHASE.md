# TDD — Red Phase

Dokumentasi ini memetakan pengujian mandatory (Red Phase) berdasarkan pemetaan test suite. Tujuan Red Phase: menulis test yang gagal terlebih dahulu untuk setiap Use Case kritikal sebelum implementasi.

Catatan:
- Hanya test() yang bersifat mandatory / penting untuk fungsional dasar yang dimasukkan.
- Kolom "Ekspektasi Red" menjelaskan mengapa test kemungkinan akan gagal pada fase awal dan apa yang harus diimplementasikan.

---

## Ringkasan Runbook
- Lokasi file test yang relevan tercantum di kolom File Test.
- Jalankan test per-file untuk melihat status Red (fail). Gunakan runner proyek (Pest/PHPUnit).

---

## Tabel Pemetaan — Red Phase

| Use Case | TUC Kode | File Test | Test Function (deskripsi) | Ekspektasi Red (kenapa harus dibuat gagal) | Implementasi target (next) |
|---|---:|---|---|---|---|
| UC-01 Mengelola Master Data — User | TUC-0001 | tests/Feature/Admin/ManajemenUserTest.php | "dapat menampilkan halaman daftar user" — list users tersedia | Halaman/Livewire ListUsers belum dibuat atau belum mengembalikan data yang benar | Buat/aktifkan ListUsers page, load table dan policy akses |
| UC-01 Mengelola Master Data — User | TUC-0002 | tests/Feature/Admin/ManajemenUserTest.php | "dapat menampilkan daftar user" — table records | Table rendering atau resource table belum mengembalikan records | Implement tabel resource dan factory data untuk test |
| UC-01 Mengelola Master Data — User | TUC-0003 | tests/Feature/Admin/ManajemenUserTest.php | "dapat membuat user baru" — CreateUser form submit | Form create atau handler create() belum terimplementasi atau validasi belum terpenuhi | Implement CreateUser page, form handling, dan database persist |
| UC-01 Mengelola Master Data — User | TUC-0004 | tests/Feature/Admin/ManajemenUserTest.php | "dapat mengedit user yang sudah ada" — EditUser save | Edit action / save() tidak ada atau policy memblokir | Implement EditUser page & save handler, pastikan policy benar |
| UC-01 Mengelola Master Data — Supplier | TUC-0005 | tests/Feature/Admin/ManajemenSupplierTest.php | (mandatory) list/create supplier | Resource/CRUD supplier belum tersedia atau tenant constraints | Implement Supplier resource CRUD untuk Admin |
| UC-01 Mengelola Master Data — Product | TUC-0006 | tests/Feature/Admin/ManajemenProdukTest.php | "dapat menampilkan halaman daftar produk" | ListProducts page/table belum mengembalikan records untuk Admin panel | Implement Product resource listing dan table schema |
| UC-01 Mengelola Master Data — Product | TUC-0007 | tests/Feature/Admin/ManajemenProdukTest.php | "dapat membuat produk baru dengan data lengkap" — create happy-path | CreateProduct form/logic tidak lengkap (kode auto-generate, relations) | Implement CreateProduct form, product code generation, db persist |
| UC-01 Mengelola Master Data — Product | TUC-0008 | tests/Feature/Admin/ManajemenProdukTest.php | "memvalidasi field yang wajib diisi saat membuat produk" | Validasi server-side belum lengkap | Tambah validasi required fields pada resource form |
| UC-02 PO — CRUD & Workflow | TUC-0009 | tests/Feature/Admin/PurchaseOrder/ManajemenPurchaseOrderTest.php | "admin dapat melihat daftar purchase order" | List PO tidak ada / query belum memfilter supplier/relations | Implement ListPurchaseOrders resource dan relation queries |
| UC-02 PO — CRUD & Workflow | TUC-0010 | tests/Feature/Admin/PurchaseOrder/ManajemenPurchaseOrderTest.php | "admin dapat membuat purchase order baru" — create & details subtotal | Create PO form/Repeater/detil per-item belum berfungsi | Implement CreatePurchaseOrder form, Repeater handling, persistence |
| UC-02 PO — CRUD & Workflow | TUC-0011 | tests/Feature/Admin/PurchaseOrder/ManajemenPurchaseOrderTest.php | "admin dapat mengedit purchase order yang berstatus pending" | EditPurchaseOrder save() belum memodifikasi model | Implement EditPurchaseOrder save handler dan policy checks |
| UC-02 PO — CRUD & Workflow | TUC-0012 | tests/Feature/Admin/PurchaseOrder/ManajemenPurchaseOrderTest.php | "admin dapat membatalkan purchase order" — cancel action | Action cancel belum tersedia atau tidak mengubah status PO | Implement cancel action pada ViewPurchaseOrder dan update status |
| UC-02 PO — Validation | TUC-0013 | tests/Feature/Admin/PurchaseOrder/ValidasiPurchaseOrderTest.php | (mandatory) validasi pembuatan PO | Validasi business rules (qty, supplier, product) belum lengkap | Tambah server-side validation pada CreatePurchaseOrder |
| UC-03 Delivery Order (Supplier) | TUC-0014 | tests/Feature/Supplier/Shipment/ManajemenPengirimanTest.php | "supplier dapat membuat shipment dari purchase order" | CreateShipment page / association dengan PO belum lengkap | Implement CreateShipment form/flow di panel Supplier |
| UC-03 Delivery Order (Supplier) | TUC-0015 | tests/Feature/Supplier/PurchaseOrder/PurchaseOrderSupplierTest.php | "supplier tidak dapat mengakses purchase order yang tidak berwenang" | Access control / tenant enforcement mungkin belum dipasang | Pastikan Filament tenant & policies membatasi akses supplier |
| UC-04 Penerimaan Barang (Checker) | TUC-0016 | tests/Feature/App/GoodsReceipt/PenerimaanBarangTest.php | "checker dapat membuat penerimaan barang dari shipment" | CreateGoodsReceipt form / details handling belum ada atau incomplete | Implement CreateGoodsReceipt, map shipment->details ke GR details |
| UC-04 Penerimaan Barang (Checker) | TUC-0017 | tests/Feature/App/GoodsReceipt/PenerimaanBarangTest.php | "checker dapat membuat penerimaan barang secara parsial" | Partial acceptance logic (accepted vs rejected counts) belum lengkap | Implement partial acceptance handling dan status update pada PO |
| UC-04 Penerimaan Barang (Checker) | TUC-0018 | tests/Feature/App/GoodsReceipt/PenerimaanBarangTest.php | "checker dapat menangani barang yang ditolak" | Rejection reason / qty belum disimpan atau diproses | Simpan rejection fields, perbarui PO/stock sesuai business rules |
| UC-04 Penerimaan Barang (Checker) | TUC-0019 | tests/Feature/App/GoodsReceipt/PenerimaanBarangTest.php | "sistem menyelesaikan PO ketika semua barang sudah diterima" | PO completion logic mungkin belum mengakumulasi semua GRs | Implement PO status transition logic berdasarkan GR completion |
| UC-05 Verifikasi GR (Admin) | TUC-0020 | tests/Feature/App/GoodsReceipt/PenerimaanBarangTest.php | "memverifikasi dan menyelesaikan GR (callAction verify/complete)" | Admin verify/complete actions belum terpasang atau tidak memicu stock update | Implement verify/complete actions, update Product::increaseStock() dan PO status |
| UC-06 Chat (Checker, Supplier) | TUC-0021 | tests/Feature/App/Chat/FiturChatTest.php<br>tests/Feature/ChatsTest.php | "dapat menampilkan halaman chat" — Chats page | Filament Page Chats atau Livewire wirechat component belum terpasang di panel | Implement Chats Page view + livewire:wirechat embedding |
| UC-06 Chat (Checker, Supplier) | TUC-0022 | tests/Feature/App/Chat/FiturChatTest.php<br>tests/Feature/ChatsTest.php | "memerlukan autentikasi untuk mengakses halaman chat" | Middleware auth not applied to chat route/panel | Tambah middleware auth dan panel provider konfigurasi |
| UC-06 Chat (Checker, Supplier) | TUC-0023 | tests/Feature/App/Chat/FiturChatTest.php | "dapat memuat komponen wirechat" — Livewire component | Wirechat package integration (component alias) belum ter-boot atau di-register | Pastikan Wirechat package ter-publish, service provider aktif dan livewire alias 'wirechat' ada |
| UC-06 Chat (Checker, Supplier) | TUC-0024 | tests/Feature/ChatsTest.php | "dapat mengakses chat panel provider" — /app-chats route | Panel provider (ChatsPanelProvider) path/middleware mungkin belum ter-registrasi | Register provider di bootstrap/providers.php dan pastikan middleware web/auth ada |
| UC-07 Laporan Bulanan (Accounting) | TUC-0025 | tests/Feature/App/Report/LaporanBulananTest.php | "hanya accounting yang dapat mengakses halaman laporan bulanan" | Authorization for report page not enforced | Implement policy/authorization to restrict to Accounting role |
| UC-07 Laporan Bulanan (Accounting) | TUC-0026 | tests/Feature/App/Report/LaporanBulananTest.php | "dapat menampilkan tabel purchase order / stock di laporan" | Report aggregation queries or report tables not implemented | Implement MonthlyReport page, queries and table schemas |
| UC-08 Melihat Produk (Supplier) | TUC-0027 | tests/Feature/Supplier/ViewProductTest.php | "supplier hanya dapat melihat daftar produk mereka sendiri" | Tenant-scoped query (by supplier) might be missing | Implement Product::bySupplier scope usage in ListProducts for supplier panel |
| UC-08 Melihat Produk (Supplier) | TUC-0028 | tests/Feature/Supplier/ViewProductTest.php | "supplier dapat melihat detail produk milik mereka" | ViewProduct page may not check tenant + relations | Implement ViewProduct and ensure tenant check & details view |
| UC-08 Melihat Produk (Supplier) | TUC-0029 | tests/Feature/Supplier/ViewProductTest.php | "supplier tidak dapat mengakses produk supplier lain" | Route/policy might allow access to any product | Enforce tenant check & policy to return 404 for unauthorized product |

---

## Panduan singkat Red → Green → Refactor
1. Jalankan test-file yang dipetakan (contoh: file path pada kolom File Test). Test harus gagal (Red).
2. Implementasi minimum untuk membuat test lulus (Green): model scope, Livewire page, form handler, action, authorization, dan persistence.
3. Refactor code tanpa mengubah perilaku — ulangi untuk test berikutnya.

---

## Lokasi file test (referensi cepat)
- tests/Feature/Admin/ManajemenUserTest.php
- tests/Feature/Admin/ManajemenProdukTest.php
- tests/Feature/Admin/PurchaseOrder/ManajemenPurchaseOrderTest.php
- tests/Feature/Admin/PurchaseOrder/ValidasiPurchaseOrderTest.php
- tests/Feature/Supplier/PurchaseOrder/PurchaseOrderSupplierTest.php
- tests/Feature/Supplier/Shipment/ManajemenPengirimanTest.php
- tests/Feature/Supplier/ViewProductTest.php
- tests/Feature/App/GoodsReceipt/PenerimaanBarangTest.php
- tests/Feature/App/Chat/FiturChatTest.php
- tests/Feature/App/Report/LaporanBulananTest.php
- tests/Feature/ChatsTest.php

---

Jika Anda ingin, saya bisa:
- Menambahkan kolom "File:line" untuk setiap test function (mengekstrak baris di mana test didefinisikan),
- Menyimpan dokumen ini ke path lain atau menambahkan file checklist TUC di repo.
