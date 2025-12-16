# BAB III (Lanjutan)
# PERANCANGAN SISTEM

## 3.4 Perancangan Activity Diagram

### 3.4.1 Pengantar Activity Diagram

Activity diagram merupakan representasi visual dari alur kerja dan proses bisnis dalam sistem Warelink. Diagram ini menggambarkan sekuens aktivitas, decision point, dan interaksi antara aktor dengan sistem menggunakan notasi swimlane untuk memisahkan tanggung jawab masing-masing pihak. Peneliti merancang 8 activity diagram yang memetakan setiap use case utama sistem, menunjukkan bagaimana proses bisnis dijalankan dari perspektif aktor dan respons sistem terhadap setiap aksi.

Activity diagram dalam penelitian ini menggunakan PlantUML sebagai tool pemodelan dengan standar UML 2.5. Setiap diagram dirancang dengan prinsip **separation of concerns** dimana swimlane "User" atau nama aktor spesifik (Admin Gudang, Checker, Supplier, Accounting) menunjukkan aksi yang dilakukan oleh pengguna, sedangkan swimlane "System" menunjukkan proses otomatis, validasi, dan operasi backend yang dijalankan oleh aplikasi.

Kedelapan activity diagram yang dirancang mencakup seluruh siklus operasional gudang, dari manajemen data master, procurement process (PO dan DO), penerimaan dan verifikasi barang, hingga pelaporan dan komunikasi. Setiap diagram mengilustrasikan **main flow**, **alternative flow**, dan **decision point** yang menggambarkan logika bisnis dan business rules yang telah didefinisikan.

---

### 3.4.2 AD-01: Activity Diagram Manajemen Master Data

**Pemetaan:** UC-01 (Mengelola Master Data)
**File Diagram:** `1-master-data-management.puml`

#### Flow of Event

**Use Case:** Mengelola Master Data

**Deskripsi:** Use case ini memungkinkan Admin Gudang untuk melakukan operasi CRUD (Create, Read, Update, Delete) terhadap tiga entitas master data yaitu Pengguna, Supplier, dan Produk yang merupakan data fundamental sistem Warelink.

**Aktor:** Admin Gudang

**Kondisi Awal (Pre-conditions):**
- Admin Gudang sudah login ke Admin Panel
- Admin Gudang memiliki role dan permission untuk mengelola master data
- Database sistem dalam kondisi normal dan accessible

**Kondisi Akhir (Post-conditions):**
- Data master (Pengguna/Supplier/Produk) berhasil dikelola sesuai aksi yang dipilih
- Perubahan data tersimpan ke database
- Audit log mencatat aktivitas perubahan data
- Notifikasi sukses ditampilkan kepada Admin Gudang

**Aliran Kejadian Utama (Main Flow):**
1. Admin Gudang mengakses menu Master Data pada Admin Panel
2. Sistem menampilkan pilihan jenis data: Pengguna, Supplier, atau Produk
3. Admin Gudang memilih jenis data yang akan dikelola
4. Admin Gudang memilih aksi CRUD yang akan dilakukan (Create/Read/Update/Delete)
5. Admin Gudang menginput atau mengupdate data sesuai form yang tersedia
6. Sistem melakukan validasi data sesuai business rules entitas terkait
7. Sistem menyimpan data ke database
8. Sistem menampilkan notifikasi sukses kepada Admin Gudang
9. Use case selesai

**Aliran Alternatif (Alternative Flow):**

- **Alt-1: Mengelola Data Pengguna**
  1. Setelah step 3 main flow, Admin Gudang memilih "Pengguna"
  2. Sistem menampilkan daftar pengguna existing
  3. Admin Gudang melakukan aksi CRUD pada data pengguna
  4. Sistem memvalidasi unique email dan password strength
  5. Sistem memproses data pengguna
  6. Sistem menyimpan ke database
  7. Kembali ke step 8 main flow

- **Alt-2: Mengelola Data Supplier**
  1. Setelah step 3 main flow, Admin Gudang memilih "Supplier"
  2. Sistem menampilkan daftar supplier existing
  3. Admin Gudang melakukan aksi CRUD pada data supplier
  4. Sistem men-generate kode supplier dengan format SUP-xxxxx (jika create)
  5. Sistem memvalidasi data supplier
  6. Sistem menyimpan ke database
  7. Kembali ke step 8 main flow

- **Alt-3: Mengelola Data Produk**
  1. Setelah step 3 main flow, Admin Gudang memilih "Produk"
  2. Sistem menampilkan daftar produk existing
  3. Admin Gudang melakukan aksi CRUD pada data produk
  4. Sistem men-generate kode produk (jika create)
  5. Sistem memvalidasi constraint harga non-negatif dan kalkulasi reorder point
  6. Sistem mencatat pergerakan stok (jika ada perubahan quantity)
  7. Sistem menyimpan ke database
  8. Kembali ke step 8 main flow

- **Alt-4: Membatalkan Operasi**
  1. Setelah step 2 atau step 4 main flow, Admin Gudang memilih "Batal"
  2. Sistem kembali ke menu sebelumnya
  3. Use case selesai tanpa perubahan data

**Aliran Eksepsi (Exception Flow):**

- **Exc-1: Validasi Data Gagal**
  1. Pada step 6 main flow, sistem mendeteksi data tidak valid
  2. Sistem menampilkan pesan error spesifik (contoh: "Email sudah digunakan", "Harga tidak boleh negatif")
  3. Sistem mengembalikan fokus ke form input
  4. Kembali ke step 5 untuk perbaikan input

- **Exc-2: Delete Tidak Diizinkan**
  1. Admin Gudang memilih aksi Delete
  2. Sistem memeriksa business rule delete
  3. Jika data memiliki relasi aktif (contoh: User aktif, Supplier dengan PO aktif)
  4. Sistem menampilkan pesan error "Data tidak dapat dihapus karena masih digunakan"
  5. Sistem melakukan soft delete (untuk Supplier) atau menolak delete (untuk User aktif)
  6. Use case selesai tanpa hard delete

