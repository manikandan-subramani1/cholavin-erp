# Cholavin ERP agent instructions

All new or modified ERP modules must follow `docs/GLOBAL_DEVELOPMENT_STANDARDS.md`, `docs/AUTHORIZATION_MODULE_GUIDE.md`, and the roadmap in `docs/cholavin_erp_vyapar_clone_master_specification.md`.

Mandatory implementation rules:

- Use Yajra server-side DataTables for listings; the controller `index()` handles both Blade and AJAX.
- Apply optional filters with query-builder `when()` and preserve active shop/godown scope.
- Use AJAX for every mutating operation and return the standard JSON response shape.
- Use Form Request classes plus jQuery Validation for forms.
- Reload only the affected DataTable with `ajax.reload(null, false)`; never reload the page.
- Add mPDF output for applicable listings/reports using the shared `PdfService` and PDF partials.
- Keep DataTable, page, Excel, and PDF filters, sorting, headings, totals, and permissions consistent.
- Keep controllers thin; move business logic to services/actions and wrap multi-table writes in transactions.
- Use reusable helpers/components instead of duplicating AJAX, filters, response, PDF, upload, badge, or action logic.
- All actions require authentication, authorization, server validation, and shop/godown validation.
- Use the global Cholavin button classes and palette from `public/backend/assets/css/cholavin-erp.css`.
