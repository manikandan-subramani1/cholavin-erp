## Global Laravel Development Standards

Apply the following standards to every module, page, form, report, and CRUD operation in the project.

### 1. PDF Download Using mPDF

Every applicable page must include an **mPDF download option**.

PDF reports must support:

* Current search value
* Selected filters
* Sorting order
* Date range
* Party, supplier, customer, product, shop, godown, or status filter
* All visible table headings
* Company details
* Report title
* Generated date and time
* Page number
* Total records
* Relevant total calculations

The PDF output must match the currently filtered DataTable results.

Create reusable PDF methods, layouts, headers, footers, CSS files, and helper functions instead of writing duplicate mPDF code for every module.

Example reusable structure:

```text
app/
├── Helpers/
│   └── PdfHelper.php
├── Services/
│   └── PdfService.php
├── Http/
│   └── Controllers/
│       └── Reports/
├── resources/
│   └── views/
│       └── pdf/
│           ├── layouts/
│           ├── partials/
│           └── modules/
```

---

### 2. Yajra DataTables for Every Listing Page

All index and listing pages must use **Yajra server-side DataTables**.

Every DataTable must provide:

* Server-side pagination
* Search
* Column sorting
* Custom filters
* Reset-filter button
* Serial number column
* Action column
* Status display
* Loading indicator
* Empty-data message
* Responsive table support
* Export or PDF download option
* Proper error handling

Do not load all database records directly into Blade.

Do not use:

```php
Model::all();
```

Use server-side queries and return DataTables JSON responses.

---

### 3. Use a Single Index Method

Use the controller `index()` method for both:

* Initial Blade page loading
* AJAX DataTable data loading

Example:

```php
public function index(Request $request)
{
    if ($request->ajax()) {
        $query = Party::query();

        $this->applyFilters($query, $request);

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                return view('parties.partials.actions', compact('row'))->render();
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    return view('parties.index');
}
```

Avoid creating unnecessary separate methods such as:

```text
getData()
listData()
fetchData()
datatableData()
ajaxList()
```

Use separate methods only when they contain reusable query or filter logic.

---

### 4. Laravel `when()` Method for Filters

All optional filters must be applied using Laravel's `when()` method.

Example:

```php
$query = Party::query()
    ->when($request->filled('party_type'), function ($query) use ($request) {
        $query->where('party_type', $request->party_type);
    })
    ->when($request->filled('status'), function ($query) use ($request) {
        $query->where('status', $request->status);
    })
    ->when($request->filled('shop_id'), function ($query) use ($request) {
        $query->where('shop_id', $request->shop_id);
    })
    ->when($request->filled('from_date'), function ($query) use ($request) {
        $query->whereDate('created_at', '>=', $request->from_date);
    })
    ->when($request->filled('to_date'), function ($query) use ($request) {
        $query->whereDate('created_at', '<=', $request->to_date);
    });
```

Avoid repeated `if` conditions when query filters can be handled using `when()`.

---

### 5. AJAX-Only Form Submission

All create, update, delete, status-change, payment, transfer, return, and other POST operations must use AJAX.

Do not perform normal form submissions that reload the full page.

The following operations must use AJAX:

* Create
* Update
* Delete
* Restore
* Permanent delete
* Status update
* Bulk actions
* Payment entry
* Stock transfer
* Purchase entry
* Sales entry
* Return entry
* Password update
* Permission update
* Form validation
* Dependent dropdown loading

Use the correct HTTP methods:

```text
POST   – Create
PUT    – Full update
PATCH  – Partial update
DELETE – Delete
```

---

### 6. DataTable Reload Instead of Page Reload

After successful create, update, delete, or status-change operations, reload only the DataTable.

Use:

```javascript
table.ajax.reload(null, false);
```

Do not use:

```javascript
location.reload();
window.location.reload();
```

Using `false` must preserve the current pagination page.

Example:

```javascript
success: function (response) {
    if (response.status === true) {
        $('#formModal').modal('hide');
        $('#partyForm')[0].reset();

        table.ajax.reload(null, false);

        toastr.success(response.message);
    }
}
```

---

### 7. jQuery Validation for Every Form

Every form must use the **jQuery Validation Plugin** for client-side validation.

Validation must include:

* Required fields
* Minimum and maximum lengths
* Numeric validation
* Decimal validation
* Email validation
* Mobile-number validation
* Date validation
* File-type validation
* File-size validation
* Password confirmation
* Custom business rules
* Conditional field validation
* Remote validation where necessary

Example:

```javascript
$('#partyForm').validate({
    rules: {
        party_name: {
            required: true,
            minlength: 3,
            maxlength: 100
        },
        mobile_number: {
            required: true,
            digits: true,
            minlength: 10,
            maxlength: 10
        },
        email: {
            email: true
        }
    },
    messages: {
        party_name: {
            required: 'Please enter the party name.',
            minlength: 'The party name must contain at least 3 characters.'
        },
        mobile_number: {
            required: 'Please enter the mobile number.',
            digits: 'Please enter digits only.'
        }
    },
    errorElement: 'span',
    errorClass: 'invalid-feedback',
    highlight: function (element) {
        $(element).addClass('is-invalid');
    },
    unhighlight: function (element) {
        $(element).removeClass('is-invalid');
    },
    errorPlacement: function (error, element) {
        error.insertAfter(element);
    },
    submitHandler: function (form) {
        submitFormUsingAjax(form);
    }
});
```

---

### 8. Laravel Form Request Validation

Client-side validation must never replace server-side validation.

Create Laravel Form Request classes for create and update operations.

Example structure:

```text
app/
└── Http/
    └── Requests/
        └── Party/
            ├── StorePartyRequest.php
            └── UpdatePartyRequest.php
```

Example:

```php
class StorePartyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'party_name'    => ['required', 'string', 'min:3', 'max:100'],
            'mobile_number' => ['required', 'digits:10'],
            'email'         => ['nullable', 'email', 'max:150'],
            'status'        => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'party_name.required' => 'Please enter the party name.',
            'mobile_number.digits' => 'The mobile number must contain exactly 10 digits.',
        ];
    }
}
```

Return validation errors in JSON format for AJAX requests.

---

### 9. Standard JSON Response Format

All AJAX controller responses must follow one reusable response structure.

Success response:

```json
{
    "status": true,
    "message": "Party created successfully.",
    "data": {}
}
```

Validation or business-rule error:

```json
{
    "status": false,
    "message": "Validation failed.",
    "errors": {}
}
```

Use a reusable response helper.

Example:

```php
function successResponse(
    string $message,
    mixed $data = [],
    int $statusCode = 200
): JsonResponse {
    return response()->json([
        'status'  => true,
        'message' => $message,
        'data'    => $data,
    ], $statusCode);
}

function errorResponse(
    string $message,
    mixed $errors = [],
    int $statusCode = 422
): JsonResponse {
    return response()->json([
        'status'  => false,
        'message' => $message,
        'errors'  => $errors,
    ], $statusCode);
}
```

---

### 10. Reusable AJAX Form Function

Create one reusable JavaScript function for AJAX form submission.

Example:

```javascript
function submitFormUsingAjax(form) {
    const $form = $(form);
    const submitButton = $form.find('[type="submit"]');
    const originalText = submitButton.html();

    $.ajax({
        url: $form.attr('action'),
        type: $form.attr('method'),
        data: new FormData(form),
        processData: false,
        contentType: false,
        beforeSend: function () {
            submitButton
                .prop('disabled', true)
                .html('<span class="spinner-border spinner-border-sm"></span> Processing...');
        },
        success: function (response) {
            if (response.status) {
                $form[0].reset();
                $form.find('.is-invalid').removeClass('is-invalid');

                $('.modal').modal('hide');

                if ($.fn.DataTable.isDataTable('#dataTable')) {
                    $('#dataTable').DataTable().ajax.reload(null, false);
                }

                toastr.success(response.message);
            }
        },
        error: function (xhr) {
            handleAjaxError(xhr, $form);
        },
        complete: function () {
            submitButton
                .prop('disabled', false)
                .html(originalText);
        }
    });
}
```

---

### 11. Reusable AJAX Error Handler

Create a reusable function to display Laravel validation errors.

```javascript
function handleAjaxError(xhr, form) {
    form.find('.is-invalid').removeClass('is-invalid');
    form.find('.invalid-feedback').remove();

    if (xhr.status === 422) {
        const errors = xhr.responseJSON.errors;

        $.each(errors, function (field, messages) {
            const input = form.find(`[name="${field}"]`);

            input.addClass('is-invalid');

            input.after(
                `<span class="invalid-feedback">${messages[0]}</span>`
            );
        });

        toastr.error(xhr.responseJSON.message || 'Please correct the validation errors.');
        return;
    }

    toastr.error(
        xhr.responseJSON?.message ||
        'Something went wrong. Please try again.'
    );
}
```

---

### 12. Reusable DataTable Configuration

Create a common DataTable helper or base configuration.

```javascript
function initializeDataTable(options) {
    return $(options.selector).DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        searching: true,
        ordering: true,
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
        ajax: {
            url: options.url,
            data: function (data) {
                if (typeof options.filters === 'function') {
                    Object.assign(data, options.filters());
                }
            },
            error: function () {
                toastr.error('Unable to load table data.');
            }
        },
        columns: options.columns,
        order: options.order || [[0, 'desc']],
        language: {
            processing: 'Loading records...',
            emptyTable: 'No records found.',
            zeroRecords: 'No matching records found.'
        }
    });
}
```

Example usage:

```javascript
const table = initializeDataTable({
    selector: '#partyTable',
    url: partyIndexUrl,
    filters: function () {
        return {
            party_type: $('#party_type_filter').val(),
            status: $('#status_filter').val(),
            from_date: $('#from_date').val(),
            to_date: $('#to_date').val()
        };
    },
    columns: [
        { data: 'DT_RowIndex', orderable: false, searchable: false },
        { data: 'party_name', name: 'party_name' },
        { data: 'mobile_number', name: 'mobile_number' },
        { data: 'status', name: 'status' },
        { data: 'action', orderable: false, searchable: false }
    ]
});
```

---

### 13. Dynamic Filtering

Filters must reload the DataTable through AJAX.

```javascript
$('#party_type_filter, #status_filter, #shop_filter').on('change', function () {
    table.ajax.reload();
});

$('#from_date, #to_date').on('change', function () {
    table.ajax.reload();
});
```

Reset filters:

```javascript
$('#resetFilters').on('click', function () {
    $('#filterForm')[0].reset();
    table.ajax.reload();
});
```

Do not reload the page when filters change.

---

### 14. Reusable Query Filters

Create reusable query scopes, filters, traits, or service classes.

Example model scopes:

```php
public function scopeActive($query)
{
    return $query->where('status', true);
}

public function scopeDateRange($query, ?string $fromDate, ?string $toDate)
{
    return $query
        ->when($fromDate, function ($query) use ($fromDate) {
            $query->whereDate('created_at', '>=', $fromDate);
        })
        ->when($toDate, function ($query) use ($toDate) {
            $query->whereDate('created_at', '<=', $toDate);
        });
}
```

Example usage:

```php
$query = Party::query()
    ->dateRange($request->from_date, $request->to_date)
    ->when($request->filled('status'), function ($query) use ($request) {
        $query->where('status', $request->status);
    });
```

---

### 15. Helper Files and Reusable Code

