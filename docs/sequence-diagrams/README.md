# Sequence Diagrams - Sistem Informasi Warelink

Dokumentasi ini berisi **high-level sequence diagram** untuk setiap use case dalam Sistem Informasi Warelink. Sequence diagram ini dirancang untuk **fokus pada alur utama** yang konsisten dengan activity diagram, tanpa detail implementasi yang terlalu teknis.

## Pendekatan High-Level

Sequence diagram dalam repository ini menggunakan pendekatan **high-level** dengan karakteristik:

✅ **Fokus pada main flow** sesuai activity diagram
✅ **Komponen utama saja** (UI, Resource, Model, Database, External Services)
✅ **Mudah dipahami** oleh stakeholder teknis maupun bisnis
✅ **Konsisten dengan codebase** yang sudah ada
✅ **Technical details dalam notes**, bukan di sequence flow

❌ **Tidak menampilkan** semua method calls detail
❌ **Tidak menampilkan** semua loop dan conditional yang kompleks
❌ **Tidak menampilkan** setiap database query secara detail

## Apa itu Sequence Diagram?

Sequence diagram adalah diagram UML yang menggambarkan:
- **Interaksi antar komponen**: Bagaimana komponen saling berkomunikasi
- **Urutan eksekusi**: Timeline dari interaksi yang terjadi
- **Main flow**: Alur utama proses bisnis
- **Key decisions**: Keputusan penting dalam alur

### Perbedaan dengan Activity Diagram

| Activity Diagram | Sequence Diagram |
|-----------------|------------------|
| Fokus pada **alur proses bisnis** | Fokus pada **interaksi antar komponen** |
| Menunjukkan **apa yang terjadi** | Menunjukkan **siapa yang terlibat dan bagaimana** |
| Perspektif **business process** | Perspektif **system architecture** |
| Swimlanes & decision points | Actors & participants |

**Contoh:**
- **Activity Diagram**: "Admin membuat PO → System generate nomor PO → System simpan PO"
- **Sequence Diagram**: "Admin → UI → Resource → Model → Database → back to Admin"

## Daftar Sequence Diagrams

### 1. Master Data Management
**File:** `1-master-data-management.puml`
**Aktor:** Admin Gudang
**Komponen:**
- Filament UI
- Resource (Controller layer)
- Model (User, Supplier, Product)
- Database

**Main Flows:**
- CRUD User
- CRUD Supplier (with auto-generate code)
- CRUD Product (with stock management)

---

### 2. Purchase Order Management
**File:** `2-purchase-order-management.puml`
**Aktor:** Admin Gudang
**Komponen:**
- Filament UI
- PO Resource
- PurchaseOrder Model
- Database
- Notification Service

**Main Flows:**
- Create PO
- Edit PO (only if status=Pending)
- View PO
- Cancel PO (only if status=Pending)

**Key Logic:**
- Auto-generate PO number
- Calculate total amount
- Status validation
- Notification to Supplier

---

### 3. Delivery Order Management
**File:** `3-delivery-order-management.puml`
**Aktor:** Supplier
**Komponen:**
- Filament UI
- Shipment Resource
- Shipment Model
- Database
- Notification Service

**Main Flows:**
- View PO (filtered by supplier)
- Create DO with document upload
- Edit DO (only if status=Draft)
- Mark as Shipped

**Key Logic:**
- Tenant filtering (supplier_id)
- Document upload (DO scan)
- Quantity validation
- Notification to Admin & Checker

---

### 4. Goods Receipt Creation
**File:** `4-goods-receipt-creation.puml`
**Aktor:** Checker
**Komponen:**
- Filament UI
- GR Resource
- GoodsReceipt Model
- Shipment Model
- Database
- Notification Service

**Main Flows:**
- View Shipped DO
- Verify DO document
- Create Goods Receipt
- Input received vs rejected quantities
- Upload POD document

**Key Logic:**
- Document verification
- Physical inspection
- Generate GRN number
- Update shipment status to Arrived
- Notification to Admin

---

### 5. Goods Receipt Verification
**File:** `5-goods-receipt-verification.puml`
**Aktor:** Admin Gudang
**Komponen:**
- Filament UI
- GR Resource
- GoodsReceipt, Product, PurchaseOrder, Shipment Models
- Database
- Notification Service

**Main Flows:**
- View Pending GR
- Verify GR data
- Complete GR (triggers cascade updates)

