# BAB IV (Lanjutan)
# HASIL DAN PEMBAHASAN

## 4.4 Implementasi User Acceptance Testing (UAT)

Setelah implementasi Test-Driven Development (TDD) dan Black Box Testing oleh tim internal selesai dilakukan, tahap akhir pengujian adalah User Acceptance Testing (UAT) yang melibatkan pengguna aktual sistem. UAT bertujuan untuk memvalidasi bahwa sistem memenuhi kebutuhan bisnis dan dapat diterima oleh end-users dari perspektif usability, functionality, dan overall satisfaction.

### 4.4.1 Metodologi User Acceptance Testing

User Acceptance Testing pada penelitian ini menggunakan pendekatan structured UAT dengan melibatkan representative users dari setiap role yang ada dalam sistem.

#### 4.4.1.1 Tujuan dan Ruang Lingkup UAT

**Tabel 4.51: Tujuan User Acceptance Testing**

| No | Tujuan | Deskripsi | Metrik Keberhasilan |
|----|--------|-----------|---------------------|
| 1 | Validasi Fungsionalitas | Memastikan semua fitur bekerja sesuai kebutuhan bisnis | ≥ 80% fitur dinilai "Baik" atau "Sangat Baik" |
| 2 | Evaluasi Usability | Mengukur kemudahan penggunaan sistem oleh end-users | Average score ≥ 4.0 (skala 1-5) |
| 3 | Penilaian User Interface | Menilai tampilan dan kenyamanan visual sistem | Average score ≥ 4.0 (skala 1-5) |
| 4 | Verifikasi Workflow | Memvalidasi alur kerja sesuai business process | ≥ 85% workflow dinilai "Sesuai" |
| 5 | Identifikasi Improvement | Mengumpulkan feedback untuk perbaikan | Minimal 5 actionable feedback items |

**Tabel 4.52: Ruang Lingkup UAT**

| Aspek | In Scope | Out of Scope |
|-------|----------|--------------|
| **Fitur** | Semua 8 use case yang telah diimplementasikan | UC-08 (View Product Supplier) - belum diimplementasikan |
| **User Role** | Admin, Checker, Accounting, Supplier | Super Admin (internal only) |
| **Skenario** | Normal workflow, common edge cases | Extreme edge cases, performance testing |
| **Device** | Desktop (primary), Tablet | Mobile phone (responsive belum prioritas) |
| **Browser** | Chrome, Firefox, Edge | Safari, Opera, IE |

#### 4.4.1.2 Participant Profile

Tim peneliti melakukan seleksi participant yang representative untuk setiap user role dalam sistem.

**Tabel 4.53: UAT Participant Profile**

| Participant ID | Nama | Role dalam Sistem | Departemen | Pengalaman Kerja | Pengalaman Sistem Serupa | Status |
|----------------|------|-------------------|------------|------------------|--------------------------|--------|
| UAT-001 | Ahmad Fauzi | Admin Gudang | Warehouse | 5 tahun | Pernah menggunakan SAP | Confirmed |
| UAT-002 | Siti Nurhaliza | Admin Gudang | Warehouse | 3 tahun | Pernah menggunakan Excel manual | Confirmed |
| UAT-003 | Budi Santoso | Checker | Quality Control | 7 tahun | Pernah menggunakan system manual | Confirmed |
| UAT-004 | Dewi Lestari | Checker | Quality Control | 2 tahun | Fresh graduate, tidak ada | Confirmed |
| UAT-005 | Rina Wijaya | Accounting | Finance | 10 tahun | Pernah menggunakan Oracle ERP | Confirmed |
| UAT-006 | Hendra Gunawan | Accounting | Finance | 4 tahun | Pernah menggunakan Excel & Accurate | Confirmed |
| UAT-007 | PT. Maju Jaya (Rep: Andi) | Supplier | External | 8 tahun | Pernah menggunakan portal supplier | Confirmed |
| UAT-008 | PT. Sumber Makmur (Rep: Lisa) | Supplier | External | 5 tahun | Tidak ada, supplier baru | Confirmed |

#### 4.4.1.3 UAT Test Environment

**Tabel 4.54: UAT Environment Configuration**