---

### 3.4.3 AD-02: Activity Diagram Manajemen Purchase Order

**Pemetaan:** UC-02 (Manajemen Purchase Order)
**File Diagram:** `2-purchase-order-management.puml`

#### Flow of Event

**Use Case:** Manajemen Purchase Order

**Deskripsi:** Use case ini memungkinkan Admin Gudang untuk mengelola Purchase Order (PO) sebagai dokumen permintaan pengadaan barang kepada Supplier, mencakup pembuatan, edit, view, dan pembatalan PO dengan state-based access control.

**Aktor:** Admin Gudang

**Kondisi Awal (Pre-conditions):**
- Admin Gudang sudah login ke Admin Panel
- Data master Supplier dan Produk sudah tersedia di database
- Admin Gudang memiliki permission untuk mengelola Purchase Order

**Kondisi Akhir (Post-conditions):**
- Purchase Order berhasil dikelola sesuai aksi yang dipilih
- Status PO terupdate sesuai lifecycle (Pending/Partial/Completed/Cancelled)
- Notifikasi terkirim ke Supplier terkait (untuk aksi Create dan Cancel)
- Audit log mencatat semua perubahan data PO

**Aliran Kejadian Utama (Main Flow):**
1. Admin Gudang mengakses menu Purchase Order pada Admin Panel
2. Sistem menampilkan daftar Purchase Order existing
3. Admin Gudang memilih aksi yang akan dilakukan
4. Sistem memproses aksi sesuai pilihan Admin Gudang
5. Sistem menampilkan hasil operasi
6. Use case selesai

**Aliran Alternatif (Alternative Flow):**

- **Alt-1: Buat Purchase Order**
  1. Setelah step 3 main flow, Admin Gudang memilih "Buat PO"
  2. Admin Gudang memilih Supplier dari dropdown
  3. Admin Gudang menginput detail PO (tanggal order, expected delivery date, catatan)
  4. Admin Gudang menambahkan detail produk dengan quantity dan unit price masing-masing
  5. Sistem men-generate nomor PO otomatis dengan format PO-YYYYMMDD-XXXX
  6. Sistem menghitung total amount berdasarkan sum(quantity × unit price) setiap item
  7. Sistem meset status awal = "Pending"
  8. Sistem menyimpan Purchase Order beserta detail items ke database
  9. Sistem mengirim notifikasi kepada Supplier terkait
  10. Kembali ke step 5 main flow

- **Alt-2: Edit Purchase Order**
  1. Setelah step 3 main flow, Admin Gudang memilih "Edit PO"
  2. Admin Gudang memilih Purchase Order dari list
  3. Sistem memeriksa status PO
  4. **Guard condition:** Jika status = "Pending", lanjutkan ke step 5
  5. Admin Gudang mengupdate data PO (supplier, tanggal, catatan)
  6. Admin Gudang mengupdate detail produk (tambah/edit/hapus item)
  7. Sistem menghitung ulang total amount
  8. Sistem menyimpan perubahan ke database
  9. Kembali ke step 5 main flow

- **Alt-3: Lihat Purchase Order**
  1. Setelah step 3 main flow, Admin Gudang memilih "Lihat PO"
  2. Sistem menampilkan daftar Purchase Order dengan filter dan sorting
  3. Admin Gudang dapat memilih PO tertentu untuk melihat detail lengkap
  4. Sistem menampilkan detail PO: informasi supplier, daftar produk, total amount, status, timeline
  5. Admin Gudang selesai melihat detail
  6. Kembali ke step 5 main flow

- **Alt-4: Batalkan Purchase Order**
  1. Setelah step 3 main flow, Admin Gudang memilih "Batalkan PO"
  2. Admin Gudang memilih Purchase Order dari list
  3. Sistem memeriksa status PO
  4. **Guard condition:** Jika status = "Pending", lanjutkan ke step 5
  5. Admin Gudang mengkonfirmasi pembatalan
  6. Sistem mengubah status PO menjadi "Cancelled"
  7. Sistem menyimpan perubahan ke database
  8. Sistem mengirim notifikasi pembatalan kepada Supplier
  9. Kembali ke step 5 main flow

- **Alt-5: Batal/Kembali**
  1. Setelah step 3 main flow, Admin Gudang memilih "Batal"
  2. Sistem kembali ke menu sebelumnya
  3. Use case selesai

**Aliran Eksepsi (Exception Flow):**

- **Exc-1: Edit PO dengan Status Bukan Pending**
  1. Pada Alt-2 step 4, sistem mendeteksi status PO bukan "Pending" (status Partial/Completed)
  2. Sistem menampilkan error message "PO tidak dapat diubah karena sudah diproses"
  3. Sistem menolak aksi edit
  4. Use case selesai tanpa perubahan data

- **Exc-2: Batalkan PO dengan Status Bukan Pending**
  1. Pada Alt-4 step 4, sistem mendeteksi status PO bukan "Pending"
  2. Sistem menampilkan error message "PO tidak dapat dibatalkan karena sudah diproses"
  3. Sistem menolak aksi pembatalan
  4. Use case selesai tanpa perubahan status

- **Exc-3: Validasi Detail Produk Gagal**
  1. Pada Alt-1 step 4 atau Alt-2 step 6, Admin Gudang menginput detail produk
  2. Sistem mendeteksi quantity ≤ 0 atau unit price negatif
  3. Sistem menampilkan error "Quantity harus lebih dari 0 dan harga tidak boleh negatif"
  4. Kembali ke step input detail produk untuk perbaikan

---

### 3.4.4 AD-03: Activity Diagram Manajemen Delivery Order

**Pemetaan:** UC-03 (Mengelola Delivery Order)
**File Diagram:** `3-delivery-order-management.puml`

#### Flow of Event

**Use Case:** Mengelola Delivery Order