Create reusable helper files for commonly repeated logic.

Suggested structure:

```text
app/
├── Helpers/
│   ├── ResponseHelper.php
│   ├── DateHelper.php
│   ├── NumberHelper.php
│   ├── PermissionHelper.php
│   ├── PdfHelper.php
│   ├── FileUploadHelper.php
│   └── GeneralHelper.php
├── Services/
│   ├── PdfService.php
│   ├── DataTableService.php
│   ├── FileUploadService.php
│   └── ReportService.php
├── Traits/
│   ├── HasStatus.php
│   ├── HasShopScope.php
│   └── HasDateFilters.php
└── Support/
    └── QueryFilters/
```

Reusable logic should include:

* JSON responses
* Date formatting
* Currency formatting
* Indian number formatting
* mPDF generation
* File uploads
* Image deletion
* Permission checks
* Shop and godown access
* Status badges
* Action buttons
* DataTable filters
* Date-range filters
* Serial numbers
* Common dropdown values

Do not duplicate the same logic across multiple controllers.

---

### 16. Thin Controllers

Controllers must remain small and readable.

Controllers should mainly:

* Receive the request
* Authorize the action
* Call a service
* Return a JSON response or Blade view

Move complex logic into:

* Services
* Actions
* Form Requests
* Query scopes
* Traits
* Helper classes
* Repository classes, where necessary

Avoid writing large business calculations directly inside controller methods.

---

### 17. Database Transactions

Use database transactions for operations involving multiple tables.

Example:

```php
DB::transaction(function () use ($request) {
    $sale = Sale::create([
        // Sale details
    ]);

    foreach ($request->products as $product) {
        $sale->products()->create($product);

        Stock::where('product_id', $product['product_id'])
            ->decrement('quantity', $product['quantity']);
    }
});
```

Transactions must be used for:

* Sales
* Purchases
* Returns
* Stock transfers
* Payments
* Invoice creation
* Ledger entries
* Multi-table updates

---

### 18. Delete Confirmation

All delete operations must use a confirmation modal or alert.

Example:

```javascript
$(document).on('click', '.delete-record', function () {
    const url = $(this).data('url');

    Swal.fire({
        title: 'Are you sure?',
        text: 'This record will be deleted.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it'
    }).then(function (result) {
        if (result.isConfirmed) {
            deleteRecord(url);
        }
    });
});
```

After successful deletion:

```javascript
table.ajax.reload(null, false);
```

---

### 19. Modal-Based CRUD

Where appropriate, create and update forms should open inside Bootstrap modals.

The flow should be:

```text
Click Add
→ Open modal
→ Load/reset form
→ Validate with jQuery
→ Submit using AJAX
→ Return JSON response
→ Close modal
→ Reload DataTable only
→ Display Toastr success message
```

Edit flow:

```text
Click Edit
→ Fetch record using AJAX
→ Populate the modal form
→ Validate
→ Submit update using AJAX
→ Reload DataTable without changing the current page
```

---

### 20. Common Blade Components

Create reusable Blade components or partials for:

* Page header
* Breadcrumb
* Filter section
* DataTable card
* Form input
* Select input
* Textarea
* Validation message
* Status badge
* Action buttons
* Delete confirmation
* Modal structure
* Empty state
* PDF button
* Permission-based buttons

Example:

```text
resources/views/components/
├── page-header.blade.php
├── filter-card.blade.php
├── data-table.blade.php
├── form/
│   ├── input.blade.php
│   ├── select.blade.php
│   └── textarea.blade.php
├── status-badge.blade.php
└── action-buttons.blade.php
```

---

### 21. Performance Requirements

To reduce page weight and improve performance:

* Use server-side DataTables
* Use AJAX for CRUD
* Avoid full-page reloads
* Select only required database columns
* Use eager loading to avoid N+1 queries
* Add indexes to frequently searched columns
* Paginate large data sets
* Load modal data only when needed
* Do not embed large JSON data inside Blade
* Minify production CSS and JavaScript
* Use cache for reusable master data
* Compress uploaded images
* Use queue jobs for heavy PDF and report operations when appropriate
* Avoid duplicate AJAX requests
* Debounce search inputs
* Use reusable JavaScript modules

Example:

```php
$query = Party::query()
    ->select([
        'id',
        'party_name',
        'mobile_number',
        'party_type',
        'status',
        'created_at',
    ])
    ->with([
        'group:id,name',
        'shop:id,name',
    ]);
```

---

### 22. Security Requirements

Every AJAX action must include:

* CSRF protection
* Authentication
* Authorization
* Role and permission checks
* Shop and godown access validation
* Server-side validation
* Safe file-upload validation
* Database transactions where needed
* Proper exception handling

AJAX setup:

```javascript
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});
```

Never trust IDs, totals, prices, shop IDs, godown IDs, permission values, or calculated amounts received directly from the browser.

Recalculate sensitive totals on the server.

---

### 23. Standard Page Structure

Every listing page should follow this structure:

```text
Page Heading
Breadcrumb
Action Buttons
    - Add New
    - Download PDF
    - Export Excel, where required

Filter Section
    - Searchable filter fields
    - Date range
    - Status
    - Shop
    - Godown
    - Module-specific filters
    - Reset button

DataTable
    - Serial number
    - Required headings
    - Sorting
    - Pagination
    - Search
    - Action column

Create/Edit Modal
    - jQuery validation
    - AJAX submission

Delete Confirmation
    - AJAX delete
    - DataTable reload
```

---

### 24. Required DataTable Headings

Every table must clearly define all headings.

Common headings include:

```text
S.No
Name
Code
Category
Group
Mobile Number
Email
Shop
Godown
Quantity
Rate
Amount
Balance
Status
Created Date
Updated Date
Action
```

