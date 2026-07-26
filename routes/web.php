<?php

use App\Http\Controllers\Backend\AuthController;
use App\Http\Controllers\Backend\ContactEnquiryController as BackendContactEnquiryController;
use App\Http\Controllers\Backend\LocationContextController;
use App\Http\Controllers\Backend\PasswordResetController;
use App\Http\Controllers\Backend\ProductController as BackendProductController;
use App\Http\Controllers\Frontend\ContactEnquiryController;
use App\Http\Controllers\Frontend\ProductController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'frontend.pages.home')->name('frontend.home');
Route::view('/about-us', 'frontend.pages.about')->name('frontend.about');
Route::get('/products', [ProductController::class, 'index'])->name('frontend.products');
Route::view('/delivery', 'frontend.pages.delivery')->name('frontend.delivery');
Route::view('/contact-us', 'frontend.pages.contact')->name('frontend.contact');
Route::post('/contact-enquiry', [ContactEnquiryController::class, 'store'])->name('frontend.enquiry.store');

Route::get('/admin/reset-password/{token}', [PasswordResetController::class, 'reset'])
    ->middleware('guest')->name('password.reset');
Route::post('/admin/reset-password', [PasswordResetController::class, 'update'])
    ->middleware('guest')->name('password.update');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AuthController::class, 'create'])->name('login');
        Route::post('/login', [AuthController::class, 'store'])->name('login.store');
        Route::get('/forgot-password', [PasswordResetController::class, 'request'])->name('password.request');
        Route::post('/forgot-password', [PasswordResetController::class, 'email'])->name('password.email');
    });

    Route::middleware(['auth', 'active', 'access.context', 'activity'])->group(function () {
        Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');
        Route::get('/location-context/godowns', [LocationContextController::class, 'godowns'])->name('location-context.godowns');
        Route::post('/location-context/shop', [LocationContextController::class, 'switchShop'])->name('location-context.switch-shop');
        Route::post('/location-context/godown', [LocationContextController::class, 'switchGodown'])->name('location-context.switch-godown');

        Route::view('/dashboard', 'backend.pages.dashboard')->middleware('can:dashboard.view')->name('dashboard');
        Route::get('/workspace/{module}', function (string $module) {
            $modules = config('cholavin_dashboard.modules', []);
            abort_unless(isset($modules[$module]), 404);

            return view('backend.pages.workspace', ['module' => $modules[$module], 'moduleKey' => $module]);
        })->middleware('can:dashboard.view')->name('workspace');

        Route::resource('products', BackendProductController::class)->except(['show']);
        Route::get('enquiries', [BackendContactEnquiryController::class, 'index'])->name('enquiries.index');
        Route::patch('enquiries/{enquiry}/status', [BackendContactEnquiryController::class, 'updateStatus'])->name('enquiries.status');
    });
});