**Deskripsi:** Use case ini memungkinkan Supplier untuk mengelola Delivery Order (Shipment) sebagai respon terhadap Purchase Order yang diterima, mencakup pembuatan DO, edit, view, dan pengiriman barang dengan notifikasi real-time ke gudang.

**Aktor:** Supplier

**Kondisi Awal (Pre-conditions):**
- Supplier sudah login ke Supplier Panel
- Purchase Order sudah dibuat oleh Admin Gudang dengan status Pending
- Supplier memiliki akses ke PO yang ditujukan kepada mereka (tenant isolation)

**Kondisi Akhir (Post-conditions):**
- Delivery Order berhasil dikelola sesuai aksi yang dipilih
- Status Shipment terupdate sesuai lifecycle (Draft/Shipped/Arrived/Processed)
- Notifikasi terkirim ke Admin Gudang dan Checker (untuk aksi Kirim Barang)
- Dokumen DO dan detail produk tersimpan di database

**Aliran Kejadian Utama (Main Flow):**
1. Supplier mengakses menu Purchase Order pada Supplier Panel
2. Sistem menampilkan daftar Purchase Order yang relevan untuk supplier tersebut (tenant isolation)
3. Supplier memilih aksi yang akan dilakukan
4. Sistem memproses aksi sesuai pilihan Supplier
5. Sistem menampilkan hasil operasi
6. Use case selesai

**Aliran Alternatif (Alternative Flow):**

- **Alt-1: Buat Delivery Order**
  1. Setelah step 3 main flow, Supplier memilih "Buat DO"
  2. Supplier memilih Purchase Order sebagai referensi
  3. Supplier menginput detail pengiriman (tanggal kirim, estimasi tiba, catatan pengiriman)
  4. Supplier menginput detail produk yang akan dikirim dengan quantity masing-masing
  5. Supplier mengupload dokumen pendukung DO (packing list, invoice, dll)
  6. Sistem men-generate nomor shipment otomatis
  7. Sistem memvalidasi quantity produk (tidak boleh melebihi quantity PO)
  8. Sistem meset status awal = "Draft"
  9. Sistem menyimpan Shipment beserta detail items ke database
  10. Kembali ke step 5 main flow

- **Alt-2: Edit Delivery Order**
  1. Setelah step 3 main flow, Supplier memilih "Edit DO"
  2. Supplier memilih Shipment dari list
  3. Sistem memeriksa status Shipment
  4. **Guard condition:** Jika status = "Draft", lanjutkan ke step 5
  5. Supplier mengupdate data DO (tanggal, estimasi tiba, catatan)
  6. Supplier mengupdate detail produk yang dikirim
  7. Sistem memvalidasi quantity dan menyimpan perubahan ke database
  8. Kembali ke step 5 main flow

- **Alt-3: Lihat Delivery Order**
  1. Setelah step 3 main flow, Supplier memilih "Lihat DO"
  2. Sistem menampilkan daftar Shipment dengan status masing-masing
  3. Supplier dapat memilih DO tertentu untuk melihat detail lengkap
  4. Sistem menampilkan detail DO: informasi PO, daftar produk, dokumen, status, timeline
  5. Supplier selesai melihat detail
  6. Kembali ke step 5 main flow

- **Alt-4: Kirim Barang**
  1. Setelah step 3 main flow, Supplier memilih "Kirim Barang"
  2. Supplier memilih Shipment dengan status "Draft"
  3. Supplier mengkonfirmasi pengiriman barang
  4. Sistem mengubah status Shipment menjadi "Shipped"
  5. Sistem menyimpan perubahan ke database
  6. Sistem mengirim notifikasi real-time kepada Admin Gudang dan Checker menggunakan Laravel Reverb broadcasting
  7. Tim gudang menerima notifikasi bahwa ada kiriman barang dalam perjalanan
  8. Kembali ke step 5 main flow

- **Alt-5: Batal/Kembali**
  1. Setelah step 3 main flow, Supplier memilih "Batal"
  2. Sistem kembali ke menu sebelumnya
  3. Use case selesai

**Aliran Eksepsi (Exception Flow):**

- **Exc-1: Edit DO dengan Status Bukan Draft**
  1. Pada Alt-2 step 4, sistem mendeteksi status Shipment bukan "Draft" (status Shipped/Arrived/Processed)
  2. Sistem menampilkan error message "DO tidak dapat diubah karena sudah dikirim"
  3. Sistem menolak aksi edit
  4. Use case selesai tanpa perubahan data

- **Exc-2: Validasi Quantity Gagal**
  1. Pada Alt-1 step 7 atau Alt-2 step 7, sistem memvalidasi quantity produk
  2. Sistem mendeteksi quantity yang dikirim melebihi quantity PO
  3. Sistem menampilkan error "Quantity yang dikirim tidak boleh melebihi quantity PO"
  4. Kembali ke step input detail produk untuk perbaikan

- **Exc-3: Dokumen DO Belum Diupload**
  1. Pada Alt-1 step 5, Supplier belum mengupload dokumen pendukung
  2. Sistem menampilkan warning "Dokumen pendukung disarankan untuk diupload"
  3. Supplier dapat melanjutkan tanpa dokumen atau kembali untuk upload
  4. Jika lanjut, sistem tetap menyimpan DO dengan catatan dokumen belum lengkap

---

### 3.4.5 AD-04: Activity Diagram Pembuatan Penerimaan Barang

**Pemetaan:** UC-04 (Penerimaan Barang) - Sub-flow: Membuat Goods Receipt
**File Diagram:** `4-goods-receipt-creation.puml`

#### Flow of Event

**Use Case:** Membuat Penerimaan Barang (Create Goods Receipt)

**Deskripsi:** Use case ini memungkinkan Checker untuk membuat catatan penerimaan barang (Goods Receipt) saat barang fisik tiba di gudang, mencakup inspeksi kondisi barang, pencatatan quantity received dan rejected, serta upload dokumen Proof of Delivery.

**Aktor:** Checker

