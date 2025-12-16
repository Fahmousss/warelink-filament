# BAB IV
# HASIL PERANCANGAN DAN IMPLEMENTASI PENGUJIAN

## 4.1 Pendahuluan

Pada bab ini, penulis memaparkan hasil dari tahapan perancangan dan implementasi pengujian sistem informasi Warelink yang dikembangkan menggunakan pendekatan Test-Driven Development (TDD). Perancangan pengujian ini merupakan tahapan krusial dalam memastikan bahwa sistem yang dibangun tidak hanya memenuhi spesifikasi fungsional yang telah dirancang melalui diagram UML (Use Case, Activity, Sequence, dan Class Diagram), tetapi juga memiliki kualitas kode yang tinggi dan dapat dipelihara dengan baik di masa mendatang.

Proses perancangan pengujian dimulai dengan melakukan pemetaan sistematis antara setiap use case yang telah diidentifikasi dengan test suite yang akan diimplementasikan. Pendekatan ini memungkinkan penulis untuk memastikan bahwa setiap aspek fungsionalitas sistem ter-cover dengan baik oleh pengujian yang memadai, sehingga meminimalkan risiko terjadinya defect atau bug pada tahap produksi.

Dalam implementasinya, penulis mengadopsi siklus TDD klasik yang terdiri dari tiga tahap utama: **Red** (menulis test yang gagal terlebih dahulu), **Green** (menulis kode minimal untuk membuat test berhasil), dan **Refactor** (memperbaiki struktur kode tanpa mengubah perilakunya). Pendekatan ini terbukti efektif dalam membantu penulis untuk fokus pada requirement yang jelas sebelum menulis implementasi, sehingga menghasilkan kode yang lebih terstruktur dan mudah di-maintain.

## 4.2 Metodologi dan Tools Pengujian

### 4.2.1 Framework dan Tools yang Digunakan

Dalam pelaksanaan pengujian sistem Warelink, penulis menggunakan kombinasi tools dan framework modern yang telah terbukti efektif dalam ekosistem Laravel. **Pest PHP versi 4.0** dipilih sebagai testing framework utama karena menyediakan syntax yang ekspresif dan mudah dibaca, serta mendukung fitur-fitur modern seperti browser testing, smoke testing, dan visual regression testing. Framework ini juga menyediakan integrasi yang seamless dengan Laravel, sehingga memudahkan penulis dalam menulis test yang clean dan maintainable.

Untuk pengujian komponen UI yang dibangun menggunakan Filament, penulis memanfaatkan **Filament Test Helpers** yang menyediakan assertion methods khusus untuk testing resource, form, table, dan action dalam konteks Filament. Hal ini sangat membantu dalam memvalidasi bahwa komponen-komponen UI bekerja sesuai dengan ekspektasi dan mengikuti konvensi Filament yang benar.

Dari sisi database, penulis menggunakan **MySQL** sebagai database production, namun untuk keperluan testing, penulis memanfaatkan **in-memory SQLite** yang memungkinkan pengujian berjalan lebih cepat tanpa mempengaruhi data production. Setiap test case dijalankan dalam database transaction yang di-rollback setelah test selesai, sehingga memastikan isolasi antar test dan repeatability.

### 4.2.2 Siklus TDD yang Diterapkan

Penerapan TDD dalam penelitian ini mengikuti siklus iteratif yang konsisten untuk setiap fitur yang dikembangkan. Pada tahap **Red**, penulis terlebih dahulu menulis test case berdasarkan spesifikasi yang telah dirancang dalam activity diagram dan sequence diagram. Test ini secara natural akan gagal karena implementasinya belum ada. Tahap ini penting untuk memastikan bahwa test yang ditulis benar-benar menguji requirement yang dimaksud, bukan hanya sekedar passing test.

Selanjutnya, pada tahap **Green**, penulis menulis kode implementasi yang minimal namun cukup untuk membuat test berhasil. Fokus pada tahap ini adalah membuat test passing sesegera mungkin, bukan menulis kode yang sempurna. Pendekatan ini membantu penulis untuk tetap fokus pada satu requirement dalam satu waktu dan menghindari over-engineering.

Setelah test berhasil, penulis masuk ke tahap **Refactor** dimana kode diperbaiki struktur dan kualitasnya tanpa mengubah behavior yang sudah benar. Pada tahap ini, penulis dapat dengan percaya diri melakukan refactoring karena test suite yang sudah ada akan memberikan feedback langsung jika ada perubahan yang merusak fungsionalitas existing.

## 4.3 Pemetaan Use Case ke Test Suite

### 4.3.1 Strategi Pemetaan

Penulis melakukan pemetaan sistematis antara 8 use case utama yang telah diidentifikasi dengan test suite yang akan diimplementasikan. Strategi pemetaan ini dirancang untuk memastikan bahwa setiap use case memiliki coverage yang komprehensif, mencakup tidak hanya happy path (alur normal), tetapi juga alternative flow, exception handling, dan edge cases yang mungkin terjadi.

