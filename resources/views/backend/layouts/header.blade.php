<header id="page-topbar">
    <div class="layout-width">
        <div class="navbar-header">
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-sm px-3 fs-16 header-item vertical-menu-btn topnav-hamburger-icon" id="topnav-hamburger-icon" data-sidebar-toggle aria-label="Toggle navigation">
                    <span class="hamburger-icon"><span></span><span></span><span></span></span>
                </button>
                <a href="{{ route('admin.dashboard') }}" class="d-lg-none d-flex align-items-center gap-2 text-decoration-none fw-bold text-dark">
                    <span class="avatar-xs rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center">C</span>
                    <span>Cholavin ERP</span>
                </a>
            </div>

            <div class="d-flex align-items-center gap-2 flex-grow-1">
                <form class="app-search d-none d-md-block flex-grow-1" role="search" onsubmit="return false;">
                    <div class="position-relative">
                        <input type="text" class="form-control" id="search-options" placeholder="Search anything..." autocomplete="off" aria-label="Search anything">
                        <span class="ri-search-line search-widget-icon"></span>
                        <span class="ri-close-line search-widget-icon search-widget-icon-close d-none" id="search-close-options"></span>
                    </div>
                    <div class="dropdown-menu dropdown-menu-lg" id="search-dropdown">
                        <div class="dropdown-header"><h6 class="text-overflow text-muted mb-0">Recent searches</h6></div>
                        <div class="dropdown-item-text text-muted">Search modules, products, customers and invoices.</div>
                    </div>
                </form>
                <button type="button" class="btn btn-primary d-none d-xl-inline-flex align-items-center gap-2" data-toast="Quick actions are ready.">
                    <i class="ri-flashlight-line"></i> Quick Actions
                </button>
            </div>

            <div class="d-flex align-items-center">
                <div class="dropdown d-none d-sm-block">
                    <button type="button" class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Notifications">
                        <i class="ri-notification-3-line fs-18"></i><span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">3</span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end dropdown-menu-lg p-0">
                        <div class="p-3 border-bottom"><div class="row align-items-center"><div class="col"><h6 class="m-0 fs-16">Notifications</h6></div><div class="col-auto"><span class="badge bg-soft-success text-success">3 New</span></div></div></div>
                        <div class="p-3 text-muted small">No new backend alerts.</div>
                    </div>
                </div>
                <div class="dropdown d-none d-sm-block">
                    <button type="button" class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Messages">
                        <i class="ri-message-3-line fs-18"></i><span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">5</span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end p-3" style="min-width:240px"><span class="text-muted small">No unread messages.</span></div>
                </div>

                <label class="header-context-select d-none d-xl-flex flex-column mx-1">
                    <small>Godown</small>
                    <select id="header-godown-context" data-legacy-id="header-godown" data-switch-url="{{ route('admin.location-context.switch-godown') }}">
                        <option value="">All godowns</option>
                        @foreach(($headerGodowns ?? collect()) as $godown)
                            <option value="{{ $godown->id }}" @selected(($activeGodownId ?? null) == $godown->id)>{{ $godown->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="header-context-select d-none d-xl-flex flex-column mx-1">
                    <small>Shop</small>
                    <select id="header-shop-context" data-legacy-id="header-shop" data-switch-url="{{ route('admin.location-context.switch-shop') }}">
                        <option value="">All shops</option>
                        @foreach(($headerShops ?? collect()) as $shop)
                            <option value="{{ $shop->id }}" @selected(($activeShopId ?? null) == $shop->id)>{{ $shop->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="header-context-select d-none d-lg-flex flex-column mx-1">
                    <small>FY</small>
                    <select><option>2025 - 2026</option><option>2026 - 2027</option></select>
                </label>

                <div class="dropdown ms-2">
                    <button type="button" class="btn topbar-user" id="page-header-user-dropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="avatar-sm rounded-circle bg-warning-subtle text-warning d-inline-flex align-items-center justify-content-center fw-bold">{{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}</span>
                        <span class="d-none d-xl-inline-block ms-2 text-start"><span class="d-block fw-semibold">{{ auth()->user()->name ?? 'Admin' }}</span><small class="text-muted">{{ auth()->user()->role?->name ?? 'Administrator' }}</small></span>
                        <i class="ri-arrow-down-s-line d-none d-xl-inline-block ms-1"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                        <h6 class="dropdown-header">Welcome {{ auth()->user()->name ?? 'Admin' }}!</h6>
                        <a class="dropdown-item" href="{{ route('admin.dashboard') }}"><i class="ri-dashboard-line text-muted me-2"></i>Dashboard</a>
                        <div class="dropdown-divider"></div>
                        <form method="post" action="{{ route('admin.logout') }}">@csrf<button class="dropdown-item" type="submit"><i class="ri-logout-box-r-line text-muted me-2"></i>Sign out</button></form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