**Kondisi Awal (Pre-conditions):**
- Checker sudah login ke App Panel
- Shipment dengan status "Shipped" sudah tersedia (barang dalam perjalanan)
- Barang fisik telah tiba di gudang
- Checker menerima notifikasi pengiriman barang dari sistem

**Kondisi Akhir (Post-conditions):**
- Goods Receipt berhasil dibuat dengan status "Pending"
- Status Shipment terupdate menjadi "Arrived"
- Dokumen Proof of Delivery (POD) tersimpan di sistem
- Notifikasi terkirim ke Admin Gudang untuk verifikasi
- Data quantity received dan rejected tercatat untuk setiap produk

**Aliran Kejadian Utama (Main Flow):**
1. Checker menerima notifikasi bahwa ada Delivery Order dengan status "Shipped"
2. Checker mengakses menu Delivery Order pada App Panel
3. Sistem menampilkan daftar Shipment dengan filter status "Shipped"
4. Checker memilih Shipment yang barangnya telah tiba secara fisik
5. Checker memverifikasi dokumen DO yang dibawa kurir dengan data di sistem
6. Jika dokumen sesuai, Checker mengklik "Buat Penerimaan Barang"
7. Checker melakukan inspeksi fisik terhadap kondisi barang
8. Checker menginput quantity received (jumlah diterima dalam kondisi baik) dan quantity rejected (jumlah ditolak karena rusak/tidak sesuai) untuk setiap produk
9. Checker menginput alasan penolakan jika ada produk yang rejected
10. Checker mengupload foto atau scan dokumen Proof of Delivery (POD)
11. Sistem men-generate nomor Goods Receipt Number (GRN) otomatis
12. Sistem meset status GR = "Pending"
13. Sistem meset received_by = Checker yang sedang login
14. Sistem menyimpan Goods Receipt beserta detail items ke database
15. Sistem mengupdate status Shipment menjadi "Arrived"
16. Sistem mengirim notifikasi kepada Admin Gudang untuk verifikasi
17. Sistem menampilkan hasil sukses kepada Checker
18. Use case selesai

**Aliran Alternatif (Alternative Flow):**

- **Alt-1: Dokumen DO Tidak Sesuai**
  1. Pada step 5 main flow, Checker memeriksa dokumen DO
  2. Checker menemukan ketidaksesuaian antara dokumen fisik dengan data sistem
  3. Checker memilih opsi "Dokumen tidak sesuai"
  4. Sistem menampilkan error message "Dokumen tidak sesuai dengan data sistem"
  5. Checker menghubungi Supplier untuk klarifikasi
  6. Use case selesai tanpa membuat Goods Receipt

- **Alt-2: Semua Produk Diterima Tanpa Rejection**
  1. Pada step 8 main flow, Checker menginput quantity received
  2. Quantity received = shipped quantity untuk semua produk
  3. Quantity rejected = 0 untuk semua produk
  4. Checker tidak perlu input alasan penolakan
  5. Lanjut ke step 10 main flow

- **Alt-3: Ada Produk yang Ditolak**
  1. Pada step 8 main flow, Checker menginput quantity received dan rejected
  2. Quantity rejected > 0 untuk beberapa produk
  3. Pada step 9, Checker wajib menginput alasan penolakan (rusak, tidak sesuai spesifikasi, kadaluarsa, dll)
  4. Lanjut ke step 10 main flow

**Aliran Eksepsi (Exception Flow):**

- **Exc-1: Validasi Quantity Gagal**
  1. Pada step 11 main flow, sistem melakukan validasi
  2. Sistem mendeteksi sum(quantity received + quantity rejected) > shipped quantity
  3. Sistem menampilkan error "Total quantity received dan rejected tidak boleh melebihi quantity yang dikirim"
  4. Kembali ke step 8 untuk perbaikan input quantity

- **Exc-2: Dokumen POD Belum Diupload**
  1. Pada step 10 main flow, Checker belum mengupload dokumen POD
  2. Sistem menampilkan error "Dokumen POD wajib diupload sebagai bukti penerimaan"
  3. Checker harus upload dokumen POD terlebih dahulu
  4. Kembali ke step 10 untuk upload dokumen

- **Exc-3: Alasan Penolakan Tidak Diisi**
  1. Pada step 9 main flow, ada produk dengan quantity rejected > 0
  2. Checker tidak menginput alasan penolakan
  3. Sistem menampilkan error "Alasan penolakan wajib diisi untuk produk yang ditolak"
  4. Kembali ke step 9 untuk input alasan penolakan

---

### 3.4.6 AD-05: Activity Diagram Verifikasi Penerimaan Barang

**Pemetaan:** UC-04 (Penerimaan Barang) - Sub-flow: Verifikasi & Selesaikan Goods Receipt
**File Diagram:** `5-goods-receipt-verification.puml`

#### Flow of Event

**Use Case:** Memverifikasi dan Menyelesaikan Penerimaan Barang (Verify & Complete Goods Receipt)

**Deskripsi:** Use case ini memungkinkan Admin Gudang untuk memverifikasi dan menyelesaikan Goods Receipt yang dibuat oleh Checker, yang akan men-trigger cascade updates ke stock produk, status PO, status Shipment, dan notifikasi ke Accounting.

**Aktor:** Admin Gudang

**Kondisi Awal (Pre-conditions):**
- Admin Gudang sudah login ke Admin Panel
- Goods Receipt dengan status "Pending" sudah tersedia (dibuat oleh Checker)
- Admin Gudang menerima notifikasi ada GR baru yang perlu diverifikasi
- Dokumen Proof of Delivery (POD) sudah terupload

**Kondisi Akhir (Post-conditions):**
- Goods Receipt berstatus "Completed"
- Stock quantity produk terupdate (bertambah sesuai quantity received)
- Stock movement history tercatat untuk audit trail
- Status Purchase Order terupdate (Partial/Completed berdasarkan kalkulasi)
- Status Shipment terupdate menjadi "Processed"
- Notifikasi terkirim ke Accounting untuk pelaporan keuangan
- Notifikasi terkirim ke Supplier jika ada produk yang ditolak

