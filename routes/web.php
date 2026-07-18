<?php

use App\Http\Controllers\Backend\ActivityLogController;
use App\Http\Controllers\Backend\AuthController;
use App\Http\Controllers\Backend\BankAccountController;
use App\Http\Controllers\Backend\BrandController;
use App\Http\Controllers\Backend\CategoryController;
use App\Http\Controllers\Backend\CommercialDocumentController;
use App\Http\Controllers\Backend\ContactEnquiryController as BackendContactEnquiryController;
use App\Http\Controllers\Backend\CustomerGroupController;
use App\Http\Controllers\Backend\DashboardController;
use App\Http\Controllers\Backend\DeliveryController;
use App\Http\Controllers\Backend\DeliveryRouteController;
use App\Http\Controllers\Backend\DriverController;
use App\Http\Controllers\Backend\FinancialYearController;
use App\Http\Controllers\Backend\FinancialOverviewController;
use App\Http\Controllers\Backend\GradeController;
use App\Http\Controllers\Backend\GlobalSearchController;
use App\Http\Controllers\Backend\HsnSacCodeController;
use App\Http\Controllers\Backend\InvoiceSequenceController;
use App\Http\Controllers\Backend\InvoiceTemplateController;
use App\Http\Controllers\Backend\LocationContextController;
use App\Http\Controllers\Backend\LocationController;
use App\Http\Controllers\Backend\LookupController;
use App\Http\Controllers\Backend\NotificationController;
use App\Http\Controllers\Backend\NotificationTemplateController;
use App\Http\Controllers\Backend\NumberSequenceController;
use App\Http\Controllers\Backend\PartyController;
use App\Http\Controllers\Backend\PasswordResetController;
use App\Http\Controllers\Backend\PaymentController;
use App\Http\Controllers\Backend\PaymentMethodController;
use App\Http\Controllers\Backend\PriceListController;
use App\Http\Controllers\Backend\ProductController as BackendProductController;
use App\Http\Controllers\Backend\ReportController;
use App\Http\Controllers\Backend\RoleController;
use App\Http\Controllers\Backend\SettingController;
use App\Http\Controllers\Backend\StockController;
use App\Http\Controllers\Backend\StockTransferController;
use App\Http\Controllers\Backend\SubcategoryController;
use App\Http\Controllers\Backend\SupplierGroupController;
use App\Http\Controllers\Backend\SystemMaintenanceController;
use App\Http\Controllers\Backend\TaxConfigurationController;
use App\Http\Controllers\Backend\TaxRateController;
use App\Http\Controllers\Backend\ThermalReceiptController;
use App\Http\Controllers\Backend\UnitController;
use App\Http\Controllers\Backend\UserSessionController;
use App\Http\Controllers\Backend\UserAccessController;
use App\Http\Controllers\Backend\VariantController;
use App\Http\Controllers\Backend\VehicleController;
use App\Http\Controllers\Backend\VoucherController;
use App\Http\Controllers\Frontend\ContactEnquiryController as FrontendContactEnquiryController;
use App\Http\Controllers\Frontend\ProductController as FrontendProductController;
use Illuminate\Support\Facades\Route;

Route::view('/about-us', 'frontend.pages.about')->name('frontend.about');
Route::view('/delivery', 'frontend.pages.delivery')->name('frontend.delivery');
Route::view('/contact-us', 'frontend.pages.contact')->name('frontend.contact');

Route::controller(FrontendProductController::class)->name('frontend.')->group(function () {
    Route::get('/', 'home')->name('home');
    Route::get('/products', 'index')->name('products');
});

Route::controller(FrontendContactEnquiryController::class)->name('frontend.enquiry.')->group(function () {
    Route::post('/contact-enquiry', 'store')->name('store');
});

