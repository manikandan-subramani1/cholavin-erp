@extends('backend.layouts.app')

@section('title', 'Stock Transfers | Cholavin ERP')

@section('content')
<div id="stock-transfer-module" data-index-url="{{ route('admin.stock-transfers.index') }}">
    <div class="card erp-panel mb-3"><div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-3"><div><span class="erp-eyebrow">Inventory</span><h4 class="mb-0">Stock Transfers</h4></div>@can('stock.transfer')<button id="add-transfer" class="btn btn-primary" type="button"><i class="ri-arrow-left-right-line me-1"></i>New Transfer</button>@endcan</div></div>
    <div class="erp-module-kpis" data-module-summary-root>
        <article><i class="ri-arrow-left-right-line"></i><span><small>Total Transfers</small><strong data-summary-key="records" data-summary-format="number">{{ number_format($transferSummary['records']) }}</strong><em>Current financial year</em></span></article>
        <article><i class="ri-checkbox-circle-line"></i><span><small>Completed</small><strong data-summary-key="completed" data-summary-format="number">{{ number_format($transferSummary['completed']) }}</strong><em>Stock successfully moved</em></span></article>
        <article><i class="ri-draft-line"></i><span><small>Drafts</small><strong data-summary-key="drafts" data-summary-format="number">{{ number_format($transferSummary['drafts']) }}</strong><em>Awaiting completion</em></span></article>
        <article><i class="ri-stack-line"></i><span><small>Transferred Quantity</small><strong data-summary-key="quantity" data-summary-format="quantity">{{ number_format($transferSummary['quantity'], 3) }}</strong><em>Across filtered transfers</em></span></article>
    </div>
    <div class="accordion mb-3" id="transfer-filter-accordion"><div class="accordion-item erp-panel"><h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#transfer-filter-panel"><i class="ri-filter-3-line me-2"></i>Filters</button></h2>
        <div id="transfer-filter-panel" class="accordion-collapse collapse" data-bs-parent="#transfer-filter-accordion"><div class="accordion-body"><form id="transfer-filters" class="row g-3">
            <div class="col-lg-3"><label class="form-label" for="transfer-search">Search</label><input id="transfer-search" type="search" class="form-control" placeholder="Transfer number"></div>
            <div class="col-lg-2"><label class="form-label" for="transfer-status-filter">Status</label><select id="transfer-status-filter" class="form-select"><option value="">All</option><option value="draft">Draft</option><option value="completed">Completed</option></select></div>
            <div class="col-lg-3"><label class="form-label" for="transfer-godown-filter">Godown</label><select id="transfer-godown-filter" class="form-select"><option value="">All</option>@foreach($godowns as $godown)<option value="{{ $godown->id }}">{{ $godown->name }}</option>@endforeach</select></div>
            <div class="col-lg-2"><label class="form-label" for="transfer-from-filter">From</label><input id="transfer-from-filter" type="date" class="form-control"></div>
            <div class="col-lg-2"><label class="form-label" for="transfer-to-filter">To</label><input id="transfer-to-filter" type="date" class="form-control"></div>
            <div class="col-12"><button id="reset-transfer-filters" class="btn btn-secondary" type="button">Reset filters</button></div>
        </form></div></div>
    </div></div>
    <div class="card erp-panel"><div class="card-body table-responsive"><table id="transfers-table" class="table table-hover align-middle w-100"><thead><tr><th>S.No</th><th>Number</th><th>Date</th><th>Route</th><th>Items</th><th>Status</th></tr></thead></table></div></div>

    <div id="transfer-modal" class="modal fade erp-form-modal" tabindex="-1" aria-labelledby="transfer-modal-title" aria-hidden="true"><div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl modal-fullscreen-sm-down"><div class="modal-content"><div class="modal-header"><div><span class="erp-eyebrow">Inventory movement</span><h5 id="transfer-modal-title" class="modal-title">New Stock Transfer</h5></div><button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button></div>
        <form id="transfer-form" action="{{ route('admin.stock-transfers.store') }}" method="POST" novalidate>@csrf<div class="modal-body">
            <div class="row g-3"><div class="col-md-6 form-group"><label class="form-label">From Godown <span class="text-danger">*</span></label><select name="from_godown_id" class="form-select"><option value="">Select</option>@foreach($godowns as $g)<option value="{{ $g->id }}">{{ $g->name }}</option>@endforeach</select></div>
            <div class="col-md-6 form-group"><label class="form-label">To Godown <span class="text-danger">*</span></label><select name="to_godown_id" class="form-select"><option value="">Select</option>@foreach($godowns as $g)<option value="{{ $g->id }}">{{ $g->name }}</option>@endforeach</select></div>
            <div class="col-md-6 form-group"><label class="form-label">Date <span class="text-danger">*</span></label><input name="transfer_date" type="date" value="{{ now()->toDateString() }}" class="form-control"></div>
            <div class="col-md-6 form-group"><label class="form-label">Status <span class="text-danger">*</span></label><select name="status" class="form-select"><option value="draft">Draft</option><option value="completed">Complete now</option></select></div>
            <div class="col-12 form-group"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="2"></textarea></div></div>
            <div class="d-flex align-items-center justify-content-between mt-4 mb-2"><h6 class="mb-0">Transfer items</h6><button id="add-transfer-item" type="button" class="btn btn-sm btn-secondary"><i class="ri-add-line me-1"></i>Add line</button></div>
            <div id="transfer-items"></div>
        </div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Save Transfer</button></div></form>
    </div></div></div>
    <template id="transfer-item-template"><div class="transfer-item border rounded p-3 mb-2"><div class="row g-2"><div class="col-md-6 form-group"><label class="form-label">Product</label><select data-field="product_id" class="form-select transfer-product" required><option value="">Select product</option>@foreach($products as $product)<option value="{{ $product->id }}">{{ $product->name }}{{ $product->sku ? ' — '.$product->sku : '' }}</option>@endforeach</select></div><div class="col-md-3 form-group"><label class="form-label">Quantity</label><input data-field="quantity" type="number" step="0.001" min="0.001" class="form-control" required></div><div class="col-md-2 form-group"><label class="form-label">Batch</label><input data-field="batch_number" class="form-control" maxlength="80"></div><div class="col-md-1 align-self-end"><button type="button" class="btn btn-soft-danger remove-transfer-item"><i class="ri-delete-bin-line"></i></button></div></div></div></template>
</div>
@endsection

@push('scripts')
<script src="{{ asset('backend/assets/js/modules/stock-transfers.js') }}?v={{ filemtime(public_path('backend/assets/js/modules/stock-transfers.js')) }}"></script>
@endpush
