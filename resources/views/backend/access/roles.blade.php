@extends('backend.layouts.app')

@section('title', 'Roles & Permissions | Cholavin ERP')

@section('content')
<div id="roles-module" data-index-url="{{ route('admin.roles.index') }}" data-store-url="{{ route('admin.roles.store') }}">
    <div class="card erp-panel mb-3"><div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div><span class="erp-eyebrow">Access control</span><h4 class="mb-0">Roles & Permissions</h4></div>
        @can('roles.create')<button id="add-role" type="button" class="btn btn-primary"><i class="ri-shield-user-line me-1"></i>Add Role</button>@endcan
    </div></div>

    <div class="erp-module-kpis" data-module-summary-root>
        <article><i class="ri-shield-user-line"></i><span><small>Total Roles</small><strong data-summary-key="records" data-summary-format="number">{{ number_format($roleSummary['records']) }}</strong><em>Configured access profiles</em></span></article>
        <article><i class="ri-checkbox-circle-line"></i><span><small>Active Roles</small><strong data-summary-key="active" data-summary-format="number">{{ number_format($roleSummary['active']) }}</strong><em>Available for assignment</em></span></article>
        <article><i class="ri-team-line"></i><span><small>Assigned Users</small><strong data-summary-key="users" data-summary-format="number">{{ number_format($roleSummary['users']) }}</strong><em>Users covered by roles</em></span></article>
        <article><i class="ri-key-2-line"></i><span><small>Permissions in Use</small><strong data-summary-key="permissions" data-summary-format="number">{{ number_format($roleSummary['permissions']) }}</strong><em>Distinct granted permissions</em></span></article>
    </div>

    <div class="accordion mb-3" id="role-filter-accordion"><div class="accordion-item erp-panel">
        <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#role-filter-panel"><i class="ri-filter-3-line me-2"></i>Filters</button></h2>
        <div id="role-filter-panel" class="accordion-collapse collapse" data-bs-parent="#role-filter-accordion"><div class="accordion-body"><form id="role-filters" class="row g-3">
            <div class="col-lg-6"><label class="form-label" for="role-search">Search</label><input id="role-search" type="search" class="form-control" placeholder="Search roles"></div>
            <div class="col-lg-3"><label class="form-label" for="role-status-filter">Status</label><select id="role-status-filter" class="form-select"><option value="">All</option><option value="1">Active</option><option value="0">Inactive</option></select></div>
            <div class="col-lg-3 align-self-end"><button id="reset-role-filters" class="btn btn-secondary w-100" type="button">Reset</button></div>
        </form></div></div>
    </div></div>

    <div class="card erp-panel"><div class="card-body table-responsive"><table id="roles-table" class="table table-hover align-middle w-100"><thead><tr><th>S.No</th><th>Role</th><th>Type</th><th>Permissions</th><th>Users</th><th>Status</th><th class="text-end">Action</th></tr></thead></table></div></div>

    <div class="modal fade erp-form-modal" tabindex="-1" id="role-modal" aria-labelledby="role-modal-title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl modal-fullscreen-sm-down"><div class="modal-content">
        <div class="modal-header"><div><span class="erp-eyebrow">Access control</span><h5 id="role-modal-title" class="modal-title">Add Role</h5></div><button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button></div>
        <form id="role-form" action="{{ route('admin.roles.store') }}" method="POST" novalidate>@csrf<input id="role-method" type="hidden" name="_method" value="POST">
            <div class="modal-body">
                <div class="form-group mb-3"><label class="form-label" for="role-name">Role name <span class="text-danger">*</span></label><input id="role-name" name="name" class="form-control"></div>
                <input type="hidden" name="is_active" value="0"><div id="role-status-wrap" class="form-check form-switch mb-4 d-none"><input id="role-active" class="form-check-input" type="checkbox" name="is_active" value="1" checked><label class="form-check-label" for="role-active">Active role</label></div>
                <div class="d-flex align-items-center justify-content-between mb-2"><h6 class="mb-0">Module permissions</h6><button id="toggle-role-permissions" class="btn btn-sm btn-secondary" type="button">Select all</button></div>
                <div class="accordion" id="role-permissions">
                    @foreach($permissions as $module => $items)
                        <div class="accordion-item"><h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#role-module-{{ $loop->index }}">{{ str($module)->replace('-', ' ')->title() }}</button></h2>
                            <div id="role-module-{{ $loop->index }}" class="accordion-collapse collapse"><div class="accordion-body row g-2">@foreach($items as $permission)<div class="col-6"><label class="form-check"><input class="form-check-input role-permission" type="checkbox" name="permissions[]" value="{{ $permission->id }}"><span class="form-check-label">{{ $permission->label }}</span></label></div>@endforeach</div></div>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="modal-footer"><button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary" type="submit"><i class="ri-save-3-line me-1"></i>Save Role</button></div>
        </form>
        </div></div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('backend/assets/js/modules/access-roles.js') }}?v={{ filemtime(public_path('backend/assets/js/modules/access-roles.js')) }}"></script>
@endpush
