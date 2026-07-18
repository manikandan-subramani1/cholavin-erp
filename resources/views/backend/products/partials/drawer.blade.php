<div class="product-drawer" data-product-id="{{ $product->id }}"
    data-tab-url="{{ route('admin.products.tab', [$product, '__TAB__']) }}"
    data-price-url="{{ route('admin.products.prices', $product) }}"
    data-image-url="{{ route('admin.products.images', $product) }}"
    data-barcode-url="{{ route('admin.products.barcode', $product) }}"
    data-opening-url="{{ route('admin.products.opening-stock', $product) }}"
    data-duplicate-url="{{ route('admin.products.duplicate', $product) }}">
    <header class="product-drawer-hero">
        <img src="{{ $product->imageUrl() }}" alt="{{ $product->name }}">
        <div><span class="erp-eyebrow">Item / Product Master</span><h3>{{ $product->name }}</h3><p>{{ $product->sku ?: 'No item code' }} · {{ $product->category?->name ?: 'Uncategorised' }}</p>
            <span class="badge {{ $product->is_active ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}">{{ $product->is_active ? 'Active' : 'Inactive' }}</span>
        </div>
    </header>
    <div class="product-drawer-kpis">
        <article><small>Retail</small><strong>&#8377;{{ number_format((float) ($product->retail_price ?: $product->sale_price ?: $product->price), 2) }}</strong></article>
        <article><small>Wholesale</small><strong>&#8377;{{ number_format((float) $product->wholesale_price, 2) }}</strong></article>
        <article><small>Scoped stock</small><strong>{{ number_format((float) ($stock->quantity ?? 0), 3) }}</strong></article>
        <article><small>Stock value</small><strong>&#8377;{{ number_format((float) ($stock->value ?? 0), 2) }}</strong></article>
    </div>
    <div class="product-drawer-actions">
        @can('update', $product)<a class="btn btn-primary" href="{{ route('admin.products.edit', $product) }}"><i class="ri-pencil-line"></i> Edit Item</a>
        <button class="btn btn-secondary" type="button" data-drawer-action="price"><i class="ri-price-tag-3-line"></i> Update Price</button>
        <button class="btn btn-secondary" type="button" data-drawer-action="image"><i class="ri-image-add-line"></i> Add Image</button>@endcan
        @can('stock.update')<button class="btn btn-secondary" type="button" data-drawer-action="opening"><i class="ri-archive-drawer-line"></i> Opening Stock</button>@endcan
    </div>
    <nav class="product-drawer-tabs" aria-label="Item detail tabs">
        @foreach(['overview'=>'Overview','pricing'=>'Pricing','stock'=>'Stock','stock-history'=>'Stock History','purchase-history'=>'Purchase History','sales-history'=>'Sales History','images'=>'Images','barcode'=>'Barcode','documents'=>'Documents','audit'=>'Audit'] as $key=>$label)
            <button class="{{ $loop->first ? 'is-active' : '' }}" type="button" data-product-tab="{{ $key }}">{{ $label }}</button>
        @endforeach
    </nav>
    <div class="product-tab-content" data-product-tab-content><div class="product-tab-skeleton"><span></span><span></span><span></span></div></div>
</div>