**Aliran Kejadian Utama (Main Flow):**
1. Admin Gudang menerima notifikasi bahwa ada Goods Receipt baru yang perlu diverifikasi
2. Admin Gudang mengakses menu Goods Receipt pada Admin Panel
3. Sistem menampilkan daftar Goods Receipt dengan filter status "Pending"
4. Admin Gudang memilih Goods Receipt yang akan diverifikasi
5. Sistem menampilkan detail lengkap GR: informasi Shipment, Purchase Order terkait, daftar produk dengan quantity received dan rejected, foto POD, catatan dari Checker
6. Admin Gudang melakukan review menyeluruh: memeriksa kesesuaian quantity dengan dokumen, memvalidasi kondisi barang dari foto POD, memastikan tidak ada discrepancy
7. Jika data sesuai, Admin Gudang mengklik "Verifikasi Penerimaan"
8. Sistem mengubah status GR menjadi "Verified"
9. Sistem menyimpan perubahan status
10. Admin Gudang memutuskan untuk menyelesaikan penerimaan
11. Admin Gudang mengklik "Selesaikan Penerimaan"
12. Admin Gudang mengkonfirmasi penyelesaian
13. Sistem mengubah status GR menjadi "Completed"
14. Sistem mengupdate stock quantity produk dengan menambahkan quantity received menggunakan method `increaseStock()`
15. Sistem mencatat stock movement history untuk audit trail
16. Sistem menghitung progress PO dan mengupdate status: jika semua item sudah diterima lengkap maka status = "Completed", jika sebagian diterima maka status = "Partial"
17. Sistem mengupdate status Shipment menjadi "Processed"
18. Sistem mengirim notifikasi kepada Accounting bahwa ada transaksi penerimaan barang yang sudah final
19. Jika ada produk yang ditolak, sistem mengirim notifikasi kepada Supplier
20. Sistem menampilkan hasil sukses kepada Admin Gudang
21. Use case selesai

**Aliran Alternatif (Alternative Flow):**

- **Alt-1: Verifikasi Tanpa Langsung Selesaikan**
  1. Setelah step 9 main flow, status GR = "Verified"
  2. Admin Gudang memilih "Tidak" pada konfirmasi selesaikan penerimaan
  3. Sistem kembali menampilkan daftar Goods Receipt
  4. GR tetap berstatus "Verified", belum "Completed"
  5. Admin Gudang dapat kembali lagi nanti untuk menyelesaikan
  6. Use case selesai

- **Alt-2: Data Tidak Sesuai - Koreksi Data**
  1. Pada step 6 main flow, Admin Gudang menemukan ketidaksesuaian data
  2. Admin Gudang melakukan koreksi pada quantity received atau rejected
  3. Admin Gudang menambahkan catatan tambahan mengenai discrepancy
  4. Sistem menyimpan perubahan data
  5. Lanjut ke step 7 main flow untuk verifikasi

- **Alt-3: Data Tidak Sesuai - Kembalikan ke Checker**
  1. Pada step 6 main flow, Admin Gudang menemukan ketidaksesuaian data yang signifikan
  2. Admin Gudang memilih opsi "Identifikasi masalah"
  3. Admin Gudang menginput catatan masalah yang ditemukan
  4. Admin Gudang memilih "Kembalikan ke Checker" atau "Hubungi Supplier"
  5. Sistem tetap menjaga status GR = "Pending"
  6. Sistem mengirim notifikasi ke pihak terkait (Checker/Supplier)
  7. Use case selesai tanpa completion

**Aliran Eksepsi (Exception Flow):**

- **Exc-1: GR Tidak Dalam Status Pending/Verified**
  1. Pada step 4 main flow, Admin Gudang memilih GR
  2. Sistem mendeteksi status GR bukan "Pending" atau "Verified"
  3. Sistem menampilkan error "Goods Receipt tidak dapat diverifikasi karena sudah diproses"
  4. Use case selesai tanpa perubahan

- **Exc-2: Dokumen POD Tidak Ada**
  1. Pada step 5 main flow, sistem menampilkan detail GR
  2. Sistem mendeteksi dokumen POD belum diupload
  3. Sistem menampilkan warning "Dokumen POD belum tersedia"
  4. Admin Gudang dapat memilih kembalikan ke Checker atau lanjut verifikasi dengan catatan
  5. Jika lanjut, lanjut ke step 7 main flow

- **Exc-3: Database Transaction Gagal**
  1. Pada step 13-17 main flow, sistem melakukan cascade updates
  2. Terjadi error pada salah satu operasi database
  3. Sistem melakukan rollback seluruh transaksi untuk menjaga atomicity
  4. Sistem menampilkan error "Gagal menyelesaikan penerimaan, silakan coba lagi"
  5. Status GR tetap "Verified", belum "Completed"
  6. Use case selesai tanpa perubahan stock

---

### 3.4.7 AD-06: Activity Diagram Pembuatan Laporan Bulanan

**Pemetaan:** UC-07 (Membuat Laporan Bulanan)
**File Diagram:** `6-monthly-report-generation.puml`

#### Flow of Event

**Use Case:** Membuat Laporan Bulanan

**Deskripsi:** Use case ini memungkinkan Accounting untuk membuat dan mengunduh berbagai jenis laporan bulanan (Purchase Order, Penerimaan Barang, Stok Produk, Keuangan) untuk keperluan analisis finansial, audit, dan decision making.

**Aktor:** Accounting

**Kondisi Awal (Pre-conditions):**
- Accounting sudah login ke App Panel
- Data transaksi (PO, GR, Stock Movement) sudah tersedia di database
- Accounting memiliki permission untuk mengakses menu laporan

