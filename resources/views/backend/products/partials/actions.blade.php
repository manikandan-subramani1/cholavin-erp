<div class="d-flex gap-2">
    <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-sm btn-soft-info"><i class="ri-pencil-line"></i></a>
    <button type="button" class="btn btn-sm btn-soft-danger delete-product" data-url="{{ route('admin.products.destroy', $product) }}"><i class="ri-delete-bin-line"></i></button>
</div>
