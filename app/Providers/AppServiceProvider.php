<?php

namespace App\Providers;

use App\Models\ContactEnquiry;
use App\Models\Product;
use App\Models\Setting;
use App\Policies\ContactEnquiryPolicy;
use App\Policies\ProductPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
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

        foreach (['users', 'roles', 'shops', 'godowns', 'activity-logs', 'settings'] as $module) {
            foreach (['view', 'create', 'update', 'delete'] as $action) {
                Gate::define("{$module}.{$action}", fn ($user) => $user->hasPermission("{$module}.{$action}"));
            }
        }

        $commonSettings = Schema::hasTable('settings') ? Setting::values() : [];
        View::share('commonSettings', $commonSettings);

        if (filled($commonSettings['mail_host'] ?? null)) {
            config([
                'mail.default' => 'smtp',
                'mail.mailers.smtp.host' => $commonSettings['mail_host'],
                'mail.mailers.smtp.port' => $commonSettings['mail_port'] ?: 587,
                'mail.mailers.smtp.username' => $commonSettings['mail_username'] ?: null,
                'mail.mailers.smtp.password' => $commonSettings['mail_password'] ?: null,
                'mail.mailers.smtp.scheme' => ($commonSettings['mail_encryption'] ?? 'tls') === 'ssl' ? 'smtps' : 'smtp',
                'mail.from.address' => $commonSettings['mail_from_address'] ?: config('mail.from.address'),
                'mail.from.name' => $commonSettings['mail_from_name'] ?: config('mail.from.name'),
            ]);
        }
    }
}
