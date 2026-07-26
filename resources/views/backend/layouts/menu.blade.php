@php
    $modules = config('cholavin_dashboard.modules', []);
    $groups = ['Overview', 'Workspaces', 'Master Data', 'Transactions', 'Inventory', 'Accounting', 'Operations', 'Reports', 'Administration'];
@endphp
<div class="app-menu navbar-menu">
    <div class="navbar-brand-box">
        <a href="{{ route('admin.dashboard') }}" class="logo logo-dark text-decoration-none">
            <span class="logo-sm"><span class="avatar-xs rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center">C</span></span>
            <span class="logo-lg d-flex align-items-center gap-2"><span class="avatar-sm rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center">C</span><span class="text-white fw-bold">CHOLAVIN <small class="d-block text-white-50 fw-normal">Rice Business Solution</small></span></span>
        </a>
        <a href="{{ route('admin.dashboard') }}" class="logo logo-light text-decoration-none">
            <span class="logo-sm"><span class="avatar-xs rounded-circle bg-warning text-dark d-inline-flex align-items-center justify-content-center">C</span></span>
            <span class="logo-lg d-flex align-items-center gap-2"><span class="avatar-sm rounded-circle bg-warning text-dark d-inline-flex align-items-center justify-content-center">C</span><span class="text-white fw-bold">CHOLAVIN <small class="d-block text-white-50 fw-normal">Rice Business Solution</small></span></span>
        </a>
    </div>
    <div id="scrollbar">
        <div class="container-fluid">
            <div id="two-column-menu"></div>
            <ul class="navbar-nav" id="navbar-nav">
                @foreach($groups as $group)
                    @php $groupModules = collect($modules)->filter(fn ($module) => $module['group'] === $group); @endphp
                    @if($groupModules->isNotEmpty())
                        <li class="menu-title"><span>{{ $group }}</span></li>
                        @if($group === 'Overview')
                            @foreach($groupModules as $key => $module)
                                <li class="nav-item"><a href="{{ route('admin.dashboard') }}" class="nav-link menu-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"><i class="{{ $module['icon'] }}"></i><span>{{ $module['title'] }}</span></a></li>
                            @endforeach
                        @else
                            <li class="nav-item">
                                <a class="nav-link menu-link" href="#sidebar-{{ Str::slug($group) }}" data-bs-toggle="collapse" role="button" aria-expanded="true" aria-controls="sidebar-{{ Str::slug($group) }}">
                                    <i class="ri-layout-grid-line"></i><span>{{ $group }}</span>
                                </a>
                                <div class="collapse show menu-dropdown" id="sidebar-{{ Str::slug($group) }}">
                                    <ul class="nav nav-sm flex-column">
                                        @foreach($groupModules as $key => $module)
                                            <li class="nav-item"><a href="{{ route('admin.workspace', $key) }}" class="nav-link {{ request()->routeIs('admin.workspace') && request()->route('module') === $key ? 'active' : '' }}"><i class="{{ $module['icon'] }}"></i><span>{{ $module['title'] }}</span></a></li>
                                        @endforeach
                                    </ul>
                                </div>
                            </li>
                        @endif
                    @endif
                @endforeach
                <li class="menu-title"><span>Live backend</span></li>
                <li class="nav-item"><a href="{{ route('admin.products.index') }}" class="nav-link menu-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}"><i class="ri-price-tag-3-line"></i><span>Product Catalog</span></a></li>
                <li class="nav-item"><a href="{{ route('admin.enquiries.index') }}" class="nav-link menu-link {{ request()->routeIs('admin.enquiries.*') ? 'active' : '' }}"><i class="ri-mail-line"></i><span>Enquiries</span></a></li>
            </ul>
        </div>
    </div>
    <div class="sidebar-background"></div>
    <div class="cholavin-sidebar-footer">
        <div class="cholavin-context-card"><small>Today's summary</small><strong>Workspace rebuild</strong><span><i class="ri-shield-check-line"></i> Auth protected</span><span><i class="ri-layout-grid-line"></i> 18 module shells</span></div>
    </div>
</div>
