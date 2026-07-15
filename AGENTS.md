# Cholavin ERP — Complete Specification

This document combines both parts of the Cholavin ERP specification into a single reference:

* **Part 1 — UI/UX Redesign & AJAX-Only Development** (branding, layout, modules, Supplier/Customer management, AJAX architecture, response contracts, error handling, DataTables, permissions, responsive behaviour, folder structure)
* **Part 2 — Authentication & Access Control Module** (login, roles, user-based shop/godown assignment, user-based permissions, context switching, session freshness, audit logging)

Both parts share the same branding, AJAX conventions, and permission engine — Part 2 is the concrete implementation of the permission and scoping rules referenced throughout Part 1.

---
---

# PART 1 — UI/UX Redesign and AJAX-Only Development

Redesign the complete **Cholavin ERP admin panel** as a modern rice trading, wholesale billing, inventory, and accounting system.

Use the uploaded supplier screen only as a **functional reference**. Do not copy its exact layout, component placement, card design, typography, sidebar, or color distribution. Create a unique Cholavin interface using its own branding.

### Cholavin branding

Use:

* Primary maroon: `#800020`
* Dark maroon: `#4A0012`
* Gold: `#D4AF37`
* Light gold background: `#FFF8E1`
* Main page background: `#F5F6FA`
* White cards: `#FFFFFF`
* Main text: `#242424`
* Muted text: `#747474`
* Success: `#15803D`
* Warning: `#D97706`
* Danger: `#B42318`

Logo:

```text
public/frontend/assets/img/logo/logo-hm62.png
```

The final interface must feel:

* Corporate
* Clean
* Premium
* Simple for non-technical users
* Suitable for rice shops, godowns, wholesalers, accountants, and billing staff
* Responsive for desktop, tablet, and mobile
* Fast without full-page reloads

---

## 1. Main Application Layout

### 1.1 Left Sidebar

Create a collapsible maroon sidebar.

#### Sidebar header

Display:

* Cholavin logo
* Cholavin ERP
* Small caption: `Rice Trading & Business Management`
* Sidebar collapse button

#### Sidebar menu structure

```text
Dashboard

Parties
    Customers
    Suppliers
    Customer Groups
    Supplier Groups
    Outstanding Summary

Items
    Item Master
    Categories
    Brands
    Rice Varieties
    Grades
    Units
    Price Lists
    Opening Stock

Sales
    New Sale
    Sales Invoices
    Sales Returns
    Quotations
    Delivery Challans
    Customer Payments

Purchase & Expenses
    New Purchase
    Purchase Invoices
    Purchase Returns
    Supplier Payments
    Expenses
    Expense Categories

Inventory
    Current Stock
    Shop-wise Stock
    Godown-wise Stock
    Stock Transfer
    Stock Adjustment
    Damaged Stock
    Stock History
    Low Stock Alerts

Delivery
    Delivery Orders
    Delivery Routes
    Delivery Areas
    Vehicles
    Drivers
    Delivery Tracking

Accounting
    Cash Book
    Bank Book
    Party Ledger
    General Ledger
    Payment In
    Payment Out
    Journal Entries
    Day Book

GST & Tax
    GST Summary
    GSTR-1
    GSTR-3B
    HSN Summary
    Tax Reports

Reports
    Sales Reports
    Purchase Reports
    Profit Reports
    Stock Reports
    Outstanding Reports
    Payment Reports
    Delivery Reports
    Audit Reports

Business Setup
    Shops
    Godowns
    Financial Years
    Invoice Settings
    Printer Settings
    Payment Methods
    Taxes
    Number Series

Users & Access
    Users
    Roles
    Permissions
    Shop Access
    Godown Access
    Activity Logs

Settings
```

#### Sidebar behaviour

* Open only one submenu at a time.
* Highlight the current module.
* Save collapsed or expanded state in local storage.
* Menu visibility must be based on permissions.
* Show numeric badges for:

  * Pending deliveries
  * Low stock
  * Overdue payments
  * Pending approvals
* Load sidebar badge counts through AJAX.
* Do not reload the page when expanding menus.

---

## 2. Top Header

Create a fixed top header with:

### Left section

* Mobile sidebar toggle
* Global search
* Quick-create button

### Global search

Search across:

* Customers
* Suppliers
* Items
* Invoices
* Purchases
* Payments
* Delivery orders

Search must use AJAX with debounce.

Minimum search length:

```text
2 characters
```

Show grouped results:

```text
Customers
Suppliers
Items
Invoices
Payments
```

Clicking a result should open its details page or AJAX drawer.

### Centre section

Provide context selectors:

* Active business location
* Active inventory location
* Active financial year

Example:

```text
Business Location: Chennai Head Office
Inventory Location: Chennai Central Godown
Financial Year: 2026–2027
```

Changing a selector must:

1. Call an AJAX endpoint.
2. Validate user access.
3. Update the session.
4. Refresh dashboard counters and page data.
5. Reload only affected components.
6. Show a success toast.

Do not perform a full browser refresh unless absolutely required.

### Right section

* Fullscreen control
* Theme switcher
* Notifications
* Calculator
* User profile
* Logout

---

## 3. Unique Page Design Concept

Do not use the exact left-list and right-detail layout shown in the reference.

Use a unique **three-zone workspace design**.

### Zone A: Page command bar

Display:

* Breadcrumb
* Page title
* Short description
* Current shop tag
* Current godown tag
* Main action buttons

Example:

```text
Parties / Suppliers

Supplier Management
Manage supplier purchases, payments, credit limits and outstanding balances.

[Chennai Head Office] [Central Godown]

[Import] [Export PDF] [Export Excel] [+ Add Supplier]
```

### Zone B: Summary and smart filters

Display compact KPI cards followed by filters.

Example supplier cards:

* Total suppliers
* Active suppliers
* Total payable
* Overdue amount
* Purchases this month
* Payments this month

Cards should support AJAX filtering.