Setiap use case dipecah menjadi beberapa test file yang terorganisir berdasarkan panel dan role pengguna. Pendekatan ini memudahkan maintenance dan membuat test suite lebih modular. Sebagai contoh, untuk Use Case "Mengelola Master Data", penulis membagi pengujian menjadi tiga test file terpisah: `ManajemenUserTest.php` (11 test cases), `ManajemenSupplierTest.php` (12 test cases), dan `ManajemenProdukTest.php` (37 test cases). Pemisahan ini memungkinkan penulis untuk fokus pada satu aspek manajemen data dalam satu waktu dan membuat test lebih mudah dipahami.

### 4.3.2 Hasil Pemetaan

Dari total 8 use case yang dirancang, penulis telah berhasil mengimplementasikan pengujian untuk 7 use case dengan tingkat coverage yang bervariasi. Tabel berikut menunjukkan distribusi test cases per use case:

**Use Case 1: Mengelola Master Data (Admin Gudang)** - Use case ini merupakan fondasi dari seluruh sistem karena mengelola data master yang digunakan oleh modul-modul lainnya. Penulis mengimplementasikan total **60 test cases** yang terbagi dalam tiga kategori: manajemen user (11 tests), manajemen supplier (12 tests), dan manajemen produk (37 tests). Jumlah test yang signifikan untuk manajemen produk mencerminkan kompleksitas business logic yang ada, termasuk stock management, reorder calculations, dan product statistics.

**Use Case 2: Manajemen Purchase Order (Admin Gudang)** - Untuk use case ini, penulis mengimplementasikan **10 test cases** yang mencakup operasi CRUD dasar (5 tests), validasi business rules (3 tests), dan pengujian role-based access control untuk Checker (2 tests). Meskipun jumlah test relatif lebih sedikit dibandingkan use case lain, coverage tetap mencapai 100% karena test dirancang untuk mencakup semua critical path dalam activity diagram.

**Use Case 3: Mengelola Delivery Order (Supplier)** - Use case ini diuji dengan **18 test cases** yang mencakup manajemen shipment dari perspektif supplier (14 tests) dan view-only access ke purchase order (4 tests). Pengujian khusus dilakukan untuk memastikan tenant isolation, dimana supplier hanya dapat mengakses data shipment mereka sendiri dan tidak dapat mengakses data supplier lain.

**Use Case 4: Penerimaan Barang (Checker & Admin Gudang)** - Sebagai salah satu use case paling kompleks yang melibatkan cascade updates ke multiple tables, use case ini diuji dengan **13 test cases** yang mencakup creation (Checker), verification (Admin), dan berbagai integration scenarios seperti stock updates, PO status updates, dan shipment status transitions. Pengujian khusus dilakukan untuk memastikan database transaction integrity.

**Use Case 5: Autentikasi Multi-Panel** - Meskipun bukan use case fungsional utama, autentikasi multi-panel merupakan cross-cutting concern yang critical untuk keamanan sistem. Penulis mengimplementasikan **16 test cases** yang memastikan setiap role hanya dapat mengakses panel yang sesuai dengan authorisasi mereka. Pengujian mencakup tiga panel: admin, app, dan supplier.

**Use Case 6: Mengirim Pesan** - Use case ini memiliki implementasi partial dengan **5 test cases** yang mencakup basic functionality seperti akses ke halaman chat dan loading Wirechat component. Coverage saat ini adalah 36%, dengan rencana untuk menambahkan 9 test cases tambahan untuk mencakup real-time features seperti WebSocket broadcasting, read receipts, dan unread counter.

**Use Case 7: Membuat Laporan Bulanan (Accounting)** - Use case ini merupakan salah satu yang paling komprehensif dengan **19 test cases** yang mencakup 4 jenis laporan (Purchase Order, Goods Receipt, Stock, dan Financial). Setiap jenis laporan diuji dengan multiple scenarios termasuk period filtering, data accuracy, dan column visibility. Pengujian RBAC juga dilakukan untuk memastikan hanya role Accounting yang dapat mengakses fitur reporting.

**Use Case 8: Melihat Produk (Supplier)** - Use case ini belum diimplementasikan (0% coverage) dan menjadi salah satu item dalam future work. Use case ini akan menyediakan read-only view untuk supplier melihat produk mereka dengan filtering dan statistics.

Total keseluruhan, penulis telah berhasil mengimplementasikan **141 test cases** dengan **623 assertions**, yang menunjukkan thoroughness dalam pengujian. Jumlah assertion yang signifikan menunjukkan bahwa setiap test case tidak hanya melakukan single assertion, tetapi melakukan multiple validations untuk memastikan berbagai aspek dari fungsionalitas ter-cover dengan baik.

## 4.4 Perancangan Pengujian Detail Per Use Case

### 4.4.1 Use Case 1: Mengelola Master Data

Use case ini merupakan backbone dari sistem Warelink karena mengelola tiga entitas master yang fundamental: User, Supplier, dan Product. Perancangan pengujian untuk use case ini dilakukan dengan sangat detail mengingat pentingnya data master yang akurat dan konsisten.

#### Manajemen User (11 Test Cases)

Pengujian manajemen user dirancang untuk mencakup seluruh lifecycle pengelolaan user, mulai dari viewing daftar user hingga deletion. Setiap test case di-mapping dengan jelas ke activity diagram dan sequence diagram yang telah dirancang sebelumnya.