**Key Logic:**
- **Database Transaction** untuk cascade updates:
  1. Update product stock
  2. Update PO received quantities & status
  3. Update shipment status to Processed
  4. Mark GR as Completed
- Notification to Accounting & Supplier

**Critical Flow:** Ini adalah flow paling kompleks karena memicu banyak updates atomik.

---

### 6. Monthly Report Generation
**File:** `6-monthly-report-generation.puml`
**Aktor:** Accounting
**Komponen:**
- Filament UI
- Report Page
- Report Generator
- Database

**Main Flows:**
- Generate 4 types of reports:
  1. Purchase Order Report
  2. Goods Receipt Report
  3. Stock Product Report
  4. Financial Report
- Export to PDF/Excel/CSV

**Key Logic:**
- Query data by period
- Calculate statistics & metrics
- Generate charts
- Multi-format export

---

### 7. View Product
**File:** `7-view-product.puml`
**Aktor:** Supplier
**Komponen:**
- Filament UI
- Product Resource
- Product Model
- Database

**Main Flows:**
- View product list (filtered by supplier)
- Filter/search products
- View product details with statistics
- View transaction history
- View stock movement history
- View trend charts
- Export product data

**Key Logic:**
- Tenant filtering
- Calculate statistics (acceptance rate, projected stock, etc.)
- Generate trend charts
- Read-only access for supplier

---

### 8. Messaging
**File:** `8-messaging.puml`
**Aktor:** All Users
**Komponen:**
- Livewire Wirechat Component
- Chat & Message Models
- Database
- Laravel Reverb (WebSocket)
- Notification Service

**Main Flows:**
- Create private chat
- Create group chat
- Open existing chat
- Send message
- Receive message real-time

**Key Logic:**
- Permission checking (canCreateChats, canCreateGroups)
- Real-time delivery via Reverb WebSocket
- Read receipts
- Push notifications
- Unread counter

---

## Komponen Utama dalam Diagram

### 1. Actor
User atau role yang memulai interaksi:
- Admin Gudang
- Supplier
- Checker
- Accounting

### 2. Filament UI / Livewire
Layer presentasi yang menampilkan interface ke user.

### 3. Resource / Page
Layer controller di Filament yang menangani request/response.

### 4. Model
Eloquent model yang merepresentasikan business entity:
- User, Supplier, Product
- PurchaseOrder, Shipment, GoodsReceipt
- Chat, Message

### 5. Database
Layer persistence untuk menyimpan data.

### 6. External Services
- Notification: untuk kirim notifikasi
- Reverb: WebSocket server untuk real-time messaging

## Cara Membaca Sequence Diagram

### Timeline dari Atas ke Bawah
Diagram dibaca **dari atas ke bawah** mengikuti urutan waktu:

```
Actor -> UI: User action
UI -> Resource: Request
Resource -> Model: Operation
Model -> DB: Query
DB --> Model: Data (return)
Model --> Resource: Result (return)
Resource --> UI: Response (return)
UI --> Actor: Display
```

### Activation Boxes
Kotak vertikal menunjukkan komponen sedang aktif memproses:
```
activate Component
... processing ...
deactivate Component
```

### Alt (Alternative)
Menunjukkan conditional flow (if-else):
```
alt Condition True
    ... do this ...
else Condition False
    ... do that ...
end
```

### Notes
Catatan untuk technical details tanpa mengganggu main flow:
```
note right of Component
    Technical details here
end note
```

## Hubungan dengan Diagram Lain

```
Class Diagram (Structural)
    ↓ defines structure
Activity Diagram (Behavioral - Business View)
    ↓ shows what happens
Sequence Diagram (Behavioral - Architecture View)
    ↓ shows who & how
Implementation (Code)
```

### Activity Diagram → Sequence Diagram

**Activity Diagram menunjukkan WHAT:**
```
Admin: Buat Purchase Order
System: Generate nomor PO
System: Hitung total amount
System: Simpan PO
System: Kirim notifikasi ke Supplier
```

**Sequence Diagram menunjukkan WHO & HOW:**
```
Admin -> UI: Klik "Buat PO"
UI -> Resource: Submit PO data
Resource -> PO: Create PO (generates number, calculates total)
PO -> DB: Save PO
Resource -> Notif: Send to Supplier
```

## Pola-pola Umum

### 1. Basic CRUD Flow
```
Actor -> UI: Access menu
UI -> Resource: Request list
Resource -> Model: Get data
Model -> DB: Query
DB --> Model: Data
Model --> Resource: Collection
Resource --> UI: List data
UI --> Actor: Display
```

