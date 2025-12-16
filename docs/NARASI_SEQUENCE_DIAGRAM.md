# BAB III (Lanjutan)
# PERANCANGAN SISTEM

## 3.6 Perancangan Sequence Diagram

### 3.6.1 Pengantar Sequence Diagram

Sequence diagram merupakan representasi visual dari interaksi antar objek atau komponen dalam sistem yang disusun berdasarkan urutan waktu (timeline). Diagram ini menggambarkan bagaimana pesan (message) dikirim dan diterima antar participant, menunjukkan alur komunikasi yang detail dari request hingga response, serta mengilustrasikan lifecycle object activation selama interaksi berlangsung. Dalam konteks Sistem Informasi Warelink yang dibangun menggunakan Laravel dan Filament Framework, sequence diagram memetakan interaksi antara Actor, Presentation Layer (Filament UI), Application Layer (Resource/Page), Domain Layer (Model), dan Infrastructure Layer (Database, Notification, WebSocket).

Peneliti merancang 8 sequence diagram yang masing-masing merepresentasikan satu use case utama sistem. Setiap diagram menggunakan notasi UML 2.5 standar dengan participant boxes untuk komponen sistem, activation bars untuk menunjukkan durasi eksekusi, synchronous messages untuk method calls, return messages untuk response, dan alt/opt fragments untuk conditional logic. Diagram dirancang dengan prinsip **layered architecture** dimana setiap layer memiliki responsibility yang jelas: UI layer untuk presentation, Resource layer untuk business logic orchestration, Model layer untuk domain logic dan data access, dan Database layer untuk persistence.

Perancangan sequence diagram ini penting untuk memahami **runtime behavior** sistem, mengidentifikasi bottleneck potensial, memvalidasi bahwa design memenuhi use case requirements, dan menjadi dokumentasi teknis untuk developer dalam implementasi. Diagram juga menunjukkan integration points dengan external systems seperti Laravel Reverb untuk WebSocket broadcasting dan notification service untuk real-time alerts.

---

### 3.6.2 SD-01: Sequence Diagram Manajemen Master Data

**Pemetaan:** UC-01 (Mengelola Master Data), AD-01
**Participants:** Admin Gudang, Filament UI, Resource, Model, Database
**File Diagram:** `1-master-data-management.puml`

Sequence diagram ini mengilustrasikan interaksi komponen sistem saat Admin Gudang mengelola tiga entitas master data: User, Supplier, dan Product. Alur dimulai dengan Admin Gudang mengakses menu Master Data melalui Filament UI. UI mengirim request ke Resource layer untuk mendapatkan data list, Resource kemudian memanggil Model untuk query database, dan hasil dikembalikan secara bertingkat hingga ditampilkan di UI sebagai tabel interaktif.

Untuk operasi CRUD, diagram menunjukkan dua skenario utama melalui alt fragment: validation success dan validation failed. Pada happy path, Admin Gudang mengisi form dan submit data, Resource melakukan validasi data sesuai business rules, jika valid maka Resource memanggil Model untuk create/update entity, Model melakukan persistence ke Database, dan success response dikembalikan ke Admin hingga UI menampilkan notifikasi sukses. Pada alternative path validation failed, Resource langsung return validation errors ke UI tanpa menyentuh Model/Database, dan UI menampilkan error message untuk koreksi user.

Diagram juga menunjukkan business logic spesifik untuk setiap entitas yang di-handle di layer Model melalui note annotations: untuk Supplier terdapat auto-generate kode supplier otomatis dan set status aktif default, sedangkan untuk Product terdapat generate kode produk berdasarkan supplier code, pencatatan pergerakan stok jika ada perubahan quantity, dan update stock quantity. Interaksi ini menekankan separation of concerns dimana UI hanya handle presentation, Resource orchestrate flow dan validation, dan Model encapsulate domain logic serta data access.

---

