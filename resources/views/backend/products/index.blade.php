@extends('backend.layouts.app')
@section('title', 'Item & Product Master | Cholavin ERP')
@section('content')
@php
    $categories = $masters['categories'];
    $brands = $masters['brands'];
    $variants = $masters['variants'];
    $grades = $masters['grades'];
    $units = $masters['units'];
@endphp
<div id="products-module" class="product-master"
    data-index-url="{{ route('admin.products.index') }}"
    data-base-url="{{ url('/admin/products') }}"
    data-pdf-url="{{ route('admin.products.pdf') }}"
    data-create-url="{{ route('admin.products.create') }}"
    data-stock-url="{{ route('admin.stock.index') }}"
    data-adjust-url="{{ route('admin.documents.index', 'stock-adjustments') }}"
    data-transfer-url="{{ route('admin.stock-transfers.index') }}"
    data-can-create="{{ auth()->user()->can('products.create') ? 1 : 0 }}"
    data-can-update="{{ auth()->user()->can('products.update') ? 1 : 0 }}"
    data-can-stock="{{ auth()->user()->can('stock.update') ? 1 : 0 }}">

    <section class="product-command erp-panel">
        <div>
            <span class="erp-eyebrow">Item & Product Master</span>
            <h1>Rice product catalogue</h1>
            <p>Manage item identity, classification, pricing, barcode and location-aware stock.</p>
        </div>
        <div class="product-command-actions">
            @can('products.export')<button id="products-pdf" class="btn btn-secondary" type="button"><i class="ri-file-pdf-2-line"></i> Export</button>@endcan
            @can('create', AppModelsProduct::class)<a href="{{ route('admin.products.create') }}" class="btn btn-primary"><i class="ri-add-line"></i> Add Item</a>@endcan
        </div>
    </section>

    <section class="product-summary" aria-label="Item summary">
        @foreach([
            ['total','Total Items','ri-box-3-line','All catalogue records'],
            ['active','Active Items','ri-checkbox-circle-line','Ready for daily use'],
            ['low_stock','Low Stock','ri-alarm-warning-line','At or below reorder'],
            ['out_of_stock','Out of Stock','ri-close-circle-line','Needs replenishment'],
            ['stock_value','Total Stock Value','ri-money-rupee-circle-line','At average cost'],
            ['without_image','Without Image','ri-image-line','Needs product photo'],
            ['without_barcode','Without Barcode','ri-barcode-line','Needs identification'],
        ] as [$key,$label,$icon,$hint])
        <button class="product-summary-card" type="button" data-summary-filter="{{ $key }}">
            <i class="{{ $icon }}"></i><span><small>{{ $label }}</small>
            <strong>@if($key === 'stock_value')&#8377;@endif{{ number_format($metrics[$key], $key === 'stock_value' ? 2 : 0) }}</strong>
            <em>{{ $hint }}</em></span>
        </button>
        @endforeach
    </section>

    <section class="product-filter erp-panel">
        <div class="product-filter-head"><div><span class="erp-eyebrow">Smart filters</span><h2>Find an item fast</h2></div><button id="reset-product-filters" class="btn btn-light" type="button"><i class="ri-refresh-line"></i> Reset</button></div>
        <form id="product-filters" class="product-filter-grid">
            <label><span>Name</span><input id="product-search" type="search" class="form-control" placeholder="Search item name"></label>
            <label><span>Item code</span><input id="product-code-filter" class="form-control" placeholder="SKU / code"></label>
            <label><span>Barcode</span><input id="product-barcode-filter" class="form-control" placeholder="Scan or type"></label>
            <label><span>Category</span><select id="product-category-filter" class="form-select"><option value="">All categories</option>@foreach($categories as $item)<option value="{{ $item->id }}">{{ $item->name }}</option>@endforeach</select></label>
            <label><span>Rice variety</span><select id="product-variant-filter" class="form-select"><option value="">All varieties</option>@foreach($variants as $item)<option value="{{ $item->id }}">{{ $item->name }}</option>@endforeach</select></label>
            <label><span>Grade</span><select id="product-grade-filter" class="form-select"><option value="">All grades</option>@foreach($grades as $item)<option value="{{ $item->id }}">{{ $item->name }}</option>@endforeach</select></label>
            <label><span>Brand</span><select id="product-brand-filter" class="form-select"><option value="">All brands</option>@foreach($brands as $item)<option value="{{ $item->id }}">{{ $item->name }}</option>@endforeach</select></label>
            <label><span>Unit</span><select id="product-unit-filter" class="form-select"><option value="">All units</option>@foreach($units as $item)<option value="{{ $item->id }}">{{ $item->name }}</option>@endforeach</select></label>
            <label><span>Stock status</span><select id="product-stock-filter" class="form-select"><option value="">All stock</option><option value="available">Available</option><option value="low">Low stock</option><option value="out">Out of stock</option></select></label>
            <label><span>Active status</span><select id="product-status-filter" class="form-select"><option value="">All statuses</option><option value="1">Active</option><option value="0">Inactive</option></select></label>
        </form>
    </section>

    <section class="erp-panel product-table-panel">
        <div class="product-table-head"><div><span class="erp-eyebrow">Live catalogue</span><h2>Items and stock visibility</h2></div><span class="product-context-pill"><i class="ri-shield-check-line"></i> Server-scoped results</span></div>
        <div class="table-responsive">
            <table id="products-table" class="table align-middle w-100">
                <thead><tr><th>Image</th><th>Item</th><th>Category</th><th>Variety</th><th>Grade</th><th>Unit</th><th>Purchase Price</th><th>Selling Price</th><th>Godown Stock</th><th>Shop Stock</th><th>Status</th><th>Actions</th></tr></thead>
            </table>
        </div>
        <div class="product-empty d-none" id="product-empty"><i class="ri-inbox-archive-line"></i><h3>No items match these filters</h3><p>Clear a filter or add a new rice product.</p></div>
    </section>

    <nav class="product-quickbar" aria-label="Product quick actions">
        @can('products.create')<button type="button" data-product-quick="add"><i class="ri-add-box-line"></i><span>Add Item</span></button>@endcan
        @can('stock.update')<button type="button" data-product-quick="opening"><i class="ri-archive-drawer-line"></i><span>Opening Stock</span></button>@endcan
        @can('products.update')<button type="button" data-product-quick="barcode"><i class="ri-barcode-line"></i><span>Print Barcode</span></button><button type="button" data-product-quick="price"><i class="ri-price-tag-3-line"></i><span>Update Price</span></button><button type="button" data-product-quick="image"><i class="ri-image-add-line"></i><span>Upload Image</span></button>@endcan
        @can('stock.update')<button type="button" data-product-quick="adjust"><i class="ri-scales-3-line"></i><span>Adjust Stock</span></button>@endcan
        @can('stock.transfer')<button type="button" data-product-quick="transfer"><i class="ri-arrow-left-right-line"></i><span>Transfer Stock</span></button>@endcan
    </nav>
</div>
@endsection
@push('scripts')<script src="{{ asset('backend/assets/js/modules/products-index.js') }}?v={{ filemtime(public_path('backend/assets/js/modules/products-index.js')) }}"></script>@endpush