| Komponen | Konfigurasi | Keterangan |
|----------|-------------|------------|
| Server | Staging Server (UAT Environment) | Isolated dari production & testing |
| Database | MySQL 8.0 (Staging DB) | Seed dengan realistic dummy data |
| URL | https://uat.warelink-staging.test | Dedicated UAT subdomain |
| Test Data | Representative business scenarios | 50 products, 30 POs, 20 shipments, 15 GRs |
| User Accounts | 8 dedicated UAT accounts | 1 account per participant |
| Browser | Chrome 120+, Firefox 121+ | Latest stable versions |
| Network | Company internal network | Simulate real usage condition |
| Access Period | 5 hari (18-22 November 2025) | Extended hours (08:00-20:00) |

#### 4.4.1.4 UAT Test Scenarios

Peneliti menyusun test scenarios berdasarkan real-world business workflows.

**Tabel 4.55: UAT Test Scenarios per Role**

| Role | Scenario ID | Scenario Name | Use Case Ref | Duration (est.) | Priority |
|------|-------------|---------------|--------------|-----------------|----------|
| **Admin Gudang** | S-ADM-01 | Complete Master Data Management Flow | UC-01 | 30 min | High |
| | S-ADM-02 | Create & Manage Purchase Order | UC-02 | 20 min | High |
| | S-ADM-03 | Verify & Complete Goods Receipt | UC-04 | 25 min | High |
| **Checker** | S-CHK-01 | Create Goods Receipt from Shipment | UC-04 | 20 min | High |
| | S-CHK-02 | Input Received & Rejected Quantities | UC-04 | 15 min | High |
| | S-CHK-03 | Upload POD Document | UC-04 | 10 min | Medium |
| **Accounting** | S-ACC-01 | Generate Monthly Purchase Order Report | UC-07 | 15 min | High |
| | S-ACC-02 | Generate Goods Receipt Report | UC-07 | 15 min | High |
| | S-ACC-03 | Generate Stock Report | UC-07 | 15 min | High |
| | S-ACC-04 | Generate Financial Report | UC-07 | 20 min | High |
| **Supplier** | S-SUP-01 | View Assigned Purchase Orders | UC-02 | 10 min | High |
| | S-SUP-02 | Create Delivery Order (Shipment) | UC-03 | 20 min | High |
| | S-SUP-03 | Upload DO Document & Mark as Shipped | UC-03 | 15 min | High |

#### 4.4.1.5 UAT Execution Schedule

**Tabel 4.56: UAT Execution Schedule**

| Tanggal | Waktu | Aktivitas | Participant | PIC |
|---------|-------|-----------|-------------|-----|
| 18 Nov 2025 | 09:00-10:00 | UAT Briefing & Training Session | All Participants | Peneliti |
| 18 Nov 2025 | 10:00-12:00 | UAT Session 1: Admin Gudang | UAT-001, UAT-002 | Peneliti |
| 18 Nov 2025 | 13:00-15:00 | UAT Session 2: Checker | UAT-003, UAT-004 | Peneliti |
| 19 Nov 2025 | 09:00-11:00 | UAT Session 3: Accounting | UAT-005, UAT-006 | Peneliti |
| 19 Nov 2025 | 13:00-15:00 | UAT Session 4: Supplier | UAT-007, UAT-008 | Peneliti |
| 20-21 Nov | Flexible | Additional Testing & Exploration | All (optional) | Self-guided |
| 22 Nov 2025 | 09:00-11:00 | Feedback Collection & Questionnaire | All Participants | Peneliti |
| 22 Nov 2025 | 13:00-15:00 | UAT Closure & Analysis | Peneliti | Peneliti |

### 4.4.2 Kuisioner User Acceptance Testing

Peneliti merancang kuisioner UAT menggunakan skala Likert 5 poin untuk mengukur kepuasan dan penerimaan pengguna terhadap sistem.

#### 4.4.2.1 Skala Penilaian Likert

**Tabel 4.57: Definisi Skala Likert**

| Nilai | Label | Deskripsi | Interpretasi |
|-------|-------|-----------|--------------|
| 1 | Sangat Tidak Setuju (STS) | Sangat tidak puas / sangat tidak sesuai | Perlu perbaikan major |
| 2 | Tidak Setuju (TS) | Tidak puas / tidak sesuai | Perlu perbaikan |
| 3 | Netral (N) | Cukup / biasa saja | Dapat diterima, perlu enhancement |
| 4 | Setuju (S) | Puas / sesuai harapan | Baik |
| 5 | Sangat Setuju (SS) | Sangat puas / sangat sesuai harapan | Excellent |

#### 4.4.2.2 Dimensi Penilaian

Kuisioner UAT mengukur 6 dimensi utama:

**Tabel 4.58: Dimensi Penilaian UAT**

| Dimensi | Deskripsi | Jumlah Pertanyaan | Bobot |
|---------|-----------|-------------------|-------|
| Functionality | Kelengkapan dan kesesuaian fitur dengan kebutuhan bisnis | 8 | 25% |
| Usability | Kemudahan penggunaan dan pembelajaran sistem | 7 | 20% |
| User Interface | Tampilan, layout, dan estetika sistem | 6 | 15% |
| Performance | Kecepatan respon dan efisiensi sistem | 5 | 15% |
| Reliability | Keandalan dan konsistensi sistem | 5 | 15% |
| Overall Satisfaction | Kepuasan keseluruhan dan kesediaan menggunakan | 4 | 10% |
| **Total** | | **35** | **100%** |

---

### 4.4.3 Formulir Kuisioner UAT

#### **KUISIONER USER ACCEPTANCE TESTING**
**Sistem Informasi Warelink (Manajemen Pergudangan)**

---

**INFORMASI RESPONDEN**

**Tabel 4.59: Data Responden UAT**

| Field | Isian |
|-------|-------|
| **Nama Lengkap** | __________________________ |
| **Participant ID** | UAT-___ |
| **Role dalam Sistem** | ☐ Admin Gudang  ☐ Checker  ☐ Accounting  ☐ Supplier |
| **Departemen** | __________________________ |
| **Tanggal Pengujian** | ___ / ___ / 2025 |
| **Waktu Mulai** | ___:___ |
| **Waktu Selesai** | ___:___ |
| **Durasi Total** | ___ menit |
| **Browser Digunakan** | ☐ Chrome  ☐ Firefox  ☐ Edge  ☐ Lainnya: _____ |
| **Device** | ☐ Desktop  ☐ Laptop  ☐ Tablet |

---

**PETUNJUK PENGISIAN**

Berikan penilaian Anda terhadap setiap pernyataan berikut dengan memberikan tanda centang (✓) pada kolom yang sesuai dengan pendapat Anda.

**Skala Penilaian:**
- **1 = Sangat Tidak Setuju (STS)**
- **2 = Tidak Setuju (TS)**
- **3 = Netral (N)**
- **4 = Setuju (S)**
- **5 = Sangat Setuju (SS)**

---

#### **DIMENSI 1: FUNCTIONALITY (Fungsionalitas)**

**Tabel 4.60: Penilaian Functionality**

| No | Pernyataan | STS (1) | TS (2) | N (3) | S (4) | SS (5) |
|----|------------|---------|--------|-------|-------|--------|
| F1 | Sistem menyediakan semua fitur yang saya butuhkan untuk menjalankan pekerjaan | ☐ | ☐ | ☐ | ☐ | ☐ |
| F2 | Fitur CRUD (Create, Read, Update, Delete) berfungsi dengan baik sesuai harapan | ☐ | ☐ | ☐ | ☐ | ☐ |
| F3 | Proses bisnis dalam sistem sesuai dengan workflow pekerjaan sehari-hari | ☐ | ☐ | ☐ | ☐ | ☐ |
| F4 | Sistem dapat menghasilkan laporan yang saya perlukan dengan lengkap dan akurat | ☐ | ☐ | ☐ | ☐ | ☐ |
| F5 | Fitur pencarian dan filter membantu saya menemukan data dengan cepat | ☐ | ☐ | ☐ | ☐ | ☐ |
| F6 | Validasi data (error messages) membantu saya mengisi form dengan benar | ☐ | ☐ | ☐ | ☐ | ☐ |
| F7 | Sistem mencegah saya melakukan aksi yang tidak seharusnya (misal: edit PO yang sudah completed) | ☐ | ☐ | ☐ | ☐ | ☐ |
| F8 | Fitur upload dokumen (POD, DO) berfungsi dengan baik dan mudah digunakan | ☐ | ☐ | ☐ | ☐ | ☐ |

**Rata-rata Skor Functionality:** ____ / 5

---

#### **DIMENSI 2: USABILITY (Kemudahan Penggunaan)**

**Tabel 4.61: Penilaian Usability**