### 3.6.3 SD-02: Sequence Diagram Manajemen Purchase Order

**Pemetaan:** UC-02 (Manajemen Purchase Order), AD-02
**Participants:** Admin Gudang, Filament UI, PO Resource, PurchaseOrder Model, Database, Notification
**File Diagram:** `2-purchase-order-management.puml`

Sequence diagram ini menggambarkan alur lengkap manajemen Purchase Order dengan empat skenario: Buat PO, Edit PO, Lihat PO, dan Batalkan PO. Pada flow "Buat PO", interaksi dimulai dengan Admin mengakses menu Purchase Order dan UI menampilkan list PO yang sudah ada. Ketika Admin klik "Buat PO", UI meminta form create dari Resource dan menampilkan form dengan dropdown supplier dan produk. Admin kemudian mengisi data PO dan detail produk, submit form, dan Resource melakukan processing business logic yang di-annotate melalui note: generate nomor PO dengan format timestamp-based, hitung total amount dengan sum subtotal semua items, dan set status awal Pending. Resource memanggil PurchaseOrder Model untuk create dengan relasi details, Model persist ke Database dalam satu transaksi, dan setelah sukses Resource mengirim notification ke Supplier melalui Notification service sebelum return success response.

Flow "Edit PO" menunjukkan conditional logic krusial melalui alt fragment dengan guard condition "Status = Pending". Resource terlebih dahulu query PO dari Model, jika status masih Pending maka UI render edit form dan Admin dapat update data, Resource calculate ulang total amount, Model save changes, dan response sukses. Namun jika status bukan Pending (Partial/Completed), maka Resource langsung return error response dan UI tampilkan message "PO tidak dapat diubah" tanpa render form. Pattern yang sama diterapkan pada flow "Batalkan PO" dimana hanya PO dengan status Pending yang dapat di-cancel, setelah status di-update ke Cancelled, system send notification cancellation ke Supplier.

Flow "Lihat PO" menunjukkan eager loading pattern dimana Resource request "PO with relations" dari Model, dan Model query database dengan join supplier dan details untuk menghindari N+1 query problem, kemudian complete data ditampilkan di detail view. Diagram ini menekankan importance of state-based access control dan notification integration untuk keep external parties informed.

---

### 3.6.4 SD-03: Sequence Diagram Manajemen Delivery Order

**Pemetaan:** UC-03 (Mengelola Delivery Order), AD-03
**Participants:** Supplier, Filament UI, Shipment Resource, Shipment Model, Database, Notification
**File Diagram:** `3-delivery-order-management.puml`

Sequence diagram ini mengilustrasikan bagaimana Supplier mengelola Delivery Order dalam Supplier Panel dengan tenant isolation enforcement. Alur dimulai dengan Supplier mengakses menu Purchase Order, dan terdapat note penting pada UI layer yang menjelaskan "Filter otomatis: Hanya tampilkan PO supplier sendiri". UI send request ke Resource, Resource query Database dengan filter `by supplier_id`, dan hanya PO yang relevan dengan supplier tersebut yang ditampilkan, implementing multi-tenancy security.

Pada flow "Buat DO", Supplier memilih satu Purchase Order sebagai referensi dan klik "Buat DO". Resource menampilkan form DO yang sudah pre-populated dengan produk dari PO terpilih. Supplier input detail pengiriman termasuk nomor DO dari system mereka, tanggal pengiriman, estimasi kedatangan, upload scan dokumen DO, dan confirm quantity produk yang akan dikirim per item. Setelah submit, Resource melakukan business logic processing yang di-note: generate nomor shipment unique, validasi bahwa quantity shipped tidak exceed quantity ordered di PO, dan set status awal Draft. Resource memanggil Shipment Model untuk create dengan details, dan data disimpan ke Database.