Only headings relevant to the module should be displayed.

The database query, DataTable columns, Blade headings, Excel export, and mPDF headings must remain consistent.

---

### 25. Final Mandatory Rules

The following rules must be followed throughout the project:

1. Every listing page must use Yajra server-side DataTables.
2. Every listing must support pagination, search, sorting, and filtering.
3. Every applicable page must support mPDF download.
4. PDF reports must respect current filters and sorting.
5. Every form must use jQuery Validation.
6. Every form must also use Laravel server-side validation.
7. Every create, update, delete, and status operation must use AJAX.
8. Do not reload the entire page after an operation.
9. Reload only the DataTable using `ajax.reload(null, false)`.
10. Use the controller `index()` method for Blade and DataTable AJAX responses.
11. Use Laravel `when()` for optional query filters.
12. Use helper files, services, scopes, traits, and reusable components.
13. Avoid duplicate controller, JavaScript, PDF, filter, and validation code.
14. Use standard JSON responses.
15. Use database transactions for multi-table operations.
16. Apply authorization and shop/godown access checks to every request.
17. Optimize database queries and reduce page weight.
18. Keep controllers thin and move business logic into reusable classes.
19. Keep all DataTable, PDF, Excel, and page headings consistent.
20. Maintain clean, modular, secure, and reusable Laravel code.

This can be used as a global instruction prompt for Codex or another development assistant.
---

# Cholavin ERP / Vyapar Clone — Product Architecture and UI Standards

## 26. Product Identity and UI Redesign

### 26.1 Branding Source

Use the following project logo throughout the ERP:

```text
C:\xampp\htdocs\cholavin-erp\public\frontend\assets\img\logo\logo-hm62.png
```

Laravel asset usage:

```blade
<img
    src="{{ asset('frontend/assets/img/logo/logo-hm62.png') }}"
    alt="Cholavin ERP"
    class="app-logo"
>
```

Use this logo in:

- Login and forgot-password pages
- Main sidebar
- Collapsed sidebar
- Header
- Dashboard welcome section
- Invoice and mPDF header
- Browser favicon, after creating a favicon-compatible version
- Loading screen
- Empty-state illustrations where appropriate

Do not stretch, distort, crop, or apply unrelated colors to the logo.

The UI color palette must be derived from the logo and the existing frontend theme. Use one primary brand color, one darker brand shade, one soft background tint, neutral grays, and semantic status colors. Avoid using too many unrelated colors.

Create CSS variables in one theme file:

```css
:root {
    --brand-primary: #PRIMARY_FROM_LOGO;
    --brand-primary-dark: #DARKER_BRAND_SHADE;
    --brand-primary-soft: #SOFT_BRAND_BACKGROUND;

    --surface: #ffffff;
    --surface-muted: #f6f8fb;
    --border-color: #e5e9f0;

    --text-primary: #1f2937;
    --text-secondary: #667085;
    --text-muted: #98a2b3;

    --success: #198754;
    --warning: #f59e0b;
    --danger: #dc3545;
    --info: #0dcaf0;

    --sidebar-width: 260px;
    --header-height: 68px;
    --card-radius: 12px;
    --control-radius: 8px;
}
```

The exact logo colors must be sampled from `logo-hm62.png` during implementation rather than guessed.

---

## 26.2 Corporate UI Direction

The complete application must be redesigned as a clean, modern, corporate ERP interface.

The design should be:

- Professional
- Easy to understand
- Suitable for billing and inventory operations
- Optimized for non-technical users
- Consistent across every module
- Responsive for desktop, tablet, and mobile
- Fast and low in page weight
- Focused on business actions rather than decoration

Avoid:

- Excessive gradients
- Too many card colors
- Large decorative banners
- Unnecessary animations
- Crowded navigation
- Tiny buttons
- Inconsistent icon styles
- Duplicate page headings
- Heavy shadows
- Full-page reloads

Use:

- Clear section hierarchy
- Consistent spacing
- Strong headings
- Short labels
- Meaningful icons
- Compact filters
- Large primary action buttons
- Visible current shop and godown context
- Proper empty states
- Responsive DataTables
- Context-sensitive quick actions

---

## 26.3 Main Layout Structure

The authenticated application layout must contain:

```text
Main Layout
├── Sidebar
│   ├── Logo
│   ├── Dashboard
│   ├── Quick Create
│   ├── Masters
│   ├── Parties
│   ├── Sales
│   ├── Purchases
│   ├── Inventory
│   ├── Payments
│   ├── Expenses
│   ├── Delivery
│   ├── Reports
│   ├── Users and Access
│   └── Settings
├── Header
│   ├── Sidebar Toggle
│   ├── Global Search
│   ├── Active Shop Dropdown
│   ├── Active Godown Dropdown
│   ├── Financial Year
│   ├── Notifications
│   └── User Menu
├── Content Area
│   ├── Page Header
│   ├── Breadcrumb
│   ├── Quick Actions
│   ├── Alerts and Dependency Warnings
│   ├── Filters
│   └── Main Content
└── Footer
```

---

## 26.4 Sidebar Requirements

The sidebar must:

- Display the Cholavin logo
- Support expanded and collapsed modes
- Display only permitted modules
- Display only permitted submodules
- Highlight the active route
- Use one consistent icon library
- Support grouped menus
- Remember collapse state
- Work properly on mobile
- Hide unauthorized links using Blade authorization

Example:

```blade
@can('sales.view')
    <li class="nav-item">
        <a href="{{ route('sales.index') }}"
           class="nav-link {{ request()->routeIs('sales.*') ? 'active' : '' }}">
            <i class="ri-shopping-cart-line"></i>
            <span>Sales</span>
        </a>
    </li>
@endcan
```

Do not rely only on hiding sidebar links. Routes and controllers must also enforce authorization.

