@php
    $user = auth()->user();
    $documentModule = request()->routeIs('admin.documents.*') ? request()->route('module') : null;
    $reportKey = request()->routeIs('admin.reports.*') ? request()->route('report') : null;
    $partyType = request()->routeIs('admin.parties.*') ? request()->route('type') : null;
    $paymentType = request()->query('type');

    $menuItem = static fn (string $label, string $url, string $icon, bool $allowed, bool $active = false, ?string $badge = null): array => compact('label', 'url', 'icon', 'allowed', 'active', 'badge');
    $masterItem = fn (string $key, string $icon = 'ri-database-2-line'): array => $menuItem(
        config("erp_modules.reference.{$key}.title", str($key)->replace('-', ' ')->title()),
        route("admin.{$key}.index"),
        $icon,
        $user->can("{$key}.view"),
        request()->routeIs("admin.{$key}.*"),
    );
    $documentItem = fn (string $key, ?string $label = null, string $icon = 'ri-file-list-3-line'): array => $menuItem(
        $label ?: config("erp_modules.documents.{$key}.title", str($key)->replace('-', ' ')->title()),
        route('admin.documents.index', $key),
        $icon,
        $user->can("{$key}.view"),
        $documentModule === $key,
    );

    $partyItems = [
        $menuItem('Customers', route('admin.parties.index', 'customers'), 'ri-user-smile-line', $user->can('customers.view'), $partyType === 'customers'),
        $menuItem('Suppliers', route('admin.parties.index', 'suppliers'), 'ri-truck-line', $user->can('suppliers.view'), $partyType === 'suppliers'),
        $masterItem('customer-groups', 'ri-group-line'),
        $masterItem('supplier-groups', 'ri-team-line'),
    ];

    $itemItems = [
        $menuItem('Products', route('admin.products.index'), 'ri-shopping-bag-3-line', $user->can('viewAny', App\Models\Product::class), request()->routeIs('admin.products.*')),
        $masterItem('categories', 'ri-price-tag-3-line'),
        $masterItem('subcategories', 'ri-node-tree'),
        $masterItem('brands', 'ri-award-line'),
        $masterItem('units', 'ri-ruler-2-line'),
        $masterItem('variants', 'ri-git-branch-line'),
        $masterItem('grades', 'ri-medal-line'),
        $masterItem('price-lists', 'ri-money-rupee-circle-line'),
        $masterItem('tax-rates', 'ri-percent-line'),
        $masterItem('hsn-sac-codes', 'ri-barcode-box-line'),
    ];

    $saleItems = [
        $documentItem('sales-invoices', 'Sale Invoices', 'ri-bill-line'),
        $menuItem('Payment-In', route('admin.payments.index', ['type' => 'customer_collection']), 'ri-hand-coin-line', $user->can('payments.view'), request()->routeIs('admin.payments.*') && $paymentType === 'customer_collection', 'overdue_payments'),
        $documentItem('sales-orders', 'Sale Orders', 'ri-file-list-2-line'),
        $documentItem('delivery-challans', null, 'ri-truck-line'),
        $documentItem('sales-returns', 'Sale Returns / Credit', 'ri-arrow-go-back-line'),
        $documentItem('credit-notes', null, 'ri-file-reduce-line'),
        $documentItem('sales-quotations', 'Sale Quotations', 'ri-file-paper-2-line'),
        $documentItem('pos-billing', 'POS Billing', 'ri-store-3-line'),
        $menuItem('Thermal Receipt Sample', route('admin.sales.thermal-receipt.index'), 'ri-printer-line', $user->can('sales.view'), request()->routeIs('admin.sales.*')),
    ];

    $purchaseItems = [
        $documentItem('purchase-bills', null, 'ri-bill-line'),
        $menuItem('Payment-Out', route('admin.payments.index', ['type' => 'supplier_payment']), 'ri-refund-2-line', $user->can('payments.view'), request()->routeIs('admin.payments.*') && $paymentType === 'supplier_payment'),
        $documentItem('purchase-orders', null, 'ri-file-list-2-line'),
        $documentItem('goods-receipts', null, 'ri-inbox-archive-line'),
        $documentItem('purchase-returns', null, 'ri-arrow-go-back-line'),
        $documentItem('debit-notes', null, 'ri-file-reduce-line'),
        $menuItem('Expense Entry', route('admin.payments.index', ['type' => 'expense']), 'ri-wallet-3-line', $user->can('payments.view'), request()->routeIs('admin.payments.*') && $paymentType === 'expense'),
    ];

    $inventoryItems = [
        $menuItem('Stock', route('admin.stock.index'), 'ri-stack-line', $user->can('stock.view'), request()->routeIs('admin.stock.*'), 'low_stock'),
        $menuItem('Stock Transfers', route('admin.stock-transfers.index'), 'ri-arrow-left-right-line', $user->can('stock.transfer'), request()->routeIs('admin.stock-transfers.*'), 'pending_approvals'),
        $documentItem('stock-adjustments', null, 'ri-scales-3-line'),
        $documentItem('damaged-stock', null, 'ri-delete-bin-6-line'),
        $documentItem('expired-stock', null, 'ri-time-line'),
    ];

    $growthItems = [
        $menuItem('Enquiries', route('admin.enquiries.index'), 'ri-message-3-line', $user->can('viewAny', App\Models\ContactEnquiry::class), request()->routeIs('admin.enquiries.*')),
        $menuItem('Notifications', route('admin.notifications.index'), 'ri-notification-3-line', $user->can('notifications.view'), request()->routeIs('admin.notifications.*')),
        $masterItem('notification-templates', 'ri-mail-settings-line'),
    ];

    $cashBankItems = [
        $menuItem('All Receipts & Payments', route('admin.payments.index'), 'ri-exchange-funds-line', $user->can('payments.view'), request()->routeIs('admin.payments.*') && ! in_array($paymentType, ['customer_collection', 'supplier_payment', 'expense'], true), 'overdue_payments'),
        $masterItem('bank-accounts', 'ri-bank-line'),
        $masterItem('payment-methods', 'ri-bank-card-line'),
    ];

    $accountingItems = [
        $menuItem('Vouchers / Journal', route('admin.vouchers.index'), 'ri-book-2-line', $user->can('accounts.view'), request()->routeIs('admin.vouchers.*')),
        $masterItem('financial-years', 'ri-calendar-check-line'),
        $masterItem('invoice-sequences', 'ri-sort-number-asc'),
        $masterItem('tax-configurations', 'ri-percent-line'),
        $masterItem('number-sequences', 'ri-list-ordered'),
        $masterItem('invoice-templates', 'ri-layout-4-line'),
    ];

    $deliveryItems = [
        $menuItem('Delivery Assignments', route('admin.deliveries.index'), 'ri-route-line', $user->can('deliveries.view'), request()->routeIs('admin.deliveries.*'), 'pending_delivery'),
        $masterItem('vehicles', 'ri-truck-line'),
        $masterItem('drivers', 'ri-steering-2-line'),
        $masterItem('delivery-routes', 'ri-road-map-line'),
    ];

    $administrationItems = [
        $menuItem('Users', route('admin.users.index'), 'ri-user-settings-line', $user->can('users.view'), request()->routeIs('admin.users.*')),
        $menuItem('Roles & Permissions', route('admin.roles.index'), 'ri-shield-user-line', $user->can('roles.view'), request()->routeIs('admin.roles.*')),
        $menuItem('Shops & Godowns', route('admin.locations.index'), 'ri-store-2-line', $user->can('shops.view'), request()->routeIs('admin.locations.*')),
        $menuItem('Sessions', route('admin.sessions.index'), 'ri-device-line', $user->can('sessions.view'), request()->routeIs('admin.sessions.*')),
        $menuItem('Activity Logs', route('admin.activity-logs.index'), 'ri-history-line', $user->can('activity-logs.view'), request()->routeIs('admin.activity-logs.*')),
        $menuItem('Company Settings', route('admin.settings.index'), 'ri-settings-4-line', $user->can('settings.view'), request()->routeIs('admin.settings.*')),
        $menuItem('Backup & Data Tools', route('admin.maintenance.index'), 'ri-database-2-line', $user->can('maintenance.view'), request()->routeIs('admin.maintenance.*')),
    ];

    $menuGroups = [
        ['id' => 'sidebarParties', 'label' => 'Parties', 'icon' => 'ri-group-line', 'items' => $partyItems],
        ['id' => 'sidebarItems', 'label' => 'Items', 'icon' => 'ri-shopping-bag-line', 'items' => $itemItems],
        ['id' => 'sidebarSales', 'label' => 'Sale', 'icon' => 'ri-receipt-line', 'items' => $saleItems],
        ['id' => 'sidebarPurchases', 'label' => 'Purchase & Expense', 'icon' => 'ri-shopping-cart-2-line', 'items' => $purchaseItems],
        ['id' => 'sidebarInventory', 'label' => 'Inventory', 'icon' => 'ri-archive-stack-line', 'items' => $inventoryItems],
        ['id' => 'sidebarGrowth', 'label' => 'Grow Your Business', 'icon' => 'ri-line-chart-line', 'items' => $growthItems],
        ['id' => 'sidebarCashBank', 'label' => 'Cash, Bank & Assets', 'icon' => 'ri-bank-line', 'items' => $cashBankItems],
        ['id' => 'sidebarAccounting', 'label' => 'Accounting', 'icon' => 'ri-book-open-line', 'items' => $accountingItems],
        ['id' => 'sidebarDelivery', 'label' => 'Delivery', 'icon' => 'ri-truck-line', 'items' => $deliveryItems],
    ];

    $reportGroups = [
        ['id' => 'sidebarSalesReports', 'label' => 'Sales & Purchase', 'keys' => ['sales', 'purchases', 'gst']],
        ['id' => 'sidebarInventoryReports', 'label' => 'Inventory', 'keys' => ['stock', 'stock-valuation', 'low-stock', 'expiry']],
        ['id' => 'sidebarPartyReports', 'label' => 'Party & Payment', 'keys' => ['parties', 'party-ledger', 'receivables', 'payables', 'payments']],
        ['id' => 'sidebarAccountingReports', 'label' => 'Accounting', 'keys' => ['expenses', 'day-book', 'cash-book', 'bank-book', 'general-ledger', 'profit-loss']],
        ['id' => 'sidebarAuditReports', 'label' => 'Audit', 'keys' => ['audit', 'user-activity']],
    ];