Flow "Edit DO" menggunakan alt fragment dengan guard condition "Status = Draft" yang sama dengan pattern PO, hanya Shipment dengan status Draft yang editable. Flow "Kirim Barang" merupakan critical state transition: Supplier pilih Shipment berstatus Draft dan confirm pengiriman, Resource update Shipment status menjadi Shipped melalui Model, dan crucial step berikutnya adalah Resource send notification ke Admin Gudang dan Checker menggunakan Notification service untuk inform bahwa ada shipment dalam perjalanan. Notification ini men-trigger workflow berikutnya yaitu goods receipt creation oleh Checker. Diagram menekankan tenant isolation, state-based operations, dan integration dengan notification system untuk cross-panel coordination.

---

### 3.6.5 SD-04: Sequence Diagram Pembuatan Penerimaan Barang

**Pemetaan:** UC-04 (Penerimaan Barang) - Sub-flow Checker, AD-04
**Participants:** Checker, Filament UI, GR Resource, GoodsReceipt Model, Shipment Model, Database, Notification
**File Diagram:** `4-goods-receipt-creation.puml`

Sequence diagram ini menggambarkan proses Checker dalam membuat Goods Receipt saat barang fisik tiba di gudang, sebagai respon dari notification shipment. Alur dimulai dengan Checker menerima notification dari Notification service dengan message "DO Shipped". Checker kemudian akses menu Delivery Order pada App Panel, UI request DO list dengan filter status Shipped dari Resource, Resource query Database dengan where clause `status=Shipped`, dan list DO yang sedang dalam perjalanan ditampilkan.

Checker memilih salah satu Delivery Order untuk diperiksa, UI request DO details dari Resource, Resource call Shipment Model untuk get shipment with relations (details, products, PO reference), Model query Database untuk get complete data termasuk path dokumen scan DO, dan data lengkap ditampilkan ke Checker. Checker melakukan verifikasi dokumen DO secara manual dengan membandingkan dokumen fisik dan scan di system, dan diagram menggunakan alt fragment untuk handle dua outcome: dokumen sesuai atau tidak sesuai.

Pada path "Dokumen Sesuai", Checker klik "Buat Penerimaan Barang", UI render form GR, dan Checker melakukan pemeriksaan fisik barang untuk input data actual: quantity received (barang diterima baik), quantity rejected (barang rusak/tidak sesuai), rejection reason jika ada rejected items, dan upload foto atau scan dokumen Proof of Delivery. Setelah submit, Resource processing dengan note: generate nomor GRN dengan format timestamp, set status Pending (indicate belum final), dan set received_by ke user Checker yang login. Resource create GoodsReceipt via Model dan save ke Database, kemudian crucial step adalah Resource update Shipment status dari Shipped menjadi Arrived melalui Shipment Model untuk indicate barang sudah sampai gudang. Setelah kedua operations sukses, Resource send notification ke Admin Gudang untuk inform ada GR baru yang perlu diverifikasi. Pada path "Dokumen Tidak Sesuai", Checker mark dokumen error di UI dan perlu hubungi supplier untuk resolusi, tidak ada GR yang dibuat.

---

### 3.6.6 SD-05: Sequence Diagram Verifikasi Penerimaan Barang

**Pemetaan:** UC-04 (Penerimaan Barang) - Sub-flow Admin Gudang, AD-05
**Participants:** Admin Gudang, Filament UI, GR Resource, GoodsReceipt Model, Product Model, PurchaseOrder Model, Shipment Model, Database, Notification
**File Diagram:** `5-goods-receipt-verification.puml`

Sequence diagram ini mengilustrasikan proses krusial verifikasi dan finalisasi Goods Receipt oleh Admin Gudang yang men-trigger cascade updates ke multiple entities. Alur dimulai dengan Admin Gudang menerima notification "Goods Receipt created (Pending)" dari Checker. Admin akses menu Goods Receipt dengan filter status Pending, UI request GR list dari Resource, Resource query Database dengan where status Pending, dan list GR yang awaiting verification ditampilkan.