### 2. Create with Validation
```
Actor -> UI: Submit data
UI -> Resource: Create request
Resource -> Resource: Validate
alt Valid
    Resource -> Model: Create
    Model -> DB: Save
else Invalid
    Resource --> UI: Errors
end
```

### 3. Status-based Update
```
Resource -> Model: Find by ID
Model -> DB: Query
alt Status allows update
    Resource -> Model: Update
else Status blocks update
    Resource --> UI: Error
end
```

### 4. Transaction Pattern
```
note: Transaction Start
Resource -> Model1: Update
Resource -> Model2: Update
Resource -> Model3: Update
note: Transaction Commit
```

### 5. Notification Pattern
```
Resource -> Model: Save
Resource -> Notif: Send notification
```

### 6. Real-time Broadcasting
```
Component -> Message: Create
Component -> Reverb: Broadcast
Reverb -> Recipient: Real-time push
```

## Cara Render Diagram

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

# Generate SVG (recommended)
plantuml -tsvg 1-master-data-management.puml

# Generate semua diagram
cd docs/sequence-diagrams
plantuml *.puml
```

## Best Practices

### ✅ DO:
- Fokus pada main flow dan key interactions
- Gunakan notes untuk technical details
- Konsisten dengan activity diagram
- Keep it simple and readable
- Use meaningful participant names

### ❌ DON'T:
- Terlalu banyak detail teknis di flow
- Menampilkan setiap method call
- Membuat diagram terlalu kompleks
- Mengabaikan activity diagram

## Validasi dengan Kode

Meskipun sequence diagram high-level, komponen dan flow harus tetap konsisten dengan kode:

**Sequence Diagram:**
```
Resource -> PO: Create PO
note: - Generate PO number
      - Calculate total
      - Set status = Pending
```

**Kode (tetap ada di implementasi):**
```php
// Di PurchaseOrderResource atau Observer
$po = PurchaseOrder::create([...]);
$po->po_number = $this->generatePONumber();
$po->total_amount = $po->calculateTotalAmount();
$po->status = PurchaseOrderStatus::Pending;
```

Flow dan business logic harus match, tapi sequence diagram tidak perlu show setiap line of code.

## Kapan Menggunakan Diagram Ini?

### 1. **Planning & Design Phase**
- Memahami flow sebelum coding
- Design review dengan team
- Diskusi dengan stakeholder

### 2. **Development Phase**
- Panduan implementasi
- Memastikan semua komponen ter-integrate
- Code review

### 3. **Documentation Phase**
- Dokumentasi arsitektur sistem
- Onboarding developer baru
- Knowledge transfer

### 4. **Maintenance Phase**
- Memahami existing flow sebelum changes
- Debug & troubleshooting
- Refactoring planning

## Perbedaan dengan Low-Level Sequence Diagram

| High-Level (ini) | Low-Level |
|-----------------|-----------|
| Main components only | All classes & objects |
| Key interactions | Every method call |
| Business flow focus | Implementation focus |
| Easy to understand | Very detailed |
| For all stakeholders | For developers only |
| Stays relevant longer | Needs frequent updates |

## Catatan Penting

1. **Konsisten dengan Activity Diagram**: Setiap flow mengikuti activity diagram
2. **High-level but accurate**: Simplified tapi tetap benar secara arsitektur
3. **Notes untuk details**: Technical details di notes, bukan di flow
4. **Transaction awareness**: Flow kompleks menggunakan database transaction
5. **Notification flows**: Setiap operasi penting mengirim notifikasi
6. **Tenant filtering**: Supplier hanya akses data mereka sendiri
7. **Status validation**: Edit/delete hanya allowed untuk status tertentu
8. **Real-time features**: Messaging menggunakan WebSocket via Reverb

## Referensi

- [PlantUML Sequence Diagram](https://plantuml.com/sequence-diagram)
- [UML Sequence Diagram Guide](https://www.lucidchart.com/pages/uml-sequence-diagram)
- [Activity Diagrams](../activity-diagrams/README.md)
- [Class Diagram](../../Untitled%20Diagram-Page-4.drawio.pdf)

---

**Dibuat:** 2025-11-06
**Sistem:** Warelink - Warehouse Management System
**Versi:** 2.0 (High-Level)
**Type:** Behavioral Modeling - Sequence Diagrams (Simplified)