For example, clicking `Overdue Amount` should filter the supplier table to overdue suppliers.

### Zone C: Main data workspace

Use a full-width server-side DataTable.

When the user selects a record, open a right-side AJAX drawer containing complete details.

This creates a different interface from the screenshot while retaining fast record access.

---

## 4. Supplier Management Page

### 4.1 Supplier list page

Route:

```php
GET /admin/parties/suppliers
```

Display supplier KPI cards:

```text
Total Suppliers
Active Suppliers
Total Payable
Overdue Payable
Purchases This Month
Payments This Month
```

#### Filter section

Provide:

* Search by name, code, phone or GSTIN
* Supplier group
* Status
* Outstanding condition
* Credit status
* State
* Created date range
* Purchase date range
* Shop
* Sort order

Outstanding options:

```text
All
No Outstanding
Payable
Advance Paid
Overdue
Credit Limit Exceeded
```

#### Supplier DataTable columns

```text
Checkbox
Supplier
Supplier Code
Group
Mobile
GSTIN
Location
Last Purchase
Total Purchases
Paid Amount
Outstanding
Credit Limit
Status
Actions
```

Supplier column should show:

* Avatar or initials
* Supplier name
* Supplier code
* Mobile number

#### Row actions

```text
View
Edit
Add Purchase
Make Payment
Purchase Return
View Ledger
Send WhatsApp
Send Email
Print Statement
Deactivate
Delete
```

Use a compact dropdown instead of showing every action as a separate button.

#### Bulk actions

```text
Activate
Deactivate
Assign Group
Export
Send Statement
Delete
```

All bulk operations must use AJAX.

---

## 5. Supplier Detail Drawer

Clicking a supplier should open a right-side drawer covering approximately 45% of the screen.

Load content through AJAX.

Endpoint:

```php
GET /admin/parties/suppliers/{supplier}/summary
```

### Drawer header

Display:

* Supplier initials or logo
* Supplier name
* Supplier code
* Active or inactive status
* Group
* Mobile
* WhatsApp
* Email
* GSTIN
* Address

Header quick actions:

```text
Call
WhatsApp
Email
Edit
More
```

### Supplier financial summary

Display:

```text
Current Outstanding
Overdue Amount
Total Purchases
Total Payments
Purchase Returns
Available Credit
Last Purchase Date
Next Payment Due
```

Use clear accounting labels:

* `Payable` means the business must pay the supplier.
* `Advance` means the business has paid more than the current liability.
* Do not show negative values without an understandable label.

For example:

```text
₹25,000 Payable
₹4,000 Advance
No Outstanding
```

### Drawer tabs

```text
Overview
Purchases
Payments
Returns
Ledger
Items
Addresses
Documents
Notes
Activity
```

Each tab must load content through AJAX only when opened.

Do not load all tab contents during the first request.

---

## 6. Supplier Overview Tab

Display:

### Business information

* Supplier group
* Contact person
* Mobile
* Alternate mobile
* Email
* GST registration type
* GSTIN
* PAN
* State
* Payment terms
* Credit limit
* Opening balance

### Address section

* Billing address
* Pickup address
* Alternate address
* Google Maps link

### Recent activity

Show the latest:

* Purchase
* Payment
* Purchase return
* Note
* Document upload
* Profile update

### Quick action panel

```text
+ New Purchase
Make Payment
Create Purchase Return
Download Ledger
Add Note
Upload Document
Set Reminder
```

---

## 7. Supplier Purchases Tab

Use a server-side DataTable.

Columns:

```text
Purchase Number
Supplier Invoice Number
Invoice Date
Due Date
Shop
Godown
Subtotal
Tax
Total
Paid
Balance
Status
Actions
```

Statuses:

```text
Draft
Unpaid
Partially Paid
Paid
Overdue
Cancelled
Returned
```

Actions:

```text
View
Edit
Print
Download PDF
Record Payment
Create Return
Cancel
```

Filters:

* Status
* Date range
* Shop
* Godown
* Amount range
* Due state

---

## 8. Supplier Payment Flow

Clicking `Make Payment` opens a modal or drawer.

### Payment form

Fields:

```text
Supplier
Current Payable
Payment Date
Amount
Payment Method
Cash/Bank Account
Reference Number
Discount Received
TDS Amount
Other Adjustment
Notes
Upload Proof
Next Payment Reminder
```

### Calculation logic

```text
Net Payment = Payment Amount + Discount + TDS + Other Valid Adjustments
New Outstanding = Current Outstanding - Net Payment
```

Do not allow payment above the outstanding unless `Allow advance payment` is enabled.

When excess payment is permitted:

```text
Excess amount = Supplier Advance
```

### AJAX payment process

1. User opens payment modal.
2. Fetch supplier balance through AJAX.
3. User enters amount.
4. Update balance preview instantly.
5. Validate on the client.
6. Submit via AJAX.
7. Validate again on the server.
8. Start a database transaction.
9. Create payment record.
10. Create supplier ledger entry.
11. Update purchase allocations.
12. Update supplier balance.
13. Create account transaction.
14. Save attachment.
15. Save activity log.
16. Commit transaction.
17. Return JSON.
18. Close modal.
19. Refresh affected cards.
20. Reload DataTable without resetting pagination.
21. Show success toast.
22. Offer receipt print or PDF download.

---

## 9. Add and Edit Supplier

Use an AJAX drawer instead of navigating to a separate page.

### Sections

#### Basic details

```text
Supplier Name
Supplier Code
Supplier Group
Contact Person
Mobile
Alternate Mobile
WhatsApp Number
Email
Status
```

#### Tax details

```text
GST Registration Type
GSTIN
PAN
State
Tax Preference
```

#### Financial settings

```text
Opening Balance
Opening Balance Type
Credit Limit
Credit Period
Default Payment Method
Default Purchase Price List
```

Opening balance types:

```text
Payable
Advance
None
```