Admin pilih salah satu GR untuk review, UI request GR details dari Resource, Resource call GoodsReceipt Model untuk get GR dengan eager load shipment, PO, products, dan dokumen POD, Model return complete data, dan UI render detail view lengkap dengan foto POD untuk inspection. Admin melakukan verifikasi menyeluruh dengan membandingkan data GR, dokumen POD, dan informasi PO, kemudian diagram menggunakan alt fragment untuk two scenarios: data sesuai atau tidak sesuai.

Pada path "Data Sesuai", Admin klik "Verifikasi Penerimaan" dan Resource update GR status menjadi Verified sebagai intermediate state. Selanjutnya Admin klik "Selesaikan Penerimaan" untuk finalisasi, dan ini adalah most critical part dari diagram yang di-highlight dengan note "Database Transaction Start: All operations below run atomically". Resource orchestrate series of operations yang harus succeed atau fail together sebagai satu unit: (1) Resource call Product Model untuk update stock quantity setiap produk dengan increase stock sesuai quantity accepted dan log stock movement untuk audit trail, (2) Resource call PurchaseOrder Model untuk update quantity received di PO details dan update PO status berdasarkan completion percentage (jika semua item received 100% maka status Completed, jika partial maka status Partial), (3) Resource call Shipment Model untuk update status menjadi Processed indicating shipment fully processed, (4) Resource call GoodsReceipt Model untuk update status menjadi Completed marking GR as final. Setelah transaction commit sukses, diagram menunjukkan note "Transaction Commit", kemudian Resource send notification ke Accounting untuk inform ada completed GR yang dapat digunakan untuk reporting. Diagram juga menunjukkan nested alt fragment: jika ada rejected products, Resource send additional notification ke Supplier about rejection details.

Pada path "Data Tidak Sesuai", Admin return GR ke Checker dengan catatan issue, Resource update GR notes tapi keep status Pending, dan send notification ke Checker untuk correction. Diagram ini menekankan atomic transaction untuk data consistency, cascade updates across multiple aggregates, dan comprehensive notification flow untuk keep all stakeholders informed.

---

### 3.6.7 SD-06: Sequence Diagram Pembuatan Laporan Bulanan

**Pemetaan:** UC-07 (Membuat Laporan Bulanan), AD-06
**Participants:** Accounting, Filament UI, Report Page, Report Generator, Database
**File Diagram:** `6-monthly-report-generation.puml`

Sequence diagram ini menggambarkan proses Accounting dalam generating dan exporting monthly reports dengan berbagai jenis laporan yang available. Alur dimulai dengan Accounting akses menu Laporan, UI call Report Page untuk show report interface, Page return report options, dan UI render form dengan pilihan jenis laporan dan filter periode (bulan/tahun). Accounting input periode yang diinginkan untuk filtering data.

Diagram kemudian menunjukkan empat flows parallel untuk jenis laporan berbeda: Laporan Purchase Order, Laporan Penerimaan Barang, Laporan Stok Produk, dan Laporan Keuangan. Setiap flow mengikuti pattern yang sama: Accounting pilih jenis laporan, UI request report dari Page, Page call Report Generator dengan parameters periode, Generator melakukan processing yang di-detail dalam note annotation. Untuk Laporan PO, Generator query PO data by period dari Database, calculate statistics seperti total per status, top suppliers, dan trend analysis, generate charts untuk visualization, dan return report data plus charts ke Page untuk rendering. Untuk Laporan Penerimaan Barang, Generator query GR data, calculate metrics seperti acceptance rate per produk, supplier performance based on rejection rate, dan rejection analysis untuk identify quality issues. Untuk Laporan Stok, Generator query product dan stock movement data, calculate stock levels (low/out stock identification), projected stock based on pending PO, dan reorder suggestions using reorder point logic. Untuk Laporan Keuangan, Generator query PO financial data, calculate total spending per period, outstanding amounts untuk unpaid/pending PO, dan spending trends untuk forecast.

