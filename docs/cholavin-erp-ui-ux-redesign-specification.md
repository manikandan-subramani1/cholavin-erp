Act as a Senior Laravel Architect, Senior ERP Product Engineer, Senior UI/UX Designer, Database Architect, and Performance Optimization Engineer.

You are working on an existing Laravel 12 project called:

CHOLAVIN ERP

This is a complete rice wholesale, retail, billing, inventory, accounting, auditing, delivery, and multi-location ERP system.

Use the attached UI reference image only as a design direction. Do not copy it pixel-for-pixel. Recreate the same level of quality with a cleaner, more efficient, more maintainable implementation.

The system hierarchy is:

Company → Godown → Shop

One company can have multiple godowns.
One godown can have multiple shops.
Every shop belongs to exactly one godown.

The project must support:

- Multi-godown management
- Multi-shop management
- User-wise godown assignment
- User-wise shop assignment
- Role-based access control
- Module-wise permission
- Action-wise permission
- Financial-year filtering
- Super Admin consolidated access
- AJAX-only actions
- Fast billing
- Inventory control
- Accounting and audit logs

==================================================
1. PRIMARY OBJECTIVE
==================================================

Redesign and improve the complete Cholavin ERP project so that it becomes:

- User friendly
- Fast
- Responsive
- Easy for non-technical users
- Suitable for billing staff
- Suitable for godown managers
- Suitable for shop managers
- Suitable for accountants
- Suitable for auditors
- Easy to maintain
- Secure
- Scalable
- AJAX powered
- Optimized for quick daily usage

Do not unnecessarily rewrite working backend logic.

First inspect the existing project carefully.

Before modifying anything, analyze:

- Existing routes
- Existing controllers
- Existing models
- Existing migrations
- Existing Blade files
- Existing JavaScript
- Existing AJAX endpoints
- Existing permissions
- Existing authentication
- Existing database relationships
- Existing DataTables
- Existing business logic

Preserve existing logic where it is correct.

Refactor only where necessary.

Do not break:

- Existing routes
- Existing database data
- Existing controller methods
- Existing permissions
- Existing AJAX endpoints
- Existing validation
- Existing reports
- Existing billing flow

==================================================
2. TECHNOLOGY STACK
==================================================

Use:

- Laravel 12
- PHP 8.2+
- MySQL
- Blade
- Bootstrap 5
- jQuery
- AJAX
- Yajra DataTables
- jQuery Validate
- Select2
- Flatpickr
- SweetAlert2 or jQuery Confirm
- Toastr
- mPDF
- Laravel Excel

Do not introduce React, Vue, Inertia, Livewire, or another frontend framework.

Do not convert the project into an SPA framework.

Use Laravel Blade with AJAX partial loading.

==================================================
3. BRAND DESIGN
==================================================

Use Cholavin branding.

Primary maroon:

#800020

Dark maroon:

#4A0012

Gold:

#D4AF37

Light gold:

#FFF8E1

Page background:

#F5F6FA

White cards:

#FFFFFF

Main text:

#242424

Muted text:

#747474

Success:

#15803D

Warning:

#D97706

Danger:

#B42318

Use the existing logo:

public/frontend/assets/img/logo/logo-hm62.png

The UI must feel:

- Premium
- Corporate
- Clean
- Modern
- Friendly
- Fast
- Responsive
- Touch friendly
- Easy for billing counters
- Easy for staff with limited computer knowledge

Use:

- Rounded cards
- Soft shadows
- Clear spacing
- Large click areas
- Consistent icon styles
- Strong visual hierarchy
- Clear form labels
- Helpful empty states
- Skeleton loading
- Button loading states
- Toast feedback
- Minimal unnecessary borders

==================================================
4. GLOBAL APPLICATION LAYOUT
==================================================

Create one consistent layout for all modules.

The layout must contain:

1. Collapsible left sidebar
2. Fixed top header
3. Dynamic main content area
4. Global quick actions
5. Godown selector
6. Shop selector
7. Financial-year selector
8. Notification center
9. User profile
10. Global search
11. AJAX page loader
12. Breadcrumb
13. Page title
14. Summary cards
15. Filter panel
16. Main table or workspace
17. Right-side drawer
18. Bottom quick actions where required

