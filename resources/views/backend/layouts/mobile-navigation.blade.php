<nav class="erp-mobile-nav" aria-label="Mobile primary navigation">
    @can('dashboard.view')<a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"><i class="ri-home-4-line"></i><span>Home</span></a>@endcan
    @can('sales-invoices.view')<a href="{{ route('admin.documents.index', 'sales-invoices') }}" class="{{ request()->route('module') === 'sales-invoices' ? 'active' : '' }}"><i class="ri-receipt-line"></i><span>Sales</span></a>@endcan
    <button type="button" id="erp-mobile-create" class="erp-mobile-create" aria-label="Open Quick Create"><i class="ri-add-line"></i><span>Create</span></button>
    @can('payments.view')<a href="{{ route('admin.payments.index') }}" class="{{ request()->routeIs('admin.payments.*') ? 'active' : '' }}"><i class="ri-exchange-funds-line"></i><span>Payments</span></a>@endcan
    <button type="button" id="erp-mobile-more" aria-label="Open navigation"><i class="ri-menu-4-line"></i><span>More</span></button>
</nav>