#### Address

```text
Address Line 1
Address Line 2
Area
City
District
State
Pincode
Country
```

#### Other details

```text
Bank Name
Account Holder
Account Number
IFSC
UPI ID
Notes
Documents
```

### Code generation

Supplier code format:

```text
SUP-0001
SUP-0002
```

Generate the final code on the server to avoid duplicate codes.

---

## 10. Customer Management Page

Use the same design system as suppliers, but customer logic must remain separate.

Customer summary cards:

```text
Total Customers
Receivable Amount
Overdue Receivable
Sales This Month
Collections This Month
Credit Limit Exceeded
```

Customer drawer tabs:

```text
Overview
Sales
Payments
Returns
Ledger
Deliveries
Items
Addresses
Documents
Notes
Activity
```

Customer actions:

```text
New Sale
Receive Payment
Create Sales Return
Create Delivery
Send Statement
Print Ledger
```

For customers:

* Outstanding normally means `Receivable`.
* Advance means customer credit.
* Never use `Payable` for a normal customer balance.

---

## 11. Unified Party Workspace

Create a common party profile experience while maintaining separate customer and supplier records.

Routes:

```php
/admin/parties/customers
/admin/parties/suppliers
/admin/parties/groups
```

Do not mix customer and supplier accounting entries accidentally.

A party may optionally be both a customer and supplier.

In that case:

```text
Customer Receivable: ₹40,000
Supplier Payable: ₹25,000
Net Exposure: ₹15,000 Receivable
```

Show separate ledgers unless an authorized user performs a formal settlement adjustment.

---

## 12. Empty-State and Missing-Master Logic

When a required master record is unavailable, never display a broken empty dropdown.

Example:

```text
No supplier groups are available.

[Create Supplier Group]
```

Other examples:

```text
No payment methods configured.
[Configure Payment Methods]

No godown available for this shop.
[Create Godown]

No rice grades configured.
[Add Rice Grade]

No units configured.
[Add Unit]

No bank account configured.
[Add Bank Account]
```

Clicking the action should open the corresponding AJAX modal or drawer.

After creating the master record:

1. Close the child modal.
2. Refresh the original dropdown through AJAX.
3. Automatically select the newly created record.
4. Preserve all entered form data.

---

## 13. AJAX-Only Architecture

All create, update, delete, filter, status-change, import, allocation, and transaction operations must work through AJAX.

Use:

* Laravel 12
* Blade
* Bootstrap 5
* jQuery
* AJAX
* Yajra DataTables
* jQuery Validate
* Select2
* Flatpickr
* SweetAlert2 or jQuery Confirm
* Toastr
* mPDF
* Laravel Excel

### Do not use

* Full-page form submission
* Browser page reload after save
* Inline JavaScript inside Blade files
* Duplicate AJAX logic
* Hardcoded URLs
* Unstructured JSON responses
* Direct balance modification without ledger entries
* Frontend-only financial calculations

---

## 14. Standard AJAX Response Format

Every AJAX endpoint must return a consistent JSON structure.

### Success response

```json
{
    "success": true,
    "message": "Supplier created successfully.",
    "data": {
        "id": 25,
        "supplier_code": "SUP-0025"
    },
    "refresh": {
        "datatable": true,
        "summary": true,
        "drawer": false
    },
    "redirect": null
}
```

### Validation response

HTTP status:

```text
422
```

```json
{
    "success": false,
    "message": "Please correct the highlighted fields.",
    "errors": {
        "supplier_name": [
            "The supplier name field is required."
        ],
        "mobile": [
            "The mobile number has already been taken."
        ]
    }
}
```

### Business-rule response

HTTP status:

```text
409
```

```json
{
    "success": false,
    "message": "This supplier cannot be deleted because purchase transactions exist.",
    "error_code": "SUPPLIER_HAS_TRANSACTIONS"
}
```

### Unauthorized response

HTTP status:

```text
403
```

```json
{
    "success": false,
    "message": "You do not have permission to perform this action."
}
```

### Server error response

HTTP status:

```text
500
```

```json
{
    "success": false,
    "message": "The operation could not be completed. Please try again.",
    "reference": "ERR-20260715-00045"
}
```

Never send raw exception details to production users.

---

## 15. Reusable AJAX Handler

Create a common JavaScript service.

```javascript
window.CholavinAjax = {
    request(options) {
        const defaults = {
            method: 'GET',
            data: {},
            processData: true,
            contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
            showLoader: true,
            disableButton: null
        };

        const settings = $.extend({}, defaults, options);

        if (settings.showLoader) {
            AppLoader.show();
        }

        if (settings.disableButton) {
            ButtonLoader.start(settings.disableButton);
        }

        return $.ajax({
            url: settings.url,
            type: settings.method,
            data: settings.data,
            processData: settings.processData,
            contentType: settings.contentType,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .done(function (response) {
            if (typeof settings.onSuccess === 'function') {
                settings.onSuccess(response);
            }
        })
        .fail(function (xhr) {
            AjaxErrorHandler.handle(xhr, settings.form);
        })
        .always(function () {
            AppLoader.hide();

            if (settings.disableButton) {
                ButtonLoader.stop(settings.disableButton);
            }
        });
    }
};
```

---

## 16. AJAX Form Submission

Use delegated events so dynamically loaded forms also work.

```javascript
$(document).on('submit', '.ajax-form', function (event) {
    event.preventDefault();

    const form = $(this);
    const submitButton = form.find('[type="submit"]');

    if (!form.valid()) {
        return;
    }

    const formData = new FormData(this);

    CholavinAjax.request({
        url: form.attr('action'),
        method: form.attr('method') || 'POST',
        data: formData,
        processData: false,
        contentType: false,
        form: form,
        disableButton: submitButton,
        onSuccess: function (response) {
            toastr.success(response.message);

            if (response.refresh?.datatable) {
                window.activeDataTable?.ajax.reload(null, false);
            }

            if (response.refresh?.summary) {
                PartySummary.reload();
            }

            if (response.refresh?.drawer) {
                PartyDrawer.reload();
            }

            if (response.redirect) {
                window.location.href = response.redirect;
                return;
            }

            form.closest('.modal').modal('hide');
            form.closest('.offcanvas').offcanvas('hide');
        }
    });
});
```

