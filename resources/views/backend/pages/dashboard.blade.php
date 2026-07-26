@extends('backend.layouts.app')

@section('title', 'Dashboard | Cholavin ERP')

@php
    $modules = config('cholavin_dashboard.modules', []);
    $kpis = [
        ['label' => "Today's Sales", 'value' => '₹ 2,45,680', 'change' => '+18.6% vs Yesterday', 'icon' => 'ri-shopping-cart-2-line'],
        ['label' => "Today's Purchase", 'value' => '₹ 1,12,350', 'change' => '+12.4% vs Yesterday', 'icon' => 'ri-shopping-bag-3-line'],
        ['label' => "Today's Collection", 'value' => '₹ 2,15,980', 'change' => '+20.1% vs Yesterday', 'icon' => 'ri-wallet-3-line'],
        ['label' => 'Gross Profit', 'value' => '₹ 1,32,780', 'change' => '+16.8% vs Yesterday', 'icon' => 'ri-line-chart-line'],
        ['label' => 'Stock Value', 'value' => '₹ 48,75,320', 'change' => '↓ 2.5% vs Yesterday', 'icon' => 'ri-stack-line'],
    ];
    $sellingItems = [
        ['name' => 'Ponni Rice (25kg)', 'detail' => 'Premium rice · Bag', 'value' => '850 Bags'],
        ['name' => 'Idli Rice (25kg)', 'detail' => 'Everyday rice · Bag', 'value' => '620 Bags'],
        ['name' => 'Basmati Rice (20kg)', 'detail' => 'Aromatic rice · Bag', 'value' => '410 Bags'],
        ['name' => 'Sona Masoori (25kg)', 'detail' => 'Raw rice · Bag', 'value' => '395 Bags'],
        ['name' => 'Raw Rice (25kg)', 'detail' => 'Rice grain · Bag', 'value' => '320 Bags'],
    ];
    $activities = [
        ['icon' => 'ri-file-add-line', 'title' => 'Sales invoice created', 'detail' => 'INV-2025-00125 · 2 mins ago'],
        ['icon' => 'ri-hand-coin-line', 'title' => 'Payment received', 'detail' => '₹ 25,000 from Sri Traders · 15 mins ago'],
        ['icon' => 'ri-arrow-left-right-line', 'title' => 'Stock transfer completed', 'detail' => 'Main Godown to Namakkal Shop · 30 mins ago'],
        ['icon' => 'ri-shopping-bag-line', 'title' => 'Purchase invoice created', 'detail' => 'PUR-2025-00089 · 1 hour ago'],
    ];
    $quickActions = [
        ['icon' => 'ri-shopping-cart-2-line', 'label' => 'New Sale', 'module' => 'sales-billing'],
        ['icon' => 'ri-shopping-bag-line', 'label' => 'New Purchase', 'module' => 'purchase-management'],
        ['icon' => 'ri-arrow-left-right-line', 'label' => 'Stock Transfer', 'module' => 'stock-transfer'],
        ['icon' => 'ri-hand-coin-line', 'label' => 'Receive Payment', 'module' => 'payment-collection'],
        ['icon' => 'ri-file-damage-line', 'label' => 'Make Payment', 'module' => 'payment-collection'],
        ['icon' => 'ri-user-add-line', 'label' => 'New Customer', 'module' => 'customer-workspace'],
        ['icon' => 'ri-user-add-line', 'label' => 'New Supplier', 'module' => 'supplier-workspace'],
        ['icon' => 'ri-price-tag-3-line', 'label' => 'New Item', 'route' => 'admin.products.create'],
        ['icon' => 'ri-bar-chart-box-line', 'label' => 'View Reports', 'module' => 'reports-analytics'],
    ];
@endphp

<div class="cholavin-dashboard-welcome">
    <span class="cholavin-eyebrow">Overview · {{ now()->format('d M Y') }}</span>
    <h1>Good Morning, {{ auth()->user()->name ?? 'Karthik' }}! 👋</h1>
    <p>Here’s what’s happening with your business today. Choose a module to continue the rebuild.</p>
</div>

<div class="cholavin-kpi-grid">
    @foreach($kpis as $kpi)
        <div class="cholavin-kpi"><span class="cholavin-kpi-icon"><i class="{{ $kpi['icon'] }}"></i></span><small>{{ $kpi['label'] }}</small><strong>{{ $kpi['value'] }}</strong><em>{{ $kpi['change'] }}</em></div>
    @endforeach
</div>

<div class="cholavin-grid-2">
    <section class="cholavin-panel">
        <div class="cholavin-panel-head"><div><span class="cholavin-eyebrow">Performance</span><h2>Sales Overview</h2></div><div class="cholavin-legend"><span>Sales Amount</span><span>Collection</span></div></div>
        <div class="cholavin-panel-body"><div data-dashboard-chart class="cholavin-chart"></div></div>
    </section>
    <section class="cholavin-panel">
        <div class="cholavin-panel-head"><div><span class="cholavin-eyebrow">This month</span><h2>Top Selling Items</h2></div><button class="cholavin-outline-button" type="button" data-toast="The item performance filter is ready.">This Month <i class="ri-arrow-down-s-line"></i></button></div>
        <div class="cholavin-panel-body"><ul class="cholavin-list">@foreach($sellingItems as $index => $item)<li><span class="cholavin-list-rank">{{ $index + 1 }}</span><div class="cholavin-list-main"><strong>{{ $item['name'] }}</strong><small>{{ $item['detail'] }}</small></div><span class="cholavin-list-value">{{ $item['value'] }}</span></li>@endforeach</ul></div>
    </section>