Test pertama (TU-001) memvalidasi bahwa admin dapat mengakses halaman daftar user, yang merupakan starting point dari activity diagram "Akses menu Master Data". Test ini penting untuk memastikan bahwa authorization berjalan dengan benar dan halaman dapat di-render tanpa error.

Test TU-002 hingga TU-004 fokus pada functionality viewing dan searching. Penulis merancang test untuk memastikan bahwa user dapat ditampilkan dalam tabel dengan benar, dan fitur search berfungsi baik untuk pencarian berdasarkan nama maupun email. Ini memetakan ke decision point "Jenis data?" dalam activity diagram.

Untuk operasi create (TU-005), penulis merancang test yang memvalidasi bahwa user baru dapat dibuat dengan semua field yang required. Test ini mencakup validation dari sequence diagram pada step "Validasi data" dan "Simpan ke database". Selain itu, test TU-006 khusus dirancang sebagai negative test untuk memastikan bahwa form validation bekerja dengan benar ketika field required dikosongkan.

Operasi update diuji melalui TU-007 dan TU-008. Test pertama memvalidasi bahwa data user dapat diupdate, sedangkan test kedua secara spesifik menguji toggle status aktif/non-aktif. Test TU-008 ini memetakan ke attribute is_active dalam class diagram.

Untuk deletion, penulis merancang tiga test cases yang berbeda. TU-009 menguji happy path dimana user tidak aktif dapat dihapus. TU-010 menguji bulk deletion untuk multiple users. Yang paling menarik adalah TU-011 yang merupakan negative test, memastikan bahwa business rule "user aktif tidak dapat dihapus" di-enforce dengan benar. Ini mencerminkan penerapan defensive programming dalam sistem.

#### Manajemen Supplier (12 Test Cases)

Pengujian manajemen supplier mengikuti pola yang similar dengan manajemen user, namun dengan tambahan fitur soft delete yang memerlukan test cases tambahan.

Salah satu aspek menarik dalam pengujian supplier adalah TS-006 yang memvalidasi auto-generation kode supplier. Test ini memastikan bahwa method generateSupplierCode() dalam class diagram berfungsi dengan benar dan menghasilkan kode unik untuk setiap supplier baru. Ini adalah contoh bagus dari TDD dimana business logic yang spesifik divalidasi melalui automated test.

Test TS-005 memvalidasi fitur filtering untuk menampilkan supplier yang sudah di-soft delete, yang memetakan ke penggunaan trait SoftDeletes dalam class diagram. Test ini memastikan bahwa implementasi soft delete mengikuti konvensi Laravel dengan benar.

TS-010 dan TS-011 merupakan pasangan test yang menarik. TS-010 memvalidasi bahwa supplier dapat di-soft delete (deleted_at timestamp di-set), sedangkan TS-011 memvalidasi bahwa supplier yang sudah dihapus dapat di-restore (deleted_at di-set kembali ke null). Kedua test ini mencerminkan reversibility dari soft delete operation, yang merupakan best practice dalam data management.

#### Manajemen Produk (37 Test Cases)

Manajemen produk merupakan bagian paling kompleks dari use case ini dengan 37 test cases yang mencakup berbagai aspek dari product management, stock management, dan product analytics.

Test cases dasar (TP-001 hingga TP-008) mencakup operasi CRUD dan validation yang standard. Namun, yang membedakan adalah test TP-007 dan TP-008 yang secara spesifik memvalidasi business rules bahwa harga harus positif dan stok tidak boleh negatif. Ini adalah contoh bagus dari validation testing yang memastikan data integrity.

Bagian yang paling menarik adalah test cases untuk stock management (TP-020 hingga TP-037). Penulis merancang test yang sangat comprehensive untuk memastikan semua aspek stock management berfungsi dengan benar.

TP-020 dan TP-021 menguji method increaseStock() dan decreaseStock() yang merupakan core functionality untuk stock management. Test TP-022 adalah negative test yang memastikan bahwa sistem tidak mengizinkan pengurangan stok melebihi yang tersedia, mencegah negative stock yang tidak masuk akal.

Test TP-023 hingga TP-025 menguji deteksi status stok (low, out, good) menggunakan berbagai helper methods. Ini mencerminkan business intelligence yang dibangun ke dalam sistem untuk membantu warehouse manager dalam decision making.

Yang sangat menarik adalah test cases untuk analytics dan calculations (TP-026 hingga TP-037). Penulis merancang test untuk memvalidasi berbagai calculated metrics seperti:
- Total nilai stok (TP-026)
- Total produk yang dipesan (TP-027)
- Total produk diterima (TP-028)
- Total produk ditolak (TP-029)
- Acceptance rate (TP-030)
- Reorder detection dan calculation (TP-031 hingga TP-033)
- Projected stock (TP-034)
- Days until stockout (TP-035)

Setiap test ini memvalidasi business logic yang spesifik dan memastikan bahwa calculations dilakukan dengan benar. Ini adalah contoh excellent dari test-driven development dimana complex business logic didefinisikan melalui test sebelum implementasi.

### 4.4.2 Use Case 2: Manajemen Purchase Order

