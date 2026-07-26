
Cholavin ERP — Modules 02–18 UI/UX & Codex Guide

Architecture: Company → Godown → ShopStack: Laravel 12, PHP 8.2+, MySQL, Blade, Bootstrap 5, jQuery, AJAX, Yajra DataTablesUse this document together with the module reference image.

Global Rules

Preserve correct existing routes, controllers, models, migrations, field names, permission keys and business logic.

Inspect the current module before editing.

Use Cholavin branding: #800020, #4A0012, #D4AF37, #F5F6FA, #FFFFFF.

Use AJAX for navigation, forms, filters, tables, drawers, tabs, status changes and quick actions.

Enforce authentication, company, godown, shop, financial year, module and action permissions on the backend.

Use database transactions for sales, purchases, payments, returns, stock transfers and adjustments.

Keep controllers thin; use Form Requests, Policies, Services and Eloquent scopes where useful.

Use server-side DataTables, skeleton loaders, toast messages, empty states and responsive mobile layouts.

Do not modify unrelated modules.

Standard Page Layout

Permission-aware sidebar

Fixed top header

Godown, shop and financial-year selectors

Breadcrumb, title and description

KPI cards

Search and smart filters

Main DataTable or transaction workspace

Module quick actions

Right-side AJAX drawer

Lazy-loaded tabs

Activity timeline

Responsive mobile cards

Standard AJAX Response

{
  "success": true,
  "message": "Operation completed successfully.",
  "data": {},
  "refresh": {
    "datatable": true,
    "summary": true,
    "drawer": false
  }
}

Module 02 — Login & Authentication

UI Sections / Tabs

Login

Forgot password

Reset password

OTP/2FA

Device approval

Session expired

Location selection

KPI / Important Information

Username/email/mobile

Password

Remember me

Password visibility

Forgot password

Sign in

Support/version

Quick Actions

AJAX login

Role-based landing

Load assigned godowns and shops

Financial-year access

Login audit

Throttle/lock

Codex Prompt

Implement Module 02: Login and Authentication only. Use the attached Module 02 image as visual direction. Build branded Login, Forgot Password, Reset Password, OTP, 2FA, Device Approval, Account Locked, Session Expired and Location Selection screens. Preserve correct existing Laravel authentication. Use AJAX submission, inline validation, button loaders, throttling, secure session creation, login audit logs, assigned godown/shop loading and role-based landing. Auto-select one assigned location; show a selector for multiple locations. First inspect current auth routes, controllers, middleware, models, session keys, Blade files and JavaScript, then list files to modify and implement only this module.

Module 03 — Customer Workspace

UI Sections / Tabs

Overview

Sales

Payments

Returns

Outstanding

Ledger

Delivery

Documents

Notes

Activity

KPI / Important Information

Total customers

Active customers

Receivable

Overdue

Sales this month

Collections this month

Credit exceeded

Quick Actions

Add Customer

New Sale

Receive Payment

Sales Return

Ledger

Statement

Delivery

WhatsApp

Call

Codex Prompt

Implement Module 03: Customer Workspace only. Use the Module 03 image. Create KPI cards, filters, Yajra server-side DataTable, quick actions and a right-side drawer with Overview, Sales, Payments, Returns, Outstanding, Ledger, Delivery, Documents, Notes and Activity tabs. Use AJAX for create, edit, payment, sale, return, notes, documents, status and drawer tabs. Customer balances must display as Receivable, Advance or No Outstanding. Enforce assigned shop/godown, module, action and financial-year permissions. Inspect current customer, sales, payment and ledger logic first and preserve correct behavior.

Module 04 — Supplier Workspace

UI Sections / Tabs

Overview

Purchases

Payments

Returns

Outstanding

Ledger

Items

Addresses

Documents

Notes

Activity

KPI / Important Information

Total suppliers

Active suppliers

Total purchases

Payable

Overdue payable

Payments this month

Available credit

Quick Actions

Add Supplier

New Purchase

Make Payment

Purchase Return

Ledger

Statement

WhatsApp

Reminder

Codex Prompt