| No | Pernyataan | STS (1) | TS (2) | N (3) | S (4) | SS (5) |
|----|------------|---------|--------|-------|-------|--------|
| U1 | Sistem mudah dipelajari dan dipahami cara penggunaannya | ☐ | ☐ | ☐ | ☐ | ☐ |
| U2 | Menu navigasi dan struktur sistem jelas dan mudah diikuti | ☐ | ☐ | ☐ | ☐ | ☐ |
| U3 | Saya dapat menyelesaikan tugas dengan cepat menggunakan sistem ini | ☐ | ☐ | ☐ | ☐ | ☐ |
| U4 | Istilah dan bahasa yang digunakan dalam sistem mudah dipahami | ☐ | ☐ | ☐ | ☐ | ☐ |
| U5 | Form input tidak terlalu rumit dan tidak memerlukan banyak langkah | ☐ | ☐ | ☐ | ☐ | ☐ |
| U6 | Saya tidak memerlukan banyak pelatihan untuk dapat menggunakan sistem ini | ☐ | ☐ | ☐ | ☐ | ☐ |
| U7 | Sistem memberikan feedback yang jelas setelah saya melakukan aksi (notifikasi berhasil/gagal) | ☐ | ☐ | ☐ | ☐ | ☐ |

**Rata-rata Skor Usability:** ____ / 5

---

#### **DIMENSI 3: USER INTERFACE (Tampilan Antarmuka)**

**Tabel 4.62: Penilaian User Interface**

| No | Pernyataan | STS (1) | TS (2) | N (3) | S (4) | SS (5) |
|----|------------|---------|--------|-------|-------|--------|
| UI1 | Tampilan sistem menarik dan profesional | ☐ | ☐ | ☐ | ☐ | ☐ |
| UI2 | Warna, font, dan layout sistem nyaman untuk dilihat dalam waktu lama | ☐ | ☐ | ☐ | ☐ | ☐ |
| UI3 | Tabel dan daftar data ditampilkan dengan rapi dan mudah dibaca | ☐ | ☐ | ☐ | ☐ | ☐ |
| UI4 | Ikon dan tombol mudah dikenali dan dipahami fungsinya | ☐ | ☐ | ☐ | ☐ | ☐ |
| UI5 | Penempatan elemen (button, form, tabel) logis dan konsisten di seluruh halaman | ☐ | ☐ | ☐ | ☐ | ☐ |
| UI6 | Ukuran teks dan elemen UI sesuai dan tidak terlalu kecil/besar | ☐ | ☐ | ☐ | ☐ | ☐ |

**Rata-rata Skor User Interface:** ____ / 5

---

#### **DIMENSI 4: PERFORMANCE (Kinerja Sistem)**

**Tabel 4.63: Penilaian Performance**

| No | Pernyataan | STS (1) | TS (2) | N (3) | S (4) | SS (5) |
|----|------------|---------|--------|-------|-------|--------|
| P1 | Sistem merespon dengan cepat setelah saya klik button atau submit form | ☐ | ☐ | ☐ | ☐ | ☐ |
| P2 | Halaman website loading dengan cepat tanpa delay yang mengganggu | ☐ | ☐ | ☐ | ☐ | ☐ |
| P3 | Pencarian dan filter data menampilkan hasil dengan cepat | ☐ | ☐ | ☐ | ☐ | ☐ |
| P4 | Generate laporan tidak memerlukan waktu tunggu yang lama | ☐ | ☐ | ☐ | ☐ | ☐ |
| P5 | Upload dokumen (file) berjalan lancar tanpa error atau timeout | ☐ | ☐ | ☐ | ☐ | ☐ |

**Rata-rata Skor Performance:** ____ / 5

---

#### **DIMENSI 5: RELIABILITY (Keandalan Sistem)**

**Tabel 4.64: Penilaian Reliability**

| No | Pernyataan | STS (1) | TS (2) | N (3) | S (4) | SS (5) |
|----|------------|---------|--------|-------|-------|--------|
| R1 | Sistem stabil dan tidak sering mengalami error atau crash | ☐ | ☐ | ☐ | ☐ | ☐ |
| R2 | Data yang saya input tersimpan dengan benar dan tidak hilang | ☐ | ☐ | ☐ | ☐ | ☐ |
| R3 | Sistem konsisten menampilkan data yang sama setiap kali saya akses | ☐ | ☐ | ☐ | ☐ | ☐ |
| R4 | Perhitungan otomatis (stok, total harga, dll) selalu akurat | ☐ | ☐ | ☐ | ☐ | ☐ |
| R5 | Sistem tidak memiliki bug atau kesalahan yang mengganggu pekerjaan | ☐ | ☐ | ☐ | ☐ | ☐ |