Use this layout structure:

- Left Sidebar
- Top Header
- Page Command Bar
- Summary Section
- Smart Filter Section
- Main Content Section
- Right Drawer
- Activity Timeline
- Quick Action Bar

==================================================
5. SIDEBAR STRUCTURE
==================================================

Create a workspace-based sidebar.

Menu:

Dashboard

Customer Workspace
    Customers
    Customer Groups
    Outstanding
    Customer Payments
    Customer Ledger
    Sales History
    Sales Returns
    Delivery History

Supplier Workspace
    Suppliers
    Supplier Groups
    Supplier Payable
    Supplier Payments
    Supplier Ledger
    Purchase History
    Purchase Returns

Item and Product Master
    Items
    Categories
    Brands
    Rice Varieties
    Grades
    Units
    Price Lists
    Barcodes
    Opening Stock

Sales Workspace
    New Bill
    Sales Invoices
    Sales Returns
    Quotations
    Delivery Challans
    Customer Payments

Purchase Workspace
    New Purchase
    Purchase Invoices
    Purchase Returns
    Supplier Payments
    Expenses

Inventory Workspace
    Stock Overview
    Godown Stock
    Shop Stock
    Stock Transfer
    Stock Adjustment
    Damaged Stock
    Low Stock
    Stock History

Accounting Workspace
    Cash Book
    Bank Book
    Customer Ledger
    Supplier Ledger
    Payment In
    Payment Out
    Journal Entries
    Day Book
    Profit and Loss

Delivery Workspace
    Delivery Orders
    Delivery Routes
    Drivers
    Vehicles
    Proof of Delivery
    Delivery Tracking

Reports and Analytics
    Sales Reports
    Purchase Reports
    Stock Reports
    Profit Reports
    Outstanding Reports
    GST Reports
    Audit Reports

Business Setup
    Company
    Godowns
    Shops
    Financial Years
    Taxes
    Payment Methods
    Invoice Series
    Printer Settings

Users and Access
    Users
    Roles
    Permissions
    Godown Access
    Shop Access
    Activity Logs
    Login Logs

Settings

Sidebar requirements:

- Show only authorized menus
- Open one submenu at a time
- Highlight current module
- Support collapsed mode
- Save state in localStorage
- Show badges for low stock, pending delivery, overdue payments, and pending approvals
- Load badge counts using AJAX
- Add pinned menu items
- Add recent pages
- Add quick-create menu

==================================================
6. COMPANY, GODOWN, AND SHOP ACCESS
==================================================

Hierarchy:

Company → Godown → Shop

Super Admin:

- Can access all godowns
- Can access all shops
- Can access all modules
- Can access all reports
- Can view consolidated company data
- Can switch godown and shop at any time

Normal user:

- Can see only assigned godowns
- Can see only assigned shops
- Can see only assigned modules
- Can perform only assigned actions

Final access must be calculated using:

Role Permission
+
Godown Permission
+
Shop Permission
+
Module Permission
+
Action Permission
+
Financial Year Permission
=
Final Access

Every controller request must validate:

- User authentication
- User status
- Active company
- Active godown
- Active shop
- Active financial year
- Module permission
- Action permission
- Record scope

Do not depend only on hidden buttons.

Backend validation is mandatory.

==================================================
7. AUTHENTICATION MODULE
==================================================

Create one common login page for all users.

Route:

/login

Fields:

- Username, email, or mobile
- Password
- Remember me
- Forgot password
- Login button

Optional support:

- CAPTCHA
- OTP
- Two-factor authentication
- Device verification
- Password expiry
- Account lock
- Login attempt limit

Login flow:

1. Validate credentials
2. Check user active status
3. Load roles
4. Load godown access
5. Load shop access
6. Load module permissions
7. Load action permissions
8. Load financial years
9. Create login session
10. Log IP, device, browser, and login time
11. Open assigned workspace

If the user has only one godown and one shop:

- Select automatically

If the user has multiple assigned locations:

- Show location selection

Login must use AJAX.

Show:

Signing in...

After success:

Login successful. Loading workspace...

==================================================
8. TOP HEADER
==================================================

Add:

- Sidebar toggle
- Global search
- Quick action button
- Notification icon
- Calculator
- Godown selector
- Shop selector
- Financial-year selector
- Fullscreen
- Theme switcher
- User profile
- Logout

Changing godown, shop, or financial year must:

1. Use AJAX
2. Validate access
3. Update session
4. Refresh page data
5. Refresh summary cards
6. Refresh DataTables
7. Refresh badges
8. Preserve current module where possible
9. Show toast message

No full page reload.

==================================================
9. UNIVERSAL PAGE DESIGN
==================================================

Every listing page must contain:

1. Breadcrumb
2. Page title
3. Description
4. Current godown tag
5. Current shop tag
6. Main quick-action buttons
7. KPI summary cards
8. Smart filter panel
9. Server-side DataTable
10. Bulk actions
11. Right-side detail drawer
12. Empty states
13. Skeleton loading
14. Toast notifications

Use right-side drawers for:

- View
- Create
- Edit
- Quick payment
- Quick purchase
- Quick sale
- Quick stock transfer
- Notes
- Documents

Use small modals only for:

- Delete confirmation
- OTP
- Password
- Small confirmation
- Approval rejection

==================================================
10. MODULE 01 - DASHBOARD
==================================================

Create three dashboard views:

Super Admin Dashboard
Godown Dashboard
Shop Dashboard

Super Admin dashboard cards:

- Today’s Sales
- Today’s Purchases
- Today’s Collection
- Today’s Expenses
- Gross Profit
- Net Profit
- Customer Receivable
- Supplier Payable
- Cash Balance
- Bank Balance
- Stock Value
- Low Stock
- Pending Transfers
- Pending Deliveries

Charts:

- Sales trend
- Purchase trend
- Collection trend
- Profit trend
- Shop comparison
- Godown comparison
- Top products
- Top customers
- Top suppliers
- Receivable ageing
- Payable ageing

Quick actions:

- New Sale
- New Purchase
- Receive Payment
- Make Payment
- Stock Transfer
- New Customer
- New Supplier
- New Item
- View Reports

Dashboard data must load through AJAX.

Cards must support drill-down.

Clicking a card should apply filters and open the related page.

==================================================
11. MODULE 02 - LOGIN AND AUTHENTICATION
==================================================

Design:

- Clean branded login card
- Logo at top
- Minimal form
- Password visibility toggle
- Remember me
- Forgot password
- Support contact
- Version number
- Security message
- Mobile responsive

Add screens for:

- Login
- Forgot password
- Reset password
- OTP verification
- 2FA verification
- Device approval
- Account locked
- Session expired

==================================================
12. MODULE 03 - CUSTOMER WORKSPACE
==================================================

Customer listing page:

Summary cards:

- Total customers
- Active customers
- Receivable
- Overdue
- Sales this month
- Collections this month
- Credit-limit exceeded

Filters:

- Name
- Mobile
- Code
- Customer group
- Shop
- Outstanding status
- Credit status
- State
- Date range

DataTable columns:

- Customer
- Code
- Group
- Mobile
- Location
- Total sales
- Paid
- Receivable
- Credit limit
- Last sale
- Status
- Actions

Quick actions:

- Add Customer
- New Sale
- Receive Payment
- Sales Return
- Create Delivery
- View Ledger
- Download Statement
- WhatsApp
- Call
- Add Note

Customer detail drawer tabs:

- Overview
- Sales
- Payments
- Returns
- Outstanding
- Ledger
- Delivery
- Documents
- Notes
- Activity

==================================================
13. MODULE 04 - SUPPLIER WORKSPACE
==================================================

Supplier summary cards:

- Total suppliers
- Active suppliers
- Total purchases
- Supplier payable
- Overdue payable
- Payments this month
- Available credit

Filters:

- Supplier name
- Code
- Mobile
- Group
- GSTIN
- Status
- Payable condition
- Credit status
- Godown
- Purchase date range

DataTable columns:

- Supplier
- Code
- Group
- Mobile
- GSTIN
- Last purchase
- Total purchases
- Paid
- Payable
- Credit limit
- Status
- Actions

Quick actions:

- Add Supplier
- New Purchase
- Make Payment
- Purchase Return
- View Ledger
- Download Statement
- WhatsApp
- Call
- Upload Document
- Add Reminder