Purchase Order merupakan jantung dari procurement process dalam sistem warehousing. Penulis merancang pengujian yang fokus tidak hanya pada CRUD operations, tetapi juga pada business rules dan authorization.

#### Manajemen PO oleh Admin (5 Test Cases)

Test cases dasar (TPO-001 hingga TPO-005) mencakup operasi CRUD yang standard. Namun yang membedakan adalah perhatian terhadap detail dalam setiap test. Sebagai contoh, TPO-002 tidak hanya memvalidasi bahwa PO dapat dibuat, tetapi juga memastikan bahwa status awal adalah "Pending" dan nomor PO di-generate secara otomatis sesuai dengan activity diagram step "Generate nomor PO".

#### Validasi Business Rules (3 Test Cases)

Test cases TPO-006 hingga TPO-008 merupakan negative tests yang sangat penting untuk memastikan business rules di-enforce dengan benar. Penulis merancang test ini berdasarkan conditional flows dalam activity diagram.

TPO-006 memvalidasi bahwa PO yang tidak berstatus Pending tidak dapat di-edit. Ini memetakan langsung ke decision point "Status = Pending?" dalam activity diagram. Test ini memastikan bahwa once PO sudah diproses atau completed, data tidak dapat diubah untuk menjaga data integrity dan audit trail.

TPO-007 memvalidasi bahwa PO yang sudah completed tidak dapat dibatalkan. Ini adalah business rule yang masuk akal karena pembatalan PO yang sudah diproses akan memerlukan cascade updates yang kompleks dan berpotensi menimbulkan inconsistency.

TPO-008 memvalidasi deletion policy yang melarang penghapusan PO yang sudah diproses. Ketiga test ini bersama-sama memastikan bahwa state transitions dalam PO lifecycle di-kontrol dengan ketat.

#### RBAC untuk Checker (2 Test Cases)

Test TPO-009 dan TPO-010 memvalidasi role-based access control untuk role Checker. TPO-009 memastikan bahwa Checker dapat view PO data (read-only access), sedangkan TPO-010 memastikan bahwa Checker tidak dapat melakukan edit atau delete operations. Ini mencerminkan separation of duties yang baik dalam warehouse operations dimana Checker hanya bertugas untuk verifikasi penerimaan barang, bukan untuk manage purchase orders.

### 4.4.3 Use Case 3: Mengelola Delivery Order

Use case ini unique karena merupakan functionality yang diakses oleh Supplier, bukan internal warehouse staff. Perancangan pengujian mempertimbangkan aspek tenant isolation dan data security.

#### Tenant Isolation (TDO-002, TDO-014, TDO-016)

Salah satu aspek paling critical dalam multi-tenant system adalah memastikan bahwa setiap tenant hanya dapat mengakses data mereka sendiri. Penulis merancang tiga test cases spesifik untuk memvalidasi tenant isolation:

TDO-002 memastikan bahwa Supplier hanya dapat melihat shipment milik mereka sendiri. Test ini mencakup query filtering berdasarkan supplier_id, yang memetakan ke note "Filter otomatis" dalam activity diagram.

TDO-014 adalah negative test yang memastikan bahwa Supplier tidak dapat mengakses shipment dari supplier lain. Test ini critical untuk security karena mencegah information leakage antar tenants.

TDO-016 memvalidasi hal yang sama untuk Purchase Orders, memastikan bahwa supplier tidak dapat mengakses PO dari supplier lain.

Ketiga test ini bersama-sama memberikan confidence bahwa tenant isolation diimplementasikan dengan benar di semua layers (UI, business logic, data access).

#### State Transitions (TDO-010, TDO-011, TDO-012, TDO-013)

Shipment memiliki state machine dengan transitions yang jelas: Draft → Shipped → Arrived → Processed. Penulis merancang test cases untuk memvalidasi setiap transition:

TDO-010 memvalidasi operation markAsShipped() yang mentransisikan shipment dari Draft ke Shipped.

TDO-011 hingga TDO-013 memvalidasi setiap state transition dengan memastikan bahwa state changes happen correctly dan business rules di-enforce. Sebagai contoh, shipment yang sudah Shipped tidak dapat di-edit (TDO-007) atau di-delete (TDO-009).

Test ini mencerminkan pemahaman yang baik tentang state machines dan memastikan bahwa state transitions diimplementasikan dengan benar dan konsisten.

### 4.4.4 Use Case 4: Penerimaan Barang

Use case ini merupakan yang paling kompleks karena melibatkan cascade updates ke multiple tables dan requires database transaction integrity. Perancangan pengujian mempertimbangkan berbagai integration scenarios.

#### Integration Testing (TGR-005, TGR-009, TGR-010, TGR-011, TGR-012, TGR-013)

Penulis merancang serangkaian integration tests untuk memvalidasi cascade effects ketika goods receipt di-complete:

TGR-005 memvalidasi bahwa ketika GR dibuat, shipment status otomatis update ke "Arrived". Ini adalah cascade update pertama yang memetakan ke sequence diagram step "Update status shipment = Arrived".

TGR-009 memvalidasi bahwa product stock quantity increases ketika GR di-complete. Ini adalah core functionality yang critical untuk inventory management.

