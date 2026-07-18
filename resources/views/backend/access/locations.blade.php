@extends('backend.layouts.app')

@section('title', 'Shops & Godowns | Cholavin ERP')

@section('content')
<div id="locations-module" data-index-url="{{ route('admin.locations.index') }}" data-shop-store-url="{{ route('admin.shops.store') }}" data-godown-store-url="{{ route('admin.godowns.store') }}">
    <div class="card erp-panel mb-3"><div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div><span class="erp-eyebrow">Organisation setup</span><h4 class="mb-0">Shops & Godowns</h4></div>
        <div class="d-flex gap-2">@can('shops.create')<button id="add-shop" class="btn btn-primary" type="button"><i class="ri-store-2-line me-1"></i>Add Shop</button>@endcan @can('godowns.create')<button id="add-godown" class="btn btn-primary" type="button"><i class="ri-building-4-line me-1"></i>Add Godown</button>@endcan</div>
    </div></div>

    <div class="erp-module-kpis" data-module-summary-root>
        <article><i class="ri-store-2-line"></i><span><small>Total Shops</small><strong data-summary-key="shops" data-summary-format="number">{{ number_format($locationSummary['shops']) }}</strong><em>Business locations</em></span></article>
        <article><i class="ri-checkbox-circle-line"></i><span><small>Active Shops</small><strong data-summary-key="active_shops" data-summary-format="number">{{ number_format($locationSummary['active_shops']) }}</strong><em>Available for billing</em></span></article>
        <article><i class="ri-building-4-line"></i><span><small>Total Godowns</small><strong data-summary-key="godowns" data-summary-format="number">{{ number_format($locationSummary['godowns']) }}</strong><em>Inventory locations</em></span></article>
        <article><i class="ri-archive-stack-line"></i><span><small>Active Godowns</small><strong data-summary-key="active_godowns" data-summary-format="number">{{ number_format($locationSummary['active_godowns']) }}</strong><em>Ready for stock movement</em></span></article>
    </div>

    <div class="accordion mb-3" id="location-filter-accordion"><div class="accordion-item erp-panel">
        <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#location-filter-panel"><i class="ri-filter-3-line me-2"></i>Filters</button></h2>
        <div id="location-filter-panel" class="accordion-collapse collapse" data-bs-parent="#location-filter-accordion"><div class="accordion-body"><form id="location-filters" class="row g-3">
            <div class="col-lg-4"><label class="form-label" for="location-search">Search</label><input id="location-search" class="form-control" type="search" placeholder="Name or code"></div>
            <div class="col-lg-3"><label class="form-label" for="location-shop-filter">Godowns linked to</label><select id="location-shop-filter" class="form-select"><option value="">All shops</option>@foreach($shops as $shop)<option value="{{ $shop->id }}">{{ $shop->name }}</option>@endforeach</select></div>
            <div class="col-lg-3"><label class="form-label" for="location-status-filter">Status</label><select id="location-status-filter" class="form-select"><option value="">All</option><option value="1">Active</option><option value="0">Inactive</option></select></div>
            <div class="col-lg-2 align-self-end"><button id="reset-location-filters" class="btn btn-secondary w-100" type="button">Reset</button></div>
        </form></div></div>
    </div></div>

    @php($locationEntity = request('entity', 'shops'))
    <nav class="erp-workspace-tabs" aria-label="Location workspace">
        <a href="{{ route('admin.locations.index', ['entity' => 'shops']) }}" class="{{ $locationEntity === 'shops' ? 'active' : '' }}"><i class="ri-store-2-line"></i>Shop Management</a>
        <a href="{{ route('admin.locations.index', ['entity' => 'godowns']) }}" class="{{ $locationEntity === 'godowns' ? 'active' : '' }}"><i class="ri-building-4-line"></i>Godown Management</a>
    </nav>
    <div class="row g-3">
        <div class="col-12 {{ $locationEntity === 'shops' ? '' : 'd-none' }}"><div class="card erp-panel h-100"><div class="card-header"><h5 class="mb-0">Shop Management</h5></div><div class="card-body table-responsive"><table id="shops-table" class="table table-hover align-middle w-100"><thead><tr><th>S.No</th><th>Name</th><th>Code</th><th>Godowns</th><th>Users</th><th>Status</th><th>Action</th></tr></thead></table></div></div></div>
        <div class="col-12 {{ $locationEntity === 'godowns' ? '' : 'd-none' }}"><div class="card erp-panel h-100"><div class="card-header"><h5 class="mb-0">Godown Management</h5></div><div class="card-body table-responsive"><table id="godowns-table" class="table table-hover align-middle w-100"><thead><tr><th>S.No</th><th>Name</th><th>Code</th><th>Shops</th><th>Users</th><th>Status</th><th>Action</th></tr></thead></table></div></div></div>
    </div>

    <div class="modal fade erp-form-modal" tabindex="-1" id="shop-modal" aria-labelledby="shop-modal-title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg modal-fullscreen-sm-down"><div class="modal-content">
            <div class="modal-header"><div><span class="erp-eyebrow">Organisation setup</span><h5 id="shop-modal-title" class="modal-title">Add Shop</h5></div><button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button></div>
            <form id="shop-form" method="POST" action="{{ route('admin.shops.store') }}" novalidate>@csrf<input id="shop-method" name="_method" type="hidden" value="POST"><div class="modal-body">
                <div class="form-group mb-3"><label class="form-label" for="shop-name">Shop name <span class="text-danger">*</span></label><input id="shop-name" name="name" class="form-control"></div>
                <div class="form-group mb-3"><label class="form-label" for="shop-code">Code <span class="text-danger">*</span></label><input id="shop-code" name="code" class="form-control"></div>
                <div class="form-group mb-3"><label class="form-label" for="shop-address">Address</label><textarea id="shop-address" name="address" class="form-control" rows="4"></textarea></div>
                <div id="shop-status-wrap" class="d-none"><input type="hidden" name="is_active" value="0"><div class="form-check form-switch"><input id="shop-active" class="form-check-input" type="checkbox" name="is_active" value="1" checked><label for="shop-active" class="form-check-label">Active shop</label></div></div>
            </div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Save Shop</button></div></form>
        </div></div>
    </div>

    <div class="modal fade erp-form-modal" tabindex="-1" id="godown-modal" aria-labelledby="godown-modal-title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg modal-fullscreen-sm-down"><div class="modal-content">
            <div class="modal-header"><div><span class="erp-eyebrow">Organisation setup</span><h5 id="godown-modal-title" class="modal-title">Add Godown</h5></div><button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button></div>
            <form id="godown-form" method="POST" action="{{ route('admin.godowns.store') }}" novalidate>@csrf<input id="godown-method" name="_method" type="hidden" value="POST"><div class="modal-body">
                <div class="form-group mb-3"><label class="form-label" for="godown-name">Godown name <span class="text-danger">*</span></label><input id="godown-name" name="name" class="form-control"></div>
                <div class="form-group mb-3"><label class="form-label" for="godown-code">Code <span class="text-danger">*</span></label><input id="godown-code" name="code" class="form-control"></div>
                <div class="form-group mb-3"><label class="form-label" for="godown-shops">Linked shops</label><select id="godown-shops" name="shop_ids[]" class="form-select" multiple>@foreach($shops as $shop)<option value="{{ $shop->id }}">{{ $shop->name }}</option>@endforeach</select></div>
                <div class="form-group mb-3"><label class="form-label" for="godown-address">Address</label><textarea id="godown-address" name="address" class="form-control" rows="4"></textarea></div>
                <div id="godown-status-wrap" class="d-none"><input type="hidden" name="is_active" value="0"><div class="form-check form-switch"><input id="godown-active" class="form-check-input" type="checkbox" name="is_active" value="1" checked><label for="godown-active" class="form-check-label">Active godown</label></div></div>
            </div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Save Godown</button></div></form>
        </div></div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('backend/assets/js/modules/access-locations.js') }}?v={{ filemtime(public_path('backend/assets/js/modules/access-locations.js')) }}"></script>
@endpush