---

## 26.5 Header Requirements

The header must display the current operating context.

Required controls:

- Active shop selector
- Active godown selector
- Financial year selector
- Notification panel
- Current user and role
- Profile, password change, and logout
- Global search, where practical

The selected shop and godown must always be clearly visible.

Example:

```text
Shop: Cholavin Main Branch
Godown: Central Warehouse
FY: 2026–2027
```

---

## 26.6 Authentication Page Redesign

The authentication page must use the Cholavin logo and brand palette.

### Desktop Layout

Use a two-column layout:

```text
Left Side
- Brand visual
- Cholavin ERP title
- Short product message
- Key benefits
- Soft branded background

Right Side
- Logo
- Welcome heading
- Login form
- Remember me
- Forgot password
- Support information
```

### Login Form Fields

- Username, email, or mobile number
- Password
- Show/hide password
- Remember me
- CAPTCHA, optional
- Login button

### Authentication UI Requirements

- Corporate appearance
- Minimal form fields
- Clear validation
- AJAX submission, where used
- Loading state on login button
- Keyboard-friendly form
- Mobile responsive
- No unnecessary carousel or large image slider
- Error messages shown near the relevant fields
- Account inactive and locked messages shown clearly

---

## 26.7 Dashboard Redesign

The dashboard must be role-aware, shop-aware, godown-aware, and permission-aware.

It must not show the same dashboard to every user.

### Dashboard Header

Display:

- Greeting
- User name
- Current role
- Active shop
- Active godown
- Current date
- Primary quick actions

Example quick actions:

- New Sale
- New Purchase
- Add Payment
- Add Expense
- Add Party
- Stock Transfer

Only permitted actions must be shown.

### KPI Cards

Recommended KPI cards:

- Today's Sales
- Today's Purchases
- Amount Collected
- Amount Paid
- Receivables
- Payables
- Current Stock Value
- Low Stock Items
- Pending Deliveries
- Today's Expenses
- Gross Profit
- Net Profit

Each card must:

- Show a meaningful icon
- Show the current value
- Show comparison where relevant
- Link to the filtered listing page
- Respect shop and godown context
- Hide if the user lacks permission

### Dashboard Sections

Recommended dashboard sections:

1. Sales trend
2. Purchase trend
3. Profit versus expense
4. Receivable and payable summary
5. Low-stock products
6. Pending payments
7. Recent sales
8. Recent purchases
9. Delivery status
10. Top-selling products
11. Top customers
12. Important alerts
13. Master-data setup status
14. User activity, for authorized administrators

Do not load every dashboard widget for every user. Load only the widgets needed for the current role and permission set.

---

## 26.8 Quick Action System

Every module must contain a visible quick-action area.

Examples:

### Parties

- Add Customer
- Add Supplier
- Add Group
- Import Parties
- View Ledger

### Sales

- New Sale
- Sales Return
- Receive Payment
- Print Last Invoice
- View Pending Payments

### Purchase

- New Purchase
- Purchase Return
- Pay Supplier
- Add Supplier
- View Payables

### Inventory

- Add Product
- Add Category
- Add Unit
- Add Opening Stock
- Stock Transfer
- Stock Adjustment

### Reports

- Sales Report
- Purchase Report
- Stock Report
- Profit and Loss
- Party Ledger
- GST Report

Quick-action buttons must:

- Respect permissions
- Use clear labels and icons
- Link to the relevant route
- Remain consistent across modules
- Be available near the page heading
- Avoid showing unauthorized actions

---

## 26.9 Missing Master Data and Zero-Record Guidance

When a transaction depends on master data and the required record count is zero, the UI must not show only an empty dropdown.

It must display an actionable message with a direct URL.

Examples:

### No Products

```text
No products are available.
Create at least one product before making a sale.

[Add Product]
```

### No Customers

```text
No customers are available for this transaction.

[Add Customer]
```

### No Suppliers

```text
No suppliers are available.

[Add Supplier]
```

### No Units or Categories

```text
Product setup is incomplete.
Add the required unit and category before creating products.

[Add Unit] [Add Category]
```

### No Godown

```text
No godown is assigned to the selected shop.

[Create Godown]
```

### Rules

The dependency warning must:

- Explain what is missing
- Explain why it is required
- Provide a direct permitted action URL
- Hide links the user cannot access
- Provide a contact-admin message if the user lacks create permission
- Prevent invalid transaction submission
- Be reusable across modules

Blade example:

```blade
@if ($productCount === 0)
    <x-dependency-alert
        title="No products available"
        message="Create at least one product before making a sale."
        permission="products.create"
        :url="route('products.create')"
        action-label="Add Product"
    />
@endif
```

Fallback for unauthorized users:

```text
Products have not been configured. Contact your administrator.
```

---

## 26.10 Reusable Empty-State Component

Create:

```text
resources/views/components/empty-state.blade.php
resources/views/components/dependency-alert.blade.php
resources/views/components/quick-actions.blade.php
```

The empty-state component must support:

- Icon
- Title
- Description
- Primary action
- Secondary action
- Required permission
- Optional help link

---

# 27. Vyapar Clone — Recommended Module Map

The Cholavin ERP should contain the following primary modules.

## 27.1 Authentication and Access Control

- Login
- Forgot password
- Users
- Roles
- Permissions
- User overrides
- Shop assignment
- Godown assignment
- Session monitoring
- Audit logs
- Login history

## 27.2 Organization and Branch Setup

- Company profile
- Shops
- Godowns
- Financial years
- Invoice sequences
- Tax configuration
- Bank accounts
- Payment methods

## 27.3 Product Masters

- Products
- Categories
- Subcategories
- Brands
- Units
- Variants
- Grades
- Price lists
- Tax rates
- HSN or SAC codes
- Barcode
- Opening stock

