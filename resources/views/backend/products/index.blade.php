@extends('backend.layouts.app')

@section('title', 'Products | Cholavin ERP')

@push('styles')
<style>
    .erp-metric { border: 0; border-radius: 8px; box-shadow: 0 8px 24px rgba(15, 23, 42, .06); }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Products</h4>
            @can('create', App\Models\Product::class)<div class="page-title-right"><a href="{{ route('admin.products.create') }}" class="btn btn-primary"><i class="ri-add-line me-1"></i>Add Product</a></div>@endcan
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4"><div class="card erp-metric"><div class="card-body"><span class="text-muted">Total Products</span><h3>{{ \App\Models\Product::count() }}</h3></div></div></div>
    <div class="col-md-4"><div class="card erp-metric"><div class="card-body"><span class="text-muted">Active</span><h3>{{ \App\Models\Product::where('is_active', true)->count() }}</h3></div></div></div>
    <div class="col-md-4"><div class="card erp-metric"><div class="card-body"><span class="text-muted">Homepage</span><h3>{{ \App\Models\Product::where('show_on_homepage', true)->count() }}</h3></div></div></div>
</div>

<div class="card">
    <div class="card-header"><h5 class="card-title mb-0">Product Register</h5></div>
    <div class="card-body">
        <table id="products-table" class="table table-hover align-middle dt-responsive nowrap w-100">
            <thead class="table-light">
                <tr>
                    <th>#</th><th>Image</th><th>Name</th><th>Price</th><th>Unit</th><th>Status</th><th>Homepage</th><th>Sort</th><th>Action</th>
                </tr>
            </thead>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } });

    const table = $('#products-table').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ordering: false,
        ajax: '{{ route('admin.products.index') }}',
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex'},
            {data: 'image_preview', name: 'image_preview'},
            {data: 'name', name: 'name'},
            {data: 'price', name: 'price'},
            {data: 'unit', name: 'unit'},
            {data: 'is_active', name: 'is_active'},
            {data: 'show_on_homepage', name: 'show_on_homepage'},
            {data: 'sort_order', name: 'sort_order'},
            {data: 'action', name: 'action'},
        ]
    });

    $(document).on('click', '.delete-product', function () {
        const url = $(this).data('url');
        Swal.fire({ title: 'Delete product?', text: 'This will remove the product and image.', icon: 'warning', showCancelButton: true, confirmButtonText: 'Delete' })
            .then((result) => {
                if (! result.isConfirmed) return;
                $.ajax({ url, type: 'DELETE' }).done(function (res) {
                    Swal.fire('Deleted', res.message, 'success');
                    table.ajax.reload(null, false);
                });
            });
    });
});
</script>
@endpush
