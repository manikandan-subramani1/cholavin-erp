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
    <div class="alert alert-info">Backups are private compressed NDJSON snapshots. They are never placed in the public web
        directory.</div>
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
@endsection
@push('scripts')
    <script>
        $(function() {
            const token = document.querySelector('meta[name="csrf-token"]').content;
            const table = initializeDataTable({
                selector: '#backups-table',
                url: @json(route('admin.maintenance.index')),
                columns: [{
                    data: 'DT_RowIndex',
                    orderable: false,
                    searchable: false
                }, {
                    data: 'name'
                }, {
                    data: 'size'
                }, {
                    data: 'created_at'
                }, {
                    data: 'action',
                    orderable: false,
                    searchable: false
                }],
                order: [
                    [1, 'desc']
                ]
            });
            $('#create-backup').on('click', function() {
                $.ajax({
                    url: @json(route('admin.maintenance.backups.store')),
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': token
                    }
                }).done(response => {
                    table.ajax.reload(null, false);
                    if (window.toastr) toastr.success(response.message);
                }).fail(xhr => handleAjaxError(xhr));
            });
            $('#import-form').validate({
                submitHandler: form => submitFormUsingAjax(form, {
                    table: '#backups-table'
                })
            });
        });
    </script>
@endpush
