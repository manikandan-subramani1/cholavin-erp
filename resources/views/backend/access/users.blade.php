@extends('backend.layouts.app')

@section('title', 'Users & Access | Cholavin ERP')

@section('content')
<div id="users-module"
    data-index-url="{{ route('admin.users.index') }}"
    data-store-url="{{ route('admin.users.store') }}" data-roles-url="{{ route('admin.roles.index') }}" data-locations-url="{{ route('admin.locations.index') }}" data-activity-url="{{ route('admin.activity-logs.index') }}">
    <div class="card erp-panel mb-3">
        <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div><span class="erp-eyebrow">Access control</span><h4 class="mb-1">Users & Access</h4><p class="text-muted mb-0">Manage login accounts, role assignment, shop/godown scope, financial-year access, and status.</p></div>
            @can('users.create')
                <button id="add-user" class="btn btn-primary" type="button"><i class="ri-user-add-line me-1"></i>Add User</button>
            @endcan
        </div>
    </div>

    <div class="erp-module-kpis" data-module-summary-root>
        <article><i class="ri-team-line"></i><span><small>Total Users</small><strong data-summary-key="records" data-summary-format="number">{{ number_format($userSummary['records']) }}</strong><em>All ERP accounts</em></span></article>
        <article><i class="ri-user-follow-line"></i><span><small>Active Users</small><strong data-summary-key="active" data-summary-format="number">{{ number_format($userSummary['active']) }}</strong><em>Allowed to sign in</em></span></article>
        <article><i class="ri-user-unfollow-line"></i><span><small>Inactive Users</small><strong data-summary-key="inactive" data-summary-format="number">{{ number_format($userSummary['inactive']) }}</strong><em>Access disabled</em></span></article>
        <article><i class="ri-shield-user-line"></i><span><small>Assigned Roles</small><strong data-summary-key="roles" data-summary-format="number">{{ number_format($userSummary['roles']) }}</strong><em>Roles currently in use</em></span></article>
    </div>

    <div class="accordion mb-3" id="user-filter-accordion">
        <div class="accordion-item erp-panel">
            <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#user-filter-panel"><i class="ri-filter-3-line me-2"></i>Filters</button></h2>
            <div id="user-filter-panel" class="accordion-collapse collapse" data-bs-parent="#user-filter-accordion"><div class="accordion-body">
                <form id="user-filters" class="row g-3">
                    <div class="col-lg-3"><label class="form-label" for="user-search">Search</label><input id="user-search" class="form-control" type="search" placeholder="Name, username or email"></div>
                    <div class="col-lg-3"><label class="form-label" for="user-role-filter">Role</label><select id="user-role-filter" class="form-select"><option value="">All roles</option>@foreach($roles as $role)<option value="{{ $role->id }}">{{ $role->name }}</option>@endforeach</select></div>
                    <div class="col-lg-2"><label class="form-label" for="user-shop-filter">Shop</label><select id="user-shop-filter" class="form-select"><option value="">All shops</option>@foreach($shops as $shop)<option value="{{ $shop->id }}">{{ $shop->name }}</option>@endforeach</select></div>
                    <div class="col-lg-2"><label class="form-label" for="user-status-filter">Status</label><select id="user-status-filter" class="form-select"><option value="">All</option><option value="1">Active</option><option value="0">Inactive</option></select></div>
                    <div class="col-lg-2 align-self-end"><button id="reset-user-filters" class="btn btn-secondary w-100" type="button">Reset</button></div>
                </form>
            </div></div>
        </div>
    </div>

    <div class="card erp-panel"><div class="card-body table-responsive">
        <table id="users-table" class="table table-hover align-middle w-100"><thead><tr><th>S.No</th><th>User</th><th>Role</th><th>Assigned locations</th><th>Status</th><th class="text-end">Action</th></tr></thead></table>
    </div></div>

    <div class="modal fade erp-form-modal" tabindex="-1" id="user-modal" aria-labelledby="user-modal-title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl modal-fullscreen-sm-down"><div class="modal-content">
        <div class="modal-header"><div><span class="erp-eyebrow">Access control</span><h5 id="user-modal-title" class="modal-title">Add User</h5></div><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
        <form id="user-form" method="POST" action="{{ route('admin.users.store') }}" novalidate>
            @csrf <input id="user-method" type="hidden" name="_method" value="POST">
            <div class="modal-body"><div class="row g-3">
                <div class="col-md-6 form-group"><label class="form-label" for="user-name">Name <span class="text-danger">*</span></label><input id="user-name" name="name" class="form-control"></div>
                <div class="col-md-6 form-group"><label class="form-label" for="user-username">Username <span class="text-danger">*</span></label><input id="user-username" name="username" class="form-control"></div>
                <div class="col-md-6 form-group"><label class="form-label" for="user-email">Email <span class="text-danger">*</span></label><input id="user-email" name="email" type="email" class="form-control"><small class="text-muted">The account invitation and secure password link will be sent here.</small></div>
                <div class="col-md-6 form-group"><label class="form-label" for="user-mobile">Mobile</label><input id="user-mobile" name="mobile" class="form-control"></div>
                <div class="col-md-6 form-group"><label class="form-label" for="user-password">Temporary password <span class="create-required text-danger">*</span></label><input id="user-password" name="password" type="password" class="form-control" autocomplete="new-password"><small class="create-password-note text-muted">This password is never included in email; the user receives a secure setup link.</small><small class="edit-password-note text-muted d-none">Leave blank to keep the current password.</small></div>
                <div class="col-md-6 form-group"><label class="form-label" for="user-role">Role <span class="text-danger">*</span></label><select id="user-role" name="role_id" class="form-select"><option value="">Select role</option>@foreach($roles as $role)<option value="{{ $role->id }}">{{ $role->name }}</option>@endforeach</select></div>
                <div class="col-12 form-group"><label class="form-label" for="user-shops">Shops</label><select id="user-shops" name="shops[]" class="form-select" multiple>@foreach($shops as $shop)<option value="{{ $shop->id }}">{{ $shop->name }}</option>@endforeach</select></div>
                <div class="col-12 form-group"><label class="form-label" for="user-godowns">Godowns</label><select id="user-godowns" name="godowns[]" class="form-select" multiple>@foreach($godowns as $godown)<option value="{{ $godown->id }}" data-shop-ids="{{ implode(',', $godown->shops->modelKeys()) }}">{{ $godown->name }}</option>@endforeach</select><small class="text-muted">Only godowns linked to the selected shops can be assigned.</small></div>
                <div class="col-12 form-group"><label class="form-label" for="user-financial-years">Financial Years</label><select id="user-financial-years" name="financial_years[]" class="form-select" multiple>@foreach($financialYears as $financialYear)<option value="{{ $financialYear->id }}">{{ $financialYear->name }} ({{ $financialYear->code }})</option>@endforeach</select><small class="text-muted">The first selected year becomes the user's default financial year.</small></div>
                <div class="col-12"><input type="hidden" name="is_active" value="0"><div class="form-check form-switch"><input id="user-active" class="form-check-input" type="checkbox" name="is_active" value="1" checked><label class="form-check-label" for="user-active">Active account</label></div></div>
            </div></div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary"><i class="ri-save-3-line me-1"></i>Save User</button></div>
        </form>
        </div></div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('backend/assets/js/modules/access-users.js') }}?v={{ filemtime(public_path('backend/assets/js/modules/access-users.js')) }}"></script>
@endpush
