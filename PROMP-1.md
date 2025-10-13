You are an expert Laravel developer tasked with building a complete Warehouse Management System called **WareLink** for PT Buana Sentosa Nusantara. The system must be implemented using **Laravel 12**, following the **MVC architecture** strictly. Use **Blade templating** for views, **AlpineJS** for reactive interactions, **TailwindCSS v4** for styling, **MySQL** as the database, **Laravel Breeze** for authentication scaffolding, **Pusher** for real-time chat, and **standalone Blade components** for modularity.

The system must comply with the **Software Requirement Specification (SRS)** defined earlier, including modules for **Supplier, Checker, Admin, and Accounting** with their respective functionalities:
1. Supplier Module
- Login: The supplier can access the system using a username and password.
- Create Sell Order: The supplier fills out a product detail form to create a sales transaction.
- Transaction History: The system displays a list of transactions along with their current statuses.
- Delivery Proof: The supplier can upload a photo as proof of shipment for completed transactions.

1. Checker & Admin Module
- Login: Authentication using username and password.
- Home: Displays an overview of ongoing transactions.
- Transaction List: Shows all active incoming goods transactions.
- Goods Receipt Validation: Records and verifies incoming goods for specific transactions.
- Invoice: Allows users to generate and print physical invoices.
- Data Recapitulation: Filters and displays reports based on a selected time period.
- Member List: Manages system users (add, edit, delete).
- Chatbox: Provides an internal communication feature among users with saved conversation history.

1. Accounting Module
- Receive Monthly Report: The system automatically sends monthly reports to the accounting module.
- Stock Reconciliation: Compares transaction data with warehouse stock records.
- Financial Report Generation: Produces final financial reports based on reconciled data.

All code must follow **PSR-12 coding standards**.

----

## **Models: The Heart of Your Application's Data ❤️**

### **Guiding Philosophy**

In Laravel, Eloquent models are far more than just representations of database tables. They are the core of your application's business logic, encapsulating the rules, relationships, and interactions related to your data. Our goal is to follow the "fat model, thin controller" principle, meaning that any logic directly related to a piece of data—how it's formatted, retrieved, or behaves—should live within its model.

-----

### **Core Properties & Configuration**

Every model should be properly configured for security and convenience.

#### **Mass Assignment Protection (`$fillable`)**

This is a **critical security feature**. The `$fillable` array defines which attributes are allowed to be set through mass assignment (e.g., `Model::create($request->all())`). This prevents malicious users from updating sensitive columns they shouldn't have access to.

```php
// app/Models/Transaction.php
class Transaction extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'supplier_id',
        'product_name',
        'quantity',
        'price_per_unit',
        'status',
        'delivery_proof_path',
    ];
}
```

#### **Attribute Casting (`$casts`)**

Casting automatically converts attribute values to common data types. This is essential for working with dates, booleans, and especially **Enums**. It ensures your data is always in the correct format when you access it.

```php
// app/Models/Transaction.php
use App\Enums\TransactionStatus;

class Transaction extends Model
{
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'validated_at' => 'datetime',
        'status' => TransactionStatus::class, // Automatically cast to and from the Enum
        'price_per_unit' => 'decimal:2',
    ];
}
```

-----

### **Registering Authorization Policies (`#[UsePolicy]`) 🛡️**

To keep your authorization logic organized, you should link a model directly to its corresponding policy class. The `#[UsePolicy]` attribute provides an explicit, discoverable connection, allowing Laravel to automatically find the correct policy for a given model.

This auto-discovery is what powers helpers like `$this->authorize()` in controllers and the `@can` directive in Blade templates, simplifying authorization checks throughout your application.

**1. Create the Policy:**

```bash
php artisan make:policy TransactionPolicy --model=Transaction
```

**2. Register it on the Model:**

```php
<?php

namespace App\Models;

use App\Policies\TransactionPolicy;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[UsePolicy(TransactionPolicy::class)]
class Transaction extends Model
{
    use HasFactory;

    // ... rest of the model properties and methods
}
```

-----

### **Defining Relationships**

Relationships are the most powerful feature of Eloquent. They allow you to easily access related data. Always define the inverse of a relationship and use proper type hints and docblocks for IDE autocompletion.

```php
// app/Models/Transaction.php
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read \App\Models\User $supplier
 * @property-read \App\Models\User|null $validator
 */
class Transaction extends Model
{
    /**
     * Get the supplier that owns the transaction.
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supplier_id');
    }

    /**
     * Get the user who validated the transaction.
     */
    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }
}
```

-----

### **Data Transformation (Accessors & Mutators)**

Use the modern `Attribute` class to define custom logic for retrieving or setting attribute values. This is perfect for creating computed values or formatting data.

