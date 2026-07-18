@extends('backend.layouts.app')

@section('title', 'Financial Overview | Cholavin ERP')

@section('content')
<div id="financial-overview-module" data-chart='@json($chartData)' data-payments-url="{{ route('admin.payments.index') }}" data-vouchers-url="{{ route('admin.vouchers.index') }}" data-reports-url="{{ route('admin.reports.index', 'profit-loss') }}">
    <div class="card erp-panel mb-3"><div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-3"><div><span class="erp-eyebrow">Accounting workspace</span><h4 class="mb-0">Financial Overview</h4><p class="text-muted mb-0">Income, expenses, exposure and stock value for the active business context.</p></div><div class="d-flex flex-wrap gap-2">@can('payments.create')<a href="{{ route('admin.payments.index', ['type' => 'customer_collection']) }}" class="btn btn-secondary"><i class="ri-arrow-down-circle-line me-1"></i>Receive</a>@endcan @can('accounts.create')<a href="{{ route('admin.vouchers.index') }}" class="btn btn-secondary"><i class="ri-file-list-3-line me-1"></i>Journal</a>@endcan @can('reports.view')<a href="{{ route('admin.reports.index', 'profit-loss') }}" class="btn btn-primary"><i class="ri-file-chart-line me-1"></i>Profit &amp; Loss</a>@endcan</div></div></div>

    <div class="erp-financial-kpis">
        @foreach([
            ['Income', 'income', 'ri-arrow-down-circle-line', 'is-income'],
            ['Expenses', 'expenses', 'ri-arrow-up-circle-line', 'is-expense'],
            ['Net Profit', 'net_profit', 'ri-funds-line', 'is-profit'],
            ['Receivables', 'receivables', 'ri-hand-coin-line', 'is-receivable'],
            ['Payables', 'payables', 'ri-refund-2-line', 'is-payable'],
            ['Stock Value', 'stock_value', 'ri-stack-line', 'is-stock'],
        ] as [$label, $key, $icon, $tone])
            <article class="{{ $tone }}"><i class="{{ $icon }}"></i><span><small>{{ $label }}</small><strong>₹{{ number_format($summary[$key], 2) }}</strong><em>Active shop / year scope</em></span></article>
        @endforeach
    </div>



    <div class="erp-finance-actions" aria-label="Financial quick actions">
        <a href="{{ route('admin.payments.index', ['type' => 'customer_collection']) }}"><i class="ri-arrow-down-circle-line"></i><span><strong>Receive Payment</strong><small>Customer collection</small></span></a>
        <a href="{{ route('admin.payments.index', ['type' => 'supplier_payment']) }}"><i class="ri-arrow-up-circle-line"></i><span><strong>Make Payment</strong><small>Supplier payout</small></span></a>
        <a href="{{ route('admin.payments.index', ['type' => 'expense']) }}"><i class="ri-receipt-line"></i><span><strong>Add Expense</strong><small>Operating cost</small></span></a>
        <a href="{{ route('admin.vouchers.index') }}"><i class="ri-file-list-3-line"></i><span><strong>Journal Entry</strong><small>Balanced posting</small></span></a>
        <a href="{{ route('admin.reports.index', 'receivables') }}"><i class="ri-hand-coin-line"></i><span><strong>Receivable Ageing</strong><small>Outstanding drill-down</small></span></a>
        <a href="{{ route('admin.reports.index', 'payables') }}"><i class="ri-refund-2-line"></i><span><strong>Payable Ageing</strong><small>Supplier drill-down</small></span></a>
    </div>

    <div class="row g-4">
        <div class="col-xl-8"><div class="card erp-panel h-100"><div class="card-header"><div><span class="erp-eyebrow">Twelve-month trend</span><h5 class="mb-0">Income versus expenses</h5></div></div><div class="card-body"><div id="financial-overview-trend" class="erp-chart erp-chart-lg"></div></div></div></div>
        <div class="col-xl-4"><div class="card erp-panel h-100"><div class="card-header"><div><span class="erp-eyebrow">Current result</span><h5 class="mb-0">Profit composition</h5></div></div><div class="card-body"><div id="financial-overview-composition" class="erp-chart erp-chart-lg"></div></div></div></div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('backend/assets/js/modules/financial-overview.js') }}?v={{ filemtime(public_path('backend/assets/js/modules/financial-overview.js')) }}"></script>
@endpush
