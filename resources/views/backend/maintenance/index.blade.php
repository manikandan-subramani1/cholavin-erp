@extends('backend.layouts.app')
@section('title', 'Backup & Data Tools | Cholavin ERP')
@section('content')
    <div class="page-title-box d-flex justify-content-between align-items-center">
        <div><span class="erp-eyebrow">Settings & Audit</span>
            <h4>Backup and Data Tools</h4>
        </div>
        @can('maintenance.create')
            <button id="create-backup" class="btn btn-primary"><i class="ri-save-3-line me-1"></i>Create Backup</button>
        @endcan
    </div>
    <div id="maintenance-module" data-index-url="{{ route('admin.maintenance.index') }}" data-backup-url="{{ route('admin.maintenance.backups.store') }}">
    <div class="alert alert-info">Backups are private compressed NDJSON snapshots. They are never placed in the public web
        directory.</div>
    <div class="accordion mb-3" id="backup-filter-accordion"><div class="accordion-item erp-panel"><h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#backup-filter-panel"><i class="ri-filter-3-line me-2"></i>Filters</button></h2><div id="backup-filter-panel" class="accordion-collapse collapse"><div class="accordion-body"><form id="backup-filters" class="row g-3"><div class="col-lg-9"><label class="form-label" for="backup-search">Search backups</label><input id="backup-search" class="form-control" type="search" placeholder="Backup filename"></div><div class="col-lg-3 align-self-end"><button id="reset-backup-filters" class="btn btn-secondary w-100" type="button">Reset</button></div></form></div></div></div></div>
    <div class="row g-3">
        <div class="col-xl-7">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">Database Backups</h5>
                </div>
                <div class="card-body table-responsive">
                    <table id="backups-table" class="table w-100">
                        <thead>
                            <tr>
                                <th>S.No</th>
                                <th>File</th>
                                <th>Size</th>
                                <th>Created</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-xl-5">
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0">CSV Export</h5>
                </div>
                <div class="card-body d-flex flex-wrap gap-2">
                    @can('maintenance.export')@foreach (['reference-masters' => 'Reference Masters', 'products' => 'Products', 'parties' => 'Parties'] as $key => $label)
                        <a class="btn btn-secondary"
                            href="{{ route('admin.maintenance.export', $key) }}">{{ $label }}</a>
                    @endforeach @endcan
                </div>
            </div>
            @can('maintenance.import')
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Controlled CSV Import</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">Export a dataset first and retain its headings. Imports update matching codes/SKUs
                            and never post stock.</p>
                        <form id="import-form" method="POST" action="{{ route('admin.maintenance.import') }}"
                            enctype="multipart/form-data">@csrf<div class="mb-3"><label
                                    class="form-label">Dataset</label><select name="dataset" class="form-select" required>
                                    <option value="reference-masters">Reference Masters</option>
                                    <option value="products">Products</option>
                                    <option value="parties">Parties</option>
                                </select></div>
                            <div class="mb-3"><label class="form-label">CSV File</label><input name="file" type="file"
                                    accept=".csv,text/csv" class="form-control" required></div><button class="btn btn-success"
                                type="submit">Validate and Import</button>
                        </form>
                    </div>
                </div>
            @endcan
        </div>
    </div>
    <div class="card mt-3">
        <div class="card-header">
            <h5 class="mb-0">Recent Laravel Error Log</h5>
        </div>
        <div class="card-body">
            <pre class="bg-dark text-light p-3 rounded overflow-auto" style="max-height:420px;white-space:pre-wrap">{{ $errorLog }}</pre>
        </div>
    </div>
    </div>
@endsection
@push('scripts')
    <script src="{{ asset('backend/assets/js/modules/maintenance.js') }}?v={{ filemtime(public_path('backend/assets/js/modules/maintenance.js')) }}"></script>
@endpush