Supplier drawer tabs:

- Overview
- Purchases
- Payments
- Returns
- Ledger
- Items
- Addresses
- Documents
- Notes
- Activity

==================================================
14. MODULE 05 - ITEM AND PRODUCT MASTER
==================================================

Create item listing with:

Summary cards:

- Total items
- Active items
- Low stock
- Out of stock
- Total stock value
- Without image
- Without barcode

Filters:

- Search by name, code, or barcode
- Category
- Rice variety
- Grade
- Brand
- Unit
- Stock status
- Active status

DataTable columns:

- Image
- Item code
- Item name
- Category
- Variety
- Grade
- Unit
- Purchase price
- Selling price
- Godown stock
- Shop stock
- Barcode
- Status
- Actions

Item detail drawer tabs:

- Overview
- Pricing
- Stock
- Stock history
- Purchase history
- Sales history
- Images
- Barcode
- Documents
- Audit

Quick actions:

- Add Item
- Add Opening Stock
- Print Barcode
- Adjust Stock
- Transfer Stock
- Update Price
- Upload Image
- Duplicate Item

==================================================
15. MODULE 06 - SALES BILLING
==================================================

Create a Petpooja-style fast billing screen with unique Cholavin design.

Layout:

Left:

- Categories
- Rice varieties
- Grades
- Favourite items
- Recently used items

Center:

- Product search
- Barcode search
- Product cards
- Product image
- Item name
- Grade
- Unit
- Selling price
- Available shop stock
- Quick-add button

Right:

- Customer selector
- Add customer
- Cart
- Quantity
- Rate
- Discount
- Tax
- Charges
- Delivery charge
- Round-off
- Grand total
- Payment button

Bottom quick actions:

- Hold Bill
- Resume Bill
- Recent Bills
- Quotation
- Delivery
- Save
- Save and Print
- Save and Pay

Payment methods:

- Cash
- UPI
- Card
- Bank
- Credit
- Split payment

Billing flow:

1. Select customer
2. Search or scan item
3. Add item
4. Validate shop stock
5. Review cart
6. Apply discount or charges
7. Hold, save, or pay
8. Receive payment
9. Generate invoice
10. Reduce shop stock
11. Update customer ledger
12. Update accounting
13. Print or share invoice

Add keyboard shortcuts:

- F1 New Bill
- F2 Hold Bill
- F3 Recent Bills
- F4 Customer
- F5 Item Search
- F6 Payment
- F7 Save
- F8 Print
- Escape Clear Cart

All billing actions must use AJAX.

Use database transactions for final save.

Prevent duplicate bills using idempotency keys.

==================================================
16. MODULE 07 - SALES INVOICES
==================================================

Summary cards:

- Today’s invoices
- Paid invoices
- Partial invoices
- Due invoices
- Overdue amount
- Cancelled invoices

Filters:

- Invoice number
- Customer
- Shop
- Status
- Payment status
- Date range
- Amount range
- Created by

DataTable columns:

- Invoice number
- Date
- Customer
- Shop
- Total
- Paid
- Balance
- Due date
- Payment status
- Delivery status
- Created by
- Actions

Quick actions:

- View
- Edit
- Print
- Download PDF
- Share WhatsApp
- Receive Payment
- Sales Return
- Duplicate
- Cancel

==================================================
17. MODULE 08 - PURCHASE MANAGEMENT
==================================================

Purchase screen:

- Supplier selector
- Supplier invoice number
- Purchase date
- Due date
- Godown
- Item search
- Barcode scan
- Item rows
- Quantity
- Unit
- Rate
- Discount
- Tax
- Freight
- Loading charge
- Other charges
- Grand total
- Paid amount
- Balance

Flow:

1. Select supplier
2. Select godown
3. Add items
4. Review quantity and rate
5. Apply tax and charges
6. Save purchase
7. Increase godown stock
8. Create supplier ledger
9. Create accounting entry
10. Print or download purchase

Quick actions:

- New Purchase
- Supplier Payment
- Purchase Return
- Add Expense
- Download PDF
- Attach Invoice

==================================================
18. MODULE 09 - STOCK TRANSFER
==================================================

