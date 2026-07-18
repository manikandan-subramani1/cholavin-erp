<a class="erp-skip-link" href="#app-content">Skip to main content</a>

<header id="page-topbar" class="erp-topbar">
    <div class="layout-width">
        <div class="navbar-header">
            <div class="erp-header-start">
                <div class="navbar-brand-box horizontal-logo">
                    <a href="{{ route('admin.dashboard') }}" class="logo logo-dark" aria-label="Cholavin ERP home">
                        <span class="logo-sm"><img class="erp-brand-mark" src="{{ asset('frontend/assets/img/logo/favicon.png') }}" alt=""></span>
                        <span class="logo-lg"><img class="erp-brand-logo" src="{{ asset($commonSettings['brand_logo'] ?? 'frontend/assets/img/logo/logo-hm62.png') }}" alt="{{ $commonSettings['company_name'] ?? 'Cholavin' }}"></span>
                    </a>
                    <a href="{{ route('admin.dashboard') }}" class="logo logo-light" aria-label="Cholavin ERP home">
                        <span class="logo-sm"><img class="erp-brand-mark" src="{{ asset('frontend/assets/img/logo/favicon.png') }}" alt=""></span>
                        <span class="logo-lg"><img class="erp-brand-logo" src="{{ asset($commonSettings['brand_logo'] ?? 'frontend/assets/img/logo/logo-hm62.png') }}" alt="{{ $commonSettings['company_name'] ?? 'Cholavin' }}"></span>
                    </a>
                </div>

                <button type="button" class="erp-icon-button vertical-menu-btn topnav-hamburger" id="topnav-hamburger-icon" aria-label="Toggle navigation">
                    <span class="hamburger-icon"><span></span><span></span><span></span></span>
                </button>

                <form class="erp-global-search" id="global-search-form" data-search-url="{{ route('admin.global-search') }}" role="search">
                    <i class="ri-search-line" aria-hidden="true"></i>
                    <input type="search" placeholder="Search customers, products, invoices…" aria-label="Search modules and records" autocomplete="off" id="search-options">
                    <kbd class="erp-search-shortcut">/</kbd>
                    <button class="erp-search-clear d-none" id="search-close-options" type="button" aria-label="Clear search"><i class="ri-close-circle-fill"></i></button>
                    <div class="dropdown-menu p-0" id="search-dropdown" aria-live="polite">
                        <div id="global-search-results" class="global-search-results">
                            <div class="global-search-state"><i class="ri-search-line"></i><span>Enter at least 2 characters to search.</span></div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="erp-header-actions">
                <div class="dropdown">
                    <button class="erp-quick-create-button" id="erp-quick-create-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="ri-add-line"></i><span>Quick Create</span><i class="ri-arrow-down-s-line"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end erp-command-menu" aria-labelledby="erp-quick-create-toggle">
                        <div class="erp-command-heading"><span>Quick Create</span><small>Start common work instantly</small></div>
                        <div class="erp-command-grid">
                            @can('sales-invoices.create')<a href="{{ route('admin.documents.index', 'sales-invoices') }}" class="erp-command-item is-sales"><i class="ri-receipt-line"></i><span>New Sale</span><kbd>Alt+S</kbd></a>@endcan
                            @can('purchase-bills.create')<a href="{{ route('admin.documents.index', 'purchase-bills') }}" class="erp-command-item is-purchase"><i class="ri-shopping-cart-2-line"></i><span>New Purchase</span><kbd>Alt+P</kbd></a>@endcan
                            @can('payments.create')<a href="{{ route('admin.payments.index', ['type' => 'customer_collection']) }}" class="erp-command-item is-customer"><i class="ri-hand-coin-line"></i><span>Payment In</span><kbd>Alt+R</kbd></a>@endcan
                            @can('payments.create')<a href="{{ route('admin.payments.index', ['type' => 'supplier_payment']) }}" class="erp-command-item is-accounting"><i class="ri-refund-2-line"></i><span>Payment Out</span><kbd>Alt+O</kbd></a>@endcan
                            @can('stock.transfer')<a href="{{ route('admin.stock-transfers.index') }}" class="erp-command-item is-inventory"><i class="ri-arrow-left-right-line"></i><span>Stock Transfer</span></a>@endcan
                            @can('products.create')<a href="{{ route('admin.products.create') }}" class="erp-command-item is-inventory"><i class="ri-shopping-bag-3-line"></i><span>Product</span></a>@endcan
                        </div>
                    </div>
                </div>

                <button type="button" class="erp-icon-button d-lg-none" id="erp-context-mobile-toggle" aria-label="Open business context" title="Business context">
                    <i class="ri-building-2-line"></i>
                </button>

                <div class="dropdown d-none d-md-block">
                    <button type="button" class="erp-icon-button" id="erp-calculator-toggle" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" aria-label="Open calculator" title="Calculator">
                        <i class="ri-calculator-line"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end erp-calculator" aria-labelledby="erp-calculator-toggle">
                        <div class="erp-calculator-head"><span>Calculator</span><button type="button" data-calc-action="clear">Clear</button></div>
                        <input id="erp-calculator-display" value="0" readonly aria-label="Calculator display">
                        <div class="erp-calculator-grid">
                            @foreach(['7','8','9','/','4','5','6','*','1','2','3','-','0','.','=','+'] as $key)
                                <button type="button" data-calc-key="{{ $key }}" class="{{ in_array($key, ['/', '*', '-', '+', '='], true) ? 'is-operator' : '' }}">{{ $key === '*' ? '×' : ($key === '/' ? '÷' : $key) }}</button>
                            @endforeach
                        </div>
                    </div>
                </div>

                <button type="button" class="erp-icon-button d-none d-sm-grid" id="erp-fullscreen-toggle" data-toggle="fullscreen" aria-label="Toggle fullscreen" title="Fullscreen"><i class="ri-fullscreen-line"></i></button>
                <button type="button" class="erp-icon-button light-dark-mode d-none d-sm-grid" aria-label="Switch theme" title="Theme"><i class="ri-moon-line"></i></button>

                <div id="notificationDropdown" class="erp-notification-link">
                    @can('notifications.view')
                        <a href="{{ route('admin.notifications.index') }}" class="erp-icon-button" aria-label="Open notifications" title="Notifications"><i class="ri-notification-3-line"></i><span class="erp-notification-dot"></span></a>
                    @endcan
                </div>

                <div class="dropdown erp-user-menu">
                    <button type="button" class="erp-user-button" id="page-header-user-dropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="erp-user-avatar">{{ str(auth()->user()->name ?? 'U')->substr(0, 1)->upper() }}</span>
                        <span class="erp-user-copy"><strong>{{ auth()->user()->name ?? 'User' }}</strong><small>{{ auth()->user()->role?->name }}</small></span>
                        <i class="ri-arrow-down-s-line"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end erp-user-dropdown">
                        <div class="erp-user-dropdown-head"><span class="erp-user-avatar">{{ str(auth()->user()->name ?? 'U')->substr(0, 1)->upper() }}</span><div><strong>{{ auth()->user()->name ?? 'User' }}</strong><small>{{ auth()->user()->email }}</small></div></div>
                        @can('settings.view')<a class="dropdown-item" href="{{ route('admin.settings.index') }}"><i class="ri-settings-4-line"></i>Company Settings</a>@endcan
                        @can('sessions.view')<a class="dropdown-item" href="{{ route('admin.sessions.index') }}"><i class="ri-device-line"></i>Active Sessions</a>@endcan
                        <div class="dropdown-divider"></div>
                        <form id="ajax-logout-form" method="post" action="{{ route('admin.logout') }}">@csrf<button class="dropdown-item text-danger" type="submit"><i class="ri-logout-box-r-line"></i>Logout</button></form>
                    </div>
                </div>
            </div>
        </div>

        @if (($headerGodowns ?? collect())->isNotEmpty() || ($headerShops ?? collect())->isNotEmpty() || ($headerFinancialYears ?? collect())->isNotEmpty())
            <div class="erp-context-bar" id="erp-context-panel">
                <div class="erp-context-bar-head d-lg-none"><div><span>Working Context</span><small>Choose where you are working</small></div><button id="erp-context-mobile-close" type="button" aria-label="Close context"><i class="ri-close-line"></i></button></div>
                <div id="erp-business-context" class="erp-context-form"
                    data-shops-url="{{ route('admin.location-context.shops') }}"
                    data-switch-shop-url="{{ route('admin.location-context.switch-shop') }}"
                    data-switch-godown-url="{{ route('admin.location-context.switch-godown') }}"
                    data-switch-financial-year-url="{{ route('admin.location-context.switch-financial-year') }}">
                    <div class="erp-context-field erp-context-shop">
                        <i class="ri-store-2-line erp-context-field-icon"></i>
                        <label for="header-shop-context" class="erp-context-label">Shop</label>
                        <select id="header-shop-context" name="shop_id" class="form-select form-select-sm erp-context-select"
                            data-placeholder="Search shop" data-allow-clear="{{ auth()->user()->isSuperAdmin() ? 'true' : 'false' }}"
                            data-allow-all="{{ auth()->user()->isSuperAdmin() ? 'true' : 'false' }}" data-can-switch="{{ auth()->user()->can('shops.switch') ? '1' : '0' }}"
                            @disabled(! auth()->user()->can('shops.switch') || $headerShops->count() <= 1)>
                            @if(auth()->user()->isSuperAdmin() && !$activeGodownId)<option value="">All shops</option>@endif
                            @foreach ($headerShops as $shop)<option value="{{ $shop->id }}" @selected($activeShopId === $shop->id)>#{{ $shop->id }} — {{ $shop->name }} ({{ $shop->code }})</option>@endforeach
                        </select>
                    </div>
                    <div class="erp-context-field erp-context-godown">
                        <i class="ri-home-gear-line erp-context-field-icon"></i>
                        <label for="header-godown-context" class="erp-context-label">Godown</label>
                        <select id="header-godown-context" name="godown_id" class="form-select form-select-sm erp-context-select"
                            data-placeholder="Search godown" data-allow-clear="{{ auth()->user()->isSuperAdmin() ? 'true' : 'false' }}"
                            data-can-switch="{{ auth()->user()->can('godowns.switch') ? '1' : '0' }}"
                            @disabled(! auth()->user()->can('godowns.switch') || $headerGodowns->count() <= 1)>
                            @if(auth()->user()->isSuperAdmin())<option value="">All godowns</option>@endif
                            @foreach ($headerGodowns as $godown)<option value="{{ $godown->id }}" @selected($activeGodownId === $godown->id)>#{{ $godown->id }} — {{ $godown->name }} ({{ $godown->code }})</option>@endforeach
                        </select>
                    </div>
                    @if (($headerFinancialYears ?? collect())->isNotEmpty())
                        <div class="erp-context-field erp-context-financial-year">
                            <i class="ri-calendar-2-line erp-context-field-icon"></i>
                            <label for="header-financial-year-context" class="erp-context-label">Financial Year</label>
                            <select id="header-financial-year-context" name="financial_year_id" class="form-select form-select-sm erp-context-select"
                                data-placeholder="Select financial year" data-can-switch="{{ auth()->user()->can('financial-years.switch') ? '1' : '0' }}"
                                @disabled(! auth()->user()->can('financial-years.switch') || $headerFinancialYears->count() <= 1)>
                                @foreach ($headerFinancialYears as $financialYear)<option value="{{ $financialYear->id }}" @selected($activeFinancialYearId === $financialYear->id)>{{ $financialYear->name }} ({{ $financialYear->code }})</option>@endforeach
                            </select>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
</header>

{{-- Retained for compatibility with the existing theme runtime; no demo notifications are rendered. --}}
<div id="removeNotificationModal" class="modal fade" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-body"></div></div></div></div>

