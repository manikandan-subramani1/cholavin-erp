@extends('backend.layouts.app')

@section('title', 'Executive Dashboard | Cholavin ERP')

@section('content')
<main id='dashboard-module' class='executive-dashboard'
    data-endpoints='@json($dashboard['endpoints'])'
    data-initial-preset='{{ request('preset', 'this_month') }}'>
    <section class='dashboard-command-bar'>
        <div class='dashboard-heading'>
            <div class='dashboard-brand-mark'><img src='{{ asset('frontend/assets/img/logo/logo-hm62.png') }}' alt='Cholavin'></div>
            <div><div class='dashboard-breadcrumb'><span>Home</span><i class='ri-arrow-right-s-line'></i><strong>Dashboard</strong></div>
                <h1>Executive Dashboard</h1><p>{{ $companyName }} · {{ $dashboard['mode_label'] }} · {{ $dashboard['role'] }}</p></div>
        </div>
        <button class='btn btn-brand dashboard-filter-toggle' type='button' data-bs-toggle='collapse' data-bs-target='#dashboard-filters' aria-expanded='true'><i class='ri-filter-3-line'></i><span>Filters</span></button>
    </section>

    <section id='dashboard-filters' class='collapse show dashboard-filter-panel'>
        <div class='dashboard-presets' role='group' aria-label='Dashboard date range'>
            @foreach(['today'=>'Today','yesterday'=>'Yesterday','this_week'=>'This Week','this_month'=>'This Month','last_month'=>'Last Month','this_quarter'=>'This Quarter','financial_year'=>'This Financial Year','custom'=>'Custom'] as $value=>$label)
                <button type='button' class='dashboard-preset {{ request('preset', 'this_month') === $value ? 'active' : '' }}' data-preset='{{ $value }}'>{{ $label }}</button>
            @endforeach
        </div>
        <div id='dashboard-custom-range' class='dashboard-custom-range d-none'>
            <label>From<input id='dashboard-date-from' type='date' class='form-control' value='{{ request('date_from') }}'></label>
            <label>To<input id='dashboard-date-to' type='date' class='form-control' value='{{ request('date_to') }}'></label>
            <button id='dashboard-apply-custom' class='btn btn-brand' type='button'>Apply</button>
        </div>
        <div class='dashboard-scope-pill'><i class='ri-shield-check-line'></i><span>Server-enforced {{ $dashboard['mode_label'] }}</span></div>
    </section>

    @unless($dashboard['has_financial_year'])
        <div class='dashboard-setup-warning' role='alert'><i class='ri-calendar-close-line'></i><div><strong>Dashboard data unavailable.</strong><span>An active financial year is required.</span></div>@can('financial-years.view')<a href='{{ route('admin.financial-years.index') }}' class='btn btn-sm btn-brand'>Configure Financial Year</a>@endcan</div>
    @endunless

    <section class='dashboard-kpi-section' aria-labelledby='dashboard-kpi-title'>
        <div class='dashboard-section-title'><div><span>Live performance</span><h2 id='dashboard-kpi-title'>Business at a glance</h2></div><small id='dashboard-period-label'>Loading selected period…</small></div>
        <div id='dashboard-kpis' class='dashboard-kpi-grid' aria-live='polite'>
            @for($i=0;$i<10;$i++)<article class='dashboard-kpi-card is-skeleton'><span></span><strong></strong><small></small></article>@endfor
        </div>
    </section>

    <nav class='dashboard-tabs-wrap' aria-label='Dashboard workspaces'>
        <div class='nav dashboard-tabs' role='tablist'>
            @foreach($dashboard['tabs'] as $key=>$label)
                <button class='nav-link {{ $key === 'overview' ? 'active' : '' }}' data-bs-toggle='tab' data-bs-target='#dashboard-tab-{{ $key }}' data-dashboard-tab='{{ $key }}' type='button' role='tab'>{{ $label }}</button>
            @endforeach
        </div>
    </nav>

    <div class='tab-content dashboard-tab-content'>
        <section id='dashboard-tab-overview' class='tab-pane fade show active' role='tabpanel'>
            <div class='dashboard-workspace-grid'>
                @if(auth()->user()->can('reports.view') || (auth()->user()->can('sales-invoices.view') && auth()->user()->can('purchase-bills.view')))
                <article class='dashboard-panel dashboard-panel-wide'><header><div><span>Performance trend</span><h3>Sales vs Purchase</h3></div><button class='dashboard-panel-action' data-drilldown='sales' aria-label='Open sales report'><i class='ri-arrow-right-up-line'></i></button></header><div class='dashboard-chart' data-chart='sales-purchase'></div></article>
                @endif
                @can('reports.view')
                <article class='dashboard-panel'><header><div><span>Profitability</span><h3>Gross vs Net Profit</h3></div></header><div class='dashboard-chart' data-chart='profit'></div></article>
                @endcan
                @if(auth()->user()->can('payments.view') || auth()->user()->can('accounts.view') || auth()->user()->can('reports.view'))
                <article class='dashboard-panel'><header><div><span>Cash flow</span><h3>Collection Trend</h3></div></header><div class='dashboard-chart' data-chart='collections'></div></article>
                @endif
                @if(auth()->user()->can('sales-invoices.view') || auth()->user()->can('reports.view'))
                <article class='dashboard-panel'><header><div><span>Best sellers</span><h3>Top Rice Products</h3></div></header><div class='dashboard-ranking' data-top='products'></div></article>
                @endif
                <article class='dashboard-panel'><header><div><span>Attention</span><h3>Alerts & Reminders</h3></div></header><div class='dashboard-alert-list' data-dashboard-alerts></div></article>
                <article class='dashboard-panel dashboard-panel-wide'><header><div><span>Audit trail</span><h3>Recent Activity</h3></div>@can('activity-logs.view')<a href='{{ route('admin.activity-logs.index') }}'>View all</a>@endcan</header><div class='dashboard-activity-list' data-dashboard-activity></div></article>
            </div>
        </section>
        @foreach(array_keys($dashboard['tabs']) as $tab) @continue($tab === 'overview')
            <section id='dashboard-tab-{{ $tab }}' class='tab-pane fade' role='tabpanel'><div class='dashboard-tab-loader' data-lazy-tab='{{ $tab }}'><span class='spinner-border spinner-border-sm'></span> Loading {{ $tab }} workspace…</div></section>
        @endforeach
    </div>

    <section class='dashboard-quick-actions' aria-labelledby='dashboard-actions-title'>
        <div class='dashboard-section-title'><div><span>One-click workflow</span><h2 id='dashboard-actions-title'>Quick actions</h2></div></div>
        <div class='dashboard-action-grid'>
            @forelse($dashboard['actions'] as $action)
                @if(($action['requires_shop'] && !$dashboard['has_shop']) || !$dashboard['has_financial_year'])
                    <article class='dashboard-action is-unavailable' tabindex='0' title='A shop and active financial year are required'><i class='{{ $action['icon'] }}'></i><span><strong>{{ $action['label'] }} unavailable</strong><small>A shop and active financial year are required.</small></span>@can('shops.view')<a href='{{ route('admin.locations.index') }}'>Configure Shop</a>@endcan</article>
                @else
                    <a href='{{ $action['url'] }}' class='dashboard-action'><i class='{{ $action['icon'] }}'></i><span><strong>{{ $action['label'] }}</strong><small>{{ $action['description'] }}</small></span><b class='ri-arrow-right-line'></b></a>
                @endif
            @empty
                <div class='dashboard-empty'>No quick actions are assigned to your role.</div>
            @endforelse
        </div>
    </section>

    <div class='dashboard-mobile-actions'>
        @foreach(array_slice($dashboard['actions'],0,4) as $action)<a href='{{ $action['url'] }}'><i class='{{ $action['icon'] }}'></i><span>{{ $action['label'] }}</span></a>@endforeach
    </div>
</main>
@endsection

@push('scripts')
<script src='{{ asset('backend/assets/js/modules/dashboard.js') }}?v={{ filemtime(public_path('backend/assets/js/modules/dashboard.js')) }}'></script>
@endpush