```php
// app/Models/Transaction.php
use Illuminate\Database\Eloquent\Casts\Attribute;

class Transaction extends Model
{
    /**
     * Calculate the total price of the transaction.
     * This is a computed, read-only attribute.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function totalPrice(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->quantity * $this->price_per_unit,
        );
    }
}
```

-----

### **Query Scopes (Reusable Logic)**

Scopes allow you to encapsulate reusable query logic, keeping your controllers clean and your code DRY.

#### **Local Scopes (`#[Scope]`)**

Use local scopes for common, chainable query constraints.

```php
// app/Models/Transaction.php
use Illuminate\Database\Eloquent\Builder;

class Transaction extends Model
{
    /**
     * Scope a query to only include transactions pending validation.
     */
    #[Scope]
    public function scopePendingValidation(Builder $query): void
    {
        $query->where('status', TransactionStatus::CONFIRMED)
              ->whereNull('validated_at');
    }
}

// Usage in a controller:
// Transaction::pendingValidation()->get();
```

#### **Global Scopes (`#[ScopedBy]`)**

Use global scopes to apply constraints to **all** queries for a given model. This is perfect for multi-tenancy rules, like ensuring a supplier can only ever see their own data.

**1. Create the Scope Class:**

```bash
php artisan make:scope SupplierScope
```

```php
// app/Models/Scopes/SupplierScope.php
namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;
use App\Enums\UserRole;

class SupplierScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        // If the authenticated user is a supplier, only show their transactions.
        if (Auth::check() && Auth::user()->role === UserRole::SUPPLIER) {
            $builder->where('supplier_id', Auth::id());
        }
    }
}
```

**2. Apply the Scope to the Model:**

```php
// app/Models/Transaction.php
use App\Models\Scopes\SupplierScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;

#[ScopedBy([SupplierScope::class])]
class Transaction extends Model
{
    // ... model contents
}
```

-----

### **Automating Logic with Observers (`#[ObservedBy]`)**

Observers listen for Eloquent model events (like `created`, `updated`, `deleted`) and allow you to automatically handle side effects, such as sending notifications, logging activity, or clearing cache.

**1. Create the Observer Class:**

```bash
php artisan make:observer TransactionObserver --model=Transaction
```

```php
// app/Observers/TransactionObserver.php
namespace App\Observers;

use App\Models\Transaction;
use Illuminate\Support\Facades\Log;

class TransactionObserver
{
    /**
     * Handle the Transaction "created" event.
     */
    public function created(Transaction $transaction): void
    {
        // Log activity whenever a new transaction is created.
        Log::info("New transaction #{$transaction->id} created by supplier #{$transaction->supplier_id}.");
        // You could also dispatch a job or send a notification here.
    }
}
```

**2. Register the Observer on the Model:**

```php
// app/Models/Transaction.php
use App\Observers\TransactionObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

#[ObservedBy([TransactionObserver::class])]
class Transaction extends Model
{
    // ... model contents
}
```

---

## **Controllers: The Application's Traffic Directors**

### **Purpose in the MVC Architecture**

In Laravel's MVC (Model-View-Controller) architecture, controllers act as the central routing point for handling user requests. Their primary job is to orchestrate the flow of data between the **Model** (the data layer) and the **View** (the presentation layer). A well-designed controller is **thin**, meaning it contains minimal business logic and delegates complex tasks to other parts of the application, such as the Service Layer.

-----

### **Controller Types and Usage**

To maintain a clean and organized codebase, choose the appropriate controller type for the task at hand.

#### **Resource Controllers for CRUD**

Use **Resource Controllers** exclusively for managing the lifecycle of a model (Create, Read, Update, Delete). This convention provides a predictable structure for standard CRUD operations.

  * **Generation Command:**
    ```bash
    php artisan make:controller Admin/ProductController --resource --model=Product
    ```
  * **Resulting Methods:** `index`, `create`, `store`, `show`, `edit`, `update`, `destroy`.

#### **Single Action Controllers for Specific Tasks**

When a controller performs only one distinct action, use a **Single Action (Invokable) Controller**. This approach promotes the Single Responsibility Principle and results in smaller, more focused classes.

  * **Generation Command:**
    ```bash
    php artisan make:controller Reports/GenerateMonthlyReportController --invokable
    ```
  * **Example Usage:**
    ```php
    // In a Single Action Controller, the logic resides in the __invoke method.
    public function __invoke(GenerateReportRequest $request)
    {
        // ... logic to generate the report
    }
    ```

-----

### **Core Responsibilities & Workflow**

A controller method should follow a clear, consistent workflow. Its role is orchestration, not implementation.