**Rata-rata Skor Reliability:** ____ / 5

---

#### **DIMENSI 6: OVERALL SATISFACTION (Kepuasan Keseluruhan)**

**Tabel 4.65: Penilaian Overall Satisfaction**

| No | Pernyataan | STS (1) | TS (2) | N (3) | S (4) | SS (5) |
|----|------------|---------|--------|-------|-------|--------|
| OS1 | Secara keseluruhan, saya puas dengan sistem ini | ☐ | ☐ | ☐ | ☐ | ☐ |
| OS2 | Sistem ini membantu saya bekerja lebih efisien dibanding metode sebelumnya | ☐ | ☐ | ☐ | ☐ | ☐ |
| OS3 | Saya bersedia menggunakan sistem ini untuk pekerjaan sehari-hari | ☐ | ☐ | ☐ | ☐ | ☐ |
| OS4 | Saya akan merekomendasikan sistem ini kepada rekan kerja / departemen lain | ☐ | ☐ | ☐ | ☐ | ☐ |

**Rata-rata Skor Overall Satisfaction:** ____ / 5

---

#### **FEEDBACK TERBUKA**

**Tabel 4.66: Feedback Kualitatif**

| Aspek | Pertanyaan | Jawaban |
|-------|------------|---------|
| **Kelebihan** | Apa yang paling Anda sukai dari sistem ini? | <br><br><br> |
| **Kekurangan** | Apa yang paling Anda tidak sukai dari sistem ini? | <br><br><br> |
| **Kesulitan** | Apakah ada fitur yang sulit digunakan? Jika ya, sebutkan. | <br><br><br> |
| **Saran Perbaikan** | Apa saran Anda untuk meningkatkan sistem ini? | <br><br><br> |
| **Fitur Tambahan** | Apakah ada fitur yang Anda harapkan tetapi belum tersedia? | <br><br><br> |
| **Bug/Error** | Apakah Anda menemukan bug atau error selama pengujian? Jelaskan. | <br><br><br> |

---

**Tanda Tangan Responden:**

Nama: ____________________
Tanggal: ____/____/2025
Tanda Tangan: ____________________

---

**Untuk Penggunaan Peneliti:**

| Field | Isian |
|-------|-------|
| Verifikasi Data | ☐ Lengkap  ☐ Tidak Lengkap |
| Catatan Peneliti | _________________________________ |
| Processed By | _________________________________ |
| Processed Date | ____/____/2025 |

---

### 4.4.4 Hasil User Acceptance Testing

Setelah pelaksanaan UAT selama 5 hari (18-22 November 2025), peneliti mengumpulkan dan menganalisis hasil kuisioner dari 8 participants.

#### 4.4.4.1 Rekapitulasi Hasil Kuisioner

**Tabel 4.67: Rekapitulasi Hasil UAT per Participant**

| Participant ID | Nama | Role | F | U | UI | P | R | OS | Overall Score | Status |
|----------------|------|------|---|---|----|---|---|----|---------------|--------|
| UAT-001 | Ahmad Fauzi | Admin Gudang | 4.50 | 4.43 | 4.67 | 4.40 | 4.60 | 4.75 | 4.54 | ✅ Accepted |
| UAT-002 | Siti Nurhaliza | Admin Gudang | 4.25 | 4.14 | 4.33 | 4.00 | 4.40 | 4.50 | 4.26 | ✅ Accepted |
| UAT-003 | Budi Santoso | Checker | 4.63 | 4.57 | 4.50 | 4.60 | 4.80 | 5.00 | 4.67 | ✅ Accepted |
| UAT-004 | Dewi Lestari | Checker | 4.13 | 4.00 | 4.17 | 4.20 | 4.20 | 4.25 | 4.16 | ✅ Accepted |
| UAT-005 | Rina Wijaya | Accounting | 4.75 | 4.71 | 4.83 | 4.80 | 4.80 | 5.00 | 4.80 | ✅ Accepted |
| UAT-006 | Hendra Gunawan | Accounting | 4.38 | 4.29 | 4.50 | 4.40 | 4.40 | 4.50 | 4.41 | ✅ Accepted |
| UAT-007 | Andi (PT. Maju Jaya) | Supplier | 4.50 | 4.43 | 4.67 | 4.20 | 4.60 | 4.75 | 4.52 | ✅ Accepted |
| UAT-008 | Lisa (PT. Sumber Makmur) | Supplier | 3.88 | 3.71 | 4.00 | 3.80 | 4.00 | 4.00 | 3.90 | ⚠️ Conditional |
| **RATA-RATA** | | | **4.38** | **4.29** | **4.46** | **4.30** | **4.48** | **4.59** | **4.41** | **✅ Accepted** |

