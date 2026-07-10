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

<form action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}" method="post" enctype="multipart/form-data">
    @csrf
    @if($product->exists) @method('PUT') @endif
    <div class="card product-form-card">
        <div class="card-body">
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label">Product Name <span class="text-danger">*</span></label><input type="text" name="name" class="form-control" value="{{ old('name', $product->name) }}" required></div>
                        <div class="col-md-6"><label class="form-label">Short Title</label><input type="text" name="sub_title" class="form-control" value="{{ old('sub_title', $product->sub_title) }}"></div>
                        <div class="col-md-4"><label class="form-label">Price</label><input type="number" step="0.01" name="price" class="form-control" value="{{ old('price', $product->price) }}"></div>
                        <div class="col-md-4"><label class="form-label">Unit</label><input type="text" name="unit" class="form-control" placeholder="25kg / Bag" value="{{ old('unit', $product->unit) }}"></div>
                        <div class="col-md-4"><label class="form-label">Sort Order</label><input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $product->sort_order ?? 0) }}"></div>
                        <div class="col-12"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="5">{{ old('description', $product->description) }}</textarea></div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <label class="form-label">Product Image</label>
                    <div class="mb-3"><img class="image-preview" id="imagePreview" src="{{ $product->exists ? $product->imageUrl() : asset('backend/assets/images/no-image.png') }}" alt="preview"></div>
                    <input type="file" name="image" class="form-control" accept="image/*" onchange="document.getElementById('imagePreview').src = window.URL.createObjectURL(this.files[0])">
                    <div class="form-check form-switch mt-4"><input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" @checked(old('is_active', $product->exists ? $product->is_active : true))><label class="form-check-label" for="is_active">Active on website</label></div>
                    <div class="form-check form-switch mt-2"><input class="form-check-input" type="checkbox" name="show_on_homepage" value="1" id="show_on_homepage" @checked(old('show_on_homepage', $product->show_on_homepage))><label class="form-check-label" for="show_on_homepage">Show on homepage/index</label></div>
                </div>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-end gap-2"><a href="{{ route('admin.products.index') }}" class="btn btn-light">Cancel</a><button class="btn btn-primary" type="submit"><i class="ri-save-line me-1"></i>Save Product</button></div>
    </div>
</form>
@endsection
