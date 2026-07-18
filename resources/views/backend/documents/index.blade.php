@extends('backend.layouts.app')
@section('title', $module['title'].' | Cholavin ERP')
@push('styles')
<style>.document-item-row td{min-width:110px}.document-item-row td:first-child{min-width:230px}</style>
@endpush
@section('content')
<div id="commercial-documents-module"
    data-base-url="{{ route('admin.documents.index', $moduleKey) }}"
    data-pdf-url="{{ route('admin.documents.pdf', $moduleKey) }}"
    data-product-url="{{ route('admin.lookups.products') }}"
    data-party-url="{{ route('admin.lookups.parties', ['type' => $module['party_type']]) }}"
    data-party-required="{{ $module['party_type'] ? '1' : '0' }}"
    data-purchase="{{ $module['group'] === 'Purchases' ? '1' : '0' }}">
<div class="page-title-box d-flex justify-content-between align-items-center">
    <div><span class="erp-eyebrow">{{ $module['group'] }}</span><h4>{{ $module['title'] }}</h4></div>
    <div class="d-flex gap-2">
        @can($moduleKey.'.export')<button id="document-pdf" class="btn btn-secondary">PDF</button>@endcan
        @can($moduleKey.'.create')<button id="add-document" class="btn btn-primary">Add New</button>@endcan
    </div>
</div>
<div class="erp-module-kpis" data-module-summary-root>
    <article><i class="ri-file-list-3-line"></i><span><small>Total Documents</small><strong data-summary-key="records" data-summary-format="number">{{ number_format($documentSummary['records']) }}</strong><em>{{ $module['title'] }}</em></span></article>
    <article><i class="ri-checkbox-circle-line"></i><span><small>Posted Value</small><strong data-summary-key="posted_total" data-summary-format="money">₹{{ number_format($documentSummary['posted_total'], 2) }}</strong><em>Confirmed transactions</em></span></article>
    <article><i class="ri-wallet-3-line"></i><span><small>Outstanding</small><strong data-summary-key="outstanding" data-summary-format="money">₹{{ number_format($documentSummary['outstanding'], 2) }}</strong><em>Balance remaining</em></span></article>
    <article><i class="ri-draft-line"></i><span><small>Drafts</small><strong data-summary-key="drafts" data-summary-format="number">{{ number_format($documentSummary['drafts']) }}</strong><em>Awaiting posting</em></span></article>
</div>
@if($moduleKey === 'pos-billing')
    <section class="erp-pos-catalog" aria-labelledby="pos-catalog-title">
        <div class="erp-pos-catalog-head"><div><span class="erp-eyebrow">Fast billing</span><h5 id="pos-catalog-title">Choose an item to start a new bill</h5></div><div class="erp-pos-search"><i class="ri-search-line"></i><input id="pos-product-search" type="search" placeholder="Search item or SKU"></div></div>
        <div class="erp-pos-product-grid">
            @forelse($posProducts as $product)
                <button type="button" class="erp-pos-product" data-product-id="{{ $product->id }}" data-search="{{ str($product->name.' '.$product->sku)->lower() }}">
                    <img src="{{ $product->imageUrl() }}" alt=""><span><strong>{{ $product->name }}</strong><small>{{ $product->sku ?: 'No SKU' }}</small><em>₹{{ number_format((float) ($product->sale_price ?: $product->price), 2) }}</em><b>{{ number_format((float) ($product->available_stock ?? 0), 3) }} available</b></span>
                </button>
            @empty
                <div class="erp-dashboard-empty compact"><p>No active items are available for billing.</p></div>
            @endforelse
        </div>
    </section>
@endif
@if($module['stock_effect'] && !$activeGodownId)
    <div class="alert alert-warning">Select a godown in the header before posting this stock-affecting document.</div>
@endif
<div class="accordion mb-3" id="document-filter-accordion"><div class="accordion-item erp-panel">
<h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#document-filter-panel"><i class="ri-filter-3-line me-2"></i>Filters</button></h2>
<div id="document-filter-panel" class="accordion-collapse collapse" data-bs-parent="#document-filter-accordion"><div class="accordion-body"><form id="document-filters" class="row g-3">
    <div class="col-md-2"><label class="form-label" for="document-search">Search</label><input id="document-search" type="search" class="form-control" placeholder="Number or party"></div>
    <div class="col-md-2"><label class="form-label">Status</label><select id="document-status-filter" class="form-select"><option value="">All</option><option value="draft">Draft</option><option value="posted">Posted</option></select></div>
    <div class="col-md-3"><label class="form-label">From</label><input id="document-from-filter" type="date" class="form-control"></div>
    <div class="col-md-3"><label class="form-label">To</label><input id="document-to-filter" type="date" class="form-control"></div>
    <div class="col-md-2 align-self-end"><button id="reset-document-filters" type="button" class="btn btn-secondary w-100">Reset</button></div>
</form></div></div></div></div>
<div class="card erp-panel"><div class="card-body table-responsive"><table id="documents-table" class="table table-hover w-100">
    <thead><tr><th>S.No</th><th>Number</th><th>Date</th><th>Party</th><th>Godown</th><th>Items</th><th>Total</th><th>Balance</th><th>Status</th><th>Action</th></tr></thead>
</table></div></div>

<div id="document-modal" class="modal fade erp-form-modal" tabindex="-1" aria-labelledby="document-modal-title" aria-hidden="true"><div class="modal-dialog modal-dialog-scrollable modal-fullscreen-sm-down modal-xl"><div class="modal-content">
<form id="document-form" method="POST">
    @csrf
    <div class="modal-header"><div><span class="erp-eyebrow">{{ $module['group'] }}</span><h5 id="document-modal-title" class="modal-title">{{ str($module['title'])->singular() }}</h5></div><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
    <div class="modal-body">@include('backend.documents.'.$moduleKey.'.form')</div>
    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-success">Save Document</button></div>
</form></div></div></div>
</div>
@endsection
@push('scripts')
<script src="{{ asset('backend/assets/js/modules/commercial-documents.js') }}?v={{ filemtime(public_path('backend/assets/js/modules/commercial-documents.js')) }}"></script>
@endpush