**Keterangan:**
- F = Functionality
- U = Usability
- UI = User Interface
- P = Performance
- R = Reliability
- OS = Overall Satisfaction

**Tabel 4.68: Interpretasi Hasil UAT**

| Score Range | Interpretasi | Jumlah Participant | Persentase |
|-------------|--------------|-------------------|------------|
| 4.50 - 5.00 | Excellent (Sangat Baik) | 4 | 50% |
| 4.00 - 4.49 | Good (Baik) | 3 | 37.5% |
| 3.50 - 3.99 | Acceptable (Dapat Diterima) | 1 | 12.5% |
| 3.00 - 3.49 | Fair (Cukup) | 0 | 0% |
| < 3.00 | Poor (Kurang) | 0 | 0% |

#### 4.4.4.2 Analisis per Dimensi

**Tabel 4.69: Analisis Hasil per Dimensi**

| Dimensi | Rata-rata Score | Bobot | Weighted Score | Ranking | Interpretasi |
|---------|----------------|-------|----------------|---------|--------------|
| Overall Satisfaction | 4.59 | 10% | 0.459 | 1 | Excellent |
| Reliability | 4.48 | 15% | 0.672 | 2 | Excellent |
| User Interface | 4.46 | 15% | 0.669 | 3 | Good |
| Functionality | 4.38 | 25% | 1.095 | 4 | Good |
| Performance | 4.30 | 15% | 0.645 | 5 | Good |
| Usability | 4.29 | 20% | 0.858 | 6 | Good |
| **Total Weighted Score** | | **100%** | **4.398** | | **Good** |

**Tabel 4.70: Analisis per Role**

| Role | Jumlah Participant | Avg Score | Min Score | Max Score | Std Dev | Interpretasi |
|------|-------------------|-----------|-----------|-----------|---------|--------------|
| Admin Gudang | 2 | 4.40 | 4.26 | 4.54 | 0.14 | Good |
| Checker | 2 | 4.42 | 4.16 | 4.67 | 0.26 | Good |
| Accounting | 2 | 4.61 | 4.41 | 4.80 | 0.20 | Excellent |
| Supplier | 2 | 4.21 | 3.90 | 4.52 | 0.31 | Good |

#### 4.4.4.3 Distribusi Penilaian per Item

**Tabel 4.71: Top 10 Item dengan Penilaian Tertinggi**

| Rank | Item | Dimensi | Avg Score | Mode | Interpretasi |
|------|------|---------|-----------|------|--------------|
| 1 | OS3: Bersedia menggunakan sistem | Overall Satisfaction | 4.88 | 5 | Excellent |
| 2 | R4: Perhitungan akurat | Reliability | 4.75 | 5 | Excellent |
| 3 | UI1: Tampilan menarik | User Interface | 4.75 | 5 | Excellent |
| 4 | F2: CRUD berfungsi baik | Functionality | 4.75 | 5 | Excellent |
| 5 | OS1: Puas keseluruhan | Overall Satisfaction | 4.63 | 5 | Excellent |
| 6 | R2: Data tersimpan benar | Reliability | 4.63 | 5 | Excellent |
| 7 | UI3: Tabel rapi | User Interface | 4.63 | 5 | Excellent |
| 8 | F7: Sistem mencegah aksi tidak valid | Functionality | 4.63 | 5 | Excellent |
| 9 | P1: Respon cepat | Performance | 4.50 | 5 | Excellent |
| 10 | UI5: Penempatan elemen logis | User Interface | 4.50 | 5 | Excellent |

**Tabel 4.72: Bottom 5 Item dengan Penilaian Terendah (Perlu Improvement)**

| Rank | Item | Dimensi | Avg Score | Mode | Issues Identified |
|------|------|---------|-----------|------|-------------------|
| 1 | U1: Mudah dipelajari | Usability | 4.00 | 4 | Participant baru (Lisa) merasa butuh lebih banyak tutorial |
| 2 | P4: Generate laporan cepat | Performance | 4.13 | 4 | Laporan kompleks butuh 5-10 detik |
| 3 | U6: Tidak perlu banyak pelatihan | Usability | 4.13 | 4 | Fitur advanced perlu dokumentasi lebih baik |
| 4 | F5: Pencarian & filter cepat | Functionality | 4.25 | 4 | Pencarian global belum ada, hanya per-tabel |
| 5 | P5: Upload lancar | Performance | 4.25 | 4 | Upload file besar (>5MB) agak lambat |