1.  **Validate Input with Form Requests:** All incoming data must be validated and authorized using a dedicated **Form Request** class. This keeps the controller clean and centralizes validation logic.
2.  **Authorize the Action:** Ensure the authenticated user has permission to perform the requested action. This is typically done by calling the `authorize` method, which leverages your application's Policies.
3.  **Delegate Business Logic to the Service Layer:** The controller should **never** contain complex function for business logic (e.g., processing payments, calculating data, interacting with external APIs). This logic belongs in a dedicated **Service Class**. The controller calls the service and receives the result.
4.  **Return a Response:** Based on the result from the service, the controller returns an appropriate response, such as a **Blade view**, a **redirect**, or a **JSON object**.

#### **Example: Moving Logic to a Service Layer**

**Before (Fat Controller - Bad Practice):**

```php
// In TransactionController.php
public function store(StoreTransactionRequest $request)
{
    Gate::authorize('create', Transaction::class);

    // BAD: Business logic is mixed in the controller
    $transaction = new Transaction($request->validated());
    $transaction->status = TransactionStatus::PENDING;
    $transaction->invoice_number = 'INV-' . time(); // Logic for generating invoice number
    $transaction->save();

    // BAD: Side effects are handled here
    Log::info('New transaction created: ' . $transaction->id);
    Notification::send($transaction->supplier, new NewOrderPlaced($transaction));

    return redirect()->route('transactions.index')->with('success', 'Transaction created!');
}
```

**After (Thin Controller - Good Practice):**

```php
// In TransactionController.php
public function store(StoreTransactionRequest $request, TransactionService $transactionService)
{
    // 1. Authorization is handled by the Form Request or here
    Gate::authorize('create', Transaction::class);

    // 2. Delegate logic to the service layer
    $transaction = $transactionService->createTransaction($request->validated(), $request->user());

    // 3. Return the response
    return redirect()->route('transactions.index')->with('success', 'Transaction created!');
}
```

-----

### **Directory Structure**

Organize controllers into subdirectories within `app/Http/Controllers` based on the **actor (user role)** or **business process** they relate to. This makes the codebase intuitive and easy to navigate.

```
app/Http/Controllers/
├── Auth/
│   ├── LoginController.php
│   └── RegisterController.php
├── Admin/
│   ├── DashboardController.php
│   ├── ReportController.php
│   └── UserController.php         # Resource Controller
├── Supplier/
│   ├── OrderController.php        # Resource Controller
│   └── UploadProofController.php  # Single Action Controller
└── HomeController.php
```

### **Authorization within Controllers**

Enforce authorization directly within your controller methods using helpers that connect to your Policies. This is a critical security step.

  * **Using the `can` Helper (Recommended):** The `can` method, This method simply returns a boolean (true or false).

    ```php
    public function update(UpdateProductRequest $request, Product $product)
    {
        // This will check the 'update' method in the ProductPolicy
        if ($request->user()->cannot('create', Post::class)) {
            abort(403);
        }

        // ... logic to update the product
    }
    ```

  * **Using the `Gate` Facade:** For checks that aren't related to a specific model or for more complex scenarios, the `Gate` facade can be used.

    ```php
    use Illuminate\Support\Facades\Gate;

    public function destroy(Product $product)
    {
        Gate::authorize('delete', $product);

        // ... logic to delete the product
    }
    ```
---

## **Views: Crafting the User Interface**

### **Role in the Application**

The view layer is the face of our application. It's responsible for presenting data and capturing user input. We will use Laravel's **Blade** templating engine to create a highly modular and maintainable user interface, powered by a strict, component-first architecture.

-----

### **UI/UX Theme: "Corporate Intranet Circa 2000"** 🏛️

The entire visual design must evoke the functional, no-frills aesthetic of a corporate web application from the early 2000s. The goal is clarity and utility over modern flair.

  * **Layout**: Use a **fixed-width, centered layout** (`max-w-7xl mx-auto`). The design will feature a distinct sidebar and a main content area, reminiscent of classic desktop applications.
  * **Color Palette**: Stick to a conservative color scheme.
      * **Backgrounds**: Light grays (`bg-gray-100`, `bg-gray-200`).
      * **Primary/Accent**: A standard "system blue" for links, buttons, and headers (`bg-blue-600`, `text-blue-600`).
      * **Borders**: Muted grays (`border-gray-400`).
  * **Typography**: Use standard system fonts. Configure Tailwind to use `font-sans` (Arial, Helvetica) for UI text and `font-serif` (Times New Roman, Georgia) for document-style content if needed.
  * **Borders & Shadows**: Embrace the "box model." Elements should be clearly defined by **solid 1px borders**. Use minimal to **no box shadows**. For button states, a subtle `inset` shadow is preferred over modern drop shadows.
  * **Components**: Buttons and form inputs will have sharp corners (`rounded-none` or `rounded-sm`) and solid background colors.