## 27.4 Party Management

- Customers
- Suppliers
- Customer groups
- Supplier groups
- Addresses
- Credit limits
- Opening balances
- Party ledger
- Receivables
- Payables

## 27.5 Sales

- Sales quotation
- Sales order
- Delivery challan
- Sales invoice
- POS billing
- Sales return
- Credit note
- Payment collection
- Invoice printing
- WhatsApp or email sharing

## 27.6 Purchases

- Purchase order
- Goods receipt
- Purchase bill
- Purchase return
- Debit note
- Supplier payment
- Purchase expense allocation

## 27.7 Inventory

- Shop stock
- Godown stock
- Stock transfer
- Stock adjustment
- Damaged stock
- Expired stock
- Batch tracking
- Low-stock alerts
- Stock valuation
- Stock movement ledger

## 27.8 Payments and Accounts

- Cash receipt
- Cash payment
- Bank receipt
- Bank payment
- Contra entry
- Expense
- Income
- Party settlement
- Day book
- Cash book
- Bank book
- General ledger

## 27.9 Delivery Management

- Delivery assignment
- Vehicle assignment
- Driver assignment
- Route planning
- Delivery status
- Proof of delivery
- Cash collection
- Delivery expense

## 27.10 Reports

- Sales reports
- Purchase reports
- Stock reports
- Party reports
- Payment reports
- Expense reports
- GST reports
- Profit and loss
- Audit reports
- User activity reports

## 27.11 Notification and Reminder

- Payment reminders
- Low-stock alerts
- Pending-delivery alerts
- Expiry alerts
- User notifications
- Email, SMS, or WhatsApp integration

## 27.12 Settings and Audit

- Application settings
- Invoice templates
- Number sequences
- Backup settings
- Activity logs
- Error logs
- Login logs
- Data import and export

Implementation status: the Section 27 modules are implemented through shared reference-master, commercial-document, inventory, accounting, reporting, notification, delivery, and maintenance engines. They are not empty placeholder modules. See `docs/ERP_MODULE_IMPLEMENTATION.md` for the implementation map, business invariants, and provider-dependent channel boundary.

---

# 28. Module 1 — Authentication, Access Control, Shop and Godown Context

## 28.1 Module Overview

The Authentication and Access Control module is the entry point and security foundation of the Cholavin ERP system.

This module is responsible for:

- Secure user login
- Common login for all users
- Role-based access control
- User-specific permission overrides
- Dynamic shop management
- Dynamic godown management
- Shop and godown assignment
- Gate and Policy-based authorization
- Blade-level access control
- Active shop and godown switching
- Session monitoring
- Login and activity audit logs

Although all users access the application through a common login page, each user can access only the shops, godowns, modules, records, and actions assigned by the Super Admin.

The permission implementation should follow the same Gate, Policy, middleware, and Blade authorization pattern already used in:

```text
C:\xampp\htdocs\invicts-website-main
```

That project may be used as an implementation reference, but the Cholavin ERP permission design must also include shop access, godown access, active business context, and record-level scoping.

---

## 28.2 Objectives

- Provide secure authentication
- Support one common login
- Enable dynamic shop creation
- Enable dynamic godown creation
- Support dynamic roles and permissions
- Assign roles to users
- Assign shops to users
- Assign godowns to users
- Restrict modules and actions
- Support direct user permission overrides
- Restrict database records by operating context
- Switch shops and godowns without logout
- Hide unauthorized Blade elements
- Prevent unauthorized direct URL access
- Record login, logout, context switch, and business activity logs

---

## 28.3 Permission Model

Use a hybrid permission model:

```text
Effective Permission
= Role Permission
+ User Allow Override
- User Deny Override
+ Shop Assignment
+ Godown Assignment
+ Record Ownership/Context Rule
```

A direct deny should take priority over a role allow, except for Super Admin.

### Permission Naming

Use consistent permission keys:

```text
dashboard.view

shops.view
shops.create
shops.update
shops.delete
shops.switch

godowns.view
godowns.create
godowns.update
godowns.delete
godowns.switch

users.view
users.create
users.update
users.delete
users.assign_role
users.assign_shop
users.assign_godown
users.assign_permission

products.view
products.create
products.update
products.delete
products.export

sales.view
sales.create
sales.update
sales.delete
sales.print
sales.return
sales.receive_payment
```

---

## 28.4 Recommended Database Tables

```text
users
roles
permissions
role_user
permission_role
permission_user
shops
godowns
shop_user
godown_user
login_histories
user_sessions
activity_logs
context_switch_logs
```

### `permission_user`

Recommended fields:

```text
id
user_id
permission_id
effect          allow | deny
created_by
created_at
updated_at
```

### `shop_user`

Recommended fields:

```text
id
user_id
shop_id
is_default
status
created_by
created_at
updated_at
```

### `godown_user`

Recommended fields:

```text
id
user_id
godown_id
shop_id
is_default
status
created_by
created_at
updated_at
```

A user must not be assigned to a godown unless the related shop is also assigned.

---

## 28.5 Login Flow

### Step 1: Credential Verification

Validate:

- User exists
- Password matches
- User is active
- Account is not locked
- Role is active
- Login attempt limit is not exceeded

### Step 2: Access Resolution

Load:

- User roles
- Role permissions
- User permission overrides
- Assigned shops
- Assigned godowns
- Allowed modules
- Allowed actions

### Step 3: Default Context Resolution

For Super Admin:

- Use the last selected shop, if valid
- Otherwise use the configured default shop
- Otherwise use the first active shop
- Select a valid godown under that shop

For normal users:

- Use the last permitted shop, if valid
- Otherwise use the assigned default shop
- Otherwise use the first active assigned shop
- Use the assigned default godown under that shop
- Otherwise use the first permitted godown under that shop

