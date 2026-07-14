@extends('backend.layouts.app')

@section('title', 'Session Monitoring | Cholavin ERP')

@push('styles')
@endpush

@section('content')
<div class="page-title-box d-flex flex-wrap justify-content-between align-items-center gap-3">
    <div><span class="erp-eyebrow">Access control</span><h4 class="mb-0">Session Monitoring</h4></div>
</div>

<div class="card erp-panel mb-3">
    <div class="card-body">
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
    </div>
</div>

<div class="card erp-panel">
    <div class="card-header"><h5 class="mb-0">Authenticated sessions</h5></div>
    <div class="card-body table-responsive">
        <table id="sessions-table" class="table table-hover align-middle context-data-table w-100">
            <thead><tr><th>S.No</th><th>User</th><th>Role</th><th>IP address</th><th>Device / Browser</th><th>Shop</th><th>Godown</th><th>Last activity</th><th>Status</th><th>Action</th></tr></thead>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    $.ajaxSetup({headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content}});
    const table = initializeDataTable({
        selector: '#sessions-table',
        url: '{{ route('admin.sessions.index') }}',
        order: [[7, 'desc']],
        filters: function () { return {user_id: $('#session-user-filter').val(), status: $('#session-status-filter').val()}; },
        columns: [
            {data: 'DT_RowIndex', orderable: false, searchable: false},
            {data: 'user_name', name: 'users.name'}, {data: 'role_name', name: 'roles.name'},
            {data: 'ip_address', name: 'sessions.ip_address'}, {data: 'user_agent', name: 'sessions.user_agent'},
            {data: 'shop_name', name: 'shops.name'}, {data: 'godown_name', name: 'godowns.name'},
            {data: 'last_activity', name: 'sessions.last_activity'}, {data: 'status', orderable: false, searchable: false},
            {data: 'action', orderable: false, searchable: false}
        ]
    });

    $('#session-user-filter, #session-status-filter').on('change', function () { table.ajax.reload(); });
    $('#reset-session-filters').on('click', function () { document.getElementById('session-filters').reset(); table.ajax.reload(); });
    $(document).on('click', '.revoke-session', function () {
        const url = this.dataset.url;
        Swal.fire({title: 'Force logout this session?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Force logout'})
            .then(function (result) {
                if (!result.isConfirmed) return;
                $.ajax({url: url, type: 'DELETE'})
                    .done(function (response) { Swal.fire('Session revoked', response.message, 'success'); table.ajax.reload(null, false); })
                    .fail(function (xhr) {
                        Swal.fire('Unable to revoke session', xhr.responseJSON?.message || 'Please try again.', 'error');
                    });
            });
    });
});
</script>
@endpush
