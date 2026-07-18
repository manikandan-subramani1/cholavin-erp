@extends('backend.layouts.app')

@section('title', 'Stock | Cholavin ERP')

@section('content')
<div id="stock-module" data-index-url="{{ route('admin.stock.index') }}" data-pdf-url="{{ route('admin.stock.pdf') }}">
    <div class="card erp-panel mb-3"><div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-3"><div><span class="erp-eyebrow">Inventory</span><h4 class="mb-0">Shop & Godown Stock</h4></div><div>
        @can('stock.export')<button id="stock-pdf" type="button" class="btn btn-secondary"><i class="ri-file-pdf-2-line me-1"></i>PDF</button>@endcan
        @can('stock.transfer')<a href="{{ route('admin.stock-transfers.index') }}" class="btn btn-primary"><i class="ri-arrow-left-right-line me-1"></i>Stock Transfer</a>@endcan
    </div></div></div>
    <div class="erp-stock-kpis" aria-live="polite">
        <article><i class="ri-box-3-line"></i><span><small>Total Items</small><strong data-stock-summary="products">{{ number_format($stockSummary['products']) }}</strong><em>Active stock records</em></span></article>
        <article><i class="ri-stack-line"></i><span><small>On-hand Quantity</small><strong data-stock-summary="quantity">{{ number_format($stockSummary['quantity'], 3) }}</strong><em>Across selected locations</em></span></article>
        <article><i class="ri-money-rupee-circle-line"></i><span><small>Stock Value</small><strong data-stock-summary="value">₹{{ number_format($stockSummary['value'], 2) }}</strong><em>Average cost valuation</em></span></article>
        <article><i class="ri-error-warning-line"></i><span><small>Low Stock</small><strong data-stock-summary="low_stock">{{ number_format($stockSummary['low_stock']) }}</strong><em>Require attention</em></span></article>
        <article><i class="ri-close-circle-line"></i><span><small>Out of Stock</small><strong data-stock-summary="out_of_stock">{{ number_format($stockSummary['out_of_stock']) }}</strong><em>Unavailable items</em></span></article>
    </div>
    <div class="accordion mb-3" id="stock-filter-accordion"><div class="accordion-item erp-panel"><h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#stock-filter-panel"><i class="ri-filter-3-line me-2"></i>Filters</button></h2>
        <div id="stock-filter-panel" class="accordion-collapse collapse" data-bs-parent="#stock-filter-accordion"><div class="accordion-body"><form id="stock-filters" class="row g-3">
            <div class="col-lg-3"><label class="form-label" for="stock-search">Search</label><input id="stock-search" class="form-control" type="search" placeholder="Product or SKU"></div>
            <div class="col-lg-3"><label class="form-label" for="stock-product-filter">Product</label><select id="stock-product-filter" class="form-select"><option value="">All products</option>@foreach($products as $product)<option value="{{ $product->id }}">{{ $product->name }}{{ $product->sku ? ' — '.$product->sku : '' }}</option>@endforeach</select></div>
            <div class="col-lg-3"><label class="form-label" for="stock-godown-filter">Godown</label><select id="stock-godown-filter" class="form-select"><option value="">All godowns</option>@foreach($godowns as $godown)<option value="{{ $godown->id }}">{{ $godown->name }}</option>@endforeach</select></div>
            <div class="col-lg-2 align-self-end"><div class="form-check form-switch mb-2"><input id="low-stock-only" class="form-check-input" type="checkbox"><label class="form-check-label" for="low-stock-only">Low stock only</label></div></div>
            <div class="col-lg-1 align-self-end"><button id="reset-stock-filters" class="btn btn-secondary w-100" type="button"><i class="ri-refresh-line"></i></button></div>
        </form></div></div>
    </div></div>
    <div class="card erp-panel"><div class="card-body table-responsive"><table id="stock-table" class="table table-hover context-data-table align-middle w-100"><thead><tr><th>S.No</th><th>Product</th><th>SKU</th><th>Godown</th><th>Batch</th><th>Expiry</th><th>Quantity</th><th>Average Cost</th><th>Value</th><th>Status</th></tr></thead></table></div></div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('backend/assets/js/modules/stock.js') }}?v={{ filemtime(public_path('backend/assets/js/modules/stock.js')) }}"></script>
@endpush