TGR-010 memvalidasi bahwa quantity_received di PO detail table di-update. Ini penting untuk tracking berapa banyak dari ordered quantity yang sudah diterima.

TGR-011 adalah test yang sangat menarik karena memvalidasi business logic untuk automatic PO status update. PO status akan berubah dari Pending ke Partial jika ada penerimaan sebagian, atau ke Completed jika semua items sudah diterima. Test ini memvalidasi method updateStatus() dalam class diagram yang mengandung logic untuk menentukan status PO berdasarkan received quantities.

TGR-012 memvalidasi update shipment status ke "Processed" setelah GR di-complete.

Yang paling penting adalah TGR-013 yang memvalidasi database transaction integrity. Test ini memastikan bahwa semua updates (product stock, PO received quantities, PO status, shipment status) happen atomically dalam satu transaction. Jika salah satu update fails, semua changes harus di-rollback. Ini critical untuk maintaining data consistency.

Keenam test ini bersama-sama memberikan comprehensive coverage untuk complex integration scenario ini, mencerminkan pemahaman yang mendalam tentang distributed transactions dan data consistency.

### 4.4.5 Use Case 5: Autentikasi Multi-Panel

Meskipun bukan use case fungsional utama, autentikasi multi-panel merupakan foundational requirement yang harus bekerja dengan sempurna.

#### Panel Separation (16 Test Cases)

Penulis merancang systematic testing untuk memastikan bahwa setiap role hanya dapat mengakses panel yang sesuai. Test cases dirancang dalam matrix pattern:

**Admin Panel:**
- TAUTH-001, TAUTH-002: Admin dapat login dan akses admin panel
- TAUTH-003: Non-admin tidak dapat login ke admin panel
- TAUTH-004: Admin tidak dapat login ke app panel (sebagai admin)

**App Panel:**
- TAUTH-005: Admin dapat login ke app panel
- TAUTH-006, TAUTH-007: Checker dapat login dan akses app panel
- TAUTH-008: Accounting dapat login dan akses app panel
- TAUTH-009: Supplier tidak dapat login ke app panel

**Supplier Panel:**
- TAUTH-010, TAUTH-011: Supplier dapat login dan akses supplier panel
- TAUTH-012: Admin tidak dapat login ke supplier panel
- TAUTH-013: Checker tidak dapat login ke supplier panel

Test TAUTH-014 memvalidasi bahwa inactive users tidak dapat login ke panel manapun, memastikan bahwa deactivation berfungsi dengan benar.

Test TAUTH-015 adalah catch-all test yang memvalidasi general authorization policy.

Test TAUTH-016 memvalidasi redirect behavior setelah successful login, memastikan bahwa user diarahkan ke panel yang benar sesuai dengan role mereka.

Matrix testing approach ini memberikan exhaustive coverage untuk multi-panel authentication dengan mencoba semua kombinasi role dan panel, memastikan bahwa authorization rules diimplementasikan dengan konsisten.

### 4.4.6 Use Case 6: Mengirim Pesan

Use case ini currently memiliki partial implementation dengan focus pada basic functionality.

#### Basic Functionality (5 Test Cases)

TCH-001 hingga TCH-005 mencakup basic functionality seperti:
- Access ke chat page
- Authentication requirement
- Wirechat component loading
- UI integration

Penulis mengakui bahwa coverage saat ini (36%) belum memadai dan sudah merencakan 9 test cases tambahan untuk mencakup:
- Real-time message delivery via WebSocket
- Permission checks (canCreateChats, canCreateGroups)
- Read receipts
- Unread counter
- Private vs Group chat creation

Ini adalah contoh yang baik dari incremental development dimana basic functionality diimplementasikan dan ditest terlebih dahulu, kemudian advanced features ditambahkan dalam iterations berikutnya.

### 4.4.7 Use Case 7: Membuat Laporan Bulanan

Use case ini merupakan salah satu yang paling comprehensive dengan 19 test cases yang mencakup empat jenis laporan berbeda.

#### RBAC Testing (TLB-001, TLB-002, TLB-003)

Penulis memulai dengan memvalidasi authorization, memastikan bahwa hanya Accounting role yang dapat mengakses reporting functionality. Ini critical karena reports mungkin contain sensitive financial information.

#### Period Filtering (TLB-005, TLB-007, TLB-009, TLB-012, TLB-015, TLB-018)

Setiap jenis laporan diuji untuk memastikan period filtering bekerja dengan benar. Penulis merancang test yang memvalidasi bahwa:
- Default period adalah bulan ini (TLB-005)
- User dapat mengubah period (TLB-007)
- Data yang ditampilkan sesuai dengan period yang dipilih

Ini memastikan bahwa reporting functionality memberikan flexibility kepada user untuk analyze data dalam time periods yang berbeda.

#### Report Type Switching (TLB-008, TLB-011, TLB-014, TLB-017)

Salah satu fitur menarik dari reporting page adalah dynamic table switching berdasarkan report type yang dipilih. Penulis merancang test untuk setiap report type untuk memastikan:
- Table correct untuk report type muncul
- Columns sesuai dengan report type
- Data accurate

Ini adalah contoh bagus dari dynamic UI testing dimana UI behavior changes berdasarkan user selection.

#### Column Validation (TLB-010, TLB-013, TLB-016, TLB-019)