---

## 17. Instant User Response

Every user action must provide immediate visual feedback.

### Button state

When clicked:

```text
Save Supplier
```

Change to:

```text
Saving...
```

Disable the button until the request finishes.

### Optimistic feedback

For simple status changes:

* Update the badge immediately.
* Send the AJAX request.
* Revert the badge when the request fails.

Do not use optimistic updates for:

* Payments
* Stock adjustments
* Purchases
* Sales
* Returns
* Ledger entries

Financial and stock transactions must wait for a confirmed server response.

### Skeleton loading

Use skeleton placeholders for:

* Detail drawer
* Summary cards
* Dashboard widgets
* Tab content

Avoid showing a large blocking spinner for every request.

### Toast messages

Use:

* Success toast for completed actions
* Warning toast for incomplete configurations
* Error toast for failed requests
* Information toast for background operations

---

## 18. AJAX Error Handling

Implement a central error handler.

```javascript
const AjaxErrorHandler = {
    handle(xhr, form = null) {
        if (xhr.status === 422) {
            this.showValidationErrors(xhr.responseJSON.errors, form);
            return;
        }

        if (xhr.status === 401) {
            toastr.error('Your session has expired. Please log in again.');
            setTimeout(() => window.location.href = '/login', 1200);
            return;
        }

        if (xhr.status === 403) {
            toastr.error(xhr.responseJSON?.message || 'Permission denied.');
            return;
        }

        if (xhr.status === 409) {
            toastr.warning(xhr.responseJSON?.message || 'The operation is not allowed.');
            return;
        }

        if (xhr.status === 419) {
            toastr.error('Security token expired. Refreshing the page.');
            setTimeout(() => window.location.reload(), 1200);
            return;
        }

        if (xhr.status === 429) {
            toastr.warning('Too many requests. Please wait and try again.');
            return;
        }

        toastr.error(
            xhr.responseJSON?.message ||
            'Something went wrong. Please try again.'
        );
    },

    showValidationErrors(errors, form) {
        if (!form) {
            toastr.error('Please verify the entered information.');
            return;
        }

        form.find('.is-invalid').removeClass('is-invalid');
        form.find('.invalid-feedback.ajax-error').remove();

        $.each(errors, function (field, messages) {
            const input = form.find(`[name="${field}"]`);

            input.addClass('is-invalid');

            input.after(`
                <div class="invalid-feedback ajax-error">
                    ${messages[0]}
                </div>
            `);
        });

        const firstInvalid = form.find('.is-invalid').first();

        if (firstInvalid.length) {
            firstInvalid.trigger('focus');
        }
    }
};
```

---

## 19. DataTable Behaviour

Use Yajra server-side DataTables.

```javascript
const supplierTable = $('#supplier-table').DataTable({
    processing: true,
    serverSide: true,
    responsive: true,
    stateSave: true,
    searchDelay: 500,
    ajax: {
        url: routes.suppliers.index,
        data: function (data) {
            data.group_id = $('#filter-group').val();
            data.status = $('#filter-status').val();
            data.outstanding_type = $('#filter-outstanding').val();
            data.date_from = $('#filter-date-from').val();
            data.date_to = $('#filter-date-to').val();
        }
    },
    columns: [
        { data: 'checkbox', orderable: false, searchable: false },
        { data: 'supplier' },
        { data: 'group_name' },
        { data: 'mobile' },
        { data: 'last_purchase_at' },
        { data: 'total_purchase' },
        { data: 'outstanding' },
        { data: 'status' },
        { data: 'actions', orderable: false, searchable: false }
    ]
});
```

Filter changes:

```javascript
$(document).on('change', '.supplier-filter', function () {
    supplierTable.ajax.reload();
});
```

After create, update, payment, or delete:

```javascript
supplierTable.ajax.reload(null, false);
```

This must preserve the current page.

---

## 20. Delete Logical Flow

Do not immediately delete important business records.

### Supplier deletion

Before deletion, check:

* Purchases
* Payments
* Returns
* Ledger entries
* Opening balance
* Documents
* Linked item prices

When transactions exist:

```text
Supplier cannot be deleted because transactions are available.

[Deactivate Supplier]
[View Transactions]
```

Use soft deletes.

Deletion flow:

1. User clicks delete.
2. Show confirmation modal.
3. User enters an optional reason.
4. Submit AJAX request.
5. Server checks permission.
6. Server checks dependencies.
7. Delete or reject.
8. Record activity log.
9. Return JSON.
10. Reload DataTable.
11. Refresh KPI cards.
12. Close drawer when necessary.

---

## 21. Permission Logic

Every button and endpoint must check permissions.

Examples:

```text
suppliers.view
suppliers.create
suppliers.update
suppliers.delete
suppliers.export
supplier_payments.create
supplier_ledger.view
purchases.create
purchases.update
purchases.cancel
```

Frontend permission checks are only for UI visibility.

Backend middleware and policies must always enforce access.

Example:

```php
Route::middleware('can.access:suppliers.view')
    ->get('/suppliers', [SupplierController::class, 'index']);
```

Also validate:

* Business location access
* Godown access
* Financial year access
* Module access
* Record ownership or assigned scope

> **See Part 2** for the full user-based shop assignment, godown assignment, and permission override implementation that powers this section.

---

## 22. Financial Transaction Safety

For purchases, payments, returns, stock transfers, and adjustments:

* Use database transactions.
* Lock relevant records when calculating balances.
* Prevent duplicate submissions.
* Generate transaction reference numbers on the server.
* Maintain an immutable ledger trail.
* Do not directly overwrite historical ledger balances.
* Store created-by, updated-by, shop, godown, and financial year.
* Record before-and-after values in activity logs.
* Use idempotency keys for critical AJAX transactions.

Example request header:

```text
X-Idempotency-Key: unique-browser-generated-key
```

This prevents duplicate payments when a user double-clicks or the network retries.

---

## 23. Page-Level Quick Actions

Every module must provide context-aware quick actions.

### Supplier page

```text
Add Supplier
Add Purchase
Make Payment
Purchase Return
Download Ledger
```

### Customer page

```text
Add Customer
New Sale
Receive Payment
Sales Return
Create Delivery
```

### Inventory page

```text
Add Item
Opening Stock
Stock Transfer
Stock Adjustment
Print Barcode
```

### Purchase page

```text
New Purchase
Supplier Payment
Purchase Return
Add Expense
```

Disable actions when setup is incomplete and explain why.

Example:

```text
New Purchase unavailable.
Configure a godown before creating purchases.

[Configure Godown]
```

---

## 24. Responsive Behaviour

### Desktop

* Expanded sidebar
* Full DataTable
* Right-side detail drawer
* Multi-column forms

### Tablet

* Collapsed sidebar
* Horizontally scrollable tables
* Drawer width approximately 70%
* Two-column forms

### Mobile

* Sidebar becomes offcanvas
* KPI cards become horizontally scrollable
* Tables switch to compact card rows
* Detail drawer becomes full screen
* Forms become single column
* Bottom sticky action bar

Mobile sticky actions:

```text
Call
WhatsApp
Purchase
Payment
More
```

---

## 25. Suggested Blade Component Structure

```text
resources/views/
├── layouts/
│   ├── app.blade.php
│   ├── sidebar.blade.php
│   ├── header.blade.php
│   └── footer.blade.php
│
├── components/
│   ├── page-header.blade.php
│   ├── summary-card.blade.php
│   ├── filter-panel.blade.php
│   ├── empty-state.blade.php
│   ├── status-badge.blade.php
│   ├── ajax-modal.blade.php
│   ├── ajax-drawer.blade.php
│   ├── action-dropdown.blade.php
│   └── permission-button.blade.php
│
└── admin/
    └── parties/
        └── suppliers/
            ├── index.blade.php
            ├── _table.blade.php
            ├── _form.blade.php
            ├── _drawer.blade.php
            ├── _summary.blade.php
            └── tabs/
                ├── overview.blade.php
                ├── purchases.blade.php
                ├── payments.blade.php
                ├── returns.blade.php
                ├── ledger.blade.php
                ├── items.blade.php
                ├── addresses.blade.php
                ├── documents.blade.php
                ├── notes.blade.php
                └── activity.blade.php
```

JavaScript structure:

```text
resources/js/
├── core/
│   ├── ajax.js
│   ├── errors.js
│   ├── loader.js
│   ├── modal.js
│   ├── drawer.js
│   └── datatable.js
│
└── modules/
    └── suppliers/
        ├── index.js
        ├── form.js
        ├── payment.js
        ├── drawer.js
        └── ledger.js
```

---

## 26. Final Implementation Requirements

The completed Cholavin ERP interface must:

1. Use a unique design rather than copying the supplied reference.
2. Follow Cholavin maroon and gold branding.
3. Use full-width DataTables with AJAX detail drawers.
4. Provide instant visual feedback for every user action.
5. Use AJAX for all CRUD and operational actions.
6. Reload only affected components.
7. Preserve table filters, pagination, and selected tabs.
8. Use consistent JSON responses.
9. Centralize AJAX and error handling.
10. Validate every operation on both frontend and backend.
11. Protect financial and inventory operations with database transactions.
12. Maintain complete ledger and activity history.
13. Display clear payable, receivable, and advance labels.
14. Provide setup links when required master data is missing.
15. Make the system easy for shop staff and non-technical users.
16. Support permission-based shops, godowns, modules, and actions.
17. Remain responsive on desktop, tablet, and mobile.
18. Never reload the full page after normal successful operations.

---
---

# PART 2 — Authentication & Access Control Module

This module is the entry point of the Cholavin ERP system. It governs login, role-based access control, dynamic shop/godown scoping, permission enforcement, financial-year context, and session/activity auditing — all through the same AJAX-only architecture used across Part 1.

---

## 1. Module Overview

All users log in through a single common login page. After authentication, each user's visible shops, godowns, modules, and actions are determined entirely by permissions assigned by the Super Admin.

Business flow context:

```text
Stock Flow:
Godown 1 / Godown 2 (multiple godowns, multiple stores per godown)
        │
        ▼
   Stock Receipt (Purchase / Transfer In)
        │
        ▼
   Godown Stock Ledger
        │
   ┌────┴─────┐
   ▼          ▼
 Shop 1     Shop 2   (stock transferred out to shops for retail/wholesale billing)
```

A Super Admin can switch between shops and godowns instantly. A restricted user only sees the shops/godowns explicitly assigned to them — the sidebar, dashboard, dropdowns, and DataTables all scope to that assignment automatically.

---

## 2. Objectives

1. Provide secure authentication for all users.
2. Allow Super Admin to create shops and godowns dynamically (unlimited, not hardcoded).
3. Support full role-based access control (RBAC).
4. Restrict data and actions by shop.
5. Restrict data and actions by godown.
6. Restrict data and actions by module and sub-module.
7. Scope every transaction to an active financial year.
8. Give Super Admin an unrestricted, aggregated view across all shops, godowns, and financial years.
9. Maintain a full audit trail of logins, context switches, and permission-sensitive actions.
10. Load every module/menu click through AJAX with an instant skeleton loader — no full page reloads.

---

## 3. User Roles

