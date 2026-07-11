<div class="app-menu navbar-menu">
    <div class="navbar-brand-box">
        <a href="{{ route('admin.dashboard') }}" class="logo logo-dark"><span class="logo-sm"><i class="ri-seedling-fill brand-glyph"></i></span><span class="logo-lg"><img class="erp-brand-logo" src="{{ asset($commonSettings['brand_logo'] ?? 'frontend/assets/img/logo/logo-hm62.png') }}" alt="{{ $commonSettings['company_name'] ?? 'Cholavin' }}"></span></a>
        <a href="{{ route('admin.dashboard') }}" class="logo logo-light"><span class="logo-sm"><i class="ri-seedling-fill brand-glyph"></i></span><span class="logo-lg"><img class="erp-brand-logo" src="{{ asset($commonSettings['brand_logo'] ?? 'frontend/assets/img/logo/logo-hm62.png') }}" alt="{{ $commonSettings['company_name'] ?? 'Cholavin' }}"></span></a>
        <button type="button" class="btn btn-sm p-0 fs-20 header-item float-end btn-vertical-sm-hover" id="vertical-hover"><i class="ri-record-circle-line"></i></button>
    </div>
    <div class="dropdown sidebar-user m-1 rounded">
        <button type="button" class="btn material-shadow-none" data-bs-toggle="dropdown"><span class="d-flex align-items-center gap-2"><span class="avatar-xs"><span class="avatar-title rounded-circle bg-primary">{{ str(auth()->user()->name)->substr(0, 1)->upper() }}</span></span><span class="text-start"><span class="d-block fw-medium">{{ auth()->user()->name }}</span><span class="d-block fs-12 text-muted">{{ auth()->user()->role?->name }}</span></span></span></button>
        <div class="dropdown-menu dropdown-menu-end"><form method="post" action="{{ route('admin.logout') }}">@csrf<button class="dropdown-item"><i class="mdi mdi-logout me-1"></i> Logout</button></form></div>
    </div>
    <div id="scrollbar"><div class="container-fluid"><ul class="navbar-nav" id="navbar-nav">
        <li class="menu-title"><span>Menu</span></li>
        <li class="nav-item"><a class="nav-link menu-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}"><i class="ri-dashboard-2-line"></i><span>Dashboard</span></a></li>
        @can('viewAny', App\Models\Product::class)<li class="nav-item"><a class="nav-link menu-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}" href="{{ route('admin.products.index') }}"><i class="ri-shopping-bag-3-line"></i><span>Products</span></a></li>@endcan
        @can('viewAny', App\Models\ContactEnquiry::class)<li class="nav-item"><a class="nav-link menu-link {{ request()->routeIs('admin.enquiries.*') ? 'active' : '' }}" href="{{ route('admin.enquiries.index') }}"><i class="ri-message-3-line"></i><span>Enquiries</span></a></li>@endcan
        @if(auth()->user()->can('users.view') || auth()->user()->can('roles.view') || auth()->user()->can('shops.view') || auth()->user()->can('activity-logs.view') || auth()->user()->can('settings.view'))
        <li class="menu-title"><span>Access Control</span></li>
        @can('users.view')<li class="nav-item"><a class="nav-link menu-link" href="{{ route('admin.users.index') }}"><i class="ri-user-settings-line"></i><span>Users</span></a></li>@endcan
        @can('roles.view')<li class="nav-item"><a class="nav-link menu-link" href="{{ route('admin.roles.index') }}"><i class="ri-shield-user-line"></i><span>Roles & Permissions</span></a></li>@endcan
        @can('shops.view')<li class="nav-item"><a class="nav-link menu-link" href="{{ route('admin.locations.index') }}"><i class="ri-store-2-line"></i><span>Shops & Godowns</span></a></li>@endcan
        @can('activity-logs.view')<li class="nav-item"><a class="nav-link menu-link" href="{{ route('admin.activity-logs.index') }}"><i class="ri-history-line"></i><span>Activity Logs</span></a></li>@endcan
        @can('settings.view')<li class="nav-item"><a class="nav-link menu-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}" href="{{ route('admin.settings.index') }}"><i class="ri-settings-4-line"></i><span>Company Settings</span></a></li>@endcan
        @endif
    </ul></div></div>
    <div class="sidebar-background"></div>
</div>
<div class="vertical-overlay"></div>
