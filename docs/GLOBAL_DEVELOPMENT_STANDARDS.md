# Global Laravel Development Standards

These standards are mandatory for every Cholavin ERP module, page, form, report, listing, and CRUD operation. `AGENTS.md` makes this document part of the repository-wide development contract.

## 1. Listing and DataTable contract

- Every listing uses Yajra server-side DataTables with pagination, debounced search, sorting, responsive behavior, serial number, status, action column, loading state, empty state, error handling, filters, reset, and PDF/export actions where applicable.
- Never load a listing with `Model::all()` or send a large JSON dataset through Blade.
- Use one controller `index(Request $request)` for the Blade response and AJAX DataTable response. Do not add `getData`, `fetchData`, `ajaxList`, or equivalent methods.
- Select only required columns and eager-load required relationships.
- Listing queries, PDF queries, and export queries must share the same filter/query service or scope.

```php
public function index(Request $request)
{
    if ($request->ajax()) {
        $query = Party::query()
            ->select(['id', 'name', 'mobile', 'status', 'created_at'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->when($request->filled('shop_id'), fn ($query) => $query->where('shop_id', $request->shop_id));

        return DataTables::eloquent($query)->addIndexColumn()->toJson();
    }

    return view('backend.parties.index');
}
```

## 2. Filters and query reuse

- Apply every optional filter with Laravel `when()`.
- Standard filters are search, status, date range, shop, godown, party, supplier, customer, and product as relevant.
- Filter changes and reset actions reload only the DataTable through AJAX.
- Put reusable date ranges, statuses, and location filtering in query scopes, traits, or filter services.
- Always enforce the active shop/godown context on the server; browser IDs are never trusted.

## 3. AJAX-only mutations

Create, update, delete, restore, permanent delete, status changes, payments, transfers, sales, purchases, returns, password changes, permission changes, bulk actions, and dependent dropdowns use AJAX. Use POST for create, PUT for full update, PATCH for partial update, and DELETE for delete.

On success:

1. Close the modal when applicable.
2. Clear validation errors and reset the form when appropriate.
3. Reload only the affected table with `table.ajax.reload(null, false)`.
4. Show a Toastr message.

Never use `location.reload()` or `window.location.reload()` for these operations. Use `submitFormUsingAjax`, `handleAjaxError`, `reloadDataTable`, and `initializeDataTable` from `public/backend/assets/js/erp-common.js`.

## 4. Validation

- Every form uses jQuery Validation for immediate feedback and a dedicated Laravel Form Request for authoritative server validation.
- Cover required, lengths, numeric/decimal, email, mobile, dates, file type/size, confirmation, conditional rules, remote rules, and business rules as applicable.
- AJAX validation errors use HTTP 422 and map errors back to the correct form fields.
- Client-side validation never replaces authorization or server-side validation.

## 5. Standard JSON responses

Use `App\Helpers\ResponseHelper`.

```json
{"status":true,"message":"Record created successfully.","data":{}}
```

```json
{"status":false,"message":"Validation failed.","errors":{}}
```

Do not invent module-specific response shapes.

## 6. mPDF reports

Every applicable listing/report provides an mPDF download using `App\Services\PdfService`, `resources/views/pdf/layouts`, and `resources/views/pdf/partials`.

The PDF must reproduce the current DataTable state:

- search value, selected filters, date range, and sorting;
- party/supplier/customer/product/shop/godown/status filters;
- visible headings in the same order;
- company information and report title;
- generated date/time, page number, total records, and relevant totals.

The PDF endpoint must authorize the same ability and call the same filtered query builder as the DataTable. Avoid duplicate query, header, footer, and CSS code. Queue unusually heavy reports.

## 7. Forms and modal CRUD

Use Bootstrap modal CRUD where it improves speed and context. The standard flow is Add/Edit → modal → jQuery Validation → AJAX → standard JSON → close modal → preserve DataTable page → Toastr. Load edit data only when requested rather than embedding all records in Blade.

## 8. Reusable UI components

Prefer Blade components/partials for page headings, breadcrumbs, filters, tables, fields, status badges, actions, deletion confirmation, modals, empty states, PDF buttons, and permission-aware actions. Use the global Cholavin maroon/gold button palette rather than inline colors.

Button semantics:

- Primary/create/action: maroon.
- Save/confirm/warning: gold.
- Delete/destructive: deep maroon/red family.
- Secondary/info: ivory with maroon text.
- Focus state: visible gold ring.

## 9. Thin controllers and transactions

Controllers receive requests, authorize, call services/actions, and return JSON/views. Move calculations, stock logic, invoice logic, PDF preparation, imports, and multi-step workflows into services/actions. Use database transactions for sales, purchases, returns, stock transfers, payments, invoices, ledgers, and any multi-table update.

## 10. Security and authorization

Every request requires the appropriate combination of authentication, active-account middleware, Gate/Policy authorization, shop/godown access validation, Form Request validation, safe uploads, exception handling, and transactions. Recalculate prices, totals, taxes, discounts, stock, balances, and permissions on the server. Never trust IDs or calculated amounts sent by the browser.

## 11. Performance

- Use server-side pagination, indexed filter columns, selected fields, eager loading, and cached reusable master data.
- Avoid N+1 queries, duplicate AJAX calls, large embedded JSON, and unnecessary assets.
- Debounce search inputs and compress uploaded images.
- Use queued jobs for heavy exports, PDFs, imports, and notifications where appropriate.

## 12. Standard listing page order

1. Page heading and breadcrumb.
2. Add, PDF, and export actions.
3. Search/filter/date/status/shop/godown controls and reset.
4. Server-side DataTable with relevant headings.
5. Create/edit modal with validation and AJAX.
6. AJAX delete confirmation.

Database selection, DataTable columns, visible table headings, Excel headings, and PDF headings must remain consistent.

## Final review checklist

- Yajra server-side listing with shared filtered query.
- Single `index()` for Blade and AJAX.
- Filters use `when()` and reload through AJAX.
- All writes use AJAX, correct HTTP methods, standard JSON, Form Requests, and authorization.
- No full page reload after a write.
- jQuery Validation and reusable error rendering are present.
- PDF matches filters, sort, headings, totals, permissions, and location context.
- Transactions protect multi-table workflows.
- Controller remains thin and duplicated logic has been extracted.
- Queries are scoped, indexed, selective, eager-loaded, and tested.
- Buttons and reusable components use the Cholavin visual system.