Route::prefix('admin')->name('admin.')->group(function () {
    Route::controller(AuthController::class)->middleware('guest')->name('auth.')->group(function () {
        Route::get('/login', 'create')->name('index');
        Route::post('/login', 'store')->name('login');
    });

    Route::controller(PasswordResetController::class)->middleware('guest')->name('password.')->group(function () {
        Route::get('/forgot-password', 'request')->name('request');
        Route::post('/forgot-password', 'email')->name('email');
        Route::get('/reset-password/{token}', 'reset')->name('reset');
        Route::post('/reset-password', 'update')->name('update');
    });

    Route::middleware(['auth', 'active', 'access.context', 'activity'])->group(function () {
        Route::controller(DashboardController::class)->group(function () {
            Route::get('/dashboard', '__invoke')->name('dashboard');
        });
        Route::get('/financial-overview', FinancialOverviewController::class)->name('financial-overview');

        Route::get('/global-search', GlobalSearchController::class)->name('global-search');

        Route::controller(AuthController::class)->group(function () {
            Route::post('/logout', 'destroy')->name('logout');
        });

        Route::controller(LocationContextController::class)->prefix('location-context')->name('location-context.')->group(function () {
            Route::get('/shops', 'shops')->name('shops');
            Route::get('/godowns', 'godowns')->name('godowns');
            Route::post('/shop', 'switchShop')->name('switch-shop');
            Route::post('/godown', 'switchGodown')->name('switch-godown');
            Route::post('/financial-year', 'switchFinancialYear')->name('switch-financial-year');
        });

        Route::controller(BackendProductController::class)->prefix('products')->name('products.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/pdf', 'pdf')->name('pdf');
            Route::get('/{product}/edit', 'edit')->name('edit');
            Route::put('/{product}', 'update')->name('update');
            Route::delete('/{product}', 'destroy')->name('destroy');
        });

        Route::controller(FinancialYearController::class)->prefix('financial-years')->name('financial-years.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/pdf', 'pdf')->name('pdf');
            Route::get('/{id}/edit', 'edit')->name('edit');
            Route::put('/{id}', 'update')->name('update');
            Route::delete('/{id}', 'destroy')->name('destroy');
        });

        Route::controller(InvoiceSequenceController::class)->prefix('invoice-sequences')->name('invoice-sequences.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/pdf', 'pdf')->name('pdf');
            Route::get('/{id}/edit', 'edit')->name('edit');
            Route::put('/{id}', 'update')->name('update');
            Route::delete('/{id}', 'destroy')->name('destroy');
        });

        Route::controller(TaxConfigurationController::class)->prefix('tax-configurations')->name('tax-configurations.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/pdf', 'pdf')->name('pdf');
            Route::get('/{id}/edit', 'edit')->name('edit');
            Route::put('/{id}', 'update')->name('update');
            Route::delete('/{id}', 'destroy')->name('destroy');
        });

        Route::controller(BankAccountController::class)->prefix('bank-accounts')->name('bank-accounts.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/pdf', 'pdf')->name('pdf');
            Route::get('/{id}/edit', 'edit')->name('edit');
            Route::put('/{id}', 'update')->name('update');
            Route::delete('/{id}', 'destroy')->name('destroy');
        });

        Route::controller(PaymentMethodController::class)->prefix('payment-methods')->name('payment-methods.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/pdf', 'pdf')->name('pdf');
            Route::get('/{id}/edit', 'edit')->name('edit');
            Route::put('/{id}', 'update')->name('update');
            Route::delete('/{id}', 'destroy')->name('destroy');
        });

        Route::controller(CategoryController::class)->prefix('categories')->name('categories.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/pdf', 'pdf')->name('pdf');
            Route::get('/{id}/edit', 'edit')->name('edit');
            Route::put('/{id}', 'update')->name('update');
            Route::delete('/{id}', 'destroy')->name('destroy');
        });

        Route::controller(SubcategoryController::class)->prefix('subcategories')->name('subcategories.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/pdf', 'pdf')->name('pdf');
            Route::get('/{id}/edit', 'edit')->name('edit');
            Route::put('/{id}', 'update')->name('update');
            Route::delete('/{id}', 'destroy')->name('destroy');
        });

        Route::controller(BrandController::class)->prefix('brands')->name('brands.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/pdf', 'pdf')->name('pdf');
            Route::get('/{id}/edit', 'edit')->name('edit');
            Route::put('/{id}', 'update')->name('update');
            Route::delete('/{id}', 'destroy')->name('destroy');
        });

        Route::controller(UnitController::class)->prefix('units')->name('units.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/pdf', 'pdf')->name('pdf');
            Route::get('/{id}/edit', 'edit')->name('edit');
            Route::put('/{id}', 'update')->name('update');
            Route::delete('/{id}', 'destroy')->name('destroy');
        });

        Route::controller(VariantController::class)->prefix('variants')->name('variants.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/pdf', 'pdf')->name('pdf');
            Route::get('/{id}/edit', 'edit')->name('edit');
            Route::put('/{id}', 'update')->name('update');
            Route::delete('/{id}', 'destroy')->name('destroy');
        });

        Route::controller(GradeController::class)->prefix('grades')->name('grades.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/pdf', 'pdf')->name('pdf');
            Route::get('/{id}/edit', 'edit')->name('edit');
            Route::put('/{id}', 'update')->name('update');
            Route::delete('/{id}', 'destroy')->name('destroy');
        });

        Route::controller(PriceListController::class)->prefix('price-lists')->name('price-lists.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/pdf', 'pdf')->name('pdf');
            Route::get('/{id}/edit', 'edit')->name('edit');
            Route::put('/{id}', 'update')->name('update');
            Route::delete('/{id}', 'destroy')->name('destroy');
        });

        Route::controller(TaxRateController::class)->prefix('tax-rates')->name('tax-rates.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/pdf', 'pdf')->name('pdf');
            Route::get('/{id}/edit', 'edit')->name('edit');
            Route::put('/{id}', 'update')->name('update');
            Route::delete('/{id}', 'destroy')->name('destroy');
        });

        Route::controller(HsnSacCodeController::class)->prefix('hsn-sac-codes')->name('hsn-sac-codes.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/pdf', 'pdf')->name('pdf');
            Route::get('/{id}/edit', 'edit')->name('edit');
            Route::put('/{id}', 'update')->name('update');
            Route::delete('/{id}', 'destroy')->name('destroy');
        });

        Route::controller(CustomerGroupController::class)->prefix('customer-groups')->name('customer-groups.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/pdf', 'pdf')->name('pdf');
            Route::get('/{id}/edit', 'edit')->name('edit');
            Route::put('/{id}', 'update')->name('update');
            Route::delete('/{id}', 'destroy')->name('destroy');
        });

        Route::controller(SupplierGroupController::class)->prefix('supplier-groups')->name('supplier-groups.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/pdf', 'pdf')->name('pdf');
            Route::get('/{id}/edit', 'edit')->name('edit');
            Route::put('/{id}', 'update')->name('update');
            Route::delete('/{id}', 'destroy')->name('destroy');
        });

        Route::controller(VehicleController::class)->prefix('vehicles')->name('vehicles.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/pdf', 'pdf')->name('pdf');
            Route::get('/{id}/edit', 'edit')->name('edit');
            Route::put('/{id}', 'update')->name('update');
            Route::delete('/{id}', 'destroy')->name('destroy');
        });

        Route::controller(DriverController::class)->prefix('drivers')->name('drivers.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/pdf', 'pdf')->name('pdf');
            Route::get('/{id}/edit', 'edit')->name('edit');
            Route::put('/{id}', 'update')->name('update');
            Route::delete('/{id}', 'destroy')->name('destroy');
        });

        Route::controller(DeliveryRouteController::class)->prefix('delivery-routes')->name('delivery-routes.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/pdf', 'pdf')->name('pdf');
            Route::get('/{id}/edit', 'edit')->name('edit');
            Route::put('/{id}', 'update')->name('update');
            Route::delete('/{id}', 'destroy')->name('destroy');
        });

        Route::controller(InvoiceTemplateController::class)->prefix('invoice-templates')->name('invoice-templates.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/pdf', 'pdf')->name('pdf');
            Route::get('/{id}/edit', 'edit')->name('edit');
            Route::put('/{id}', 'update')->name('update');
            Route::delete('/{id}', 'destroy')->name('destroy');
        });

        Route::controller(NumberSequenceController::class)->prefix('number-sequences')->name('number-sequences.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/pdf', 'pdf')->name('pdf');
            Route::get('/{id}/edit', 'edit')->name('edit');
            Route::put('/{id}', 'update')->name('update');
            Route::delete('/{id}', 'destroy')->name('destroy');
        });

        Route::controller(NotificationTemplateController::class)->prefix('notification-templates')->name('notification-templates.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/pdf', 'pdf')->name('pdf');
            Route::get('/{id}/edit', 'edit')->name('edit');
            Route::put('/{id}', 'update')->name('update');
            Route::delete('/{id}', 'destroy')->name('destroy');
        });

        Route::controller(PartyController::class)->prefix('parties/{type}')->name('parties.')->group(function () {
            Route::get('/pdf', 'pdf')->name('pdf');
            Route::get('/summary-metrics', 'metrics')->name('metrics');
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
            Route::get('/{party}/transactions', 'transactions')->name('transactions');
            Route::get('/{party}', 'show')->name('show');
            Route::put('/{party}', 'update')->name('update');
            Route::delete('/{party}', 'destroy')->name('destroy');
        });

        Route::controller(LookupController::class)->prefix('lookups')->name('lookups.')->group(function () {
            Route::get('/products', 'products')->name('products');
            Route::get('/parties', 'parties')->name('parties');
        });

        Route::controller(CommercialDocumentController::class)->prefix('documents/{module}')->name('documents.')->group(function () {
            Route::get('/pdf', 'pdf')->name('pdf');
            Route::get('/{document}/print', 'print')->name('print');
            Route::get('/', 'index')->name('index');
            Route::get('/{document}', 'show')->name('show');
            Route::post('/', 'store')->name('store');
            Route::put('/{document}', 'update')->name('update');
            Route::delete('/{document}', 'destroy')->name('destroy');
        });

        Route::controller(StockController::class)->prefix('inventory')->name('stock.')->group(function () {
            Route::get('/stock/pdf', 'pdf')->name('pdf');
            Route::get('/stock', 'index')->name('index');
            Route::get('/movements', 'movements')->name('movements');
        });

        Route::controller(StockTransferController::class)->prefix('inventory/transfers')->name('stock-transfers.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
        });

        Route::controller(PaymentController::class)->prefix('accounts/payments')->name('payments.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
            Route::get('/pdf', 'pdf')->name('pdf');
        });

        Route::controller(VoucherController::class)->prefix('accounts/vouchers')->name('vouchers.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
            Route::get('/pdf', 'pdf')->name('pdf');
        });

        Route::controller(DeliveryController::class)->prefix('deliveries')->name('deliveries.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/pdf', 'pdf')->name('pdf');
            Route::get('/{delivery}', 'show')->name('show');
            Route::post('/', 'store')->name('store');
            Route::put('/{delivery}', 'update')->name('update');
        });

        Route::controller(NotificationController::class)->prefix('notifications')->name('notifications.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/badges', 'badges')->name('badges');
            Route::post('/generate', 'generate')->name('generate');
            Route::patch('/{notification}/read', 'read')->name('read');
        });

        Route::controller(ReportController::class)->prefix('reports/{report}')->name('reports.')->group(function () {
            Route::get('/pdf', 'pdf')->name('pdf');
            Route::get('/', 'index')->name('index');
        });

        Route::controller(BackendContactEnquiryController::class)->prefix('enquiries')->name('enquiries.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::patch('/{enquiry}/status', 'updateStatus')->name('status');
        });

        Route::controller(UserAccessController::class)->prefix('access/users')->name('users.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
            Route::get('/{user}', 'show')->name('show');
            Route::put('/{user}', 'update')->name('update');
            Route::delete('/{user}', 'destroy')->name('destroy');
            Route::get('/{user}/permissions', 'permissions')->name('permissions');
            Route::put('/{user}/permissions', 'updatePermissions')->name('permissions.update');
        });

        Route::controller(RoleController::class)->prefix('access/roles')->name('roles.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
            Route::get('/{role}', 'show')->name('show');
            Route::put('/{role}', 'update')->name('update');
            Route::delete('/{role}', 'destroy')->name('destroy');
        });

        Route::controller(LocationController::class)->prefix('access')->group(function () {
            Route::get('/locations', 'index')->name('locations.index');
            Route::post('/shops', 'storeShop')->name('shops.store');
            Route::get('/shops/{shop}', 'showShop')->name('shops.show');
            Route::put('/shops/{shop}', 'updateShop')->name('shops.update');
            Route::delete('/shops/{shop}', 'destroyShop')->name('shops.destroy');
            Route::post('/godowns', 'storeGodown')->name('godowns.store');
            Route::get('/godowns/{godown}', 'showGodown')->name('godowns.show');
            Route::put('/godowns/{godown}', 'updateGodown')->name('godowns.update');
            Route::delete('/godowns/{godown}', 'destroyGodown')->name('godowns.destroy');
        });

        Route::controller(ActivityLogController::class)->prefix('access')->group(function () {
            Route::get('/activity-logs', 'index')->name('activity-logs.index');
        });

        Route::controller(UserSessionController::class)->prefix('access')->group(function () {
            Route::get('/sessions', 'index')->name('sessions.index');
            Route::delete('/sessions/{sessionId}', 'destroy')->name('sessions.destroy');
            Route::delete('/users/{user}/sessions', 'destroyUser')->name('users.sessions.destroy');
        });

        Route::controller(SettingController::class)->prefix('settings')->name('settings.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::put('/', 'update')->name('update');
        });

        Route::controller(SystemMaintenanceController::class)->prefix('maintenance')->name('maintenance.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/backups', 'backup')->name('backups.store');
            Route::get('/backups/{name}', 'download')->where('name', '[A-Za-z0-9._-]+')->name('backups.download');
            Route::get('/export/{dataset}', 'export')->whereIn('dataset', ['reference-masters', 'products', 'parties'])->name('export');
            Route::post('/import', 'import')->name('import');
        });

        Route::controller(ThermalReceiptController::class)->prefix('sales/thermal-receipt/sample')->name('sales.thermal-receipt.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/pdf', 'downloadPdf')->name('pdf');
        });
    });
});