@endphp

<div class="app-menu navbar-menu" data-sidebar-badges-url="{{ route('admin.notifications.badges') }}">
    <div class="navbar-brand-box">
        <a href="{{ route('admin.dashboard') }}" class="logo logo-dark">
            <span class="logo-sm"><img class="erp-brand-mark" src="{{ asset('frontend/assets/img/logo/favicon.png') }}" alt="Cholavin"></span>
            <span class="logo-lg"><img class="erp-brand-logo" src="{{ asset($commonSettings['brand_logo'] ?? 'frontend/assets/img/logo/logo-hm62.png') }}" alt="{{ $commonSettings['company_name'] ?? 'Cholavin' }}"></span>
        </a>
        <a href="{{ route('admin.dashboard') }}" class="logo logo-light">
            <span class="logo-sm"><img class="erp-brand-mark" src="{{ asset('frontend/assets/img/logo/favicon.png') }}" alt="Cholavin"></span>
            <span class="logo-lg"><img class="erp-brand-logo" src="{{ asset($commonSettings['brand_logo'] ?? 'frontend/assets/img/logo/logo-hm62.png') }}" alt="{{ $commonSettings['company_name'] ?? 'Cholavin' }}"></span>
        </a>
        <button type="button" class="btn btn-sm p-0 fs-20 header-item float-end btn-vertical-sm-hover" id="vertical-hover"><i class="ri-record-circle-line"></i></button>
    </div>

    <div class="dropdown sidebar-user m-1 rounded">
        <button type="button" class="btn material-shadow-none" data-bs-toggle="dropdown">
            <span class="d-flex align-items-center gap-2">
                <span class="avatar-xs"><span class="avatar-title rounded-circle bg-primary">{{ str($user->name)->substr(0, 1)->upper() }}</span></span>
                <span class="text-start"><span class="d-block fw-medium">{{ $user->name }}</span><span class="d-block fs-12 text-muted">{{ $user->role?->name }}</span></span>
            </span>
        </button>
        <div class="dropdown-menu dropdown-menu-end">
            <form method="post" action="{{ route('admin.logout') }}">@csrf<button class="dropdown-item"><i class="mdi mdi-logout me-1"></i> Logout</button></form>
        </div>
    </div>

    <div id="scrollbar" class="h-100" data-simplebar>
        <div class="container-fluid">
            {{-- Required by the Velzon layout runtime for vertical/two-column switching. --}}
            <div id="two-column-menu"></div>
            <ul class="navbar-nav" id="navbar-nav">
                @can('dashboard.view')
                    <li class="nav-item">
                        <a class="nav-link menu-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                            <i class="ri-home-4-line"></i><span>Dashboard</span>
                        </a>
                    </li>
                @endcan 
                @foreach($menuGroups as $group)
                    @php
                        $visibleItems = collect($group['items'])->where('allowed', true)->values();
                        $groupActive = $visibleItems->contains('active', true);
                    @endphp
                    @if($visibleItems->isNotEmpty())
                        <li class="nav-item">
                            <a class="nav-link menu-link {{ $groupActive ? 'active' : 'collapsed' }}" href="#{{ $group['id'] }}" data-bs-toggle="collapse" role="button" aria-expanded="{{ $groupActive ? 'true' : 'false' }}" aria-controls="{{ $group['id'] }}">
                                <i class="{{ $group['icon'] }}"></i><span>{{ $group['label'] }}</span>
                            </a>
                            <div class="collapse menu-dropdown {{ $groupActive ? 'show' : '' }}" id="{{ $group['id'] }}">
                                <ul class="nav nav-sm flex-column">
                                    @foreach($visibleItems as $item)
                                        <li class="nav-item">
                                            <a href="{{ $item['url'] }}" class="nav-link {{ $item['active'] ? 'active' : '' }}">
                                                <i class="{{ $item['icon'] }} submenu-icon"></i><span>{{ $item['label'] }}</span>
                                                @if($item['badge'])
                                                    <span class="erp-sidebar-badge d-none" data-sidebar-badge="{{ $item['badge'] }}">0</span>
                                                @endif
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </li>
                    @endif
                @endforeach

                @can('reports.view')
                    @php
                        $reportsActive = request()->routeIs('admin.reports.*');
                    @endphp
                    <li class="nav-item">
                        <a class="nav-link menu-link {{ $reportsActive ? 'active' : 'collapsed' }}" href="#sidebarReports" data-bs-toggle="collapse" role="button" aria-expanded="{{ $reportsActive ? 'true' : 'false' }}" aria-controls="sidebarReports">
                            <i class="ri-bar-chart-box-line"></i><span>Reports</span>
                        </a>
                        <div class="collapse menu-dropdown {{ $reportsActive ? 'show' : '' }}" id="sidebarReports">
                            <ul class="nav nav-sm flex-column">
                                @foreach($reportGroups as $reportGroup)
                                    @php
                                        $reportGroupActive = in_array($reportKey, $reportGroup['keys'], true);
                                    @endphp
                                    <li class="nav-item">
                                        <a href="#{{ $reportGroup['id'] }}" class="nav-link {{ $reportGroupActive ? 'active' : 'collapsed' }}" data-bs-toggle="collapse" role="button" aria-expanded="{{ $reportGroupActive ? 'true' : 'false' }}" aria-controls="{{ $reportGroup['id'] }}">
                                            {{ $reportGroup['label'] }}
                                        </a>
                                        <div class="collapse menu-dropdown {{ $reportGroupActive ? 'show' : '' }}" id="{{ $reportGroup['id'] }}">
                                            <ul class="nav nav-sm flex-column">
                                                @foreach($reportGroup['keys'] as $key)
                                                    <li class="nav-item">
                                                        <a href="{{ route('admin.reports.index', $key) }}" class="nav-link {{ $reportKey === $key ? 'active' : '' }}">{{ \App\Services\ReportService::REPORTS[$key] }}</a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </li>
                @endcan

                @php
                    $visibleAdministrationItems = collect($administrationItems)->where('allowed', true)->values();
                    $administrationActive = $visibleAdministrationItems->contains('active', true);
                @endphp
                @if($visibleAdministrationItems->isNotEmpty())
                    <li class="nav-item">
                        <a class="nav-link menu-link {{ $administrationActive ? 'active' : 'collapsed' }}" href="#sidebarAdministration" data-bs-toggle="collapse" role="button" aria-expanded="{{ $administrationActive ? 'true' : 'false' }}" aria-controls="sidebarAdministration">
                            <i class="ri-settings-3-line"></i><span>Administration</span>
                        </a>
                        <div class="collapse menu-dropdown {{ $administrationActive ? 'show' : '' }}" id="sidebarAdministration">
                            <ul class="nav nav-sm flex-column">
                                @foreach($visibleAdministrationItems as $item)
                                    <li class="nav-item">
                                        <a href="{{ $item['url'] }}" class="nav-link {{ $item['active'] ? 'active' : '' }}">
                                            <i class="{{ $item['icon'] }} submenu-icon"></i><span>{{ $item['label'] }}</span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </li>
                @endif
            </ul>
        </div>
    </div>
    <div class="sidebar-background"></div>
</div>
<div class="vertical-overlay"></div>