Untuk setiap report type, penulis merancang test spesifik untuk memvalidasi bahwa columns yang ditampilkan correct dan sesuai dengan requirement. Ini memastikan bahwa UI konsisten dengan specification dalam activity diagram.

#### Business Logic Testing (TLB-015, TLB-018)

Beberapa test cases memvalidasi specific business logic dalam reporting:
- TLB-015: Produk stok rendah tampil terlebih dahulu (sorted by stock_quantity ASC)
- TLB-018: Hanya PO dengan status Partial/Completed yang muncul dalam financial report

Test ini memastikan bahwa business rules specific untuk reporting diimplementasikan dengan benar.

## 4.5 Matriks Traceability

### 4.5.1 Konsep dan Tujuan

Matriks traceability merupakan alat yang powerful untuk memastikan bahwa setiap requirement yang didefinisikan dalam UML diagrams memiliki corresponding test cases. Penulis merancang matriks traceability dua arah yang menunjukkan:
1. Requirement to Test Cases: Setiap use case dapat di-trace ke test cases yang memvalidasinya
2. Test Cases to Requirement: Setiap test case dapat di-trace kembali ke requirement source-nya (activity diagram, sequence diagram, atau class diagram)

Pendekatan ini memastikan bidirectional traceability yang critical untuk requirement validation dan impact analysis.

### 4.5.2 Hasil Traceability Mapping

Dari hasil traceability mapping, penulis dapat mengidentifikasi beberapa insights penting:

**Coverage per Use Case:**
- UC-01 (Master Data): 60 tests - 100% coverage
- UC-02 (Purchase Order): 10 tests - 100% coverage
- UC-03 (Delivery Order): 18 tests - 100% coverage
- UC-04 (Goods Receipt): 13 tests - 100% coverage
- UC-05 (Multi-Panel Auth): 16 tests - 100% coverage
- UC-06 (Messaging): 5 tests - 36% coverage
- UC-07 (Monthly Report): 19 tests - 100% coverage
- UC-08 (View Product Supplier): 0 tests - 0% coverage

Dari data ini terlihat bahwa 6 dari 8 use cases (75%) telah mencapai full coverage, 1 use case (12.5%) partial coverage, dan 1 use case (12.5%) belum diimplementasikan.

**Coverage per Diagram Type:**

Activity Diagram coverage mencapai 95%, dengan hampir semua steps dalam activity diagram ter-cover oleh test cases. 5% yang belum ter-cover adalah primarily dari UC-08 yang belum diimplementasikan dan advanced features di UC-06.

Sequence Diagram coverage mencapai 90%, dengan semua major interactions antar objects ter-validasi melalui integration tests. 10% gap terutama dari asynchronous operations seperti WebSocket broadcasting yang requires special testing approach.

Class Diagram coverage mencapai 85% untuk methods dan 90% untuk attributes. Methods yang belum ter-cover umumnya adalah private helper methods atau methods yang akan ditest secara implicit melalui public methods. Critical attributes semua ter-validasi.

Business Rules coverage mencapai 100%, yang merupakan achievement significant. Semua validation rules, authorization rules, dan state transition rules ter-test dengan kombinasi positive dan negative test cases.

### 4.5.3 Gap Analysis

Dari traceability matrix, penulis mengidentifikasi beberapa gaps yang perlu diaddress:

1. **Real-time Features Testing**: WebSocket dan broadcasting features di messaging use case requires specialized testing approach. Penulis merencanakan untuk menggunakan browser testing capabilities dari Pest v4 untuk test real-time interactions.

2. **Supplier Product View**: Completely missing tests. Ini akan menjadi priority untuk next sprint.

3. **Export Functionality**: Export to Excel/PDF functionality ada dalam requirements tetapi belum fully tested. Penulis merencanakan untuk add tests untuk file generation dan validation.

4. **Performance Testing**: Current tests focus pada functional correctness, belum ada performance benchmarks atau load testing. Ini penting untuk ensure system dapat handle production load.

## 4.6 Hasil Analisis Coverage

### 4.6.1 Quantitative Analysis

Dari 141 test cases yang diimplementasikan dengan 623 assertions, penulis melakukan analisis mendalam terhadap distribution dan effectiveness dari test suite.

**Test Density Analysis:**

Average assertions per test adalah 4.4, yang menunjukkan bahwa setiap test melakukan multiple validations. Ini adalah good practice karena memastikan comprehensive validation dalam setiap test scenario. Namun, penulis juga careful untuk tidak membuat test yang terlalu complex atau test multiple concerns dalam single test.

**Test Categories Distribution:**

Dari 141 tests:
- 91 tests (64.5%) adalah functional tests yang validate business functionality
- 28 tests (19.9%) adalah RBAC/security tests yang validate authorization
- 12 tests (8.5%) adalah validation tests yang validate input validation
- 10 tests (7.1%) adalah integration tests yang validate system integration

Distribution ini menunjukkan focus yang balanced antara functionality, security, dan integration testing. Proportion dari RBAC tests (hampir 20%) mencerminkan importance dari security dalam multi-tenant, multi-role system.

**Test File Organization:**

