@extends('backend.layouts.app')

@section('title', 'Contact Enquiries | Cholavin ERP')

@push('styles')
@endpush

@section('content')
<div id="enquiries-module" data-index-url="{{ route('admin.enquiries.index') }}" data-status-base-url="{{ url('/admin/enquiries') }}">
<div class="row"><div class="col-12"><div class="page-title-box d-sm-flex align-items-center justify-content-between"><h4 class="mb-sm-0">Contact Enquiries</h4><div class="page-title-right"><ol class="breadcrumb m-0"><li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li><li class="breadcrumb-item active">Enquiries</li></ol></div></div></div></div>

<div class="accordion mb-3" id="enquiry-filter-accordion"><div class="accordion-item erp-panel">
    <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#enquiry-filter-panel"><i class="ri-filter-3-line me-2"></i>Filters</button></h2>
    <div id="enquiry-filter-panel" class="accordion-collapse collapse" data-bs-parent="#enquiry-filter-accordion"><div class="accordion-body">
        <form id="enquiry-filters" class="row g-3">
            <div class="col-md-3"><label class="form-label" for="enquiry-status-filter">Status</label><select id="enquiry-status-filter" class="form-select"><option value="">All</option><option value="contacted">Contacted</option><option value="not_contacted">Not contacted</option></select></div>
            <div class="col-md-3"><label class="form-label" for="enquiry-from-filter">From date</label><input id="enquiry-from-filter" type="date" class="form-control"></div>
            <div class="col-md-3"><label class="form-label" for="enquiry-to-filter">To date</label><input id="enquiry-to-filter" type="date" class="form-control"></div>
            <div class="col-md-3 align-self-end"><button id="reset-enquiry-filters" type="button" class="btn btn-secondary w-100">Reset</button></div>
        </form>
    </div></div>
</div></div>

<div class="card">
    <div class="card-header"><h5 class="card-title mb-0">Website Enquiry Register</h5></div>
    <div class="card-body">
        <table id="enquiries-table" class="table table-hover align-middle dt-responsive nowrap w-100">
            <thead class="table-light"><tr><th>#</th><th>Name</th><th>Email</th><th>Phone</th><th>Requirement</th><th>Status</th><th>Reason</th><th>Date</th><th>Action</th></tr></thead>
        </table>
    </div>
</div>

<div class="modal fade" id="statusModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-fullscreen-sm-down">
        <form class="modal-content" id="statusForm">
            <div class="modal-header"><h5 class="modal-title">Update Enquiry Status</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <input type="hidden" id="statusEnquiryId">
                <div class="mb-3 form-group"><label class="form-label" for="enquiryStatus">Status</label><select class="form-select" id="enquiryStatus" name="status" required><option value="contacted">Contacted</option><option value="not_contacted">Not Contacted</option></select></div>
                <div class="mb-0 form-group"><label class="form-label" for="statusReason">Reason / Note</label><textarea class="form-control" id="statusReason" name="reason" rows="4" required maxlength="1000" placeholder="Add call note, customer response, or pending reason"></textarea></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Save Status</button></div>
        </form>
    </div>
</div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('backend/assets/js/modules/enquiries.js') }}?v={{ filemtime(public_path('backend/assets/js/modules/enquiries.js')) }}"></script>
@endpush
