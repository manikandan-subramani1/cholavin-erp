<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'frontend.pages.home')->name('frontend.home');
Route::view('/about-us', 'frontend.pages.about')->name('frontend.about');
Route::get('/products', [\App\Http\Controllers\Frontend\ProductController::class, 'index'])->name('frontend.products');
Route::view('/delivery', 'frontend.pages.delivery')->name('frontend.delivery');
Route::view('/contact-us', 'frontend.pages.contact')->name('frontend.contact');
Route::post('/contact-enquiry', [\App\Http\Controllers\Frontend\ContactEnquiryController::class, 'store'])->name('frontend.enquiry.store');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::view('/login', 'backend.auth.login')->name('login');
    Route::view('/dashboard', 'backend.pages.dashboard')->name('dashboard');
    Route::resource('products', \App\Http\Controllers\Backend\ProductController::class)->except(['show']);
    Route::get('enquiries', [\App\Http\Controllers\Backend\ContactEnquiryController::class, 'index'])->name('enquiries.index');
    Route::patch('enquiries/{enquiry}/status', [\App\Http\Controllers\Backend\ContactEnquiryController::class, 'updateStatus'])->name('enquiries.status');
});