**Kondisi Akhir (Post-conditions):**
- Laporan berhasil di-generate berdasarkan parameter yang dipilih
- File laporan dalam format PDF/Excel/CSV tersedia untuk didownload (opsional)
- Aktivitas generate dan download laporan tercatat dalam audit log
- Laporan menampilkan data real-time sesuai kondisi database saat query

**Aliran Kejadian Utama (Main Flow):**
1. Accounting mengakses menu Laporan pada App Panel
2. Accounting memilih periode laporan (bulan dan tahun)
3. Accounting memilih jenis laporan yang dibutuhkan
4. Sistem melakukan query data dari database berdasarkan periode dan jenis laporan
5. Sistem melakukan kalkulasi dan agregasi data
6. Sistem men-generate grafik dan chart sesuai jenis laporan
7. Sistem menampilkan preview laporan di browser
8. Accounting mereview laporan yang ditampilkan
9. Jika perlu export, Accounting memilih format file (PDF/Excel/CSV)
10. Sistem men-generate dan men-download file laporan
11. Accounting menyimpan file laporan ke lokal
12. Use case selesai

**Aliran Alternatif (Alternative Flow):**

- **Alt-1: Laporan Purchase Order**
  1. Pada step 3 main flow, Accounting memilih "Laporan Purchase Order"
  2. Sistem melakukan query data PO berdasarkan periode yang dipilih
  3. Sistem menghitung statistik dan metrik: total PO count, total amount, breakdown per supplier, status distribution (Pending/Partial/Completed/Cancelled)
  4. Sistem men-generate grafik trend pemesanan per bulan dan pie chart status PO
  5. Lanjut ke step 7 main flow

- **Alt-2: Laporan Penerimaan Barang**
  1. Pada step 3 main flow, Accounting memilih "Laporan Penerimaan Barang"
  2. Sistem melakukan query data Goods Receipt dengan status "Completed" berdasarkan periode
  3. Sistem menghitung acceptance rate, rejection rate, dan variance analysis
  4. Sistem men-generate grafik performa supplier berdasarkan acceptance rate
  5. Lanjut ke step 7 main flow

- **Alt-3: Laporan Stok Produk**
  1. Pada step 3 main flow, Accounting memilih "Laporan Stok Produk"
  2. Sistem melakukan query data produk dan stock movement history
  3. Sistem menghitung current stock level, produk dengan stok rendah (below reorder point), projected stock
  4. Sistem men-generate grafik distribusi stok per kategori produk
  5. Lanjut ke step 7 main flow

- **Alt-4: Laporan Keuangan**
  1. Pada step 3 main flow, Accounting memilih "Laporan Keuangan"
  2. Sistem melakukan query data PO dan GR dengan status "Completed"
  3. Sistem menghitung total pengeluaran (sum total amount dari PO completed), outstanding PO (sum total amount dari PO pending/partial), payment projection
  4. Sistem men-generate grafik spending trend per bulan dan cost analysis per supplier
  5. Lanjut ke step 7 main flow

- **Alt-5: Tidak Perlu Export**
  1. Pada step 8 main flow, setelah mereview laporan
  2. Accounting memilih "Tidak" untuk export
  3. Accounting menutup preview laporan
  4. Use case selesai tanpa download file

- **Alt-6: Batal/Kembali**
  1. Setelah step 2 main flow, Accounting memilih "Batal"
  2. Sistem kembali ke menu sebelumnya
  3. Use case selesai tanpa generate laporan

**Aliran Eksepsi (Exception Flow):**

- **Exc-1: Tidak Ada Data Pada Periode**
  1. Pada step 4 main flow, sistem melakukan query data
  2. Sistem mendeteksi tidak ada data transaksi pada periode yang dipilih
  3. Sistem menampilkan pesan "Tidak ada data untuk periode yang dipilih"
  4. Accounting dapat memilih periode lain atau membatalkan
  5. Jika pilih periode lain, kembali ke step 2 main flow

- **Exc-2: Error Generate File**
  1. Pada step 10 main flow, sistem men-generate file untuk download
  2. Terjadi error pada proses generate (disk full, permission error, dll)
  3. Sistem menampilkan error "Gagal men-generate file laporan, silakan coba lagi"
  4. Accounting dapat retry atau kembali ke preview laporan
  5. Jika retry, kembali ke step 9 main flow

- **Exc-3: Query Timeout**
  1. Pada step 4 main flow, sistem melakukan query data
  2. Query memakan waktu terlalu lama (lebih dari timeout threshold)
  3. Sistem menampilkan error "Query terlalu lama, silakan pilih periode yang lebih kecil"
  4. Accounting memilih periode yang lebih spesifik
  5. Kembali ke step 2 main flow

---

### 3.4.8 AD-07: Activity Diagram Melihat Produk (Supplier)

**Pemetaan:** UC-08 (Melihat Produk Supplier)
**File Diagram:** `7-view-product.puml`

#### Flow of Event

**Use Case:** Melihat Produk (Supplier)

**Deskripsi:** Use case ini memungkinkan Supplier untuk mengakses informasi katalog produk yang mereka supply secara read-only, mencakup filter/pencarian, view detail produk, riwayat stok, grafik trend, dan export data untuk keperluan tracking dan inventory planning.

**Aktor:** Supplier

**Kondisi Awal (Pre-conditions):**
- Supplier sudah login ke Supplier Panel
- Data produk untuk supplier tersebut sudah tersedia di database
- Tenant isolation aktif untuk filter produk per supplier

**Kondisi Akhir (Post-conditions):**
- Supplier mendapatkan informasi produk yang dibutuhkan
- File export tersedia untuk didownload (jika memilih export)
- Tidak ada perubahan data produk (use case bersifat read-only)

**Aliran Kejadian Utama (Main Flow):**
1. Supplier mengakses menu Produk pada Supplier Panel
2. Sistem menampilkan daftar produk dengan filter otomatis hanya menampilkan produk dari supplier tersebut (tenant isolation)
3. Supplier memilih aksi yang akan dilakukan
4. Sistem memproses aksi sesuai pilihan Supplier
5. Sistem menampilkan hasil
6. Use case selesai