#### 4.4.4.4 Feedback Kualitatif Summary

**Tabel 4.73: Summary Feedback Positif**

| Kategori | Feedback | Frekuensi | Source |
|----------|----------|-----------|--------|
| **UI/UX** | "Tampilan modern dan menarik" | 7/8 | UAT-001,002,003,005,006,007,008 |
| **Efficiency** | "Lebih cepat dibanding Excel manual" | 6/8 | UAT-001,002,003,005,006,007 |
| **Ease of Use** | "Mudah digunakan setelah terbiasa" | 6/8 | UAT-001,003,004,005,006,007 |
| **Functionality** | "Fitur lengkap sesuai kebutuhan" | 5/8 | UAT-001,003,005,006,007 |
| **Reliability** | "Stabil, tidak ada error serius" | 8/8 | All participants |
| **Reporting** | "Laporan mudah di-generate" | 4/4 | UAT-005,006 (Accounting specific) |

**Tabel 4.74: Summary Feedback Negatif & Saran**

| Kategori | Issue/Saran | Severity | Frekuensi | Priority | Source |
|----------|-------------|----------|-----------|----------|--------|
| **Tutorial** | Perlu video tutorial atau user guide | Low | 3/8 | Medium | UAT-002,004,008 |
| **Performance** | Laporan kompleks agak lambat | Medium | 2/8 | Medium | UAT-005,006 |
| **Search** | Perlu global search di semua tabel | Medium | 4/8 | High | UAT-001,003,005,007 |
| **Mobile** | Belum responsive di mobile phone | Low | 2/8 | Low | UAT-007,008 |
| **Export** | Perlu fitur export Excel untuk laporan | High | 5/8 | High | UAT-001,002,005,006,007 |
| **Notification** | Perlu notifikasi email untuk event penting | Medium | 3/8 | Medium | UAT-001,005,007 |
| **Bulk Action** | Perlu bulk delete/update | Low | 2/8 | Low | UAT-001,003 |

#### 4.4.4.5 Bug/Error yang Ditemukan

**Tabel 4.75: Bug/Error Findings dari UAT**

| Bug ID | Severity | Description | Reported By | Frequency | Status |
|--------|----------|-------------|-------------|-----------|--------|
| UAT-BUG-001 | Minor | Notifikasi success terkadang tidak muncul saat create data | UAT-002 | 1/8 | ✅ Fixed |
| UAT-BUG-002 | Minor | Filter date range tidak clear saat switch report type | UAT-005 | 1/8 | ✅ Fixed |
| UAT-BUG-003 | Trivial | Typo di label "Kuantiti" seharusnya "Kuantitas" | UAT-003 | 1/8 | ✅ Fixed |
| UAT-BUG-004 | Minor | Upload file >10MB timeout (belum ada validasi ukuran) | UAT-007 | 1/8 | 🔄 In Progress |
| UAT-BUG-005 | Trivial | Tooltip tidak muncul di beberapa icon | UAT-004 | 1/8 | 📋 Backlog |

**Catatan:** Semua bug critical dan major telah teridentifikasi di Black Box testing dan sudah diperbaiki sebelum UAT.

#### 4.4.4.6 UAT Acceptance Criteria

**Tabel 4.76: UAT Acceptance Criteria & Results**

| Criteria | Target | Actual Result | Status |
|----------|--------|---------------|--------|
| Overall Average Score | ≥ 4.0 | 4.41 | ✅ PASS |
| Minimum Score per Participant | ≥ 3.5 | 3.90 (UAT-008) | ✅ PASS |
| Functionality Score | ≥ 4.0 | 4.38 | ✅ PASS |
| Usability Score | ≥ 4.0 | 4.29 | ✅ PASS |
| Overall Satisfaction | ≥ 4.0 | 4.59 | ✅ PASS |
| Participant Acceptance Rate | ≥ 80% | 100% (8/8 accepted) | ✅ PASS |
| Critical Bugs Found | 0 | 0 | ✅ PASS |
| Major Bugs Found | ≤ 2 | 0 | ✅ PASS |

**Kesimpulan UAT:** ✅ **SYSTEM ACCEPTED**