Implement Module 04: Supplier Workspace only. Use the Module 04 image. Build supplier KPI cards, smart filters, server-side table, quick actions and drawer tabs for Overview, Purchases, Payments, Returns, Outstanding, Ledger, Items, Addresses, Documents, Notes and Activity. Use AJAX for all actions. Show balances clearly as Payable, Advance or No Outstanding. Preserve existing purchase, supplier payment and ledger logic. Enforce location and permission scope on every request.

Module 05 — Item / Product Master

UI Sections / Tabs

Overview

Pricing

Stock

Stock History

Purchases

Sales

Images

Barcode

Documents

Audit

KPI / Important Information

Total items

Active items

Low stock

Out of stock

Stock value

Without image

Without barcode

Quick Actions

Add Item

Opening Stock

Print Barcode

Adjust Stock

Transfer Stock

Update Price

Upload Image

Duplicate

Codex Prompt

Implement Module 05: Item and Product Master only. Use the Module 05 image. Support rice categories, varieties, grades, brands, units, HSN/GST, images, barcodes, wholesale/retail price, opening stock, godown stock and shop stock. Create summary cards, filters, DataTable, quick actions and a detailed drawer. Use AJAX. Never directly overwrite stock; opening stock and adjustment must create stock-ledger entries with permission checks and audit logs.

Module 06 — Sales Billing (New Bill)

UI Sections / Tabs

Categories

Product Grid

Customer

Cart

Payment

Print

KPI / Important Information

Product search

Barcode

Product image

Grade

Unit

Shop stock

Customer receivable

Grand total

Quick Actions

New Bill

Hold

Resume

Recent Bills

Quotation

Delivery

Save

Save & Print

Save & Pay

Codex Prompt

Implement Module 06: Sales Billing only. Use the Module 06 billing image. Create a fast POS screen with categories on the left, product cards in the centre and customer/cart/totals on the right. Support barcode, favourites, recent items, walk-in customer, credit warning, discounts, charges, tax, round-off, cash/UPI/card/bank/credit/split payment, hold/resume, thermal/A4 print and keyboard shortcuts. Recalculate totals and stock on the server. Use a DB transaction and row locks to atomically create invoice, sale items, stock ledger, customer ledger, payments, accounts and audit logs. Prevent negative stock and duplicate bills with idempotency keys.

Module 07 — Sales Invoices

UI Sections / Tabs

Overview

Invoice Details

Payments

Returns

Delivery

Activity

KPI / Important Information

Today invoices

Paid

Partial

Due

Overdue amount

Cancelled

Quick Actions

View

Edit

Print

PDF

WhatsApp

Receive Payment

Return

Duplicate

Cancel

Codex Prompt

Implement Module 07: Sales Invoices only. Use the Module 07 image. Create KPI cards, smart filters, status badges, server-side invoice listing, bulk actions and a right drawer. Use AJAX for filters, drawer, payment, return, print and cancellation. Enforce invoice scope and permissions. Paid or closed invoices must not be changed without authorization. Cancellation and returns must safely update stock, ledger and accounts.

Module 08 — Purchase Management

UI Sections / Tabs

Purchase Entry

Purchase Listing

Payments

Returns

Attachments

Activity

KPI / Important Information

Today purchases

Purchase amount

Supplier payable

Pending

Returns

Payments this month

Quick Actions

New Purchase

Make Payment

Purchase Return

Expense

Attach Invoice

Print/PDF

Codex Prompt

Implement Module 08: Purchase Management only. Use the Module 08 image. Build purchase listing and purchase entry with supplier, supplier invoice, purchase date, due date, receiving godown, item rows, quantity, unit, rate, discounts, tax, freight, loading and other charges, payment and balance. Purchases increase godown stock only. Use AJAX and DB transactions. Create purchase, stock-ledger, supplier-ledger and accounting entries atomically. Generate server numbers, apply financial year and prevent configured duplicate supplier invoices.

Module 09 — Stock Transfer

UI Sections / Tabs

Request

Approval

Dispatch

In Transit

Receipt

History

KPI / Important Information

Total transfers

Pending approval

Dispatched

In transit

Awaiting receipt

Completed

Quick Actions

New Transfer

Approve

Reject

Dispatch

Receive

Cancel

Print

Codex Prompt