**Aliran Alternatif (Alternative Flow):**

- **Alt-1: Filter/Cari Produk**
  1. Pada step 3 main flow, Supplier memilih aksi "Filter/Cari"
  2. Supplier menginput kriteria filter (nama produk, kode produk, range harga, kategori)
  3. Sistem melakukan query filtering berdasarkan kriteria
  4. Sistem menampilkan hasil filter yang match dengan kriteria
  5. Supplier melihat hasil filter
  6. Kembali ke step 5 main flow

- **Alt-2: Lihat Detail Produk**
  1. Pada step 3 main flow, Supplier memilih aksi "Lihat Detail"
  2. Supplier memilih satu produk dari list
  3. Sistem menampilkan detail lengkap produk: kode, nama, spesifikasi, harga, satuan
  4. Sistem menampilkan informasi stok: current quantity, reorder point, status stok
  5. Sistem menampilkan statistik produk: total quantity yang pernah dipesan, frekuensi pemesanan
  6. Supplier dapat memilih sub-aksi: lihat riwayat stok atau lihat grafik trend
  7. Supplier selesai melihat detail
  8. Kembali ke step 5 main flow

- **Alt-3: Lihat Riwayat Pergerakan Stok**
  1. Pada Alt-2 step 6, Supplier memilih "Lihat riwayat stok"
  2. Sistem melakukan query stock movement history untuk produk tersebut
  3. Sistem menampilkan riwayat pergerakan stok: tanggal, tipe movement (in/out), quantity, balance, referensi (GR number)
  4. Supplier melihat riwayat pergerakan stok
  5. Kembali ke Alt-2 step 7

- **Alt-4: Lihat Grafik Trend**
  1. Pada Alt-2 step 6, Supplier memilih "Lihat grafik trend"
  2. Sistem melakukan agregasi data pemesanan produk per periode
  3. Sistem men-generate dan menampilkan grafik trend: line chart quantity dipesan per bulan, bar chart frekuensi pemesanan
  4. Supplier menganalisis grafik untuk memahami pola pemesanan
  5. Kembali ke Alt-2 step 7

- **Alt-5: Export Data Produk**
  1. Pada step 3 main flow, Supplier memilih aksi "Export Data"
  2. Supplier memilih produk-produk tertentu (atau semua) yang akan diexport
  3. Supplier memilih format file (Excel atau CSV)
  4. Sistem men-generate file sesuai format yang dipilih
  5. Sistem men-trigger download file
  6. Supplier menyimpan file ke lokal
  7. Kembali ke step 5 main flow

- **Alt-6: Batal/Kembali**
  1. Pada step 3 main flow, Supplier memilih "Batal"
  2. Sistem kembali ke menu sebelumnya
  3. Use case selesai

**Aliran Eksepsi (Exception Flow):**

- **Exc-1: Tidak Ada Produk untuk Supplier**
  1. Pada step 2 main flow, sistem melakukan query produk
  2. Sistem mendeteksi tidak ada produk yang ter-assign ke supplier tersebut
  3. Sistem menampilkan pesan "Belum ada produk yang ter-assign untuk supplier Anda"
  4. Use case selesai tanpa menampilkan data

- **Exc-2: Filter Tidak Menemukan Hasil**
  1. Pada Alt-1 step 3, sistem melakukan query filtering
  2. Sistem tidak menemukan produk yang match dengan kriteria
  3. Sistem menampilkan pesan "Tidak ada produk yang sesuai dengan kriteria pencarian"
  4. Supplier dapat mengubah kriteria filter atau membatalkan
  5. Jika ubah kriteria, kembali ke Alt-1 step 2

- **Exc-3: Error Generate File Export**
  1. Pada Alt-5 step 4, sistem men-generate file
  2. Terjadi error pada proses generate (disk full, permission error, dll)
  3. Sistem menampilkan error "Gagal men-generate file export, silakan coba lagi"
  4. Supplier dapat retry atau membatalkan
  5. Jika retry, kembali ke Alt-5 step 3

**Catatan Implementasi:**
- Fitur ini masih berstatus "Not Implemented" dalam sistem saat ini
- Merupakan kandidat untuk future enhancement
- Use case bersifat read-only, Supplier tidak dapat melakukan create/update/delete produk

---

### 3.4.9 AD-08: Activity Diagram Mengirim Pesan

**Pemetaan:** UC-06 (Mengirim Pesan)
**File Diagram:** `8-messaging.puml`

#### Flow of Event

**Use Case:** Mengirim Pesan

**Deskripsi:** Use case ini memungkinkan semua pengguna sistem untuk berkomunikasi secara real-time menggunakan fitur messaging dengan Laravel Reverb dan WebSocket, mencakup private chat dan group chat dengan notifikasi real-time.

**Aktor:** All Users (Admin Gudang, Checker, Accounting, Supplier)

**Kondisi Awal (Pre-conditions):**
- User sudah login ke sistem (Admin Panel atau Supplier Panel atau App Panel)
- Laravel Reverb WebSocket server sedang running
- User memiliki permission untuk mengakses fitur chat

**Kondisi Akhir (Post-conditions):**
- Pesan berhasil terkirim dan tersimpan di database
- Pesan ter-broadcast secara real-time ke semua participants
- Recipient menerima notifikasi push (jika chat tertutup)
- Unread counter terupdate untuk recipient
- Status pesan berubah menjadi "terkirim"

**Aliran Kejadian Utama (Main Flow):**
1. User mengakses menu Chat/Pesan
2. Sistem menampilkan daftar chat yang sudah ada sebelumnya (jika ada)
3. User memilih aksi yang akan dilakukan
4. Sistem memproses aksi sesuai pilihan User
5. Chat siap untuk mengirim pesan
6. User mengetik dan mengirim pesan
7. Sistem memvalidasi pesan (tidak boleh kosong, check spam threshold)
8. Sistem menyimpan pesan ke database dengan timestamp
9. Sistem melakukan broadcast menggunakan Reverb/WebSocket ke semua participants
10. Sistem mengirim notifikasi push kepada recipient
11. Sistem menampilkan pesan terkirim dengan status "terkirim"
12. Use case selesai