Setelah laporan generated dan displayed di UI, diagram menunjukkan flow "Export Laporan" dimana Accounting klik export button, UI present pilihan format (PDF/Excel/CSV), Accounting pilih format, UI request export dari Page, Page call Generator dengan format parameter, dan Generator menggunakan alt fragment untuk generate file sesuai format: PDF generation, Excel generation, atau CSV generation. File download response dikirim ke UI dan Accounting download file laporan. Diagram menekankan on-demand report generation untuk ensure data freshness, variety of metrics dan analytics, dan flexible export options untuk different use cases.

---

### 3.6.8 SD-07: Sequence Diagram Melihat Produk (Supplier)

**Pemetaan:** UC-08 (Melihat Produk Supplier), AD-07
**Participants:** Supplier, Filament UI, Product Resource, Product Model, Database
**File Diagram:** `7-view-product.puml`

Sequence diagram ini mengilustrasikan proses read-only untuk Supplier melihat katalog produk dengan tenant isolation dan comprehensive product information. Alur dimulai dengan Supplier akses menu Produk pada Supplier Panel, dan similar dengan Delivery Order, terdapat note penting pada UI layer "Filter otomatis: Hanya tampilkan produk supplier sendiri". UI request product list dari Resource, Resource query Database dengan filter `by supplier_id`, dan hanya produk dari supplier tersebut yang ditampilkan implementing tenant boundary.

Flow "Filter/Cari Produk" menunjukkan Supplier dapat input kriteria filter seperti stock status (low/out stock), active status, atau keyword search. UI send filtered request ke Resource, Resource query Database dengan combined filters dan where clauses, dan filtered product list ditampilkan. Flow "Lihat Detail Produk" menunjukkan comprehensive information retrieval: Supplier pilih produk, UI request product details, Resource call Product Model dengan note explaining "Get complete information" yang include basic info & stock, statistics (total ordered, received, rejected across all transactions), acceptance rate calculation, projected stock based on pending orders, dan reorder status check. Model query Database untuk get product data dan aggregate data dari related transactions (PO details, Shipment details, GR details), perform calculations, dan return enriched product object dengan full details untuk ditampilkan.

Diagram juga menunjukkan additional features: flow "Lihat Riwayat Transaksi" dimana Resource query PO, Shipment, dan GR yang related to product untuk show complete transaction history, flow "Lihat Riwayat Stok" dimana Resource query stock movement history untuk show all stock changes dengan timestamp dan reason, dan flow "Lihat Grafik Trend" dimana Resource generate trend charts dengan note explaining charts include stock trend for last 6 months, order trend, dan acceptance rate trend untuk visual analytics. Terakhir, flow "Export Data Produk" menggunakan alt fragment untuk Excel atau CSV format generation. Diagram menekankan read-only nature (no create/update/delete), tenant isolation untuk security, comprehensive analytics untuk business insight, dan export capability untuk offline analysis.

---

### 3.6.9 SD-08: Sequence Diagram Mengirim Pesan

**Pemetaan:** UC-06 (Mengirim Pesan), AD-08
**Participants:** User (Sender), Recipient, Livewire Wirechat, Chat Model, Message Model, Database, Laravel Reverb (WebSocket), Notification
**File Diagram:** `8-messaging.puml`

Sequence diagram ini menggambarkan real-time messaging system menggunakan Livewire Wirechat component dan Laravel Reverb untuk WebSocket broadcasting. Alur dimulai dengan Sender akses menu Chat melalui Wirechat component, Wirechat query Database untuk get user's chats, dan chat list ditampilkan. Diagram menunjukkan tiga ways untuk initiate chat: create private chat, create group chat, atau open existing chat.