```text
Super Admin      — full access to every shop, godown, module, and financial year
Business Owner    — access to assigned shops/godowns, all modules within scope
Shop Manager      — access to one or more assigned shops only
Godown Manager    — access to one or more assigned godowns only
Accountant        — access to Accounting, GST & Tax, Reports (read/write per permission)
Billing Staff     — access to Sales / New Sale / Customer Payments only
Delivery Staff    — access to Delivery module only
Auditor           — read-only access across assigned scope
```

Roles are configurable. Super Admin can create custom roles and attach a granular permission set to each.

---

## 4. Permission Model

Permissions are evaluated on four layers, all of which must pass:

```text
1. Module Permission     e.g. suppliers.view, purchases.create
2. Shop Scope            e.g. user_shops table — which shops the user can access
3. Godown Scope          e.g. user_godowns table — which godowns the user can access
4. Financial Year Scope  e.g. user_financial_years table (optional restriction)
```

Example permission keys:

```text
dashboard.view
suppliers.view / .create / .update / .delete / .export
purchases.view / .create / .update / .cancel
sales.view / .create / .update / .cancel
inventory.view / .adjust / .transfer
delivery.view / .assign
accounting.view / .post
gst.view / .file
reports.view / .export
users.manage
roles.manage
settings.manage
shops.manage
godowns.manage
```

Frontend permission checks control button/menu visibility only. Every route is enforced server-side via middleware and policies, exactly as defined in Part 1, Section 21.

```php
Route::middleware(['auth', 'can.access:suppliers.view', 'shop.scope', 'godown.scope'])
    ->get('/suppliers', [SupplierController::class, 'index']);
```

---

## 5. Database Structure (suggested)

```text
users
roles
permissions
role_has_permissions
user_has_roles

shops
godowns
shop_godown            (which godowns feed which shops)

user_shops             (user_id, shop_id)
user_godowns           (user_id, godown_id)
user_financial_years   (user_id, financial_year_id)

financial_years         (id, name, start_date, end_date, is_active)

login_sessions          (user_id, ip_address, device, login_at, logout_at)
activity_logs           (user_id, module, action, before, after, ip_address, created_at)
```

Design rule: shops and godowns must never be hardcoded. Super Admin creates and edits them through `Business Setup → Shops` and `Business Setup → Godowns`, and every new shop/godown is immediately assignable to any user without a code change.

---

## 6. Login Flow

```text
1. User submits email/username + password via AJAX.
2. Server validates credentials.
3. Server checks account status (active, not locked, not expired).
4. Server loads user's roles, permissions, assigned shops, godowns, financial years.
5. Server determines the default context:
     - Default Shop      = user's first assigned shop (or last used, from session)
     - Default Godown     = user's first assigned godown (or last used)
     - Default Financial Year = system's active financial year
6. Server creates session + login_sessions record.
7. Server returns JSON with user profile, permissions, and default context.
8. Frontend stores permission flags for UI rendering (menu visibility, buttons).
9. Frontend redirects to Dashboard.
10. Dashboard loads all widgets via AJAX, scoped to the default context.
```

Login AJAX response:

```json
{
    "success": true,
    "message": "Login successful.",
    "data": {
        "user": { "id": 12, "name": "Ramesh Kumar", "role": "Shop Manager" },
        "permissions": ["dashboard.view", "sales.view", "sales.create"],
        "shops": [{ "id": 1, "name": "Chennai Head Office" }],
        "godowns": [{ "id": 3, "name": "Chennai Central Godown" }],
        "financial_years": [{ "id": 2, "name": "2026–2027", "is_active": true }],
        "default_context": { "shop_id": 1, "godown_id": 3, "financial_year_id": 2 }
    },
    "redirect": "/admin/dashboard"
}
```

Super Admin receives all shops, all godowns, and all financial years in the same payload, plus an `"is_super_admin": true` flag that the frontend uses to unlock the unrestricted views described in Section 9.

---

## 7. Context Switching (Shop / Godown / Financial Year)

This reuses the header context selectors already defined in Part 1, Section 2 (Centre section), governed by the same rules:

```text
1. User changes Shop, Godown, or Financial Year from the header dropdown.
2. Frontend calls AJAX endpoint: POST /admin/context/switch
3. Server re-validates that the user is permitted for the requested shop/godown/financial year.
4. If not permitted → 403 response, dropdown reverts to previous value.
5. If permitted:
     a. Update session context.
     b. Record the switch in activity_logs.
     c. Return the new context + updated badge counts.
6. Frontend refreshes only the affected components:
     - Sidebar badge counts
     - Dashboard widgets
     - Currently open DataTable (reload without full refresh)
7. Show a success toast: "Switched to Chennai Central Godown."
8. No full browser refresh.
```

Godown-to-shop stock visibility follows the same scoping: a user assigned to Shop 1 only sees stock that has moved from a godown into Shop 1's stock ledger, never raw godown stock unless they also hold godown access.

---

## 8. Menu / Module AJAX Loading

Every sidebar click loads the module body via AJAX rather than a full page navigation:

```text
1. User clicks a sidebar menu item (e.g. "Sales Invoices").
2. Sidebar highlights the item and shows an inline loading indicator.
3. Main content area immediately swaps to a skeleton placeholder (no blank white screen).
4. AJAX GET request loads the module's HTML/data.
5. Server checks module permission + shop/godown/financial-year scope before returning data.
6. Response replaces the skeleton with real content.
7. Browser history/URL updates via pushState, so back/forward and refresh still work.
8. Page title, breadcrumb, and command bar (Zone A) update to match the new module.
```

This keeps navigation feeling instant (billing-counter speed) while behaving like normal, bookmarkable pages.

---

## 9. Super Admin Unified View

Super Admin does not choose a single shop/godown context by default — an explicit **"All Shops"** and **"All Godowns"** option is always available in the header selectors, visible only to Super Admin.

When "All Shops" is active:

```text
Dashboard shows:
    - Combined collection across every shop
    - Combined sales, purchases, and outstanding across every shop
    - Customer and supplier totals aggregated across the whole business
    - Shop-wise breakdown table (drill down into any single shop instantly)

Reports support:
    - Cross-shop comparison
    - Cross-godown stock visibility
    - Cross-financial-year historical comparison (read-only for closed years)
```

Super Admin can still narrow to a single shop/godown at any time using the same selector — the aggregated view and the scoped view use the same components, just with a different context flag (`shop_id = null` triggers aggregation server-side).

---

## 10. Product & Billing Behaviour (POS-style, detailed line items)

Modeled on fast-billing POS software (e.g. Petpooja-style counters), applied to rice trading:

```text
New Sale / New Purchase screen shows:
    - Barcode / item search box (autocomplete, AJAX, debounced)
    - Item grid: name, variety, grade, unit, rate, available stock
    - Selected items table:
          Item | Variety | Grade | Unit | Qty | Rate | Discount | Tax | Line Total
    - Live-updating bill summary: Subtotal, Discount, Tax (CGST/SGST/IGST), Round-off, Grand Total
    - Payment split: Cash / Bank / UPI / Credit
    - Print / Save / Save & New shortcuts
```

Stock and price lookups are always scoped to the active shop and its linked godown, so billing staff can never accidentally bill from a godown/shop they don't have stock visibility into.

---

## 11. Session & Audit Logging

Every login, logout, context switch, and permission-sensitive action is logged:

```text
login_sessions: who logged in, from where, when, and when they logged out
activity_logs: module, action, before-value, after-value, shop, godown,
               financial year, IP address, timestamp
```

Super Admin has an `Activity Logs` screen (already listed under **Users & Access** in the Part 1 sidebar) with filters for user, module, shop, godown, date range, and action type — loaded via the same server-side DataTable pattern used elsewhere in the system.

Session timeout, forced logout on password change, and single/multi-device login policy should be configurable in **Settings**.

---

## 12. AJAX Response Contract

Reuses the standard response format defined in Part 1, Section 14 (success / 422 validation / 403 unauthorized / 409 business-rule / 500 server error), so the frontend's single `CholavinAjax` service and `AjaxErrorHandler` handle authentication and access-control endpoints with no additional client-side code.

A 403 from a context-scope violation (e.g. user tries to access a godown they lost access to) should always be treated by the frontend as "revert the UI to the last known-good context and re-fetch permissions," not just a toast.

---

## 13. Users List Page

Route:

```php
GET /admin/users-access/users
```

Follows the same three-zone workspace pattern used for Suppliers in Part 1.

### KPI cards

```text
Total Users
Active Users
Super Admins
Locked / Inactive Accounts
```

### Filters

```text
Search by name, email or mobile
Role
Assigned Shop
Assigned Godown
Status (Active / Inactive / Locked)
```

### User DataTable columns

```text
Checkbox
User (avatar, name, email, mobile)
Role
Assigned Shops
Assigned Godowns
Status
Last Login
Actions
```

`Assigned Shops` and `Assigned Godowns` should render as compact chips (e.g. `Chennai HO +2`) with a tooltip listing the full set.

### Row actions

```text
View
Edit
Shop Access
Godown Access
Permissions
Reset Password
Force Logout
Deactivate
Delete
```

---

## 14. User Detail Drawer

Clicking a user opens the standard right-side AJAX drawer.

Endpoint:

```php
GET /admin/users-access/users/{user}/summary
```

### Drawer tabs

```text
Overview
Roles
Shop Access
Godown Access
Permissions
Financial Year Access
Activity
```

Each tab loads its content via AJAX only when opened, exactly like the Supplier drawer tabs in Part 1.

---

## 15. User-Based Shop Assignment

### 15.1 Shop Access tab

Loaded via AJAX:

```php
GET /admin/users-access/users/{user}/shops
```

Displays every shop created under **Business Setup → Shops** as a checkbox list (not hardcoded — always pulled live):

```text
[✔] Chennai Head Office
[✔] Coimbatore Branch
[ ] Madurai Branch
[ ] Trichy Branch

Default Shop:  ( • ) Chennai Head Office   ( ) Coimbatore Branch
```

Rules:

* A user must have at least one assigned shop, unless they are Super Admin (who implicitly has all shops).
* One assigned shop must be marked **Default Shop** — this is what loads immediately after login.
* Super Admin sees a locked "All Shops" indicator instead of checkboxes, since their access is unrestricted by definition.

### 15.2 Save flow

```text
1. Admin toggles checkboxes and picks a default shop.
2. Click "Save Shop Access".
3. AJAX POST /admin/users-access/users/{user}/shops
4. Server validates the requesting admin has `users.manage` permission.
5. Server diffs old assignment vs new assignment.
6. Update `user_shops` table (insert added, remove unchecked).
7. If the user's current default shop was removed, reassign default automatically
   and warn the admin in the response message.
8. Write activity_log entry:
      before: ["Chennai Head Office"]
      after:  ["Chennai Head Office", "Coimbatore Branch"]
9. If the user has an active session, flag it `context_stale = true`
   so their next request re-pulls shop access before continuing.
10. Return JSON success.
11. Refresh the user's row chips and the drawer without closing it.
12. Show toast: "Shop access updated for Ramesh Kumar."
```

### 15.3 Database

```text
shops                 (id, name, code, address, status, ...)
user_shops             (id, user_id, shop_id, is_default, assigned_by, assigned_at)
```

---

## 16. User-Based Godown Assignment

### 16.1 Godown Access tab

Loaded via AJAX:

```php
GET /admin/users-access/users/{user}/godowns
```

Godowns shown are filtered to those linked to the user's already-assigned shops (via `shop_godown`), so an admin cannot accidentally grant godown access disconnected from any shop the user can bill from — unless the user's role is explicitly a **Godown Manager**, in which case all godowns are selectable regardless of shop linkage.

```text
Linked to Chennai Head Office:
[✔] Chennai Central Godown
[ ] Chennai Overflow Godown

Linked to Coimbatore Branch:
[ ] Coimbatore Godown
```