Implement Module 09: Stock Transfer only. Use the Module 09 image. Support Godown→Shop, Shop→Shop, Shop→Godown and Godown→Godown. Create transfer request, approval, dispatch, in-transit, partial receipt and receipt workflows. Validate source stock and source/destination access. Never transfer to the same location. Use DB transactions, row locking and immutable stock-ledger entries. Record requested_by, approved_by, dispatched_by and received_by with complete audit history.

Module 10 — Inventory / Stock

UI Sections / Tabs

Stock Overview

Godown Stock

Shop Stock

Low Stock

Damaged Stock

History

KPI / Important Information

Total items

Total quantity

Stock value

Low stock

Out of stock

Over stock

Damaged

Pending transfer

Quick Actions

Transfer

Adjust

Opening Stock

Damage Entry

Barcode

History

Export

Codex Prompt

Implement Module 10: Inventory and Stock only. Use the Module 10 image. Create live stock KPIs, location/category/grade/status filters, server-side table and quick actions. Show godown stock, shop stock, total stock, reorder level, last movement and server-calculated stock value. Use AJAX. Never directly mutate totals; all opening, adjustment, damage and transfer operations must create stock-ledger entries with permissions and audit logs.

Module 11 — Party Ledger

UI Sections / Tabs

Customer Ledger

Supplier Ledger

Transaction Detail

Statement

Activity

KPI / Important Information

Opening balance

Debit

Credit

Closing balance

Due

Advance

Quick Actions

Add Payment

Adjustment

Statement

Print

WhatsApp

Email

Note

Codex Prompt

Implement Module 11: Party Ledger only. Use the Module 11 image. Build customer and supplier ledgers with opening, debit, credit, closing and running balances, filters, drill-down and exports. Ledger rows must be immutable transaction entries. Do not overwrite history. Use AJAX for filters, transaction drawer, statements and notes. Enforce party, shop, financial-year and permission scope.

Module 12 — Payment Collection

UI Sections / Tabs

Payment Form

Invoice Allocation

Receipt

History

KPI / Important Information

Current balance

Amount

Allocated

Unallocated

New balance

Quick Actions

Receive Payment

Make Payment

Allocate

Save

Print Receipt

Codex Prompt

Implement Module 12: Payment Collection only. Use the Module 12 image. Support customer receipts, supplier payments, advance, partial allocation, multiple invoice allocation, payment proof and receipt print. Use AJAX for party lookup, balance fetch, invoice allocation, save and receipt preview. Recalculate balances on server, use DB transactions and locks, prevent duplicates, create payment, invoice allocation, ledger and account entries atomically, and record company/godown/shop/financial year/user IDs.

Module 13 — Delivery Management

UI Sections / Tabs

Overview

Items

Address

Driver

Timeline

POD

Notes

Activity

KPI / Important Information

Pending

Out for delivery

Delivered

Failed

Rescheduled

Delivery charges

Quick Actions

Assign Driver

Start

Delivered

Reschedule

Upload POD

Print Note

WhatsApp

Codex Prompt

Implement Module 13: Delivery Management only. Use the Module 13 image. Create delivery listing, assignment, route/vehicle/driver fields, status workflow, timeline and proof-of-delivery upload. Use AJAX. Support Pending, Assigned, Out for Delivery, Delivered, Failed, Rescheduled and Cancelled. Link deliveries to invoices and customer addresses. Enforce shop scope and delivery permissions, preserve status history and audit actions.

Module 14 — Reports & Analytics

UI Sections / Tabs

Sales

Purchase

Inventory

Profit

Outstanding

Payable

GST

Audit

KPI / Important Information

KPI summary

Charts

Comparison

DataTable

Drill-down

Export

Quick Actions

Generate

Apply Filter

Drill Down

PDF

Excel

Print

Codex Prompt

Implement Module 14: Reports and Analytics only. Use the Module 14 image. Create report categories with date presets, godown/shop filters, KPI cards, charts, server-side tables, comparisons, drill-down, PDF, Excel and print. Load summaries, charts and tables independently through AJAX. Apply user location and financial-year scope. Use server-side calculations, optimize queries, add indexes where needed and avoid N+1 issues.

Module 15 — Financial Dashboard

UI Sections / Tabs

Overview

Cash Flow