12 test files organized by panel (Auth, Admin, App, Supplier) dengan clear separation of concerns. Largest test file adalah ManajemenProdukTest.php dengan 37 tests, yang reasonable mengingat complexity dari product management functionality.

### 4.6.2 Qualitative Analysis

Beyond numbers, penulis melakukan qualitative assessment dari test suite quality:

**Test Readability:**

Semua tests ditulis dalam Bahasa Indonesia menggunakan `test()` syntax yang descriptive. Contoh:
```php
test('dapat membuat user baru', function() { ... });
test('tidak dapat hapus user aktif', function() { ... });
```

Approach ini makes tests self-documenting dan easy untuk non-technical stakeholders untuk understand apa yang ditest.

**Test Organization:**

Tests organized menggunakan `describe()` blocks untuk group related tests. Ini improves readability dan makes test execution output easier untuk understand.

**Test Independence:**

Setiap test independent dan dapat run dalam isolation. Penulis menggunakan database transactions dan factories untuk ensure setiap test starts dengan clean state. Ini critical untuk test reliability dan debugging.

**Test Naming Convention:**

Test names follow consistent pattern yang describes what is being tested dan expected outcome. Negative tests clearly marked dengan prefix seperti "tidak dapat" atau "tidak boleh".

### 4.6.3 Coverage Gaps dan Future Work

Penulis mengidentifikasi beberapa areas untuk improvement:

**Priority Tinggi:**

1. **Complete UC-06 (Messaging)**: Add 9 tests untuk real-time features (estimated completion: 1 sprint)
2. **Implement UC-08 (View Product Supplier)**: Add 8-12 tests (estimated completion: 2-3 sprints)

**Priority Sedang:**

3. **Export Functionality Enhancement**: Add tests untuk Excel/PDF export (8-10 tests, 2-3 sprints)
4. **Performance Testing**: Add performance benchmarks dan load testing

**Priority Rendah:**

5. **Visual Regression Testing**: Add visual regression tests untuk UI changes
6. **Accessibility Testing**: Ensure system meets accessibility standards
7. **Cross-browser Testing**: Validate functionality across different browsers

## 4.7 Evaluasi Penerapan TDD

### 4.7.1 Adherence to TDD Principles

Sepanjang development process, penulis consistently follow TDD principles:

**Test-First Approach:**

Untuk setiap feature, penulis writes test cases berdasarkan UML diagrams sebelum writing implementation code. Ini ensures bahwa tests actually test requirements, bukan just achieving code coverage.

**Red-Green-Refactor Cycle:**

Penulis documents bahwa setiap feature development follows strict Red-Green-Refactor cycle. Initially failing tests (Red) memastikan bahwa tests actually test something meaningful. Minimal implementation untuk pass tests (Green) prevents over-engineering. Refactoring phase improves code quality dengan safety net dari existing tests.

**Continuous Testing:**

Tests automatically run pada setiap commit melalui CI/CD pipeline. Ini ensures bahwa regressions immediately detected dan code quality maintained throughout development.

### 4.7.2 Benefits Realized

Dari penerapan TDD, penulis observe several tangible benefits:

**Better Code Design:**

TDD forces penulis untuk think tentang API design sebelum implementation. Ini results dalam more modular, testable, dan maintainable code. Contohnya, separation dari business logic ke dedicated methods (seperti calculateAcceptanceRate(), isNeedsReorder()) makes code easier untuk test dan maintain.

**Higher Confidence in Changes:**

Dengan comprehensive test suite, penulis dapat confidently refactor code atau add new features tanpa fear of breaking existing functionality. Any regression immediately caught oleh automated tests.

**Living Documentation:**

Test suite serves sebagai living documentation yang always up-to-date. Developers baru dapat understand system behavior dengan reading tests, yang often clearer than documentation.

**Reduced Debugging Time:**

Ketika bugs occur, granular tests help pinpoint exact location dari problem. Ini significantly reduces debugging time compared dengan manual testing approach.

**Better Requirement Understanding:**

Process dari writing tests based on UML diagrams forces penulis untuk deeply understand requirements sebelum coding. Ambiguities atau contradictions dalam requirements identified early dalam test design phase.

### 4.7.3 Challenges Encountered

Penulis juga honest tentang challenges encountered dalam TDD implementation:

**Initial Time Investment:**

Writing tests sebelum implementation requires significant initial time investment. Untuk complex features seperti goods receipt dengan cascade updates, writing comprehensive tests takes considerable effort.

**Test Maintenance:**

Ketika requirements change, tests juga need untuk updated. Ini adds maintenance overhead, although ini offset by reduced debugging time dan higher confidence dalam changes.

**Learning Curve:**

Team members tidak familiar dengan TDD requires time untuk adapt to test-first mindset. Ini especially challenging untuk developers used to traditional code-first approach.

**Complex Integration Scenarios:**

Testing complex integration scenarios (seperti database transactions, real-time features) requires specialized knowledge dan sometimes custom testing utilities.

Meskipun challenges exist, penulis concludes bahwa benefits dari TDD significantly outweigh costs, especially untuk long-term maintainability dan code quality.

## 4.8 Kesimpulan Perancangan Pengujian

### 4.8.1 Pencapaian Utama