-----

### **Architectural Foundation: Legacy Breeze Structure**

We will adopt the **file structure and component architecture** from a legacy version of Laravel Breeze as our blueprint. This provides a proven foundation for authentication and application layouts. The following files must be created and styled according to our "Corporate 2000s" theme:

  * **Layouts**:
      * `resources/views/layouts/app.blade.php` (Main authenticated layout with sidebar)
      * `resources/views/layouts/auth.blade.php` (Centered layout for login, register pages)
  * **Core Pages**:
      * `resources/views/dashboard.blade.php`
      * `resources/views/welcome.blade.php`
  * **Authentication Views (`auth/`)**:
      * `login.blade.php`, `register.blade.php`
      * `forgot-password.blade.php`, `reset-password.blade.php`
      * `confirm-password.blade.php`, `verify-email.blade.php`
  * **Settings Views (`settings/`)**:
      * `profile.blade.php`, `password.blade.php`, `appearance.blade.php`

-----

### **Blade Component Best Practices**

Our entire frontend will be composed of **Standalone Blade Components**.

  * **Strict Component Syntax**: All components must be invoked using the tag syntax. **Legacy directives like `@include`, `@section`, and `@yield` are forbidden for UI composition.**
    ```html
    <x-forms.input name="email" type="email" />

    @include('partials.input', ['name' => 'email'])
    ```
  * **Maximize Props & Slots**: Components must be made reusable through `props` for data and `slots` for content. Every component should be self-contained.
    ```html
    <x-card title="User Dashboard" :collapsible="true">
        <p>This content goes into the default slot.</p>

        <x-slot name="footer">
            <x-button.primary>View Report</x-button.primary>
        </x-slot>
    </x-card>
    ```
  * **Component Examples**: Create a robust library of reusable components, including:
      * **Forms**: `input`, `textarea`, `select`, `checkbox`, `label`, `error-message`.
      * **UI Elements**: `button`, `modal`, `card`, `alert`, `dropdown`.
      * **Complex Components**: `data-table` (with AlpineJS for sorting/filtering), `chat-window`.

-----

### **Styling with TailwindCSS v4: CSS-First Configuration**

All styling will be handled by **TailwindCSS v4**, adhering to its modern, CSS-first approach. All configuration, including theme customizations, content sources, and custom layers, will be managed directly within the main `resources/css/app.css` file. The `tailwind.config.js` file is no longer the primary method for this.

Your `resources/css/app.css` file should be structured as follows to implement the **"Corporate Intranet Circa 2000"** theme:

```css
/* resources/css/app.css */

/*
 * 1. Import the core TailwindCSS functionalities.
 */
@import 'tailwindcss';

/*
 * 2. Define the files Tailwind should scan for utility classes.
 * This ensures any classes used in your Blade views or JS are generated.
 */
@source '../../vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php';
@source '../../storage/framework/views/*.php';
@source '../**/*.blade.php';
@source '../**/*.js';

/*
 * 3. Configure and extend the theme to match the "2000s" aesthetic.
 * This replaces the old `theme.extend` object in tailwind.config.js.
 */
@theme {
  /* Define custom fonts, overriding the default. */
  --font-sans: Arial, Helvetica, sans-serif;

  /* Extend the theme with specific values for our retro look. */
  borderRadius: {
    none: '0',
    sm: '2px' /* A very slight rounding */
  };
  boxShadow: {
    'inset-2k': 'inset 1px 1px 3px rgb(0 0 0 / 0.4)' /* A classic, harsh inset shadow */
  };
}

/*
 * 4. Define the base color palette using CSS Custom Properties.
 * This makes the color scheme easy to manage.
 */
@layer base {
  :root {
    --background: 240 5% 96%;         /* Light Gray */
    --foreground: 240 10% 4%;        /* Near Black */

    --card-background: 240 5% 100%;    /* White */
    --card-foreground: 240 10% 4%;

    --primary: 220 70% 50%;          /* Corporate Blue */
    --primary-foreground: 210 40% 98%; /* White */

    --border: 240 5% 80%;            /* Muted Gray Border */
    --input: 240 5% 80%;

    --ring: 220 70% 50%;             /* Blue for focus rings */
  }

  /* A dark mode is not required for this theme, so we keep it simple. */
}

```
  * **Interactivity with AlpineJS**: All client-side interactivity (modal toggles, dropdown menus, live search, form validation feedback) will be handled by AlpineJS directly within the Blade components. This keeps our components self-contained and avoids the need for separate, large JavaScript files.

