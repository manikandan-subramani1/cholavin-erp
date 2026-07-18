@extends('backend.layouts.app')

@php
    $module = json_decode('{"title":"Tax Rates","group":"Items","singular":"Tax Rate","slug":"tax-rates","icon":"ri-percent-line"}', true);
    $fields = json_decode('[{"label":"Tax Name","name":"name","placeholder":"GST 5%","type":"text"},{"label":"Rate %","name":"rate","placeholder":"5","type":"number"},{"label":"Tax Type","name":"tax_type","options":["GST","IGST","CGST + SGST","CESS"],"type":"select"},{"label":"Effective From","name":"effective_from","type":"date"},{"label":"Status","name":"status","options":["Active","Inactive"],"type":"select"},{"label":"Notes","name":"notes","placeholder":"Tax applicability","type":"textarea"}]', true);
    $tableFields = array_slice($fields, 0, 4);
@endphp

@section('title', $module['title'] . ' | Cholavin ERP')

@section('content')
<div id="reference-master-module" class="d-grid gap-3" data-index-url="{{ route('admin.'.$module['slug'].'.index') }}" data-pdf-url="{{ route('admin.'.$module['slug'].'.pdf') }}">
    <div class="card erp-panel mb-0">
        <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div><span class="erp-eyebrow">{{ $module['group'] }}</span><h4 class="mb-0">{{ $module['title'] }}</h4></div>
            <div class="d-flex gap-2">
                @can('tax-rates.export')<button id="download-pdf" class="btn btn-secondary" type="button"><i class="ri-file-pdf-2-line me-1"></i>PDF</button>@endcan
                @can('tax-rates.create')<a class="btn btn-primary" href="{{ route('admin.tax-rates.create') }}"><i class="ri-add-line me-1"></i>Add {{ $module['singular'] }}</a>@endcan
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
                <thead><tr><th data-column="DT_RowIndex">S.No</th>@foreach($tableFields as $field)<th data-column="{{ $field['name'] }}">{{ $field['label'] }}</th>@endforeach<th data-column="record_status">Status</th><th data-column="action" class="text-end">Action</th></tr></thead>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('backend/assets/js/modules/reference-master-index.js') }}?v={{ filemtime(public_path('backend/assets/js/modules/reference-master-index.js')) }}"></script>
@endpush
