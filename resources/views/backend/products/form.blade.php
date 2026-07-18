@extends('backend.layouts.app')

@section('title', ($product->exists ? 'Edit' : 'Add') . ' Product | Cholavin ERP')

@push('styles')
<style>
    .product-form-card { border: 0; border-radius: 8px; box-shadow: 0 12px 30px rgba(15, 23, 42, .08); }
    .image-preview { width: 132px; height: 132px; object-fit: cover; border-radius: 8px; border: 1px solid #e5e7eb; }
</style>
@endpush

@section('content')
<div class="row"><div class="col-12"><div class="page-title-box d-sm-flex align-items-center justify-content-between"><h4 class="mb-sm-0">{{ $product->exists ? 'Edit' : 'Add' }} Product</h4><a href="{{ route('admin.products.index') }}" class="btn btn-light"><i class="ri-arrow-left-line me-1"></i>Back</a></div></div></div>

<form id="product-form" data-index-url="{{ route('admin.products.index') }}" action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}" method="post" enctype="multipart/form-data">
    @csrf
    @if($product->exists) @method('PUT') @endif
    <div class="card product-form-card">
        <div class="card-body">
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label">Product Name <span class="text-danger">*</span></label><input type="text" name="name" class="form-control" value="{{ old('name', $product->name) }}" required></div>
                        <div class="col-md-6"><label class="form-label">Short Title</label><input type="text" name="sub_title" class="form-control" value="{{ old('sub_title', $product->sub_title) }}"></div>
                        <div class="col-md-3"><label class="form-label">SKU</label><input name="sku" class="form-control" value="{{ old('sku',$product->sku) }}"></div><div class="col-md-3"><label class="form-label">Barcode</label><input name="barcode" class="form-control" value="{{ old('barcode',$product->barcode) }}"></div>
                        <div class="col-md-3"><label class="form-label">Category</label><select name="category_id" class="form-select"><option value="">None</option>@foreach($categories as $x)<option value="{{ $x->id }}" @selected(old('category_id',$product->category_id)==$x->id)>{{ $x->name }}</option>@endforeach</select></div><div class="col-md-3"><label class="form-label">Brand</label><select name="brand_id" class="form-select"><option value="">None</option>@foreach($brands as $x)<option value="{{ $x->id }}" @selected(old('brand_id',$product->brand_id)==$x->id)>{{ $x->name }}</option>@endforeach</select></div>
                        <div class="col-md-3"><label class="form-label">Variant</label><select name="variant_id" class="form-select"><option value="">None</option>@foreach($variants as $x)<option value="{{ $x->id }}" @selected(old('variant_id',$product->variant_id)==$x->id)>{{ $x->name }}</option>@endforeach</select></div><div class="col-md-3"><label class="form-label">Grade</label><select name="grade_id" class="form-select"><option value="">None</option>@foreach($grades as $x)<option value="{{ $x->id }}" @selected(old('grade_id',$product->grade_id)==$x->id)>{{ $x->name }}</option>@endforeach</select></div>
                        <div class="col-md-4"><label class="form-label">Price</label><input type="number" step="0.01" name="price" class="form-control" value="{{ old('price', $product->price) }}"></div>
                        <div class="col-md-4"><label class="form-label">Purchase Price</label><input type="number" step="0.01" name="purchase_price" class="form-control" value="{{ old('purchase_price',$product->purchase_price) }}"></div><div class="col-md-4"><label class="form-label">Sale Price</label><input type="number" step="0.01" name="sale_price" class="form-control" value="{{ old('sale_price',$product->sale_price) }}"></div>
                        <div class="col-md-4"><label class="form-label">Unit</label><input type="text" name="unit" class="form-control" placeholder="25kg / Bag" value="{{ old('unit', $product->unit) }}"></div>
                        <div class="col-md-4"><label class="form-label">Unit Master</label><select name="unit_id" class="form-select"><option value="">None</option>@foreach($units as $x)<option value="{{ $x->id }}" @selected(old('unit_id',$product->unit_id)==$x->id)>{{ $x->name }}</option>@endforeach</select></div><div class="col-md-4"><label class="form-label">Tax Rate</label><select name="tax_rate_id" class="form-select"><option value="">None</option>@foreach($taxRates as $x)<option value="{{ $x->id }}" @selected(old('tax_rate_id',$product->tax_rate_id)==$x->id)>{{ $x->name }}</option>@endforeach</select></div>
                        <div class="col-md-4"><label class="form-label">HSN / SAC</label><select name="hsn_sac_id" class="form-select"><option value="">None</option>@foreach($hsnCodes as $x)<option value="{{ $x->id }}" @selected(old('hsn_sac_id',$product->hsn_sac_id)==$x->id)>{{ $x->name }}</option>@endforeach</select></div><div class="col-md-4"><label class="form-label">Opening Stock</label><input @if($product->exists) disabled @else name="opening_stock" @endif type="number" min="0" step="0.001" class="form-control" value="{{ old('opening_stock',$product->opening_stock) }}"><small class="text-muted">Opening stock is posted once to the active godown. Use stock adjustments afterward.</small></div><div class="col-md-4"><label class="form-label">Reorder Level</label><input name="reorder_level" type="number" min="0" step="0.001" class="form-control" value="{{ old('reorder_level',$product->reorder_level) }}"></div>
                        <div class="col-md-4"><label class="form-label">Sort Order</label><input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $product->sort_order ?? 0) }}"></div>
                        <div class="col-12"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="5">{{ old('description', $product->description) }}</textarea></div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <label class="form-label">Product Image</label>
                    <div class="mb-3"><img class="image-preview" id="imagePreview" src="{{ $product->exists ? $product->imageUrl() : asset('backend/assets/images/no-image.png') }}" alt="preview"></div>
                    <input id="product-image" type="file" name="image" class="form-control" accept="image/jpeg,image/png,image/webp">
                    <div class="form-check form-switch mt-4"><input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" @checked(old('is_active', $product->exists ? $product->is_active : true))><label class="form-check-label" for="is_active">Active on website</label></div>
                    <div class="form-check form-switch mt-2"><input class="form-check-input" type="checkbox" name="show_on_homepage" value="1" id="show_on_homepage" @checked(old('show_on_homepage', $product->show_on_homepage))><label class="form-check-label" for="show_on_homepage">Show on homepage/index</label></div>
                </div>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-end gap-2"><a href="{{ route('admin.products.index') }}" class="btn btn-light">Cancel</a><button class="btn btn-primary" type="submit"><i class="ri-save-line me-1"></i>Save Product</button></div>
    </div>
</form>
@endsection
@push('scripts')
<script src="{{ asset('backend/assets/js/modules/products-form.js') }}?v={{ filemtime(public_path('backend/assets/js/modules/products-form.js')) }}"></script>
@endpush