Support:

- Godown to Shop
- Shop to Shop
- Shop to Godown
- Godown to Godown

Fields:

- Transfer number
- Transfer date
- Source godown
- Source shop
- Destination godown
- Destination shop
- Item
- Available quantity
- Transfer quantity
- Unit
- Transfer cost
- Transport charge
- Remarks
- Requested by
- Approved by
- Received by

Statuses:

- Draft
- Requested
- Approved
- Dispatched
- In Transit
- Partially Received
- Received
- Rejected
- Cancelled

Flow:

1. Create request
2. Validate stock
3. Submit
4. Approve
5. Dispatch
6. Reduce source stock
7. Receive
8. Increase destination stock
9. Create stock-ledger entries
10. Log audit history

==================================================
19. MODULE 10 - INVENTORY AND STOCK
==================================================

Summary cards:

- Total items
- Total quantity
- Stock value
- Low stock
- Out of stock
- Over stock
- Damaged stock
- Pending transfer

Filters:

- Godown
- Shop
- Category
- Variety
- Grade
- Stock status
- Value range
- Item search

Columns:

- Item
- Category
- Grade
- Godown stock
- Shop stock
- Total stock
- Reorder level
- Stock value
- Last movement
- Status
- Actions

Quick actions:

- Transfer
- Adjust
- Opening Stock
- Damage Entry
- Print Barcode
- Stock History
- Export

==================================================
20. MODULE 11 - PARTY LEDGER
==================================================

Support:

- Customer ledger
- Supplier ledger

Summary cards:

- Opening balance
- Total debit
- Total credit
- Closing balance
- Due amount
- Advance amount

Filters:

- Party
- Party type
- Shop
- Date range
- Transaction type
- Debit or credit

Columns:

- Date
- Reference
- Transaction type
- Description
- Debit
- Credit
- Running balance
- Created by
- Actions

Quick actions:

- Add Payment
- Add Adjustment
- Download Statement
- Print
- WhatsApp
- Email
- Add Note

==================================================
21. MODULE 12 - PAYMENT COLLECTION
==================================================

Support:

- Receive customer payment
- Make supplier payment
- Advance payment
- Invoice allocation
- Partial allocation
- Multiple invoice allocation
- Payment proof
- Receipt print

Fields:

- Party
- Current balance
- Payment date
- Amount
- Payment method
- Cash or bank account
- Reference number
- Discount
- TDS
- Adjustment
- Notes
- Attachment
- Reminder date

Flow:

1. Select party
2. Fetch balance
3. Enter amount
4. Allocate invoices
5. Preview new balance
6. Save payment
7. Update ledger
8. Update invoice balances
9. Create accounting transaction
10. Generate receipt

==================================================
22. MODULE 13 - DELIVERY MANAGEMENT
==================================================

Summary cards:

- Pending deliveries
- Out for delivery
- Delivered
- Failed
- Rescheduled
- Delivery charges

Filters:

- Shop
- Delivery status
- Driver
- Vehicle
- Route
- Date range
- Customer

Columns:

- Delivery number
- Invoice
- Customer
- Address
- Driver
- Vehicle
- Route
- Amount
- Status
- Scheduled time
- Actions

Drawer tabs:

- Overview
- Items
- Address
- Driver
- Timeline
- Proof of delivery
- Notes
- Activity

Quick actions:

- Assign Driver
- Start Delivery
- Mark Delivered
- Reschedule
- Add POD
- Print Delivery Note
- WhatsApp Customer

==================================================
23. MODULE 14 - REPORTS AND ANALYTICS
==================================================

Provide:

- Sales reports
- Purchase reports
- Stock reports
- Profit reports
- Customer outstanding
- Supplier payable
- Shop performance
- Godown performance
- GST reports
- Audit reports

Every report must include:

- KPI summary
- Charts
- Filters
- Comparison
- DataTable
- Drill-down
- Export Excel
- Export PDF
- Print

Use date presets:

- Today
- Yesterday
- This week
- This month
- Last month
- This financial year
- Custom

==================================================
24. MODULE 15 - FINANCIAL DASHBOARD
==================================================

Cards:

- Cash in hand
- Bank balance
- Receivable
- Payable
- Gross profit
- Net profit
- Expenses
- Collections

Charts:

- Cash flow
- Income vs expense
- Receivable ageing
- Payable ageing
- Profit trend
- Payment-method split

Quick actions:

- Receive Payment
- Make Payment
- Add Expense
- Journal Entry
- Cash Transfer
- Bank Transfer
- View Ledger

==================================================
25. MODULE 16 - USER AND ROLE MANAGEMENT
==================================================

Users page:

Columns:

- User
- Role
- Assigned godowns
- Assigned shops
- Modules
- Last login
- Status
- Actions

User form sections:

- Personal details
- Login credentials
- Role
- Godown access
- Shop access
- Module access
- Action permissions
- Financial-year access
- Approval limits
- Login restrictions

Roles page:

- Role name
- Description
- User count
- Permission count
- Status

Permission matrix:

Rows:

- Modules

Columns:

- View
- Create
- Update
- Delete
- Export
- Approve
- Cancel
- Print

==================================================
26. MODULE 17 - GODOWN AND SHOP MANAGEMENT
==================================================

Godown fields:

- Godown name
- Godown code
- Company
- Address
- Contact person
- Mobile
- Manager
- Status

Shop fields:

- Shop name
- Shop code
- Parent godown
- Address
- Contact person
- Mobile
- Manager
- GSTIN
- Invoice series
- Printer
- Status

Godown drawer tabs:

- Overview
- Shops
- Stock
- Transfers
- Users
- Expenses
- Reports
- Activity

Shop drawer tabs:

- Overview
- Sales
- Inventory
- Customers
- Collections
- Expenses
- Users
- Settings
- Activity

==================================================
27. MODULE 18 - SETTINGS AND CONFIGURATION
==================================================

Create setting cards for:

- Company Settings
- Business Details
- Godown Settings
- Shop Settings
- GST and Tax
- Invoice Settings
- Number Series
- Payment Methods
- Printer Settings
- Thermal Printer
- A4 Printer
- Cash Drawer
- Email
- SMS
- WhatsApp
- Notifications
- Financial Year
- Backup
- Restore
- Audit
- Security
- Theme
- Language
- Date and Currency Format

Use card-based settings navigation.

==================================================
28. AJAX NAVIGATION
==================================================

All menu clicks must use AJAX partial loading.

Flow:

1. User clicks menu
2. Prevent normal page navigation
3. Show top progress bar or content skeleton
4. Fetch Blade partial using AJAX
5. Replace only #app-content
6. Update browser URL using History API
7. Update active sidebar
8. Initialize module JavaScript
9. Restore filters
10. Hide loader

Use browser back and forward support.

Do not reload header or sidebar.

==================================================
29. STANDARD AJAX RESPONSE
==================================================

Success:

{
    "success": true,
    "message": "Saved successfully.",
    "data": {},
    "refresh": {
        "datatable": true,
        "summary": true,
        "drawer": false
    },
    "redirect": null
}

Validation error:

HTTP 422

{
    "success": false,
    "message": "Please correct the highlighted fields.",
    "errors": {}
}

Forbidden:

HTTP 403

{
    "success": false,
    "message": "You do not have permission to perform this action."
}

Conflict:

HTTP 409

{
    "success": false,
    "message": "The operation cannot be completed.",
    "error_code": "BUSINESS_RULE_FAILED"
}

Server error:

HTTP 500

{
    "success": false,
    "message": "The operation could not be completed.",
    "reference": "ERR-UNIQUE-ID"
}

==================================================
30. BACKEND ARCHITECTURE
==================================================

Use:

- Form Request validation
- Policies
- Middleware
- Services
- Repositories only where useful
- Database transactions
- Eloquent relationships
- Query scopes
- Helper services
- Activity logs
- Soft deletes
- Server-side DataTables
- Queues for slow operations
- Events and listeners where needed

Suggested structure:

app/
    Http/
        Controllers/
        Middleware/
        Requests/
    Models/
    Policies/
    Services/
    Repositories/
    Actions/
    DTOs/
    Events/
    Listeners/
    Jobs/
    Support/

Do not overengineer.

Keep controllers thin.

Move complex business logic to services.

