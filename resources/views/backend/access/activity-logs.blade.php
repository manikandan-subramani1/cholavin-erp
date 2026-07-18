@extends('backend.layouts.app')

@section('title', 'Activity Logs | Cholavin ERP')

@section('content')
<div id="activity-log-module" data-index-url="{{ route('admin.activity-logs.index') }}">
    <div class="card erp-panel mb-3"><div class="card-body"><span class="erp-eyebrow">Audit & security</span><h4 class="mb-0">Login & Activity Logs</h4></div></div>
    <div class="accordion mb-3" id="log-filter-accordion"><div class="accordion-item erp-panel"><h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#log-filter-panel"><i class="ri-filter-3-line me-2"></i>Filters</button></h2>
        <div id="log-filter-panel" class="accordion-collapse collapse" data-bs-parent="#log-filter-accordion"><div class="accordion-body"><form id="log-filters" class="row g-3">
            <div class="col-lg-3"><label class="form-label" for="log-user-filter">User</label><select id="log-user-filter" class="form-select"><option value="">All users</option>@foreach($users as $user)<option value="{{ $user->id }}">{{ $user->name }}</option>@endforeach</select></div>
            <div class="col-lg-2"><label class="form-label" for="log-event-filter">Event</label><select id="log-event-filter" class="form-select"><option value="">All events</option>@foreach($events as $event)<option value="{{ $event }}">{{ $event }}</option>@endforeach</select></div>
            <div class="col-lg-2"><label class="form-label" for="log-module-filter">Module</label><select id="log-module-filter" class="form-select"><option value="">All modules</option>@foreach($modules as $module)<option value="{{ $module }}">{{ $module }}</option>@endforeach</select></div>
            <div class="col-lg-2"><label class="form-label" for="log-from-filter">From</label><input id="log-from-filter" type="date" class="form-control"></div>
            <div class="col-lg-2"><label class="form-label" for="log-to-filter">To</label><input id="log-to-filter" type="date" class="form-control"></div>
            <div class="col-lg-1 align-self-end"><button id="reset-log-filters" class="btn btn-secondary w-100" type="button"><i class="ri-refresh-line"></i></button></div>
        </form></div></div>
    </div></div>
    <div class="card erp-panel"><div class="card-body table-responsive"><table id="activity-log-table" class="table table-hover align-middle w-100"><thead><tr><th>S.No</th><th>Time</th><th>User</th><th>Event</th><th>Module</th><th>Request</th><th>IP</th><th>Details</th></tr></thead></table></div></div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('backend/assets/js/modules/activity-logs.js') }}?v={{ filemtime(public_path('backend/assets/js/modules/activity-logs.js')) }}"></script>
@endpush
