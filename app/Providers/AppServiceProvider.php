<?php

namespace App\Providers;

use App\Models\ContactEnquiry;
use App\Models\Product;
use App\Policies\ContactEnquiryPolicy;
use App\Policies\ProductPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Product::class, ProductPolicy::class);
        Gate::policy(ContactEnquiry::class, ContactEnquiryPolicy::class);

        Gate::before(fn ($user) => $user->isSuperAdmin() ? true : null);

        foreach (config('erp_permissions', []) as $module => $actions) {
            foreach ($actions as $action) {
                $permissionCode = "{$module}.{$action}";
                Gate::define($permissionCode, fn ($user) => $user->hasPermission($permissionCode));
            }
        }

        View::share('commonSettings', config('cholavin_dashboard.common_settings', []));

    }
}
