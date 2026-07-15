@extends('backend.layouts.app')

@php
    $module = json_decode('{"title":"Invoice Templates","group":"Administration","singular":"Invoice Template","slug":"invoice-templates","icon":"ri-layout-4-line"}', true);
    $fields = json_decode('[{"label":"Template Name","name":"name","placeholder":"Retail Invoice","type":"text"},{"label":"Template Type","name":"template_type","options":["Sales","Purchase","POS","Delivery"],"type":"select"},{"label":"Paper Size","name":"paper_size","options":["A4","A5","Thermal 80mm","Thermal 58mm"],"type":"select"},{"label":"Header Text","name":"header_text","placeholder":"Invoice header","type":"text"},{"label":"Footer Text","name":"footer_text","placeholder":"Terms and footer","type":"textarea"},{"label":"Status","name":"status","options":["Active","Inactive"],"type":"select"}]', true);
    $tableFields = array_slice($fields, 0, 4);
@endphp

@section('title', $module['title'] . ' | Cholavin ERP')

@section('content')
<div class="d-grid gap-3">
    <div class="card erp-panel mb-0">
        <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div><span class="erp-eyebrow">{{ $module['group'] }}</span><h4 class="mb-0">{{ $module['title'] }}</h4></div>
            <div class="d-flex gap-2">
                @can('invoice-templates.export')<button id="download-pdf" class="btn btn-secondary" type="button"><i class="ri-file-pdf-2-line me-1"></i>PDF</button>@endcan
                @can('invoice-templates.create')<a class="btn btn-primary" href="{{ route('admin.invoice-templates.create') }}"><i class="ri-add-line me-1"></i>Add {{ $module['singular'] }}</a>@endcan
            </div>
        </div>
    </div>

    <div class="accordion" id="module-filter-accordion">
        <div class="accordion-item erp-panel">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#module-filter-panel">
                    <i class="ri-filter-3-line me-2"></i>Filters
                </button>
            </h2>
            <div id="module-filter-panel" class="accordion-collapse collapse" data-bs-parent="#module-filter-accordion">
                <div class="accordion-body">
                    <form id="module-filters" class="row g-3">
                        <div class="col-lg-4"><label class="form-label" for="filter-search">Search</label><input id="filter-search" class="form-control" type="search" placeholder="Search records"></div>
                        <div class="col-lg-2"><label class="form-label" for="filter-status">Status</label><select id="filter-status" class="form-select"><option value="">All</option><option value="1">Active</option><option value="0">Inactive</option></select></div>
                        <div class="col-lg-2"><label class="form-label" for="filter-from">From date</label><input id="filter-from" class="form-control" type="date"></div>
                        <div class="col-lg-2"><label class="form-label" for="filter-to">To date</label><input id="filter-to" class="form-control" type="date"></div>
                        <div class="col-lg-2 align-self-end"><button id="reset-filters" class="btn btn-secondary w-100" type="button"><i class="ri-refresh-line me-1"></i>Reset</button></div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="card erp-panel mb-0">
        <div class="card-body table-responsive">
            <table id="module-table" class="table table-hover align-middle w-100">
                <thead><tr><th>S.No</th>@foreach($tableFields as $field)<th>{{ $field['label'] }}</th>@endforeach<th>Status</th><th class="text-end">Action</th></tr></thead>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    const table = initializeDataTable({
        selector: '#module-table',
        url: @json(route('admin.invoice-templates.index')),
        filters: () => ({
            status: $('#filter-status').val(),
            from_date: $('#filter-from').val(),
            to_date: $('#filter-to').val()
        }),
        columns: [
            {data: 'DT_RowIndex', orderable: false, searchable: false},
            @foreach($tableFields as $field)
            {data: @json($field['name']), orderable: false, searchable: false},
            @endforeach
            {data: 'record_status', orderable: false, searchable: false},
            {data: 'action', orderable: false, searchable: false}
        ],
        order: []
    });

    let searchTimer;
    $('#filter-search').on('input', function () {
        clearTimeout(searchTimer);
        const value = this.value;
        searchTimer = setTimeout(() => table.search(value).draw(), 250);
    });
    $('#filter-status,#filter-from,#filter-to').on('change', () => table.ajax.reload(null, false));
    $('#reset-filters').on('click', function () {
        document.getElementById('module-filters').reset();
        table.search('').ajax.reload(null, false);
    });
    $(document).on('click', '.delete-record', function () {
        const url = this.dataset.url;
        Swal.fire({title: 'Delete this record?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Delete'}).then(result => {
            if (!result.isConfirmed) return;
            $.ajax({url, type: 'DELETE'}).done(response => {
                toastr.success(response.message);
                table.ajax.reload(null, false);
            }).fail(xhr => handleAjaxError(xhr));
        });
    });
    $('#download-pdf').on('click', () => {
        const params = new URLSearchParams({
            search: $('#filter-search').val() || '',
            status: $('#filter-status').val() || '',
            from_date: $('#filter-from').val() || '',
            to_date: $('#filter-to').val() || ''
        });
        window.location.href = @json(route('admin.invoice-templates.pdf')) + '?' + params;
    });
});
</script>
@endpush