### Step 4: Session Creation

Store minimal context:

```text
user_id
active_shop_id
active_godown_id
active_financial_year_id
login_history_id
login_timestamp
```

Do not store a large complete permission collection permanently in the session.

Use cached permission resolution and invalidate the cache when assignments change.

### Step 5: Dashboard Redirect

Redirect the user to the dashboard.

The dashboard and sidebar must display only permitted content and records belonging to the active context.

---

## 28.6 Super Admin Shop and Godown Switching

The Super Admin must see all active shops in the header shop dropdown.

When a shop is selected:

1. Validate the shop exists and is active
2. Update `active_shop_id`
3. Load active godowns belonging to that shop
4. Select the previous valid godown or default godown
5. Update `active_godown_id`
6. Clear context-sensitive cache
7. Write a context-switch audit log
8. Refresh dashboard widgets and DataTables
9. Do not log the user out

The godown dropdown must update dynamically through AJAX.

### Normal User Switching

Normal users may see only assigned active shops.

The godown dropdown must show only godowns that:

- Belong to the selected permitted shop
- Are assigned to the user
- Are active

A user with only one shop may see the selected shop as read-only text rather than a dropdown.

---

## 28.7 Context Switch Endpoints

Suggested routes:

```php
Route::middleware('auth')->group(function () {
    Route::get('/context/godowns', [ContextController::class, 'godowns'])
        ->name('context.godowns');

    Route::post('/context/shop', [ContextController::class, 'switchShop'])
        ->name('context.switch-shop');

    Route::post('/context/godown', [ContextController::class, 'switchGodown'])
        ->name('context.switch-godown');
});
```

All context changes must use AJAX.

Standard response:

```json
{
    "status": true,
    "message": "Operating context updated.",
    "data": {
        "active_shop_id": 2,
        "active_godown_id": 5,
        "reload": true
    }
}
```

A full login must not be required.

After switching context, reload only context-sensitive sections where practical. A normal page navigation or controlled page refresh may be used when the full page is strongly tied to shop context, but logout is never required.

---

## 28.8 Context Service

Create:

```text
app/Services/Access/BusinessContextService.php
```

Responsibilities:

- Resolve permitted shops
- Resolve permitted godowns
- Resolve default context
- Validate requested shop
- Validate requested godown
- Set active shop
- Set active godown
- Clear context cache
- Record context switch

Suggested methods:

```php
public function permittedShops(User $user): Collection;
public function permittedGodowns(User $user, int $shopId): Collection;
public function resolveDefaultContext(User $user): array;
public function switchShop(User $user, int $shopId): array;
public function switchGodown(User $user, int $godownId): array;
public function activeShopId(): ?int;
public function activeGodownId(): ?int;
```

---

## 28.9 Gate Implementation

Define a Super Admin bypass:

```php
Gate::before(function (User $user, string $ability) {
    return $user->isSuperAdmin() ? true : null;
});
```

Register permission gates dynamically:

```php
foreach (Permission::active()->pluck('name') as $permission) {
    Gate::define($permission, function (User $user) use ($permission) {
        return app(PermissionService::class)
            ->userCan($user, $permission);
    });
}
```

Cache the permission list and user permission result.

Invalidate permission cache when:

- Role changes
- Role permission changes
- User permission override changes
- User status changes
- Shop assignment changes
- Godown assignment changes

---

## 28.10 Policy Implementation

Use Policies for model-level and record-level rules.

Example:

```php
public function view(User $user, Sale $sale): bool
{
    return $user->can('sales.view')
        && app(BusinessContextService::class)
            ->canAccessRecord($user, $sale->shop_id, $sale->godown_id);
}
```

Example update rule:

```php
public function update(User $user, Sale $sale): bool
{
    return $user->can('sales.update')
        && $sale->shop_id === active_shop_id()
        && (
            $sale->godown_id === null
            || $sale->godown_id === active_godown_id()
        );
}
```

Every sensitive model should have a Policy.

---

## 28.11 Blade Authorization

Use Blade authorization for:

- Menus
- Submenus
- Create buttons
- Edit buttons
- Delete buttons
- Print buttons
- Export buttons
- Tabs
- Quick actions
- Dashboard widgets
- Shop and godown controls

Example:

```blade
@can('users.assign_shop')
    <button class="btn btn-primary" id="assignShopBtn">
        Assign Shop
    </button>
@endcan
```

Example record Policy:

```blade
@can('update', $sale)
    <button class="btn btn-sm btn-outline-primary edit-sale">
        Edit
    </button>
@endcan
```

Do not use Blade visibility as the only security layer.

---

## 28.12 Query-Level Data Restriction

Every shop-based transaction table must contain `shop_id`.

Every godown-based stock or transaction table must contain `godown_id`, where applicable.

Create a reusable scope:

```php
public function scopeForActiveContext($query)
{
    return $query
        ->when(active_shop_id(), function ($query, $shopId) {
            $query->where('shop_id', $shopId);
        })
        ->when(active_godown_id(), function ($query, $godownId) {
            $query->where(function ($query) use ($godownId) {
                $query->whereNull('godown_id')
                      ->orWhere('godown_id', $godownId);
            });
        });
}
```

Usage:

```php
$query = Sale::query()
    ->forActiveContext()
    ->when($request->filled('status'), fn ($query) =>
        $query->where('status', $request->status)
    );
```

Never trust `shop_id` or `godown_id` sent by the browser.

Assign them server-side from the validated active context.

---

## 28.13 User Management Screen

The user create/edit screen must contain tabs:

```text
Basic Details
Role Assignment
Shop Access
Godown Access
Permission Overrides
Security
Session History
Activity History
```

### Shop Assignment

