<?php

use App\Http\Controllers\Backend\AccessControlController;
use App\Http\Controllers\Backend\AuthController;
use App\Http\Controllers\Backend\DashboardController;
use App\Http\Controllers\Backend\LocationContextController;
use App\Http\Controllers\Backend\SettingController;
use App\Http\Controllers\Frontend\ContactEnquiryController;
use App\Http\Controllers\Frontend\ProductController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'frontend.pages.home')->name('frontend.home');
Route::view('/about-us', 'frontend.pages.about')->name('frontend.about');
Route::get('/products', [ProductController::class, 'index'])->name('frontend.products');
Route::view('/delivery', 'frontend.pages.delivery')->name('frontend.delivery');
Route::view('/contact-us', 'frontend.pages.contact')->name('frontend.contact');
Route::post('/contact-enquiry', [ContactEnquiryController::class, 'store'])->name('frontend.enquiry.store');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AuthController::class, 'create'])->name('login');
        Route::post('/login', [AuthController::class, 'store'])->name('login.store');
    });

    Route::middleware(['auth', 'active', 'access.context', 'activity'])->group(function () {
        Route::get('/dashboard', DashboardController::class)->name('dashboard');
        Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');
        Route::post('/location-context', [LocationContextController::class, 'update'])->name('location-context.update');

        Route::resource('products', App\Http\Controllers\Backend\ProductController::class)->except(['show']);
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
        Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
        Route::put('settings', [SettingController::class, 'update'])->name('settings.update');
    });
});
