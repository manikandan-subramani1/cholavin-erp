@extends('backend.layouts.app')

@section('title', 'Business Dashboard | Cholavin ERP')

@php
    $money = static fn ($value) => '₹'.number_format((float) $value, 2);
    $quantity = static fn ($value) => number_format((float) $value, 3);
    $hasFinancialAnalytics = $visibility['sales'] || $visibility['purchases'] || $visibility['accounts'];
    $categoryStockValue = $stock['categories']->sum('value');
    $godownStockValue = $stock['godowns']->sum('value');
@endphp

@section('content')
    <div id="dashboard-module" data-dashboard='@json($chartData)'>
    <div class="erp-dashboard-hero mb-4">
        <div class="erp-dashboard-hero-copy">
            <span class="erp-eyebrow">Cholavin ERP intelligence</span>
            <h2>Business control centre</h2>
            <p>
                {{ auth()->user()->name }}, here is the operating position for
                <strong>{{ $headerShops->firstWhere('id', $activeShopId)?->name ?? 'your business' }}</strong>
                @if($activeGodownId)
                    / <strong>{{ $headerGodowns->firstWhere('id', $activeGodownId)?->name }}</strong>
                @endif
                from {{ date('d M Y', strtotime($period['from'])) }} to {{ date('d M Y', strtotime($period['to'])) }}.
            </p>
        </div>
        <div class="erp-dashboard-controls">
            <label for="dashboard-period">Analysis period</label>
            <select id="dashboard-period" class="form-select" aria-label="Dashboard analysis period">
                @foreach([7 => 'Last 7 days', 30 => 'Last 30 days', 90 => 'Last 90 days', 365 => 'Last 12 months'] as $days => $label)
                    <option value="{{ $days }}" @selected($period['days'] === $days)>{{ $label }}</option>
                @endforeach
            </select>
            <small><i class="ri-map-pin-2-line"></i> Active shop and godown scope applied</small>
        </div>
        <div class="erp-dashboard-hero-mark"><i class="ri-pulse-line"></i></div>
    </div>

    <nav class="erp-dashboard-quick-actions" aria-label="Dashboard quick actions">
        @can('sales-invoices.create')
            <a href="{{ route('admin.documents.index', 'sales-invoices') }}" class="is-sales"><i class="ri-shopping-cart-2-line"></i><span><strong>New Sale</strong><small>Create customer invoice</small></span><kbd>Alt+S</kbd></a>
        @endcan
        @can('purchase-bills.create')
            <a href="{{ route('admin.documents.index', 'purchase-bills') }}" class="is-purchase"><i class="ri-shopping-bag-3-line"></i><span><strong>New Purchase</strong><small>Record supplier bill</small></span><kbd>Alt+P</kbd></a>
        @endcan
        @can('payments.create')
            <a href="{{ route('admin.payments.index', ['type' => 'customer_collection']) }}" class="is-payment"><i class="ri-hand-coin-line"></i><span><strong>Receive Payment</strong><small>Record collection</small></span><kbd>Alt+R</kbd></a>
        @endcan
        @can('stock.transfer')
            <a href="{{ route('admin.stock-transfers.index') }}" class="is-stock"><i class="ri-arrow-left-right-line"></i><span><strong>Stock Transfer</strong><small>Move inventory</small></span></a>
        @endcan
        @can('reports.view')
            <a href="{{ route('admin.reports.index', 'profit-loss') }}" class="is-report"><i class="ri-line-chart-line"></i><span><strong>View Reports</strong><small>Analyse business</small></span></a>
        @endcan
    </nav>

    @if(count($kpis))
        <div class="erp-dashboard-section-heading">
            <div><span class="erp-eyebrow">Top-level analysis</span><h4>Financial and stock position</h4></div>
            <span class="erp-dashboard-period-badge"><i class="ri-calendar-line"></i>{{ $period['label'] }}</span>
        </div>
        <div class="erp-dashboard-kpi-grid">
            @foreach($kpis as $kpi)
                @php
                    $positiveChange = $kpi['change'] !== null && ($kpi['inverse'] ? $kpi['change'] <= 0 : $kpi['change'] >= 0);
                @endphp
                <div class="col-xxl-3 col-xl-4 col-md-6">
                    <a href="{{ route($kpi['route'], $kpi['parameters']) }}" class="erp-analysis-card erp-analysis-{{ $kpi['tone'] }}">
                        <span class="erp-analysis-icon"><i class="{{ $kpi['icon'] }}"></i></span>
                        <span class="erp-analysis-copy">
                            <small>{{ $kpi['label'] }}</small>
                            <strong>{{ $money($kpi['value']) }}</strong>
                            @if($kpi['change'] !== null)
                                <span class="erp-analysis-trend {{ $positiveChange ? 'is-positive' : 'is-negative' }}">
                                    <i class="{{ $kpi['change'] >= 0 ? 'ri-arrow-up-line' : 'ri-arrow-down-line' }}"></i>
                                    {{ number_format(abs($kpi['change']), 1) }}% vs previous period
                                </span>
                            @else
                                <span class="erp-analysis-trend is-neutral">Current scoped balance</span>
                            @endif
                        </span>
                        <i class="ri-arrow-right-up-line erp-analysis-arrow"></i>
                    </a>
                </div>
            @endforeach
        </div>
    @endif

    @if($hasFinancialAnalytics)
        <div class="row g-4 mb-4">
            <div class="col-xxl-8 col-xl-7">
                <div class="card erp-panel erp-dashboard-card h-100">
                    <div class="card-header erp-dashboard-card-header">
                        <div><span class="erp-eyebrow">Movement analysis</span><h5>Sales, purchase and operating-cost trend</h5></div>
                        @can('reports.view')
                            <a href="{{ route('admin.reports.index', 'profit-loss') }}" class="btn btn-sm btn-outline-brand">Open P&amp;L <i class="ri-arrow-right-line"></i></a>
                        @endcan
                    </div>
                    <div class="card-body"><div id="financial-trend-chart" class="erp-chart erp-chart-lg"></div></div>
                </div>
            </div>
            <div class="col-xxl-4 col-xl-5">
                <div class="card erp-panel erp-dashboard-card h-100">
                    <div class="card-header erp-dashboard-card-header">
                        <div><span class="erp-eyebrow">Cost-level analysis</span><h5>Where the money is allocated</h5></div>
                    </div>
                    <div class="card-body erp-cost-grid">
                        @foreach($costBreakdown as $cost)
                            <div class="erp-cost-item erp-cost-{{ $cost['tone'] }}">
                                <span><i class="{{ $cost['icon'] }}"></i></span>
                                <div><small>{{ $cost['label'] }}</small><strong>{{ $money($cost['value']) }}</strong></div>
                            </div>
                        @endforeach
                    </div>
                    <div class="erp-dashboard-note">
                        <i class="ri-information-line"></i>
                        Product cost is estimated from the current purchase price. Use the Profit &amp; Loss report for posted ledger values.
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($visibility['sales'])
        <div class="erp-dashboard-section-heading">
            <div><span class="erp-eyebrow">Product-level analysis</span><h4>Top product performance</h4></div>
            @can('sales-invoices.view')<a href="{{ route('admin.documents.index', 'sales-invoices') }}" class="btn btn-sm btn-outline-brand">Sales invoices <i class="ri-arrow-right-line"></i></a>@endcan
        </div>
        <div class="row g-4 mb-4">
            <div class="col-xl-5">
                <div class="card erp-panel erp-dashboard-card h-100">
                    <div class="card-header erp-dashboard-card-header"><div><span class="erp-eyebrow">Revenue vs cost</span><h5>Top selling products</h5></div></div>
                    <div class="card-body">
                        @if($topProducts->isNotEmpty())
                            <div id="top-products-chart" class="erp-chart erp-chart-xl"></div>
                        @else
                            <div class="erp-dashboard-empty"><i class="ri-bar-chart-grouped-line"></i><h6>No product sales in this period</h6><p>Posted sales invoices will populate product revenue, cost, and margin analysis.</p></div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-xl-7">
                <div class="card erp-panel erp-dashboard-card h-100">
                    <div class="card-header erp-dashboard-card-header"><div><span class="erp-eyebrow">Margin table</span><h5>Product contribution</h5></div></div>
                    <div class="card-body p-0">
                        <div class="table-responsive erp-dashboard-table-wrap">
                            <table class="table align-middle mb-0 erp-dashboard-table">
                                <thead><tr><th>Product</th><th class="text-end">Qty</th><th class="text-end">Revenue</th><th class="text-end">Cost</th><th class="text-end">Profit</th><th class="text-end">Margin</th></tr></thead>
                                <tbody>
                                    @forelse($topProducts as $product)
                                        <tr>
                                            <td><strong>{{ $product->name }}</strong><small>{{ $product->sku ?: 'No SKU' }}</small></td>
                                            <td class="text-end">{{ $quantity($product->quantity) }}</td>
                                            <td class="text-end fw-semibold">{{ $money($product->revenue) }}</td>
                                            <td class="text-end">{{ $money($product->cost) }}</td>
                                            <td class="text-end {{ $product->profit >= 0 ? 'text-success' : 'text-danger' }}">{{ $money($product->profit) }}</td>
                                            <td class="text-end"><span class="erp-margin-badge {{ $product->margin >= 0 ? 'is-positive' : 'is-negative' }}">{{ number_format($product->margin, 1) }}%</span></td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="6"><div class="erp-dashboard-empty compact"><p>No product-level sales data is available.</p></div></td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($visibility['stock'])
        <div class="erp-dashboard-section-heading">
            <div><span class="erp-eyebrow">Stock control</span><h4>Inventory value and replenishment</h4></div>
            <div class="d-flex gap-2">
                <span class="erp-dashboard-alert-badge"><i class="ri-alarm-warning-line"></i>{{ $stock['summary']['low_stock_count'] }} low-stock item(s)</span>
                <a href="{{ route('admin.stock.index') }}" class="btn btn-sm btn-outline-brand">Stock ledger <i class="ri-arrow-right-line"></i></a>
            </div>
        </div>
        <div class="erp-stock-summary mb-4">
            <div><small>On-hand quantity</small><strong>{{ $quantity($stock['summary']['quantity']) }}</strong></div>
            <div><small>Inventory cost</small><strong>{{ $money($stock['summary']['cost_value']) }}</strong></div>
            <div><small>Potential retail value</small><strong>{{ $money($stock['summary']['retail_value']) }}</strong></div>
            <div><small>Potential margin</small><strong>{{ $money($stock['summary']['potential_margin']) }}</strong></div>
        </div>
        <div class="row g-4 mb-4">
            <div class="col-xxl-4 col-xl-6">
                <div class="card erp-panel erp-dashboard-card h-100">
                    <div class="card-header erp-dashboard-card-header"><div><span class="erp-eyebrow">Category level</span><h5>Stock cost distribution</h5></div></div>
                    <div class="card-body">
                        @if($categoryStockValue > 0)
                            <div id="stock-category-chart" class="erp-chart"></div>
                        @else
                            <div class="erp-dashboard-empty"><i class="ri-donut-chart-line"></i><h6>No valued stock</h6><p>Post opening stock or purchase bills to build category valuation.</p></div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-xxl-4 col-xl-6">
                <div class="card erp-panel erp-dashboard-card h-100">
                    <div class="card-header erp-dashboard-card-header"><div><span class="erp-eyebrow">Location level</span><h5>Godown stock value</h5></div></div>
                    <div class="card-body">
                        @if($godownStockValue > 0)
                            <div id="godown-stock-chart" class="erp-chart"></div>
                        @else
                            <div class="erp-dashboard-empty"><i class="ri-building-2-line"></i><h6>No godown valuation</h6><p>Inventory value by godown will appear after stock is posted.</p></div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-xxl-4 col-xl-12">
                <div class="card erp-panel erp-dashboard-card h-100">
                    <div class="card-header erp-dashboard-card-header"><div><span class="erp-eyebrow">Action required</span><h5>Low-stock products</h5></div></div>
                    <div class="card-body p-0">
                        <div class="erp-stock-alert-list">
                            @forelse($stock['lowStock'] as $item)
                                <div class="erp-stock-alert-row">
                                    <span class="erp-stock-alert-icon"><i class="ri-error-warning-line"></i></span>
                                    <div><strong>{{ $item->name }}</strong><small>{{ $item->sku ?: 'No SKU' }}</small></div>
                                    <div class="text-end"><strong>{{ $quantity($item->quantity) }}</strong><small>Reorder {{ $quantity($item->reorder_level) }}</small></div>
                                </div>
                            @empty
                                <div class="erp-dashboard-empty"><i class="ri-checkbox-circle-line text-success"></i><h6>Stock levels are healthy</h6><p>No product has reached its reorder level.</p></div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card erp-panel erp-dashboard-card mb-4">
            <div class="card-header erp-dashboard-card-header"><div><span class="erp-eyebrow">Product-level stock</span><h5>Highest inventory investment</h5></div></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0 erp-dashboard-table">
                        <thead><tr><th>Product</th><th class="text-end">On hand</th><th class="text-end">Cost value</th><th class="text-end">Retail value</th><th class="text-end">Potential margin</th></tr></thead>
                        <tbody>
                            @forelse($stock['topValue'] as $item)
                                <tr>
                                    <td><strong>{{ $item->name }}</strong><small>{{ $item->sku ?: 'No SKU' }}</small></td>
                                    <td class="text-end">{{ $quantity($item->quantity) }}</td>
                                    <td class="text-end fw-semibold">{{ $money($item->cost_value) }}</td>
                                    <td class="text-end">{{ $money($item->retail_value) }}</td>
                                    <td class="text-end text-success">{{ $money($item->retail_value - $item->cost_value) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5"><div class="erp-dashboard-empty compact"><p>No inventory balances are available for this location.</p></div></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <div class="erp-dashboard-section-heading">
        <div><span class="erp-eyebrow">Operations</span><h4>Module overview</h4></div>
    </div>
    <div class="row g-3 mb-4">
        @foreach($metrics as $metric)
            @if(is_array($metric['ability']) ? auth()->user()->can($metric['ability'][0], $metric['ability'][1]) : auth()->user()->can($metric['ability']))
                <div class="col-xxl-3 col-xl-4 col-md-6">
                    <a href="{{ route($metric['route'], $metric['route_parameters'] ?? []) }}" class="erp-stat-card">
                        <span class="erp-stat-icon"><i class="{{ $metric['icon'] }}"></i></span>
                        <span><small>{{ $metric['label'] }}</small><strong>{{ number_format($metric['value']) }}</strong></span>
                        <i class="ri-arrow-right-up-line erp-stat-arrow"></i>
                    </a>
                </div>
            @endif
        @endforeach
    </div>

    <div class="row g-4">
        <div class="col-xl-7">
            <div class="card erp-panel h-100">
                <div class="card-header"><div><span class="erp-eyebrow">Setup health</span><h5 class="mb-0">Master data readiness</h5></div></div>
                <div class="card-body">
                    @foreach($masterChecks as $check)
                        @php
                            $complete = $check['count'] >= ($check['expected'] ?? 1);
                        @endphp
                        <div class="erp-readiness-row">
                            <span class="erp-status-dot {{ $complete ? 'is-ready' : 'is-missing' }}"></span>
                            <div class="flex-grow-1"><strong>{{ $check['label'] }}</strong><p>{{ $complete ? 'Ready for use.' : $check['message'] }}</p></div>
                            <span class="erp-count {{ $complete ? 'is-ready' : 'is-missing' }}">{{ $check['count'] }}/{{ $check['expected'] ?? 1 }}</span>
                            @unless($complete)
                                @if(is_array($check['ability']) ? auth()->user()->can($check['ability'][0], $check['ability'][1]) : auth()->user()->can($check['ability']))
                                    <a href="{{ route($check['route'], $check['route_parameters'] ?? []) }}" class="btn btn-sm btn-brand">Fix now <i class="ri-arrow-right-line"></i></a>
                                @endif
                            @endunless
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="col-xl-5">
            <div class="card erp-panel h-100">
                <div class="card-header"><span class="erp-eyebrow">Shortcuts</span><h5 class="mb-0">Quick actions</h5></div>
                <div class="card-body erp-actions">
                    @can('create', App\Models\Product::class)<a href="{{ route('admin.products.create') }}"><i class="ri-add-box-line"></i><span><strong>Add product</strong><small>Create a sellable item</small></span></a>@endcan
                    @can('users.create')<a href="{{ route('admin.users.index') }}"><i class="ri-user-add-line"></i><span><strong>Add staff user</strong><small>Assign role and locations</small></span></a>@endcan
                    @can('shops.create')<a href="{{ route('admin.locations.index') }}"><i class="ri-store-2-line"></i><span><strong>Add location</strong><small>Create shop or godown</small></span></a>@endcan
                    @can('settings.update')<a href="{{ route('admin.settings.index') }}"><i class="ri-settings-4-line"></i><span><strong>Company settings</strong><small>Brand, contacts and mail</small></span></a>@endcan
                    @can('sales-invoices.create')<a href="{{ route('admin.documents.index', 'sales-invoices') }}"><i class="ri-bill-line"></i><span><strong>Create sales invoice</strong><small>Bill a customer and post stock</small></span></a>@endcan
                    @can('purchase-bills.create')<a href="{{ route('admin.documents.index', 'purchase-bills') }}"><i class="ri-shopping-cart-line"></i><span><strong>Enter purchase bill</strong><small>Record supplier purchase and stock</small></span></a>@endcan
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mt-1">
        <div class="col-xl-6">
            <div class="card erp-panel erp-dashboard-card h-100">
                <div class="card-header erp-dashboard-card-header"><div><span class="erp-eyebrow">Action queue</span><h5>Upcoming reminders</h5></div>@can('notifications.view')<a href="{{ route('admin.notifications.index') }}" class="btn btn-sm btn-outline-brand">View all</a>@endcan</div>
                <div class="card-body p-0 erp-dashboard-feed">
                    @forelse($reminders as $reminder)
                        <a href="{{ route('admin.notifications.index') }}"><span class="erp-feed-icon is-reminder"><i class="ri-notification-3-line"></i></span><span><strong>{{ $reminder->title }}</strong><small>{{ $reminder->message }}</small></span><time>{{ $reminder->created_at?->diffForHumans() }}</time></a>
                    @empty
                        <div class="erp-dashboard-empty compact"><p>No pending reminders for the selected business context.</p></div>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card erp-panel erp-dashboard-card h-100">
                <div class="card-header erp-dashboard-card-header"><div><span class="erp-eyebrow">Audit trail</span><h5>Recent activities</h5></div>@can('activity-logs.view')<a href="{{ route('admin.activity-logs.index') }}" class="btn btn-sm btn-outline-brand">View all</a>@endcan</div>
                <div class="card-body p-0 erp-dashboard-feed">
                    @forelse($recentActivities as $activity)
                        <div><span class="erp-feed-icon is-activity"><i class="ri-history-line"></i></span><span><strong>{{ str($activity->event ?: $activity->action)->replace(['.', '_'], ' ')->title() }}</strong><small>{{ $activity->user?->name ?? 'System' }} · {{ $activity->module ?: 'ERP' }}</small></span><time>{{ $activity->created_at?->diffForHumans() }}</time></div>
                    @empty
                        <div class="erp-dashboard-empty compact"><p>Recent activity will appear as users work in the ERP.</p></div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="erp-billing-shortcuts" aria-label="ERP keyboard shortcuts">
        <div><strong>Shortcut keys for faster work</strong><small>Works from every ERP module</small></div>
        @can('sales-invoices.create')<a href="{{ route('admin.documents.index', 'sales-invoices') }}"><kbd>F1</kbd><span>New Sale</span></a>@endcan
        @can('purchase-bills.create')<a href="{{ route('admin.documents.index', 'purchase-bills') }}"><kbd>F2</kbd><span>Purchase</span></a>@endcan
        @can('payments.create')<a href="{{ route('admin.payments.index') }}"><kbd>F3</kbd><span>Payment</span></a>@endcan
        @can('customers.view')<a href="{{ route('admin.parties.index', 'customers') }}"><kbd>F4</kbd><span>Customer</span></a>@endcan
        @can('products.view')<a href="{{ route('admin.products.index') }}"><kbd>F5</kbd><span>Item Search</span></a>@endcan
        @can('stock.view')<a href="{{ route('admin.stock.index') }}"><kbd>F6</kbd><span>Stock</span></a>@endcan
        @can('reports.view')<a href="{{ route('admin.reports.index', 'sales') }}"><kbd>F7</kbd><span>Reports</span></a>@endcan
        <button id="erp-print-current" type="button"><kbd>F8</kbd><span>Print Page</span></button>
    </div>
    </div>
@endsection

@push('scripts')
<script src="{{ asset('backend/assets/js/modules/dashboard.js') }}?v={{ filemtime(public_path('backend/assets/js/modules/dashboard.js')) }}"></script>
@endpush
