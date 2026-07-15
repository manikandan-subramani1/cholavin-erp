# Cholavin ERP module folder convention

Current direction: every reference/master workspace has its own controller, Form Request, routes, and `index/create/edit` Blades. A developer can change one module's fields and presentation without changing another module's files.

The module folder style follows the existing `C:\xampp\htdocs\invicts-website-main` backend structure: each module owns its own `index.blade.php`, `create.blade.php`, and `edit.blade.php`. Do not make those pages depend on generic wrappers such as `backend.modules.master.create`, `backend.modules.master.edit`, or one type-switched master view.

```text
app/Http/Controllers/Backend/BankAccountController.php
app/Http/Requests/BankAccounts/StoreBankAccountRequest.php
app/Http/Requests/BankAccounts/UpdateBankAccountRequest.php
app/Models/BankAccount.php
app/Policies/BankAccountPolicy.php
resources/views/backend/<module>/index.blade.php
resources/views/backend/<module>/create.blade.php
resources/views/backend/<module>/edit.blade.php
database/migrations/<timestamp>_create_<module>_table.php
```

## Required page structure

- Keep the menu structure unchanged.
- Every module has its own controller. `index()`, `create()`, and `edit()` authorize their own permission and return only that module's Blade file.
- Register each controller explicitly with `Route::controller(...)->prefix(...)->name(...)->group(...)`. Do not route multiple modules through a loop of `Route::view()` calls, `Route::resource()`, or a controller that switches behavior from a `{module}` parameter.
- `create.blade.php` is create-only and `edit.blade.php` is edit-only. A page must not inspect the current route to decide whether it is the create or edit page.
- Each module page must contain its own page variables, field names, labels, options, form layout, table headings, and page scripts. Field definitions must not come from another module's view or a type-switched master configuration.
- Shared includes are allowed only for true common layout/assets/components.
- Every listing uses the module controller's `index()` for both its Blade response and Yajra AJAX response.
- Every index page provides a collapsed Bootstrap accordion filter panel with search, status, date range, and reset controls.
- Every create/edit form uses its module Form Request, `#form-validate`, jQuery Validation, AJAX submission, and the standard JSON response.

## Backend phase

Reference/master modules currently use the `reference_masters` persistence model and `ReferenceMasterService` as shared storage infrastructure, while each module owns its controller, request rules, field mapping, routes, views, DataTable columns, filters, and permissions. Do not reintroduce a generic master controller or type-switched Blade engine.

The implemented controller must remain thin: it authorizes the action, delegates query/write work to the module service or action, and returns the module's view or standard JSON response. Multi-table writes use transactions. Shop/godown ownership and active context are always validated on the server.

## Reuse boundary

Small shared files may contain mechanical layout helpers, button styles, AJAX helpers, response helpers, PDF layout primitives, or common assets. Each module must still declare its own page fields, route names, labels, validation, controller, model, table, and business rules so a developer can understand or modify one module without tracing a type-switching engine.

## Bank account example

```text
routes/web.php
  Route::controller(BankAccountController::class)
      ->prefix('bank-accounts')
      ->name('bank-accounts.')
      ->group(function () {
          Route::get('/', 'index')->name('index');
          Route::get('/create', 'create')->name('create');
          Route::post('/', 'store')->name('store');
          Route::get('/pdf', 'pdf')->name('pdf');
          Route::get('/{id}/edit', 'edit')->name('edit');
          Route::put('/{id}', 'update')->name('update');
          Route::delete('/{id}', 'destroy')->name('destroy');
      });

BankAccountController@index  -> backend.bank-accounts.index
BankAccountController@create -> backend.bank-accounts.create
BankAccountController@edit   -> backend.bank-accounts.edit
```

The bank-account Blades own `bank_name`, `account_name`, `account_number`, `ifsc_code`, `branch`, `opening_balance`, and `status`. Another module must not import, mutate, or conditionally reuse that field list.