- Multi-select shops
- Default shop
- Active/inactive assignment
- Prevent duplicate assignment

### Godown Assignment

- Filter godowns by assigned shops
- Multi-select godowns
- Default godown
- Prevent cross-shop invalid assignments

### Permission Overrides

Show grouped permissions:

```text
Module
├── View
├── Create
├── Update
├── Delete
├── Print
├── Export
└── Approve
```

Each permission may be:

- Inherited
- Explicitly allowed
- Explicitly denied

The UI must clearly show the source of the effective permission.

---

## 28.14 Session Monitoring

Super Admin must be able to view:

- User
- Role
- Login time
- Last activity time
- IP address
- Browser
- Device
- Active shop
- Active godown
- Session status

Optional administrative actions:

- Force logout a session
- Lock a user
- Unlock a user
- Revoke all sessions

---

## 28.15 Audit Logging

Log at minimum:

- Login success
- Login failure
- Logout
- Password reset
- Role assignment
- Permission assignment
- Shop assignment
- Godown assignment
- Shop switch
- Godown switch
- Record create
- Record update
- Record delete
- Export
- PDF download
- Invoice print
- Sensitive settings change

Recommended fields:

```text
user_id
event
module
action
auditable_type
auditable_id
shop_id
godown_id
old_values
new_values
ip_address
user_agent
created_at
```

---

## 28.16 Middleware

Recommended middleware:

```text
Authenticate
EnsureUserIsActive
EnsureRoleIsActive
SetBusinessContext
EnsureShopAccess
EnsureGodownAccess
EnsurePermission
RecordLastActivity
```

Suggested route example:

```php
Route::middleware([
    'auth',
    'user.active',
    'business.context',
    'permission:sales.view',
])->group(function () {
    Route::resource('sales', SaleController::class);
});
```

---

## 28.17 Header Blade Example

```blade
<div class="header-context d-flex align-items-center gap-2">

    <select id="activeShop"
            class="form-select form-select-sm"
            @disabled($permittedShops->count() <= 1)>
        @foreach ($permittedShops as $shop)
            <option value="{{ $shop->id }}"
                @selected($shop->id === active_shop_id())>
                {{ $shop->name }}
            </option>
        @endforeach
    </select>

    <select id="activeGodown"
            class="form-select form-select-sm"
            @disabled($permittedGodowns->count() <= 1)>
        @foreach ($permittedGodowns as $godown)
            <option value="{{ $godown->id }}"
                @selected($godown->id === active_godown_id())>
                {{ $godown->name }}
            </option>
        @endforeach
    </select>

</div>
```

---

## 28.18 Context Switch JavaScript

```javascript
$('#activeShop').on('change', function () {
    $.ajax({
        url: routes.switchShop,
        type: 'POST',
        data: {
            shop_id: $(this).val()
        },
        success: function (response) {
            toastr.success(response.message);
            window.location.reload();
        },
        error: handleGlobalAjaxError
    });
});

$('#activeGodown').on('change', function () {
    $.ajax({
        url: routes.switchGodown,
        type: 'POST',
        data: {
            godown_id: $(this).val()
        },
        success: function (response) {
            toastr.success(response.message);

            if ($.fn.DataTable.isDataTable('.context-data-table')) {
                $('.context-data-table').DataTable().ajax.reload();
            } else {
                window.location.reload();
            }
        },
        error: handleGlobalAjaxError
    });
});
```

The page reload shown here is an application-context refresh, not a logout. For pages that support partial refresh, reload DataTables and widgets only.

---

## 28.19 Security Rules

- Never authorize only in Blade
- Never trust browser-provided shop or godown IDs
- Never expose unauthorized shop or godown records in dropdowns
- Validate active context on every sensitive request
- Apply Policies to record operations
- Use CSRF protection
- Regenerate the session after login
- Invalidate the session after logout
- Rate-limit login
- Hash passwords using Laravel-supported hashing
- Audit sensitive access changes
- Revoke or refresh cached permissions immediately after access changes
- Prevent users from assigning permissions they do not have authority to grant

---

## 28.20 Acceptance Criteria

This module is complete only when:

1. All users use the same login page.
2. Inactive users cannot log in.
3. Super Admin can access all shops and godowns.
4. Normal users see only assigned shops.
5. Normal users see only assigned godowns under the selected shop.
6. Super Admin can switch shop and godown without logout.
7. Normal users can switch only among permitted contexts.
8. Sidebar menus follow Gate permissions.
9. Blade buttons follow Gate or Policy rules.
10. Direct unauthorized URLs return HTTP 403.
11. DataTables return only active-context records.
12. New transactions receive shop and godown IDs from server context.
13. Role and user permission overrides work correctly.
14. A direct deny overrides a role allow.
15. User access changes invalidate permission cache.
16. Login, logout, and context switching are audited.
17. User session monitoring is available to authorized administrators.
18. Dashboard widgets and quick actions are permission-aware.
19. Missing master data shows a useful URL-based action message.
20. All UI follows the Cholavin logo and corporate visual standards.

---

# 29. Module Development Template

Every future module document must use this structure:

```text
1. Module Overview
2. Objectives
3. User Roles
4. Dependencies and Required Masters
5. Functional Requirements
6. Business Rules
7. Database Design
8. Models and Relationships
9. Routes
10. Controllers and Services
11. Gate and Policy Permissions
12. Blade Permission Rules
13. Shop and Godown Restrictions
14. Form Fields
15. jQuery Validation
16. Laravel Form Request Validation
17. AJAX Flow
18. DataTable Columns
19. Filters Using Laravel when()
20. Quick Actions
21. Empty and Zero-Record States
22. mPDF and Export Requirements
23. Audit Log Events
24. Security Rules
25. Acceptance Criteria
```

Every module must inherit the global Laravel standards defined in this document.
