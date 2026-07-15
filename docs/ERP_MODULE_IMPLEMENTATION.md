# Cholavin ERP Section 27 implementation

Section 27 uses independent module controllers, Form Requests, controller-group routes, and Blade folders for reference/master workspaces. They use AJAX CRUD, Yajra server-side tables, accordion filters, jQuery Validation, authorization, active-shop scoping, and PDF export. Shared services remain appropriate for genuine cross-module infrastructure such as reference storage, authorization context, PDF rendering, inventory posting, accounting posting, and standard JSON responses.

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

- `config/erp_modules.php` currently registers legacy reference metadata and commercial document workflows; independent reference/master pages must not use it as a generic controller or field-definition engine.
- `CommercialDocumentService` owns document totals, lifecycle, stock effects, and posting orchestration.
- `InventoryService` owns balance locking, average cost, batches, expiry, and movements.
- `AccountingPostingService` owns automatic double-entry vouchers.
- `ReportService` supplies the common server-side report contract.
- `NotificationService` generates deduplicated operational alerts.
- `DataMaintenanceService` owns private backups and controlled import/export.
- `PdfService`, `ResponseHelper`, shared JavaScript, and common Blade layouts supply the required reusable UI behavior.

## Provider-dependent channels

SMTP uses Company Settings and powers password recovery. Invoice sharing provides direct email and WhatsApp handoff without storing third-party credentials. Automated SMS/WhatsApp delivery requires the chosen provider's endpoint and credentials; the notification table already records channel, recipient, sent/failed time, and failure details so a provider adapter can be connected without changing business tables.
