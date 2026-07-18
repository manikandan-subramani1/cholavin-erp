@extends('backend.layouts.app')

@section('title', 'Session Monitoring | Cholavin ERP')

@push('styles')
@endpush

@section('content')
<div class="page-title-box d-flex flex-wrap justify-content-between align-items-center gap-3">
    <div><span class="erp-eyebrow">Access control</span><h4 class="mb-0">Session Monitoring</h4></div>
</div>

<div id="sessions-module" data-index-url="{{ route('admin.sessions.index') }}">
<div class="accordion mb-3" id="session-filter-accordion">
    <div class="accordion-item erp-panel">
        <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#session-filter-panel"><i class="ri-filter-3-line me-2"></i>Filters</button></h2>
        <div id="session-filter-panel" class="accordion-collapse collapse" data-bs-parent="#session-filter-accordion"><div class="accordion-body">
        <form id="session-filters" class="row g-3">
            <div class="col-md-4">
                <label for="session-user-filter" class="form-label">User</label>
                <select id="session-user-filter" class="form-select">
                    <option value="">All users</option>
                    @foreach ($users as $user)<option value="{{ $user->id }}">{{ $user->name }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="session-status-filter" class="form-label">Status</label>
                <select id="session-status-filter" class="form-select">
                    <option value="">All statuses</option><option value="active">Active</option><option value="expired">Expired</option>
                </select>
            </div>
            <div class="col-md-2 align-self-end"><button id="reset-session-filters" type="button" class="btn btn-secondary w-100">Reset</button></div>
        </form>
        </div></div>
    </div>
</div>

<div class="card erp-panel">
    <div class="card-header"><h5 class="mb-0">Authenticated sessions</h5></div>
    <div class="card-body table-responsive">
        <table id="sessions-table" class="table table-hover align-middle context-data-table w-100">
            <thead><tr><th>S.No</th><th>User</th><th>Role</th><th>IP address</th><th>Device / Browser</th><th>Shop</th><th>Godown</th><th>Financial year</th><th>Last activity</th><th>Status</th><th>Action</th></tr></thead>
        </table>
    </div>
</div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('backend/assets/js/modules/access-sessions.js') }}?v={{ filemtime(public_path('backend/assets/js/modules/access-sessions.js')) }}"></script>
@endpush