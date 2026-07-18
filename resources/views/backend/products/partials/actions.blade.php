<div class="d-flex gap-2">
    <button type="button" class="btn btn-sm btn-soft-primary" data-product-action="view" title="View item"><i class="ri-eye-line"></i></button>
    @can('update', $product)
    <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-sm btn-soft-info"><i class="ri-pencil-line"></i></a>
    @endcan
    @can('delete', $product)
    <button type="button" class="btn btn-sm btn-soft-danger delete-product" data-url="{{ route('admin.products.destroy', $product) }}"><i class="ri-delete-bin-line"></i></button>
    @endcan
</div>