-----

### **Conditional Rendering for a Secure UI**

Use Blade's `@can` directive to conditionally render UI elements based on user permissions defined in your Policies. This ensures that users only see the actions they are authorized to perform.

```html
<div class="actions">
    <x-button.secondary href="{{ route('transactions.show', $transaction) }}">
        View
    </x-button.secondary>

    @can('update', $transaction)
        <x-button.primary href="{{ route('transactions.edit', $transaction) }}">
            Edit
        </x-button.primary>
    @endcan
</div>
```
-----

## **Migrations: Building the Database Blueprint**

### **Guiding Philosophy**

Migrations are the definitive source of truth for your database schema. They act as version control, allowing your team to define, modify, and share the application's database structure in a consistent and reproducible way. Our goal is to create migrations that are not only functional but also **readable, performant, and maintainable**.

-----

### **Core Principles & Best Practices**

Every migration you create should adhere to these fundamental rules:

  * **Immutable History**: Once a migration has been committed and run by others, **never edit it directly**. Instead, create a *new* migration to alter the table. This prevents synchronization issues.
  * **Clear Naming Conventions**:
      * **Tables**: Plural and `snake_case` (e.g., `product_transactions`).
      * **Columns**: Singular and `snake_case` (e.g., `product_name`, `validated_at`).
      * **Pivot Tables**: Singular model names in alphabetical order (e.g., `product_user`).
  * **Use `timestamps()` and `softDeletes()`**:
      * **`$table->timestamps();`**: Automatically adds `created_at` and `updated_at` columns. This is essential for auditing and tracking record changes.
      * **`$table->softDeletes();`**: Adds a `deleted_at` column. This allows records to be "hidden" without being permanently destroyed, which is crucial for data integrity and recovery.
  * **Define Explicit Foreign Keys**: Always define foreign key constraints. This enforces relational integrity at the database level, preventing orphaned records. Use the modern `foreignIdFor()` syntax for clarity.
  * **Index for Performance**: Add indexes to all foreign key columns and any other columns that will be frequently used in `WHERE` clauses (e.g., `status`, `email`). This dramatically speeds up read queries.

-----

### **Implementation Examples**

#### **1. Modifying the Legacy `users` Table from Breeze**

Instead of editing the original `create_users_table` migration from Laravel Breeze, we create a new migration to add our required `role` column.

**Generate the migration:**

```bash
php artisan make:migration add_role_to_users_table --table=users
```

**Edit the generated migration file:**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\UserRole; // Assuming you have a UserRole Enum

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Add the 'role' column after the 'email' column for logical grouping.
            // Use a string type to store the enum's value.
            // Set a default value to ensure existing users are valid.
            $table->enum('role', ['Supplier', 'Admin', 'Accounting', 'Checker'])
                  ->after('email')
                  ->default('Supplier');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // The 'down' method should perfectly reverse the 'up' method.
            $table->dropColumn('role');
        });
    }
};
```

-----

#### **2. Creating the `transactions` Table**

This example demonstrates a complete, best-practice migration for a core table, showcasing relationships, indexing, and proper data types.

**Generate the migration:**

```bash
php artisan make:migration create_transactions_table
```

**Edit the generated migration file:**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User; // Import the User model for foreign keys

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();

            // BEST PRACTICE: Use foreignIdFor() for clear, convention-based relationships.
            // 'constrained()' automatically uses the 'users' table and 'id' column.
            // 'cascadeOnDelete()' ensures that if a supplier user is deleted, all their transactions are also deleted.
            $table->foreignIdFor(User::class, 'supplier_id')->constrained('users')->cascadeOnDelete();

            $table->string('product_name');
            $table->unsignedInteger('quantity');
            $table->decimal('price_per_unit', 8, 2);

            // Use a string column for enums and add an index for faster lookups based on status.
            $table->string('status')->index();

            $table->string('delivery_proof_path')->nullable();

            // Another foreign key for the user who validated the transaction.
            // This relationship is nullable and does not cascade on delete.
            $table->foreignIdFor(User::class, 'validated_by')->nullable()->constrained('users');
            $table->timestamp('validated_at')->nullable();

            $table->timestamps(); // Adds created_at and updated_at
            $table->softDeletes(); // Adds deleted_at for soft deletes
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
```
---

## Middleware
### Definition
Middleware filters HTTP requests entering the application.

### Requirements
- Implement **Authentication Middleware** to restrict access based on roles (Supplier, Checker, Admin, Accounting).
- Create **Custom Middleware** to log activities (e.g., stock validation, failed login attempts).
- Ensure role-based authorization is consistently applied.