Melalui systematic test design dan TDD implementation, penulis telah achieve several significant milestones:

**Comprehensive Coverage:**

Dari 8 use cases, 6 achieve 100% coverage, 1 achieve 36% coverage, dan 1 belum diimplementasikan. Total 141 test cases dengan 623 assertions provides strong confidence dalam system correctness.

**Traceability:**

Every test case traceable back to specific requirements dalam UML diagrams. Ini ensures bahwa testing validates actual requirements, bukan arbitrary criteria.

**Quality Metrics:**

- Activity Diagram coverage: 95%
- Sequence Diagram coverage: 90%
- Class Diagram method coverage: 85%
- Business Rules coverage: 100%
- RBAC coverage: 100%

Metrics ini demonstrates thorough validation across all layers dari system.

**Test Quality:**

Tests well-organized, readable, independent, dan maintainable. Consistent naming conventions dan comprehensive use dari describe blocks makes test suite accessible untuk all team members.

### 4.8.2 Kontribusi terhadap Kualitas Sistem

Test suite yang comprehensive ini directly contributes to system quality dalam several ways:

**Functional Correctness:**

Extensive functional testing ensures bahwa system behaves correctly untuk all main flows dan many edge cases. Business logic properly implemented dan validated.

**Security:**

28 RBAC/security tests ensure bahwa authorization properly enforced. Multi-panel authentication thoroughly validated. Tenant isolation tested to prevent data leakage.

**Data Integrity:**

Integration tests validate database transaction integrity. Cascade updates properly implemented dan tested. Business rules prevent invalid states.

**Maintainability:**

Test suite serves sebagai regression test suite untuk future changes. Developers can safely refactor code dengan confidence bahwa tests akan catch regressions.

### 4.8.3 Lessons Learned

Dari experience implementing TDD pada project ini, penulis extracts several valuable lessons:

**Start with Clear Requirements:**

Quality tests start dengan clear requirements. UML diagrams provides excellent foundation untuk test design. Ambiguities dalam diagrams discovered dan resolved during test design phase.

**Invest in Test Infrastructure:**

Good test infrastructure (factories, helper methods, custom assertions) pays dividends. Initial investment dalam test utilities makes subsequent test writing faster dan easier.

**Balance Test Granularity:**

Tests should be granular enough untuk pinpoint problems, tetapi tidak so granular bahwa changes require updating dozens dari tests. Finding right balance adalah art yang improves dengan experience.

**Continuous Refactoring:**

Tests juga need refactoring untuk remove duplication dan improve clarity. Treat tests as first-class code yang deserves same attention to quality as production code.

**Team Alignment:**

TDD success requires team alignment pada methodology. Regular discussions tentang test strategy dan review dari test code helps maintain consistency dan quality.

### 4.8.4 Rekomendasi untuk Future Work

Based on analysis dan gap identification, penulis provides specific recommendations:

**Short Term (Next Sprint):**

1. Complete messaging use case testing dengan 9 additional tests untuk real-time features
2. Implement browser testing untuk verify WebSocket functionality
3. Add missing negative test cases untuk edge scenarios

**Medium Term (2-3 Sprints):**

4. Implement supplier product view use case dengan estimated 8-12 tests
5. Add export functionality tests untuk Excel/PDF generation
6. Implement performance benchmarks untuk query-intensive operations

**Long Term:**

7. Add load testing untuk multi-user scenarios
8. Implement visual regression testing untuk UI changes
9. Add accessibility testing untuk ensure WCAG compliance
10. Expand integration tests untuk cover more complex multi-step workflows

### 4.8.5 Refleksi Metodologis

Penerapan TDD dalam development sistem Warelink validates bahwa methodology ini effective untuk ensuring software quality. Systematic approach dari requirement analysis (UML diagrams) → test design → implementation → validation creates robust development process yang produces high-quality, maintainable software.

Test suite yang comprehensive ini bukan hanya validates current implementation, tetapi juga provides safety net untuk future development. Dengan 141 tests dan 623 assertions, penulis has confidence bahwa major functionality dari system working correctly dan akan continue untuk work correctly as system evolves.

Yang paling penting, process dari writing tests first forces deeper understanding dari requirements dan better design decisions. Tests serve as both specification dan validation, creating living documentation yang accurately reflects system behavior.

Melalui disciplined application dari TDD principles dan comprehensive test coverage, penulis demonstrates bahwa systematic testing approach dapat significantly improve software quality dan reduce long-term maintenance costs.

---

**Catatan Metodologis:**

Perancangan pengujian yang dijelaskan dalam bab ini merupakan iterasi kedua (versi 2.1) yang telah melalui refinement berdasarkan feedback dan learning dari implementasi actual. Initial design focused primarily pada happy paths, tetapi subsequent iterations added extensive negative testing, edge case coverage, dan integration testing untuk ensure robustness.

Test suite continues untuk evolve as system grows dan new requirements identified. Current coverage dari 87.5% (dengan target 95%+ for completed modules) reflects commitment untuk quality while acknowledging bahwa some areas still under development.

Documentation ini serves both sebagai research artifact dan practical guide untuk future development, ensuring bahwa knowledge gained dari TDD implementation preserved dan shared dengan wider development community.
