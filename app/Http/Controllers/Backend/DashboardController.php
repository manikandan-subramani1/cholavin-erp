<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\ContactEnquiry;
use App\Models\Godown;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Shop;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $settings = Setting::values();

        return view('backend.pages.dashboard', [
            'metrics' => [
                ['label' => 'Products', 'value' => Product::count(), 'icon' => 'ri-shopping-bag-3-line', 'route' => 'admin.products.index', 'ability' => ['viewAny', Product::class]],
                ['label' => 'Open Enquiries', 'value' => ContactEnquiry::where('status', 'not_contacted')->count(), 'icon' => 'ri-customer-service-2-line', 'route' => 'admin.enquiries.index', 'ability' => ['viewAny', ContactEnquiry::class]],
                ['label' => 'Active Users', 'value' => User::where('is_active', true)->count(), 'icon' => 'ri-team-line', 'route' => 'admin.users.index', 'ability' => 'users.view'],
                ['label' => 'Active Locations', 'value' => Shop::where('is_active', true)->count() + Godown::where('is_active', true)->count(), 'icon' => 'ri-store-2-line', 'route' => 'admin.locations.index', 'ability' => 'shops.view'],
            ],
            'masterChecks' => [
                ['label' => 'Products', 'count' => Product::count(), 'route' => 'admin.products.create', 'ability' => ['create', Product::class], 'message' => 'Add the first product to make billing ready.'],
                ['label' => 'Shops', 'count' => Shop::count(), 'route' => 'admin.locations.index', 'ability' => 'shops.create', 'message' => 'Create a shop before entering shop-owned transactions.'],
                ['label' => 'Godowns', 'count' => Godown::count(), 'route' => 'admin.locations.index', 'ability' => 'godowns.create', 'message' => 'Add a godown for inventory movement and stock.'],
                ['label' => 'Staff users', 'count' => User::whereHas('role', fn ($query) => $query->where('is_super_admin', false))->count(), 'route' => 'admin.users.index', 'ability' => 'users.create', 'message' => 'Create staff and assign their working locations.'],
                ['label' => 'Company contact', 'count' => collect(['contact_phone', 'contact_email', 'company_address'])->filter(fn ($key) => filled($settings[$key] ?? null))->count(), 'expected' => 3, 'route' => 'admin.settings.index', 'ability' => 'settings.update', 'message' => 'Complete phone, email, and company address.'],
            ],
        ]);
    }
}