---

## Enum
### Definition
Enums in Laravel (PHP 8.1+) define a fixed set of constant values.

### Requirements
- Use Enums for **transaction status** (Pending, Confirmed, Rejected, Completed).
- Use Enums for **user roles** (Supplier, Checker, Admin, Accounting).
- Ensure Enums are strongly typed and integrated with validation rules.

-----

## **Form Requests**

### **Guiding Philosophy**

Form Requests are dedicated classes that encapsulate all the logic for validating and authorizing a specific HTTP request. Their primary purpose is to **clean up controllers** by moving this complex and often messy logic into a single, reusable class. A Form Request acts as a gatekeeper, ensuring that no unauthorized or invalid data ever reaches your application's core business logic.

-----

### **Anatomy of a Form Request**

When you generate a Form Request, it comes with two essential methods that serve as its foundation.

#### **1. The `authorize()` Method**

This method is your first line of defense. It runs **before** the validation rules are checked and determines if the currently authenticated user has permission to make the request at all. If this method returns `false`, a `403 Forbidden` HTTP response is automatically sent, and your controller method is never executed.

#### **2. The `rules()` Method**

This method returns an array of validation rules that should be applied to the incoming request data. This is where you define the "shape" of your expected data—what fields are required, what format they should be in, and any other constraints.

-----

### **Implementation: A Complete Example**

Let's create a Form Request for storing a new transaction in the WareLink system.

#### **Step 1: Generate the Class**

Use the following Artisan command to create the file at `app/Http/Requests/StoreTransactionRequest.php`:

```bash
php artisan make:request StoreTransactionRequest
```

#### **Step 2: Build the Request Class**

Now, let's implement all the best practices in the generated file.

```php
<?php

namespace App\Http\Requests;

use App\Enums\TransactionStatus;
use App\Models\Transaction;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use Propaganistas\LaravelPhone\Rules\PhoneNumber; // Example third-party library

class StoreTransactionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * This method runs BEFORE validation.
     */
    public function authorize(): bool
    {
        // Use the policy to check if the authenticated user can create a transaction.
        return $this->user()->can('create', Transaction::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Standard validation rules
            'product_name' => ['required', 'string', 'max:255'],
            'quantity' => ['required', 'integer', 'min:1'],
            'price_per_unit' => ['required', 'numeric', 'min:0'],

            // Database-aware validation rule
            'supplier_id' => ['required', 'exists:users,id'],

            // Enum-based validation rule
            'status' => ['required', new Enum(TransactionStatus::class)],

            // Example of a rule from a third-party package
            'supplier_phone' => ['required', new PhoneNumber('ID')], // Validates an Indonesian phone number
        ];
    }

    /**
     * Get custom error messages for validator errors. (Optional but recommended)
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'supplier_id.exists' => 'The selected supplier is not valid.',
            'supplier_phone.phone' => 'Please enter a valid Indonesian phone number.',
        ];
    }

    /**
     * Prepare the data for validation. (Optional)
     * This method runs BEFORE the rules are checked.
     */
    protected function prepareForValidation(): void
    {
        // Example: Sanitize a field before it's validated.
        // This merges a new, cleaned value back into the request.
        if ($this->has('product_name')) {
            $this->merge([
                'product_name' => strip_tags($this->product_name),
            ]);
        }
    }
}
```

-----

### **Usage in the Controller**

To use the Form Request, simply type-hint it in your controller method's signature. Laravel's service container will automatically resolve it, triggering the authorization and validation checks before your code runs.

```php
<?php

namespace App\Http\Controllers\Supplier;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTransactionRequest; // 1. Import the request
use App\Services\TransactionService;

class OrderController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTransactionRequest $request, TransactionService $service) // 2. Type-hint it
    {
        // If the code reaches this point, it means:
        // - The user IS authorized.
        // - The data IS valid.

        // 3. Access the validated and sanitized data.
        $validatedData = $request->validated();

        $transaction = $service->createTransaction($validatedData);

        return redirect()->route('supplier.orders.index')->with('success', 'Order created successfully!');
    }
}
```

-----

## **Custom Rules**

### **Guiding Philosophy**

While Laravel offers a rich set of built-in validation rules, every application has unique requirements that demand custom logic. Custom Validation Rules allow us to encapsulate this application-specific logic into simple, reusable classes. The goal is to create validation that is **self-contained, highly readable, and easily testable**, ensuring our controllers and Form Requests remain clean and focused.

-----

### **Implementation: Creating a Rule Object**

For any validation logic that is reusable or moderately complex, a dedicated **Rule Object** is the best practice.