</div>

<div class="cholavin-grid-2 cholavin-section">
    <section class="cholavin-panel">
        <div class="cholavin-panel-head"><div><span class="cholavin-eyebrow">Shortcuts</span><h2>Quick Actions</h2></div><span class="cholavin-status">AJAX ready</span></div>
        <div class="cholavin-panel-body"><div class="cholavin-action-grid">@foreach($quickActions as $action)<a class="cholavin-action" href="{{ isset($action['route']) ? route($action['route']) : route('admin.workspace', $action['module']) }}"><i class="{{ $action['icon'] }}"></i><span>{{ $action['label'] }}</span></a>@endforeach</div></div>
    </section>
    <section class="cholavin-panel">
        <div class="cholavin-panel-head"><div><span class="cholavin-eyebrow">Attention</span><h2>Upcoming Reminders</h2></div><button class="cholavin-outline-button" type="button" data-toast="All reminders will be connected during backend rebuild.">View all</button></div>
        <div class="cholavin-panel-body"><div class="cholavin-reminder"><i class="ri-wallet-3-line"></i><div><strong>Payment reminder</strong><small>Sri Balaji Traders</small></div><em>₹ 45,690 · Today</em></div><div class="cholavin-reminder"><i class="ri-route-line"></i><div><strong>Delivery schedule</strong><small>Namakkal Shop</small></div><em>5 Deliveries · Today</em></div><div class="cholavin-reminder"><i class="ri-error-warning-line"></i><div><strong>Stock low</strong><small>Ponni Rice (25kg)</small></div><em>12 Bags · Tomorrow</em></div><div class="cholavin-reminder"><i class="ri-bank-card-line"></i><div><strong>Cheque collection</strong><small>Karthik Enterprises</small></div><em>₹ 25,000 · 2 Days</em></div></div>
    </section>
</div>

<div class="cholavin-grid-2 cholavin-section">
    <section class="cholavin-panel"><div class="cholavin-panel-head"><div><span class="cholavin-eyebrow">Business overview</span><h2>Health at a glance</h2></div></div><div class="cholavin-panel-body"><div class="cholavin-kpi-grid" style="grid-template-columns:repeat(2,minmax(0,1fr));margin:0"><div class="cholavin-kpi"><small>Total Customers</small><strong>1,245</strong><em>↑ 8.6%</em></div><div class="cholavin-kpi"><small>Total Suppliers</small><strong>320</strong><em>↑ 5.2%</em></div><div class="cholavin-kpi"><small>Receivables</small><strong>₹ 12,45,680</strong><em>↑ 3.6%</em></div><div class="cholavin-kpi"><small>Payables</small><strong>₹ 8,65,230</strong><em style="color:#b42318">↓ 1.3%</em></div></div></div></section>
    <section class="cholavin-panel"><div class="cholavin-panel-head"><div><span class="cholavin-eyebrow">Live feed</span><h2>Recent Activities</h2></div><button class="cholavin-outline-button" type="button" data-toast="Activity log is part of the administration rebuild.">View all</button></div><div class="cholavin-panel-body">@foreach($activities as $activity)<div class="cholavin-reminder"><i class="{{ $activity['icon'] }}"></i><div><strong>{{ $activity['title'] }}</strong><small>{{ $activity['detail'] }}</small></div></div>@endforeach</div></section>
</div>

<section class="cholavin-section"><div class="cholavin-section-title"><div><span class="cholavin-eyebrow">All modules</span><h2>Module workspace blueprint</h2></div><span class="cholavin-status pending">18 shells · backend rebuild in progress</span></div><div class="cholavin-module-cards">@foreach($modules as $key => $module)@if($key !== 'dashboard')<a href="{{ route('admin.workspace', $key) }}" class="cholavin-module-card"><span class="cholavin-module-number">{{ $module['number'] }}</span><i class="{{ $module['icon'] }}"></i><strong>{{ $module['title'] }}</strong></a>@endif @endforeach</div></section>

<section class="cholavin-panel cholavin-section"><div class="cholavin-panel-head"><div><span class="cholavin-eyebrow">Universal flow</span><h2>Every listing page follows the same interaction model</h2></div></div><div class="cholavin-panel-body"><div class="row g-3 text-center"><div class="col-6 col-md-3 col-lg"><span class="cholavin-status">01</span><p class="mb-0 mt-2 small">Open module</p></div><div class="col-6 col-md-3 col-lg"><span class="cholavin-status">02</span><p class="mb-0 mt-2 small">Apply filters</p></div><div class="col-6 col-md-3 col-lg"><span class="cholavin-status">03</span><p class="mb-0 mt-2 small">View data</p></div><div class="col-6 col-md-3 col-lg"><span class="cholavin-status">04</span><p class="mb-0 mt-2 small">Open drawer</p></div><div class="col-6 col-md-3 col-lg"><span class="cholavin-status">05</span><p class="mb-0 mt-2 small">Save via AJAX</p></div><div class="col-6 col-md-3 col-lg"><span class="cholavin-status">06</span><p class="mb-0 mt-2 small">Success toast</p></div></div></div></section>
