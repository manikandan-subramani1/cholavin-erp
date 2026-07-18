@extends('backend.layouts.app')
@section('title', $title.' | Cholavin ERP')
@section('content')
<div id="report-module" data-index-url="{{ route('admin.reports.index', $report) }}" data-pdf-url="{{ route('admin.reports.pdf', $report) }}" data-finance-url="{{ route('admin.financial-overview') }}">
    <div class="card erp-panel mb-3"><div class="card-body d-flex justify-content-between align-items-center gap-3"><div><span class="erp-eyebrow">Reports & Analytics</span><h4 class="mb-1">{{ $title }}</h4><p class="text-muted mb-0">KPI summary, date presets, drill-down table, export, and financial-year scoped data.</p></div><div class="d-flex flex-wrap gap-2"><a href="{{ route('admin.financial-overview') }}" class="btn btn-secondary" data-report-action="finance"><i class="ri-line-chart-line me-1"></i>Finance</a>@can('reports.export')<button id="report-pdf" class="btn btn-secondary" type="button"><i class="ri-file-pdf-2-line me-1"></i>Download PDF</button>@endcan</div></div></div>
    <div class="erp-report-kpis" aria-live="polite">
        <article><i class="ri-file-list-3-line"></i><span><small>Records</small><strong data-report-summary="records">0</strong></span></article>
        <article><i class="ri-arrow-down-circle-line"></i><span><small>Debit / In</small><strong data-report-summary="debit">₹0.00</strong></span></article>
        <article><i class="ri-arrow-up-circle-line"></i><span><small>Credit / Out</small><strong data-report-summary="credit">₹0.00</strong></span></article>
        <article><i class="ri-funds-line"></i><span><small>Report Value</small><strong data-report-summary="amount">₹0.00</strong></span></article>
    </div>
    <div class="erp-report-presets" aria-label="Report date presets">
        <button type="button" data-report-preset="today">Today</button>
        <button type="button" data-report-preset="yesterday">Yesterday</button>
        <button type="button" data-report-preset="week">This Week</button>
        <button type="button" data-report-preset="month">This Month</button>
        <button type="button" data-report-preset="last-month">Last Month</button>
        <button type="button" data-report-preset="financial-year">Financial Year</button>
    </div>
    <div class="accordion mb-3" id="report-filter-accordion"><div class="accordion-item erp-panel"><h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#report-filter-panel"><i class="ri-filter-3-line me-2"></i>Filters</button></h2><div id="report-filter-panel" class="accordion-collapse collapse"><div class="accordion-body"><form id="report-filters" class="row g-3"><div class="col-lg-5"><label class="form-label" for="report-search">Search</label><input id="report-search" type="search" class="form-control" placeholder="Reference, party or description"></div><div class="col-lg-2"><label class="form-label" for="report-from">From</label><input id="report-from" type="date" class="form-control"></div><div class="col-lg-2"><label class="form-label" for="report-to">To</label><input id="report-to" type="date" class="form-control"></div><div class="col-lg-3 align-self-end"><button id="report-reset" class="btn btn-secondary w-100" type="button">Reset filters</button></div></form></div></div></div></div>
    <div class="card erp-panel"><div class="card-body table-responsive"><table id="report-table" class="table table-hover align-middle w-100"><thead><tr><th>S.No</th><th>Date</th><th>Reference</th><th>Party / Product / User</th><th>Description</th><th>Debit / In</th><th>Credit / Out</th><th>Amount</th><th>Status</th></tr></thead></table></div></div>
</div>
@endsection
@push('scripts')<script src="{{ asset('backend/assets/js/modules/reports.js') }}?v={{ filemtime(public_path('backend/assets/js/modules/reports.js')) }}"></script>@endpush