#### **Step 1: Generate the Rule**

Use the following Artisan command to create a new rule class in the `app/Rules` directory.

```bash
# Example for a rule that validates a product code's uniqueness
php artisan make:rule UniqueProductCode
```

-----

#### **Step 2: Define the Logic**

The core of the rule is the `validate()` method. It receives the attribute name, its value, and a `$fail` closure to call if validation does not pass.

```php
// app/Rules/UniqueProductCode.php

class UniqueProductCode implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // ... Logic to check if the product code is unique ...

        if ($isNotUnique) {
            // The message passed to $fail() will be the validation error.
            $fail('The :attribute is already in use.');
        }
    }
}
```

-----

### **Practical Examples for WareLink**

Here are implementations for the specific custom rules your application requires.

#### **Example 1: Dynamic Unique Product Code**

This rule needs to check for a unique product code but also needs to ignore the current product's ID during an update operation. We can achieve this by passing data to the rule's constructor.

**The Rule Class:**

```php
<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

class UniqueProductCode implements ValidationRule
{
    /**
     * The ID of the product to ignore during the check.
     */
    protected ?int $ignoreId;

    /**
     * Create a new rule instance.
     */
    public function __construct(?int $ignoreId = null)
    {
        $this->ignoreId = $ignoreId;
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $query = DB::table('products')->where('product_code', $value);

        if ($this->ignoreId) {
            $query->where('id', '!=', $this->ignoreId);
        }

        if ($query->exists()) {
            $fail('The :attribute has already been taken.');
        }
    }
}
```

-----

#### **Example 2: Validating a Shipping Proof File**

This rule ensures that a supplier's uploaded proof of shipment is a valid image file.

**The Rule Class:**

```php
<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

class ValidShippingProof implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$value instanceof UploadedFile) {
            $fail('A valid file must be uploaded.');
            return;
        }

        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];

        if (!in_array($value->getMimeType(), $allowedMimeTypes) || !in_array($value->getClientOriginalExtension(), $allowedExtensions)) {
            $fail('The :attribute must be an image (jpg, png, gif).');
        }
    }
}
```

-----

### **Usage in a Form Request**

The final step is to use your new Rule Objects within a Form Request. This keeps your validation definitions clean and declarative.

```php
<?php

namespace App\Http\Requests;

use App\Rules\UniqueProductCode;
use App\Rules\ValidShippingProof;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->product);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],

            // Instantiating the custom rule and passing the product ID to ignore.
            'product_code' => [
                'required',
                'string',
                new UniqueProductCode($this->product->id)
            ],

            // Using the file validation rule.
            'shipping_proof' => ['nullable', new ValidShippingProof],
        ];
    }
}
```

-----

## **Authorization with Policy**

### **Definition**

Policies are classes that organize authorization logic around a particular model or resource. They determine what actions a user can perform on a given resource.

### **Requirements**

  - Implement **Policies** for models such as `Product`, `Transaction`, `Invoice`, `Report`, etc.
  - Ensure only authorized users can perform `create`, `view`, `update`, and `delete` actions.
  - Reflect the role-based access control (RBAC) rules within each policy method.

### **Policy Registration**

Policies are linked directly to their corresponding models using the `#[UsePolicy]` attribute within the model class. This provides a clear and discoverable connection between a model and its authorization rules.

**Example: `Product` Model**

```php
<?php

namespace App\Models;

use App\Policies\ProductPolicy;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[UsePolicy(ProductPolicy::class)]
class Product extends Model
{
    use HasFactory;

    // ... model contents
}
```

### **Implementing Authorization Checks**

To maximize security and maintain clean code, authorization checks should be enforced at multiple layers of the application.

  - **In Controllers**: Use the `User` model or the `Gate` facade to authorize actions before executing controller logic. This is the most common place to perform authorization.

    ```php
    // Using the User model from the request
    if ($request->user()->can('update', $product)) {
        // Logic to update the product...
    }

    // Using the Gate facade (will automatically throw an AuthorizationException)
    Gate::authorize('delete', $product);
    ```

  - **In Route Definitions**: Apply the `can` middleware directly to your routes in `routes/web.php` to prevent unauthorized access at the earliest stage.

    ```php
    // In routes/web.php
    Route::middleware(['auth'])->group(function () {
        Route::put('/products/{product}', [ProductController::class, 'update'])
             ->can('update', 'product');

        Route::delete('/products/{product}', [ProductController::class, 'destroy'])
             ->can('delete', 'product');
    });
    ```

  - **In Blade Templates**: Use the `@can` directive to conditionally render UI elements, ensuring users only see the actions they are permitted to perform.

    ```html
    @can('update', $product)
        <a href="{{ route('products.edit', $product) }}" class="edit-button">Edit Product</a>
    @endcan

    @can('delete', $product)
        <form action="{{ route('products.destroy', $product) }}" method="POST">
            @csrf
            @method('DELETE')
            <button type="submit" class="delete-button">Delete</button>
        </form>
    @endcan
    ```
