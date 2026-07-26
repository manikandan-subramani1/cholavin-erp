# Cholavin ERP Section 27 implementation

Section 27 is implemented as shared business engines and configured modules, not as empty controller and Blade copies. This keeps authorization, numbering, totals, stock, accounting, PDF output, and active shop/godown behavior consistent.

## Implemented module map

| Area | Implementation |
|---|---|
| Authentication and access | Common login, password recovery, users, roles, permission overrides, shop/godown assignment and live switching, session revocation, login history, and activity audit |
| Organization | Company settings, shops, godowns, financial years, invoice/number sequences, tax configuration, bank accounts, and payment methods |
| Product masters | Products, SKU/barcode, categories, subcategories, brands, units, variants, grades, price lists, tax/HSN-SAC, opening stock, and reorder level |
| Parties | Customers, suppliers, groups, addresses, credit limits, opening balances, ledgers, receivables, and payables |
| Sales | Quotations, orders, delivery challans, invoices, POS, returns, credit notes, collections, PDF printing, and click-to-share email/WhatsApp actions |
| Purchases | Orders, goods receipts, bills, returns, debit notes, supplier payments, and landed/allocated expense |
| Inventory | Shop/godown balances, transfers, adjustments, damaged/expired stock, batch/expiry tracking, low-stock alerts, valuation, and movement ledger |
| Accounts | Receipts, payments, income/expense, contra/journal vouchers, automatic transaction posting, day/cash/bank books, and general ledger |
| Delivery | Vehicle/driver/route masters, assignments, status workflow, proof upload, cash collection, and delivery expense capture |
| Reports | Sales, purchase, GST, stock, valuation, expiry, parties, outstanding balances, payments, expenses, books, P&L, audit, and user activity |
| Notifications | In-app low-stock, expiry, overdue-payment, and pending-delivery alerts plus reusable templates and channel metadata |
| Settings and audit | Branding/contact/mail settings, invoice templates, numbering, private compressed backups, controlled CSV import/export, activity/login/error logs |

## Business invariants

- Posted stock documents create atomic inventory movements and cannot be edited or deleted.
- Negative stock is rejected while the balance row is locked.
- Totals and tax are recalculated on the server; browser totals are informational only.
- Purchase expense allocation is included in the document total.
- Posted invoices, bills, returns, notes, and payments create balanced, source-linked vouchers exactly once.
- Opening stock is posted once to the active godown. Later corrections use stock adjustments.
- Payments cannot exceed the related document balance.
- Vouchers must have equal debit and credit totals.
- Every operational query is restricted to the active permitted shop and, where applicable, godown.
- Backup files are stored below `storage/app/private`; filenames are validated before download.
- CSV import is limited to approved datasets, validated headings, 5,000 rows, and a transaction.

## Reusable implementation

- `config/erp_modules.php` registers reference and commercial document modules.
- `CommercialDocumentService` owns document totals, lifecycle, stock effects, and posting orchestration.
- `InventoryService` owns balance locking, average cost, batches, expiry, and movements.
- `AccountingPostingService` owns automatic double-entry vouchers.
- `ReportService` supplies the common server-side report contract.
- `NotificationService` generates deduplicated operational alerts.
- `DataMaintenanceService` owns private backups and controlled import/export.
- `PdfService`, `ResponseHelper`, shared JavaScript, and common Blade layouts supply the required reusable UI behavior.

## Provider-dependent channels

SMTP uses Company Settings and powers password recovery. Invoice sharing provides direct email and WhatsApp handoff without storing third-party credentials. Automated SMS/WhatsApp delivery requires the chosen provider's endpoint and credentials; the notification table already records channel, recipient, sent/failed time, and failure details so a provider adapter can be connected without changing business tables.


## UI and module structure

The backend follows the existing Laravel module structure and does not move business logic into views:

- module pages live below `resources/views/backend/{module}` and extend `backend.layouts.app`;
- module controllers live below `app/Http/Controllers/Backend` and keep the Blade response and server-side DataTable response in the same `index(Request $request)` action;
- module-specific validation remains in `app/Http/Requests`, business workflows remain in `app/Services`, and PDF output remains in `resources/views/pdf` through `PdfService`;
- `public/backend/assets/css/cholavin-erp.css` provides the maroon/gold Cholavin visual system, index-page styling, responsive tables, and the master-detail workspace used across modules;
- `public/backend/assets/js/erp-common.js` provides CSRF setup, AJAX form submission, validation error mapping, Toastr messages, DataTable initialization, table reloads, and shared index-page enhancement;
- `public/backend/assets/js/module-workspace.js` progressively enhances server-side DataTables with a responsive list/detail workspace and AJAX quick-view modal without changing the underlying routes or queries;
- `public/backend/assets/js/header-context.js` keeps shop, godown, and financial context controls connected to the authenticated context endpoints;
- `resources/views/backend/layouts/menu.blade.php` remains permission-aware. The sidebar exposes only records the signed-in user can access while retaining the existing module route names and test-covered navigation IDs.

The visual treatment follows the supplied reference screens: a dark Cholavin sidebar, fixed context-aware header, maroon primary actions, gold focus/active states, compact KPI cards, filter panels, server-side tables, responsive record detail panels, modal CRUD, and AJAX confirmation/error feedback. Existing controller, model, migration, permission, mPDF, and Maatwebsite Excel contracts remain unchanged.

### Module UI rollout checklist

For every new or revised module, inspect the route/controller/request/service/model first, then verify:

1. the index page has permission-aware heading/actions, relevant filters, reset, loading/empty/error states, server-side DataTable, and PDF/Excel actions where applicable;
2. create/update/delete/status actions use the shared AJAX helpers and Form Requests;
3. detail/show data is loaded on demand through an authorized JSON endpoint or a module-specific Blade detail page;
4. shop, godown, and financial-year context is enforced server-side;
5. all response, PDF, export, authorization, and rollback tests pass before the next module is changed.