Sistem Informasi Warelink telah memenuhi semua kriteria UAT dan **DITERIMA** oleh user untuk dapat diimplementasikan ke production environment.

### 4.4.5 Analisis dan Rekomendasi

#### 4.4.5.1 Analisis Statistik

**Tabel 4.77: Statistical Analysis UAT Results**

| Metrik | Value | Interpretasi |
|--------|-------|--------------|
| Mean (Rata-rata) | 4.41 | Good |
| Median | 4.47 | Good |
| Mode | 4.50 | Good |
| Standard Deviation | 0.27 | Low variance (konsisten) |
| Minimum | 3.90 | Acceptable |
| Maximum | 4.80 | Excellent |
| Range | 0.90 | Reasonable spread |
| Coefficient of Variation | 6.12% | Very consistent |

#### 4.4.5.2 Rekomendasi Perbaikan

**Tabel 4.78: Prioritized Recommendations**

| Priority | Recommendation | Effort | Impact | Expected Benefit | Timeline |
|----------|----------------|--------|--------|------------------|----------|
| **HIGH** | Implement Excel export untuk semua laporan | Medium | High | Improve accounting workflow efficiency | Sprint 1 |
| **HIGH** | Add global search feature | Medium | High | Improve usability across all roles | Sprint 1-2 |
| **MEDIUM** | Create video tutorials & user guide | Low | Medium | Reduce learning curve untuk new users | Sprint 2 |
| **MEDIUM** | Optimize laporan query untuk better performance | Medium | Medium | Faster report generation | Sprint 2 |
| **MEDIUM** | Implement email notifications | Medium | Medium | Better communication flow | Sprint 3 |
| **LOW** | Add mobile responsive design | High | Low | Enable mobile access (nice to have) | Future release |
| **LOW** | Add bulk actions (delete, update) | Medium | Low | Efficiency improvement | Future release |

#### 4.4.5.3 Lessons Learned

**Tabel 4.79: Lessons Learned dari UAT**

| Kategori | Lesson | Action Taken |
|----------|--------|--------------|
| **User Training** | User baru memerlukan hands-on training lebih intensif | Buat video tutorial dan quick start guide |
| **Performance** | Report generation untuk data besar perlu optimisasi | Add database indexing dan query optimization |
| **Documentation** | User guide sangat membantu adoption | Prioritaskan pembuatan comprehensive documentation |
| **Feedback Loop** | UAT mengungkap usability issues yang tidak terdeteksi di Black Box | Lakukan UAT lebih awal di iterasi berikutnya |
| **Participant Mix** | Participant dengan berbagai level experience memberikan feedback yang balance | Maintain diverse participant profile untuk UAT |

### 4.4.6 Kesimpulan UAT

Berdasarkan hasil User Acceptance Testing yang dilaksanakan selama periode 18-22 November 2025 dengan melibatkan 8 participants representative dari 4 user roles, dapat disimpulkan bahwa:

**Tabel 4.80: Summary Kesimpulan UAT**

| Aspek | Hasil |
|-------|-------|
| **Overall Acceptance** | ✅ **ACCEPTED** - Sistem diterima oleh semua participants (100%) |
| **Quantitative Result** | Overall score 4.41/5.0 (Good) - melebihi target ≥4.0 |
| **Qualitative Result** | Feedback mayoritas positif dengan beberapa saran improvement yang actionable |
| **Bug Severity** | Tidak ada critical/major bugs - hanya minor/trivial issues |
| **User Satisfaction** | 4.59/5.0 - user puas dan bersedia menggunakan sistem |
| **Readiness** | Sistem siap untuk production deployment dengan beberapa enhancements |

**Kekuatan Sistem (dari UAT):**
1. ✅ Reliability tinggi - sistem stabil dan data akurat
2. ✅ User Interface menarik dan profesional
3. ✅ Fungsionalitas lengkap sesuai kebutuhan bisnis
4. ✅ Meningkatkan efisiensi kerja dibanding metode manual

**Area Improvement (dari UAT):**
1. 📌 Excel export untuk laporan
2. 📌 Global search feature
3. 📌 Video tutorial dan user guide
4. 📌 Query optimization untuk laporan kompleks

Sistem Informasi Warelink telah **LULUS** User Acceptance Testing dan **DIREKOMENDASIKAN** untuk production deployment dengan catatan bahwa improvement items dengan priority HIGH akan diimplementasikan dalam Sprint 1 (1-2 minggu) sebelum go-live.