**Aliran Alternatif (Alternative Flow):**

- **Alt-1: Buat Chat Baru - Private Chat**
  1. Pada step 3 main flow, User memilih "Buat Chat Baru"
  2. User memilih jenis chat "Private"
  3. User memilih satu user tujuan dari list yang tersedia
  4. Sistem melakukan validasi akses komunikasi (business rule: Supplier hanya bisa chat dengan internal users, tidak dengan Supplier lain)
  5. Sistem memeriksa apakah private chat antara kedua user sudah pernah ada
  6. Jika sudah ada, sistem membuka existing chat
  7. Jika belum ada, sistem membuat chat room baru
  8. Lanjut ke step 5 main flow (Chat siap)

- **Alt-2: Buat Chat Baru - Group Chat**
  1. Pada step 3 main flow, User memilih "Buat Chat Baru"
  2. User memilih jenis chat "Group"
  3. User menginput nama group
  4. User memilih anggota group (minimal 2 anggota selain creator)
  5. Sistem memvalidasi jumlah anggota (minimal 2 + creator = 3 total)
  6. Sistem membuat group chat baru
  7. Sistem meset creator sebagai admin group
  8. Sistem mengirim notifikasi kepada semua anggota yang ditambahkan
  9. Lanjut ke step 5 main flow (Chat siap)

- **Alt-3: Buka Chat Existing**
  1. Pada step 3 main flow, User memilih "Buka Chat"
  2. User memilih chat dari list
  3. Sistem menampilkan riwayat pesan dengan lazy loading untuk performa
  4. Sistem secara otomatis mark semua pesan yang belum dibaca sebagai "read"
  5. Sistem decrement unread counter untuk user tersebut
  6. Lanjut ke step 5 main flow (Chat siap)

- **Alt-4: Batal/Kembali**
  1. Pada step 3 main flow, User memilih "Batal"
  2. Sistem kembali ke daftar chat atau menu sebelumnya
  3. Use case selesai tanpa mengirim pesan

**Aliran Eksepsi (Exception Flow):**

- **Exc-1: Validasi Akses Chat Gagal**
  1. Pada Alt-1 step 4, sistem memvalidasi akses komunikasi
  2. Sistem mendeteksi user tidak berhak berkomunikasi (contoh: Supplier mencoba chat dengan Supplier lain)
  3. Sistem menampilkan error "Anda tidak memiliki akses untuk berkomunikasi dengan user tersebut"
  4. Use case selesai tanpa membuat chat

- **Exc-2: Validasi Pesan Gagal - Pesan Kosong**
  1. Pada step 7 main flow, sistem memvalidasi pesan
  2. Sistem mendeteksi pesan kosong atau hanya whitespace
  3. Sistem menampilkan error "Pesan tidak boleh kosong"
  4. Kembali ke step 6 untuk input ulang pesan

- **Exc-3: Spam Threshold Exceeded**
  1. Pada step 7 main flow, sistem melakukan spam check
  2. Sistem mendeteksi user mengirim terlalu banyak pesan dalam waktu singkat
  3. Sistem menampilkan warning "Anda mengirim pesan terlalu cepat, mohon tunggu beberapa detik"
  4. User harus menunggu cooldown period
  5. Kembali ke step 6 untuk mengirim pesan

- **Exc-4: Validasi Group Chat Gagal - Anggota Kurang dari Minimal**
  1. Pada Alt-2 step 5, sistem memvalidasi jumlah anggota
  2. Sistem mendeteksi anggota yang dipilih kurang dari 2 (selain creator)
  3. Sistem menampilkan error "Group chat minimal memiliki 3 anggota (termasuk Anda)"
  4. Kembali ke Alt-2 step 4 untuk memilih anggota tambahan

- **Exc-5: WebSocket Connection Error**
  1. Pada step 9 main flow, sistem melakukan broadcast via WebSocket
  2. WebSocket connection error atau Reverb server down
  3. Sistem menyimpan pesan ke database tetapi gagal broadcast real-time
  4. Sistem menampilkan warning "Pesan tersimpan tetapi mungkin tertunda untuk diterima recipient"
  5. Sistem akan retry broadcast setelah connection restored

**Catatan Implementasi:**
- Recipient akan menerima pesan secara real-time jika chat window sedang terbuka
- Recipient akan menerima notifikasi push jika chat tertutup
- Unread counter akan increment untuk recipient
- Fitur ini memiliki implementasi partial dengan 36% test coverage
- Menjadi area yang memerlukan enhancement untuk production readiness

---

### 3.4.10 Kesimpulan Perancangan Activity Diagram

Kedelapan activity diagram yang telah dirancang memberikan visualisasi komprehensif terhadap seluruh proses bisnis dalam Sistem Informasi Warelink. Diagram-diagram ini menunjukkan interaksi yang jelas antara aktor dan sistem, decision point yang mencerminkan business rules, serta cascade effect antar proses yang menjaga konsistensi data.

Perancangan activity diagram mengikuti prinsip **BPMN-like notation** dengan swimlane untuk separation of concerns, conditional branching untuk business logic, dan system actions yang eksplisit untuk transparency. Setiap diagram telah dipetakan dengan use case dan kebutuhan fungsional yang sesuai, memastikan traceability dari requirement hingga implementasi.

Implementasi sistem berdasarkan activity diagram ini menghasilkan coverage yang tinggi: 6 dari 8 diagram sudah diimplementasikan dengan test coverage 100%, 1 diagram dengan partial implementation (36%), dan 1 diagram belum diimplementasikan. Diagram-diagram ini menjadi blueprint teknis yang memandu development team dalam implementasi fitur dan menjadi dokumentasi untuk stakeholder dalam memahami workflow sistem.