### 16.2 Save flow

Identical pattern to Shop Access (Section 15.2): AJAX POST, diff, update `user_godowns`, activity log, mark session stale, refresh chips, toast.

```php
POST /admin/users-access/users/{user}/godowns
```

### 16.3 Database

```text
godowns                (id, name, code, shop_id_primary, status, ...)
shop_godown             (shop_id, godown_id)
user_godowns             (id, user_id, godown_id, assigned_by, assigned_at)
```

---

## 17. User-Based Permissions

Permissions work on two layers so day-to-day management stays simple, while still allowing per-user exceptions:

```text
Layer 1 — Role Permissions   (baseline, inherited from the user's role)
Layer 2 — User Overrides     (per-user grant or revoke on top of the role)
```

Effective permission for a user is calculated as:

```text
Effective = (Role Permissions ∪ User Grants) − User Revokes
```

This means an Accountant role can be denied `reports.export` for one specific user without creating a whole new role, and a Billing Staff role can be granted `sales.cancel` for one trusted senior cashier without affecting the rest of the team.

### 17.1 Permissions tab

Loaded via AJAX:

```php
GET /admin/users-access/users/{user}/permissions
```

Grouped by module, matching the Part 1 sidebar structure, with a three-state toggle per permission:

```text
Parties
    View Suppliers        [ Inherited ✔ ]  ( Grant )  ( Revoke )
    Create Suppliers       [ Inherited ✔ ]  ( Grant )  ( Revoke )
    Delete Suppliers        [ Inherited ✘ ]  ( Grant )  ( Revoke )

Sales
    New Sale                [ Inherited ✔ ]  ( Grant )  ( Revoke )
    Cancel Sale               [ Inherited ✘ ]  ( Grant ✔ )  ( Revoke )   ← user override
    ...

Accounting
    View Ledger              [ Inherited ✔ ]  ( Grant )  ( Revoke )
    Post Journal Entry        [ Inherited ✔ ]  ( Grant )  ( Revoke ✔ )   ← user override
```

* `Inherited` shows the role's default state and is always visible for context.
* `Grant` and `Revoke` are mutually exclusive per-permission overrides; selecting neither means "use role default."
* A visual badge (e.g. an orange dot) marks any row with an active override, so admins can see at a glance which users deviate from their role.

### 17.2 Save flow

```text
1. Admin toggles overrides across one or more modules.
2. Click "Save Permissions".
3. AJAX POST /admin/users-access/users/{user}/permissions
4. Server validates `users.manage` + `roles.manage` as applicable.
5. Server replaces the user's override set (user_permissions table) with the submitted diff.
6. Server recalculates the user's effective permission set.
7. Write activity_log entry with the full before/after override diff.
8. Mark the user's active session `permissions_stale = true`.
9. Return JSON success with the recalculated effective permission list.
10. Frontend updates the override badges without closing the drawer.
11. Show toast: "Permissions updated for Ramesh Kumar."
```

### 17.3 Database

```text
permissions              (id, key, module, label)          e.g. sales.cancel, "Sales", "Cancel Sale"
roles                     (id, name)
role_has_permissions       (role_id, permission_id)
user_has_roles              (user_id, role_id)
user_permissions             (id, user_id, permission_id, type ENUM('grant','revoke'), assigned_by, assigned_at)
```

### 17.4 Runtime enforcement

Every request resolves effective permissions server-side — never trusts a cached frontend permission list for anything beyond menu/button visibility:

```php
// Pseudo-logic inside a Policy or Gate
$rolePermissions = $user->roles->flatMap->permissions->pluck('key');
$grants = $user->overrides()->where('type', 'grant')->pluck('permission.key');
$revokes = $user->overrides()->where('type', 'revoke')->pluck('permission.key');

$effective = $rolePermissions->merge($grants)->diff($revokes);

return $effective->contains('sales.cancel');
```

---

## 18. Session Freshness on Access Changes

Because shop, godown, and permission edits can happen while a user is actively logged in, every authenticated AJAX request should carry a lightweight context version check:

```text
1. Response headers include: X-Access-Version: {hash of user's shops+godowns+permissions}
2. Frontend compares this to the version stored at login.
3. If different:
      a. Silently re-fetch /admin/users-access/me/context
      b. Update sidebar, dropdowns, and cached permission flags
      c. If the user is currently viewing a shop/godown/module they no longer
         have access to, redirect to Dashboard with an information toast:
         "Your access was updated by an administrator."
4. No forced logout unless the account itself was deactivated or locked.
```

This keeps access changes effective almost immediately without requiring the affected user to log out and back in.

---
---

# Combined Final Requirements Checklist

The completed Cholavin ERP system (Part 1 + Part 2 together) must:

1. Use a unique design rather than copying the supplied reference, in Cholavin maroon and gold branding.
2. Use full-width DataTables with AJAX detail drawers across every module.
3. Provide instant visual feedback (button states, skeletons, toasts) for every user action.
4. Use AJAX for all CRUD, filter, status-change, and transaction operations — never a full page reload after a normal successful action.
5. Use the standard JSON response contract and centralized error handler everywhere, including auth and access-control endpoints.
6. Protect financial and inventory operations with database transactions, idempotency keys, and immutable ledger trails.
7. Enforce every permission on both frontend (visibility) and backend (middleware/policy) — with shop, godown, and financial-year scope checked on every request.
8. Support user-based shop assignment, user-based godown assignment, and user-based permission overrides on top of roles.
9. Give Super Admin an unrestricted, aggregated "All Shops / All Godowns" view alongside normal scoped views.
10. Keep a full audit trail: logins, context switches, CRUD actions, and permission changes.
11. Propagate access changes to already-logged-in users automatically, without forcing logout.
12. Remain fully responsive across desktop, tablet, and mobile, including offcanvas navigation and full-screen drawers on mobile.