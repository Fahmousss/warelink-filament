# AI Prompt for Building WareLink System using Laravel 12

## Project Overview
You are tasked to build a warehouse management system named **WareLink** for PT Buana Sentosa Nusantara. The system must comply with the **Software Requirement Specification (SRS)** defined earlier, addressing the roles of **Supplier**, **Checker**, **Admin**, and **Accounting**. The system should be implemented using **Laravel 12**, **Blade Templating**, **AlpineJS**, **TailwindCSS v4**, **MySQL**, **Laravel Breeze Authentication**, **Pusher for Chat**, and **independent reusable components**.
The architecture must follow the **MVC pattern**. Every part of the system must be modular, maintainable, and intuitive.

---

## Requirements

### Models
Each entity in the system must be represented by a Laravel **Eloquent Model**. Models should implement:
- **Accessors and Mutators** using `Attribute`.
- **Local Scopes** defined with `#[Scope]` returning query builders.
- **Global Scopes** defined with `#[ScopedBy()]`.
- **Observers** bound to models with `#[ObservedBy()]` for handling lifecycle events (create, update, delete).

Entities include:
- `User` (with roles: Supplier, Checker, Admin, Accounting)
- `SellOrder`
- `Transaction`
- `Invoice`
- `Message`
- `Report`
- `Product`
- `Stock`

### Controllers
Use **Single Action Controllers** if a controller handles only one action. For CRUD operations, use **Resource Controllers** with standard methods:
- `index`
- `create`
- `store`
- `edit`
- `update`
- `destroy`

Controllers should strictly follow separation of concerns. For example:
- `Auth\LoginController` (Single Action)
- `Supplier\SellOrderController` (Resource)
- `Checker\TransactionController` (Resource)
- `Admin\UserController` (Resource)
- `Accounting\ReportController` (Resource)
- `Chat\MessageController` (Resource with Pusher integration)

### Views
Use **Blade Templating** with separation:
- **Layouts**: master layout files under `resources/views/layouts`.
- **Components**: reusable UI elements under `resources/views/components`.
- **CSS and JS**: Tailwind v4 and AlpineJS used modularly.
- **Page Views**: each module has its dedicated view directory, e.g. `resources/views/supplier/orders`.

Follow intuitive and friendly design with responsive Tailwind classes.

### Migrations
Define schema for each entity:
- `users`: role enum, name, email, password, timestamps.
- `sell_orders`: user_id (supplier), product details, status, timestamps.
- `transactions`: sell_order_id, checker_id, validation status, timestamps.
- `invoices`: transaction_id, invoice_number, file_path, timestamps.
- `messages`: sender_id, receiver_id, content, timestamps.
- `reports`: type, generated_by, file_path, timestamps.
- `products`: name, description, unit, price, stock.
- `stocks`: product_id, quantity, transaction_id, timestamps.

### Middleware
Define middlewares for:
- **Role-based Access Control** (Supplier, Checker, Admin, Accounting).
- **Auth Verification** (Laravel Breeze).
- **Logging Middleware** for tracking requests.

### Enums
Use PHP `enum` for fixed values:
- `UserRoleEnum` { SUPPLIER, CHECKER, ADMIN, ACCOUNTING }
- `TransactionStatusEnum` { PENDING, VALIDATED, REJECTED }
- `ReportTypeEnum` { DAILY, WEEKLY, MONTHLY }

### Requests
Create **Form Request classes** for validation:
- `StoreSellOrderRequest`
- `UpdateSellOrderRequest`
- `ValidateTransactionRequest`
- `StoreMessageRequest`
- `StoreReportRequest`

### Rules
Custom validation rules for:
- File uploads (bukti pengiriman).
- Stock quantity checks.
- Invoice number format.

### Policies
Policies to enforce access control:
- `SellOrderPolicy`: only suppliers can create orders, only admins can approve.
- `TransactionPolicy`: checkers validate transactions, admin supervises.
- `ReportPolicy`: only accounting can finalize financial reports.
- `UserPolicy`: only admin manages users.

### Routes
Define routes in `routes/web.php`.
Use route groups with middleware for role-based access

### Chat with Pusher
Implement real-time chat with:
- Message model.
- MessageController for CRUD.
- Pusher integration for broadcasting messages.
- Blade + AlpineJS for frontend updates without reload.

### Development Standards
- Follow PSR-12 coding standards.
- Organize controllers, models, and requests in proper namespaces.
- Ensure test coverage for models, controllers, and requests.
- All components must be reusable and modular.