==================================================
31. DATABASE SAFETY
==================================================

For sales, purchases, payments, returns, stock adjustments, and transfers:

- Use DB transactions
- Lock stock rows when required
- Prevent duplicate submission
- Use server-generated numbers
- Use idempotency keys
- Record created_by
- Record updated_by
- Record company_id
- Record godown_id
- Record shop_id
- Record financial_year_id
- Record before and after values
- Maintain immutable ledger entries
- Never directly overwrite historical ledger balances

==================================================
32. PERFORMANCE REQUIREMENTS
==================================================

Optimize for:

- Fast first render
- Fast AJAX navigation
- Server-side pagination
- Database indexing
- Eager loading
- Debounced searches
- Lazy-loaded tabs
- Lazy-loaded drawers
- Cached permission checks
- Cached dashboard summaries
- Minimal JavaScript duplication
- Bundled assets
- Image optimization
- Avoid N+1 queries
- Avoid loading unnecessary relations
- Avoid returning full HTML when only JSON is required

Target:

- Standard AJAX page load under 2 seconds
- Table filtering under 1.5 seconds
- Drawer load under 1 second where possible
- Billing item search under 500 ms
- No duplicate financial transactions

==================================================
33. RESPONSIVE REQUIREMENTS
==================================================

Desktop:

- Expanded sidebar
- Full-width table
- Right drawer
- Multi-column forms

Tablet:

- Collapsed sidebar
- Two-column forms
- Horizontal table scrolling
- Wider drawer

Mobile:

- Offcanvas sidebar
- Full-screen drawer
- Single-column forms
- Card-style table rows
- Sticky bottom actions
- Large touch targets

==================================================
34. ACCESSIBILITY
==================================================

Implement:

- Proper labels
- Keyboard navigation
- Visible focus states
- Sufficient contrast
- ARIA labels
- Accessible dropdowns
- Error messages near fields
- No color-only meaning
- Keyboard shortcuts for billing
- Touch-friendly buttons

==================================================
35. IMPLEMENTATION RULES
==================================================

Before coding each module:

1. Inspect existing code
2. List existing routes
3. List existing files to modify
4. Identify existing logic to preserve
5. Identify missing functionality
6. Create a short implementation plan
7. Implement module by module
8. Test after each module
9. Do not rewrite unrelated files
10. Do not remove existing features

When changing UI:

- Keep route names
- Keep field names
- Keep validation names
- Keep AJAX URLs
- Keep DataTable column keys where possible
- Keep permissions
- Keep existing model relationships

When backend changes are needed:

- Explain why
- Add migration safely
- Preserve existing data
- Add indexes where needed
- Add rollback support

==================================================
36. TESTING REQUIREMENTS
==================================================

For every module test:

- Authorized user
- Unauthorized user
- Godown access
- Shop access
- Financial-year access
- Create
- Update
- Delete or deactivate
- Validation errors
- Duplicate submission
- AJAX error response
- Empty state
- Pagination
- Filters
- Mobile responsiveness
- Audit logs

For critical financial modules, add automated tests where practical.

==================================================
37. FINAL EXPECTED OUTPUT
==================================================

For each module, provide:

1. Files analyzed
2. Existing issues found
3. Proposed UI structure
4. Backend changes required
5. Database changes required
6. Routes used
7. Permissions used
8. AJAX endpoints
9. Blade files
10. JavaScript files
11. Validation rules
12. Business rules
13. Testing checklist
14. Completed implementation summary

Start with:

Phase 1:
- Global layout
- Sidebar
- Header
- AJAX page loader
- Common components
- Permission-aware menu
- Godown, shop, and financial-year selectors

Then continue:

Phase 2:
- Authentication
- Dashboard
- Customer Workspace
- Supplier Workspace

Phase 3:
- Item Master
- Billing
- Sales Invoices
- Purchase

Phase 4:
- Stock Transfer
- Inventory
- Ledger
- Payments

Phase 5:
- Delivery
- Reports
- Finance
- Users and Roles
- Godown and Shop Management
- Settings

Do not generate only sample code.

Implement the changes directly in the existing project after analysis.

Use the attached image as a UI reference and build a more efficient, production-ready version with complete backend functionality.