-----

## **Routing: The Application's Entry Points**

### **Guiding Philosophy**

The `routes/web.php` file is the map to our application. It defines every user-facing URL and connects it to a specific controller action. Our routing philosophy is to create a structure that is **secure, readable, and easily maintainable**. We will achieve this by logically grouping routes and adhering strictly to Laravel's best practices.

-----

### **Core Principles & Best Practices**

  * **Web-Only**: All routes for this application will be defined in `routes/web.php` and will be part of the `web` middleware group, providing features like session state and CSRF protection. We will not use `api.php`.
  * **Route Naming is Mandatory**: Every route **must** be given a unique name using the `->name()` method. This is critical for generating URLs with the `route()` helper in our controllers and views, preventing hardcoded URLs and making future changes easier.
  * **Logical Grouping**: Routes must be grouped by their common attributes, primarily middleware. We will use nested groups to apply authentication, verification, and role-based access control in a clean, DRY (Don't Repeat Yourself) manner.
  * **Prefixes for Clarity**: Use `->prefix()` to prepend a URI segment to all routes within a group (e.g., `/admin`). This keeps URLs organized and intuitive.
  * **Controller Syntax**: Always use the full class name syntax to reference controllers: `[UserController::class, 'index']`. This provides better support for IDEs and code navigation.

-----

### **Practical Implementation: `routes/web.php` Structure**

Here is a complete, best-practice example of how your `routes/web.php` file should be structured to incorporate Laravel Breeze's routes and your custom role-based sections.

```php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Supplier\OrderController;
use App\Http\Controllers\Checker\TransactionValidationController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group.
|
*/

// 1. Publicly Accessible Routes
Route::get('/', function () {
    return view('welcome');
});

// 2. Main Authenticated Routes (Dashboard)
// Any user who is logged in and verified can access this.
Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// 3. Role-Specific Routes
// All routes within this group require a user to be logged in.
Route::middleware('auth')->group(function () {

    // == ADMIN ROUTES ==
    // Accessible only by users with the 'admin' role.
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', AdminDashboardController::class)->name('dashboard');
        Route::resource('users', UserController::class);
        // Add other admin routes like reports here...
    });

    // == SUPPLIER ROUTES ==
    // Accessible only by users with the 'supplier' role.
    Route::middleware('role:supplier')->prefix('supplier')->name('supplier.')->group(function () {
        Route::resource('orders', OrderController::class);
        // Add other supplier routes here...
    });

    // == CHECKER ROUTES ==
    // Accessible only by users with the 'checker' role.
    Route::middleware('role:checker')->prefix('checker')->name('checker.')->group(function () {
        // Example of a single action controller route
        Route::post('/transactions/{transaction}/validate', TransactionValidationController::class)
             ->name('transactions.validate');
        // Add other checker routes here...
    });

    // == GENERAL AUTHENTICATED ROUTES ==
    // Routes accessible by any logged-in user.
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    Route::post('/chat/messages', [ChatController::class, 'store'])->name('chat.store');

});

// 4. Laravel Breeze Authentication Routes
// This file contains all the legacy routes for login, registration, password reset, etc.
require __DIR__.'/auth.php';

```
---

## Real-Time Chat
- Use **Laravel Echo** with **Pusher** for broadcasting chat messages.
- Implement **ChatMessage model** and **ChatController**.
- Messages must be stored in the database and broadcast in real-time.
- Chat UI must be built with **Blade Components**, **AlpineJS**, and **TailwindCSS**.

---

## Authentication
- Implement **Laravel Breeze** for authentication scaffolding.
- Roles must be assigned during user creation.
- Ensure only authorized users can access their module functionality.

---

## Coding Standards
- Follow **PSR-12** coding standards:
  - Class names must be in StudlyCase.
  - Methods in camelCase.
  - Indentation with 4 spaces.
  - Proper use of type hints and return types.
  - Maximum line length of 120 characters.
- Variable names must be in snack_case

---

## UI/UX Considerations
- The UI must be **responsive for all media query, user-friendly and intuitive**.
- Dashboard overview for each role (Supplier sees orders, Checker sees transactions, Admin sees reports, Accounting sees financial summaries).
- Consistent navigation with **sidebar and topbar**.
- Tailwind-based alerts and modals for confirmation actions.
- Component style using **old company in year 2000**.

---
