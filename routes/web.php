<?php

use App\Http\Controllers\Backend\AccessControlController;
use App\Http\Controllers\Backend\AuthController;
use App\Http\Controllers\Backend\DashboardController;
use App\Http\Controllers\Backend\LocationContextController;
use App\Http\Controllers\Backend\SettingController;
use App\Http\Controllers\Backend\ThermalReceiptController;
use App\Http\Controllers\Backend\UserSessionController;
use App\Http\Controllers\Backend\ReferenceMasterController;
use App\Http\Controllers\Backend\PartyController;
use App\Http\Controllers\Backend\CommercialDocumentController;
use App\Http\Controllers\Backend\LookupController;
use App\Http\Controllers\Backend\StockController;
use App\Http\Controllers\Backend\StockTransferController;
use App\Http\Controllers\Backend\PaymentController;
use App\Http\Controllers\Backend\VoucherController;
use App\Http\Controllers\Backend\DeliveryController;
use App\Http\Controllers\Backend\NotificationController;
use App\Http\Controllers\Backend\ReportController;
use App\Http\Controllers\Backend\PasswordResetController;
use App\Http\Controllers\Backend\SystemMaintenanceController;
use App\Http\Controllers\Frontend\ContactEnquiryController;
use App\Http\Controllers\Frontend\ProductController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'frontend.pages.home')->name('frontend.home');
Route::view('/about-us', 'frontend.pages.about')->name('frontend.about');
Route::get('/products', [ProductController::class, 'index'])->name('frontend.products');
Route::view('/delivery', 'frontend.pages.delivery')->name('frontend.delivery');
Route::view('/contact-us', 'frontend.pages.contact')->name('frontend.contact');
Route::post('/contact-enquiry', [ContactEnquiryController::class, 'store'])->name('frontend.enquiry.store');
Route::get('/admin/reset-password/{token}', [PasswordResetController::class, 'reset'])->middleware('guest')->name('password.reset');
Route::post('/admin/reset-password', [PasswordResetController::class, 'update'])->middleware('guest')->name('password.update');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AuthController::class, 'create'])->name('login');
        Route::post('/login', [AuthController::class, 'store'])->name('login.store');
        Route::get('/forgot-password', [PasswordResetController::class, 'request'])->name('password.request');
        Route::post('/forgot-password', [PasswordResetController::class, 'email'])->name('password.email');
    });

    Route::middleware(['auth', 'active', 'access.context', 'activity'])->group(function () {
        Route::get('/dashboard', DashboardController::class)->name('dashboard');
        Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');
        Route::get('/location-context/godowns', [LocationContextController::class, 'godowns'])->name('location-context.godowns');
        Route::post('/location-context/shop', [LocationContextController::class, 'switchShop'])->name('location-context.switch-shop');
        Route::post('/location-context/godown', [LocationContextController::class, 'switchGodown'])->name('location-context.switch-godown');

        Route::resource('products', App\Http\Controllers\Backend\ProductController::class)->except(['show']);
        Route::get('masters/{module}/pdf', [ReferenceMasterController::class, 'pdf'])->name('masters.pdf');
        Route::get('masters/{module}', [ReferenceMasterController::class, 'index'])->name('masters.index');
        Route::post('masters/{module}', [ReferenceMasterController::class, 'store'])->name('masters.store');
        Route::put('masters/{module}/{master}', [ReferenceMasterController::class, 'update'])->name('masters.update');
        Route::delete('masters/{module}/{master}', [ReferenceMasterController::class, 'destroy'])->name('masters.destroy');
        Route::get('parties/{type}/pdf', [PartyController::class, 'pdf'])->name('parties.pdf');
        Route::get('parties/{type}', [PartyController::class, 'index'])->name('parties.index');
        Route::post('parties/{type}', [PartyController::class, 'store'])->name('parties.store');
        Route::get('parties/{type}/{party}/transactions', [PartyController::class, 'transactions'])->name('parties.transactions');
        Route::get('parties/{type}/{party}', [PartyController::class, 'show'])->name('parties.show');
        Route::put('parties/{type}/{party}', [PartyController::class, 'update'])->name('parties.update');
        Route::delete('parties/{type}/{party}', [PartyController::class, 'destroy'])->name('parties.destroy');
        Route::get('lookups/products', [LookupController::class, 'products'])->name('lookups.products');
        Route::get('lookups/parties', [LookupController::class, 'parties'])->name('lookups.parties');
        Route::get('documents/{module}/pdf', [CommercialDocumentController::class, 'pdf'])->name('documents.pdf');
        Route::get('documents/{module}/{document}/print', [CommercialDocumentController::class, 'print'])->name('documents.print');
        Route::get('documents/{module}', [CommercialDocumentController::class, 'index'])->name('documents.index');
        Route::get('documents/{module}/{document}', [CommercialDocumentController::class, 'show'])->name('documents.show');
        Route::post('documents/{module}', [CommercialDocumentController::class, 'store'])->name('documents.store');
        Route::put('documents/{module}/{document}', [CommercialDocumentController::class, 'update'])->name('documents.update');
        Route::delete('documents/{module}/{document}', [CommercialDocumentController::class, 'destroy'])->name('documents.destroy');
        Route::get('inventory/stock/pdf',[StockController::class,'pdf'])->name('stock.pdf');
        Route::get('inventory/stock',[StockController::class,'index'])->name('stock.index');
        Route::get('inventory/movements',[StockController::class,'movements'])->name('stock.movements');
        Route::get('inventory/transfers',[StockTransferController::class,'index'])->name('stock-transfers.index');
        Route::post('inventory/transfers',[StockTransferController::class,'store'])->name('stock-transfers.store');
        Route::get('accounts/payments',[PaymentController::class,'index'])->name('payments.index');
        Route::post('accounts/payments',[PaymentController::class,'store'])->name('payments.store');
        Route::get('accounts/vouchers',[VoucherController::class,'index'])->name('vouchers.index');
        Route::post('accounts/vouchers',[VoucherController::class,'store'])->name('vouchers.store');
        Route::get('deliveries',[DeliveryController::class,'index'])->name('deliveries.index');
        Route::get('deliveries/{delivery}',[DeliveryController::class,'show'])->name('deliveries.show');
        Route::post('deliveries',[DeliveryController::class,'store'])->name('deliveries.store');
        Route::put('deliveries/{delivery}',[DeliveryController::class,'update'])->name('deliveries.update');
        Route::get('notifications',[NotificationController::class,'index'])->name('notifications.index');
        Route::post('notifications/generate',[NotificationController::class,'generate'])->name('notifications.generate');
        Route::patch('notifications/{notification}/read',[NotificationController::class,'read'])->name('notifications.read');
        Route::get('reports/{report}/pdf',[ReportController::class,'pdf'])->name('reports.pdf');
        Route::get('reports/{report}',[ReportController::class,'index'])->name('reports.index');
        Route::get('enquiries', [App\Http\Controllers\Backend\ContactEnquiryController::class, 'index'])->name('enquiries.index');
        Route::patch('enquiries/{enquiry}/status', [App\Http\Controllers\Backend\ContactEnquiryController::class, 'updateStatus'])->name('enquiries.status');

        Route::get('access/users', [AccessControlController::class, 'users'])->name('users.index');
        Route::post('access/users', [AccessControlController::class, 'storeUser'])->name('users.store');
        Route::put('access/users/{user}', [AccessControlController::class, 'updateUser'])->name('users.update');
        Route::delete('access/users/{user}', [AccessControlController::class, 'destroyUser'])->name('users.destroy');
        Route::get('access/users/{user}/permissions', [AccessControlController::class, 'userPermissions'])->name('users.permissions');
        Route::put('access/users/{user}/permissions', [AccessControlController::class, 'updateUserPermissions'])->name('users.permissions.update');

        Route::get('access/roles', [AccessControlController::class, 'roles'])->name('roles.index');
        Route::post('access/roles', [AccessControlController::class, 'storeRole'])->name('roles.store');
        Route::put('access/roles/{role}', [AccessControlController::class, 'updateRole'])->name('roles.update');
        Route::delete('access/roles/{role}', [AccessControlController::class, 'destroyRole'])->name('roles.destroy');

        Route::get('access/locations', [AccessControlController::class, 'locations'])->name('locations.index');
        Route::post('access/shops', [AccessControlController::class, 'storeShop'])->name('shops.store');
        Route::put('access/shops/{shop}', [AccessControlController::class, 'updateShop'])->name('shops.update');
        Route::delete('access/shops/{shop}', [AccessControlController::class, 'destroyShop'])->name('shops.destroy');
        Route::post('access/godowns', [AccessControlController::class, 'storeGodown'])->name('godowns.store');
        Route::put('access/godowns/{godown}', [AccessControlController::class, 'updateGodown'])->name('godowns.update');
        Route::delete('access/godowns/{godown}', [AccessControlController::class, 'destroyGodown'])->name('godowns.destroy');

        Route::get('access/activity-logs', [AccessControlController::class, 'activityLogs'])->name('activity-logs.index');
        Route::get('access/sessions', [UserSessionController::class, 'index'])->name('sessions.index');
        Route::delete('access/sessions/{sessionId}', [UserSessionController::class, 'destroy'])->name('sessions.destroy');
        Route::delete('access/users/{user}/sessions', [UserSessionController::class, 'destroyUser'])->name('users.sessions.destroy');
        Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
        Route::put('settings', [SettingController::class, 'update'])->name('settings.update');
        Route::get('maintenance', [SystemMaintenanceController::class, 'index'])->name('maintenance.index');
        Route::post('maintenance/backups', [SystemMaintenanceController::class, 'backup'])->name('maintenance.backups.store');
        Route::get('maintenance/backups/{name}', [SystemMaintenanceController::class, 'download'])->where('name', '[A-Za-z0-9._-]+')->name('maintenance.backups.download');
        Route::get('maintenance/export/{dataset}', [SystemMaintenanceController::class, 'export'])->whereIn('dataset', ['reference-masters', 'products', 'parties'])->name('maintenance.export');
        Route::post('maintenance/import', [SystemMaintenanceController::class, 'import'])->name('maintenance.import');

        Route::get('sales/thermal-receipt/sample', [ThermalReceiptController::class, 'index'])->name('sales.thermal-receipt.index');
        Route::get('sales/thermal-receipt/sample/pdf', [ThermalReceiptController::class, 'downloadPdf'])->name('sales.thermal-receipt.pdf');
    });
});
