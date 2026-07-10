@extends('backend.layouts.app')

@section('title', 'Contact Enquiries | Cholavin ERP')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
<link rel="stylesheet" href="{{ asset('backend/assets/libs/sweetalert2/sweetalert2.min.css') }}">
@endpush

@section('content')
<div class="row"><div class="col-12"><div class="page-title-box d-sm-flex align-items-center justify-content-between"><h4 class="mb-sm-0">Contact Enquiries</h4><div class="page-title-right"><ol class="breadcrumb m-0"><li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li><li class="breadcrumb-item active">Enquiries</li></ol></div></div></div></div>

<div class="card">
    <div class="card-header"><h5 class="card-title mb-0">Website Enquiry Register</h5></div>
    <div class="card-body">
        <table id="enquiries-table" class="table table-hover align-middle dt-responsive nowrap w-100">
            <thead class="table-light"><tr><th>#</th><th>Name</th><th>Email</th><th>Phone</th><th>Requirement</th><th>Status</th><th>Reason</th><th>Date</th><th>Action</th></tr></thead>
        </table>
    </div>
</div>

<div class="modal fade" id="statusModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" id="statusForm">
            <div class="modal-header"><h5 class="modal-title">Update Enquiry Status</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <input type="hidden" id="statusEnquiryId">
                <div class="mb-3"><label class="form-label">Status</label><select class="form-select" id="enquiryStatus" required><option value="contacted">Contacted</option><option value="not_contacted">Not Contacted</option></select></div>
                <div class="mb-0"><label class="form-label">Reason / Note</label><textarea class="form-control" id="statusReason" rows="4" required placeholder="Add call note, customer response, or pending reason"></textarea></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Save Status</button></div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
<script src="{{ asset('backend/assets/libs/sweetalert2/sweetalert2.all.min.js') }}"></script>
<script>
$(function () {
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } });
    const modal = new bootstrap.Modal(document.getElementById('statusModal'));
    const table = $('#enquiries-table').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ordering: false,
        ajax: '{{ route('admin.enquiries.index') }}',
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex'},
            {data: 'name', name: 'name'},
            {data: 'email', name: 'email'},
            {data: 'phone', name: 'phone'},
            {data: 'service', name: 'service'},
            {data: 'status', name: 'status'},
            {data: 'reason', name: 'reason', defaultContent: '-'},
            {data: 'created_at', name: 'created_at'},
            {data: 'action', name: 'action'},
        ]
    });

    $(document).on('click', '.change-status', function () {
        $('#statusEnquiryId').val($(this).data('id'));
        $('#enquiryStatus').val($(this).data('status'));
        $('#statusReason').val($(this).data('reason') || '');
        modal.show();
    });

    $('#statusForm').on('submit', function (e) {
        e.preventDefault();
        const id = $('#statusEnquiryId').val();
        $.ajax({
            url: '{{ url('/admin/enquiries') }}/' + id + '/status',
            method: 'PATCH',
            data: { status: $('#enquiryStatus').val(), reason: $('#statusReason').val() }
        }).done(function (res) {
            modal.hide();
            Swal.fire('Saved', res.message, 'success');
            table.ajax.reload(null, false);
        }).fail(function (xhr) {
            Swal.fire('Validation failed', xhr.responseJSON?.message || 'Please check the status note.', 'error');
        });
    });
});
</script>
@endpush