Receivables

Payables

Profit

Expenses

KPI / Important Information

Cash in hand

Bank balance

Receivable

Payable

Gross profit

Net profit

Expenses

Collections

Quick Actions

Receive Payment

Make Payment

Expense

Journal

Cash Transfer

Bank Transfer

Ledger

Codex Prompt

Implement Module 15: Financial Dashboard only. Use the Module 15 image. Create financial KPIs and charts for cash flow, income vs expense, receivable ageing, payable ageing, profit trend, payment-method split and expense breakdown. Values must be server-calculated from valid accounting and ledger data. Support consolidated, godown and shop views according to permission scope. Use AJAX, drill-down and optimized queries.

Module 16 — Users & Role Management

UI Sections / Tabs

Users

Roles

Permissions

Access Control

Login Logs

Activity Logs

KPI / Important Information

Total users

Active users

Roles

Restricted users

Recent logins

Quick Actions

Add User

Assign Role

Godown Access

Shop Access

Permissions

Deactivate

Reset Password

Codex Prompt

Implement Module 16: Users and Role Management only. Use the Module 16 image. Build user listing, role listing, permission matrix, godown/shop assignment, financial-year access, approval limits, login restrictions and activity views. Final access equals Role + Godown + Shop + Module + Action + Financial Year. Frontend hiding is not security; every endpoint must enforce backend access. Use AJAX for all assignments and status actions.

Module 17 — Godown & Shop Management

UI Sections / Tabs

Godowns

Shops

Stock

Transfers

Users

Reports

Activity

KPI / Important Information

Total godowns

Total shops

Active locations

Inactive locations

Quick Actions

Add Godown

Add Shop

Assign Manager

View Stock

View Users

Deactivate

Codex Prompt

Implement Module 17: Godown and Shop Management only. Use the Module 17 image. Use the fixed Company→Godown→Shop hierarchy. Create godown and shop tabs, listing, forms, manager assignment, linked users, stock, transfer and activity views. Every shop belongs to one godown. Inactive locations cannot create transactions. Unsafe re-parenting must be blocked or use a controlled migration. Use AJAX and audit all location changes.

Module 18 — Settings & Configuration

UI Sections / Tabs

Business

Tax

Invoice

Number Series

Payments

Printer

Messaging

Financial Year

Backup

Security

Theme

KPI / Important Information

Configuration status

Last updated

Integration health

Backup status

Quick Actions

Edit Setting

Save

Test Connection

Backup

Restore

Audit History

Codex Prompt

Implement Module 18: Settings and Configuration only. Use the Module 18 image. Build card-based navigation for company, godown, shop, GST/tax, invoices, number series, payment methods, thermal/A4 printers, cash drawer, email, SMS, WhatsApp, notifications, financial year, backup/restore, audit, security, theme, language, date and currency formats. Use AJAX for all forms. Encrypt sensitive values, never expose secrets in Blade/JSON, permission-check each category and audit critical changes.

Universal Completion Checklist

Codex must provide for every module:

Files analyzed

Existing routes and endpoints

Models and database tables used

Existing permissions

Existing business logic preserved

Current UI and performance issues

Files changed and created

Validation rules

AJAX response handling

Security and permission checks

Tests run

Remaining issues

Required Tests

Authorized and unauthorized user

Assigned and unassigned godown

Assigned and unassigned shop

Active and closed financial year

Create, update and deactivate

Validation errors

Duplicate submission

Empty state

Search, filters and pagination

AJAX 401, 403, 419, 422, 409 and 500

Desktop, tablet and mobile

Audit log

Transaction rollback for financial and stock modules

Recommended Order

02 Login & Authentication
03 Customer Workspace
04 Supplier Workspace
05 Item / Product Master
06 Sales Billing
07 Sales Invoices
08 Purchase Management
09 Stock Transfer
10 Inventory / Stock
11 Party Ledger
12 Payment Collection
13 Delivery Management
14 Reports & Analytics
15 Financial Dashboard
16 Users & Role Management
17 Godown & Shop Management
18 Settings & Configuration

Final Formula

Reference Image
+ This Module Guide
+ AGENTS.md
+ Existing Project Analysis
= Consistent UI with Correct ERP Logic