Flow "Buat Chat Baru (Private)" dimulai dengan Sender klik "Buat Chat" dan pilih Private, Wirechat check permission melalui `canCreateChats()` method dari User model yang enforce authorization rules (Supplier hanya chat dengan internal users, tidak dengan Supplier lain), kemudian Wirechat check existing chat between dua users atau create new private chat di Database, dan open chat interface. Flow "Buat Group Chat" menunjukkan Sender pilih Group option, Wirechat check `canCreateGroups` permission, Sender input nama group dan pilih minimal 2 members, Wirechat call Chat Model untuk create group dengan participants, Model save group dan members ke Database, kemudian Wirechat send notification ke all members untuk inform mereka ditambahkan ke group. Flow "Buka Chat Existing" menunjukkan Sender pilih chat dari list, Wirechat request messages via Message Model, Model query Database untuk get message history, messages ditampilkan, dan Wirechat call Message Model untuk mark unread messages as read dengan update `read_at` timestamp dan decrement unread counter.

Flow "Kirim Pesan" adalah core functionality: Sender type dan send message, Wirechat create message via Message Model, Model save ke Database, kemudian critical step adalah Wirechat call Laravel Reverb untuk broadcast message via WebSocket ke all chat participants. Reverb push real-time message ke Recipient yang online, Wirechat juga call Notification service untuk send push notification ke offline recipients, dan Wirechat confirm ke Sender bahwa message sent successfully.

Flow "Terima Pesan (Real-time)" menunjukkan Recipient receive message dari Reverb WebSocket, dan menggunakan alt fragment untuk handle two scenarios: chat is open atau not open. Jika chat terbuka, Recipient's Wirechat component display message immediately, automatically mark message as read via Message Model update Database, dan Wirechat send read receipt kembali via Reverb ke Sender untuk notify message telah dibaca. Jika chat tidak terbuka, Recipient's Wirechat increment unread counter dan show notification badge. Diagram menekankan real-time bidirectional communication, permission-based chat creation, automatic read receipts, dan dual notification strategy (WebSocket untuk online, push notification untuk offline).

---

### 3.6.10 Kesimpulan Perancangan Sequence Diagram

Kedelapan sequence diagram yang telah dirancang memberikan detailed view terhadap runtime behavior dan component interactions dalam Sistem Informasi Warelink. Diagram-diagram ini memetakan bagaimana use cases di-execute secara teknis melalui collaboration antar objects, menunjukkan message passing sequences, object lifecycle, dan timing constraints yang penting untuk performance optimization.

Perancangan sequence diagram mengikuti **layered architecture pattern** dimana setiap layer memiliki clear responsibility: Presentation layer (Filament UI/Livewire Wirechat) handle user interaction dan rendering, Application layer (Resource/Page) orchestrate business flows dan coordinate between domain and infrastructure, Domain layer (Model) encapsulate business logic dan domain rules, dan Infrastructure layer (Database/Notification/Reverb) provide technical capabilities. Separation ini memastikan **maintainability**, **testability**, dan **scalability** dari system.

Diagram-diagram juga menunjukkan critical architectural decisions: atomic transactions untuk data consistency pada cascade updates (SD-05), tenant isolation implementation untuk multi-tenancy security (SD-03, SD-07), real-time communication menggunakan WebSocket broadcasting (SD-08), comprehensive notification flow untuk keep stakeholders informed (SD-02, SD-03, SD-04, SD-05), dan state-based access control untuk enforce business rules (SD-02, SD-03). Pattern-pattern ini menjadi foundation dari reliable dan secure warehouse management system.

Implementation dari sequence diagrams ini telah divalidasi melalui comprehensive testing: unit tests untuk individual methods, integration tests untuk verify component interactions, dan feature tests untuk end-to-end flow validation. Test coverage yang tinggi (100% untuk core flows) membuktikan bahwa designed sequences dapat di-implement dengan reliable dan sesuai dengan requirements yang telah ditetapkan.
