@extends('backend.layouts.app')
@section('title', $title.' | Cholavin ERP')
@push('styles')
@endpush
@section('content')
<div class="party-page-toolbar">
    <div>
        <span class="erp-eyebrow">Party Management</span>
        <div class="d-flex align-items-center gap-2"><h4 class="mb-0">{{ $title }}</h4><span class="party-live-context">{{ $headerShops->firstWhere('id', $activeShopId)?->name ?? 'Select shop' }}</span></div>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <div class="btn-group party-type-switch" role="group">
            <a href="{{ route('admin.parties.index','customers') }}" class="btn {{ $partyType === 'customers' ? 'btn-primary' : 'btn-secondary' }}">Customers</a>
            <a href="{{ route('admin.parties.index','suppliers') }}" class="btn {{ $partyType === 'suppliers' ? 'btn-primary' : 'btn-secondary' }}">Suppliers</a>
        </div>
        @can($partyType.'.export')<button id="party-pdf" class="btn btn-secondary"><i class="ri-file-pdf-2-line me-1"></i>PDF</button>@endcan
        @can($partyType.'.create')<button id="add-party" class="btn btn-primary"><i class="ri-user-add-line me-1"></i>Add {{ str($title)->singular() }}</button>@endcan
    </div>
</div>

<div class="party-workspace">
    <aside class="party-list-panel">
        <div class="party-list-tools">
            <div class="party-search"><i class="ri-search-line"></i><input id="party-search" type="search" placeholder="Search name, code or mobile" autocomplete="off"></div>
            <button class="party-filter-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#party-compact-filters" aria-label="Party filters"><i class="ri-filter-3-line"></i></button>
        </div>
        <div class="collapse" id="party-compact-filters"><div class="party-compact-filters">
            <select id="party-group-filter" class="form-select form-select-sm"><option value="">All groups</option>@foreach($groups as $group)<option value="{{ $group->id }}">{{ $group->name }}</option>@endforeach</select>
            <select id="party-status-filter" class="form-select form-select-sm"><option value="">All status</option><option value="1">Active</option><option value="0">Inactive</option></select>
            <button id="reset-party-filters" class="btn btn-sm btn-secondary" type="button">Reset</button>
        </div></div>
        <div class="party-list-heading"><span>Party</span><span>Outstanding</span></div>
        <div class="party-list-table-wrap"><table id="parties-table" class="table w-100"><thead class="visually-hidden"><tr><th>Party</th><th>Outstanding</th></tr></thead></table></div>
    </aside>

    <section class="party-detail-panel">
        <div id="party-empty-state" class="party-empty-state">
            <div class="party-empty-icon"><i class="ri-user-search-line"></i></div>
            <h5>Select a {{ str($title)->singular()->lower() }}</h5>
            <p>Choose a party from the list to view profile, outstanding balance, and transactions.</p>
        </div>

        <div id="party-detail-content" class="d-none">
            <article class="party-profile-card">
                <div class="party-profile-main">
                    <div id="party-avatar" class="party-avatar">P</div>
                    <div class="party-profile-copy">
                        <div class="d-flex align-items-center flex-wrap gap-2"><h4 id="party-name" class="mb-0"></h4><span id="party-status"></span></div>
                        <div class="party-code-line"><span id="party-code"></span><span id="party-group"></span></div>
                        <div class="party-contact-grid">
                            <span><i class="ri-phone-line"></i><span id="party-mobile">-</span></span>
                            <span><i class="ri-mail-line"></i><span id="party-email">-</span></span>
                            <span><i class="ri-government-line"></i><span id="party-gstin">-</span></span>
                            <span><i class="ri-map-pin-line"></i><span id="party-address">-</span></span>
                        </div>
                    </div>
                </div>
                <div class="party-profile-actions">
                    <a id="party-whatsapp" class="btn btn-soft-success" target="_blank" rel="noopener" title="WhatsApp"><i class="ri-whatsapp-line"></i></a>
                    <a id="party-mail" class="btn btn-soft-info" title="Email"><i class="ri-mail-send-line"></i></a>
                    <button id="edit-selected-party" class="btn btn-soft-primary" type="button"><i class="ri-edit-line"></i><span>Edit</span></button>
                    <button id="delete-selected-party" class="btn btn-soft-danger" type="button"><i class="ri-delete-bin-line"></i></button>
                </div>
            </article>

            <div class="party-summary-grid">
                <div class="party-summary-card party-summary-primary"><span>Outstanding</span><strong id="party-outstanding">Rs. 0.00</strong><small id="party-balance-label">Receivable</small></div>
                <div class="party-summary-card"><span>Total Business</span><strong id="party-business">Rs. 0.00</strong><small>Posted transactions</small></div>
                <div class="party-summary-card"><span>{{ $partyType === 'customers' ? 'Collections' : 'Payments' }}</span><strong id="party-payments">Rs. 0.00</strong><small>Recorded payments</small></div>
                <div class="party-summary-card"><span>Credit Available</span><strong id="party-credit">Rs. 0.00</strong><small><span id="party-transaction-count">0</span> transactions</small></div>
            </div>

            <article class="party-transactions-card">
                <div class="party-transactions-header">
                    <div><h5>Transactions</h5><p>Invoices, returns, notes, and payments for this party</p></div>
                    <div class="d-flex flex-wrap gap-2">
                        @if($partyType === 'customers')
                            @can('sales-invoices.create')<a href="{{ route('admin.documents.index','sales-invoices') }}" class="btn btn-primary btn-sm"><i class="ri-add-line"></i>Add Sale</a>@endcan
                        @else
                            @can('purchase-bills.create')<a href="{{ route('admin.documents.index','purchase-bills') }}" class="btn btn-primary btn-sm"><i class="ri-add-line"></i>Add Purchase</a>@endcan
                        @endif
                        @can('payments.create')<a href="{{ route('admin.payments.index') }}" class="btn btn-secondary btn-sm"><i class="ri-money-rupee-circle-line"></i>Payment</a>@endcan
                    </div>
                </div>
                <div class="party-transaction-filters">
                    <select id="transaction-status-filter" class="form-select form-select-sm"><option value="">All status</option><option value="draft">Draft</option><option value="posted">Posted</option></select>
                    <input id="transaction-from-filter" type="date" class="form-control form-control-sm" aria-label="Transactions from date">
                    <input id="transaction-to-filter" type="date" class="form-control form-control-sm" aria-label="Transactions to date">
                    <button id="reset-transaction-filters" class="btn btn-sm btn-secondary" type="button">Reset</button>
                </div>
                <div class="table-responsive"><table id="party-transactions-table" class="table align-middle w-100"><thead><tr><th>S.No</th><th>Type</th><th>Number</th><th>Date</th><th>Total</th><th>Balance</th><th>Due Date</th><th>Status</th><th></th></tr></thead></table></div>
            </article>
        </div>
    </section>
</div>

<div id="party-modal" class="modal fade" tabindex="-1"><div class="modal-dialog modal-xl"><div class="modal-content"><form id="party-form" method="POST">@csrf
    <div class="modal-header"><div><span class="erp-eyebrow">Party Management</span><h5 class="modal-title mb-0">{{ str($title)->singular() }} Details</h5></div><button class="btn-close" type="button" data-bs-dismiss="modal"></button></div>
    <div class="modal-body"><input type="hidden" name="_method" value="POST"><div class="row g-3">
        <div class="col-md-4"><label class="form-label">Name <span class="text-danger">*</span></label><input name="name" class="form-control" required maxlength="190"></div>
        <div class="col-md-2"><label class="form-label">Code <span class="text-danger">*</span></label><input name="code" class="form-control text-uppercase" required maxlength="60"></div>
        <div class="col-md-3"><label class="form-label">Group</label><select name="group_id" class="form-select"><option value="">None</option>@foreach($groups as $group)<option value="{{ $group->id }}">{{ $group->name }}</option>@endforeach</select></div>
        <div class="col-md-3"><label class="form-label">Mobile</label><input name="mobile" class="form-control" minlength="7" maxlength="30"></div>
        <div class="col-md-4"><label class="form-label">Email</label><input name="email" type="email" class="form-control"></div>
        <div class="col-md-2"><label class="form-label">GSTIN</label><input name="gstin" class="form-control" maxlength="30"></div>
        <div class="col-md-2"><label class="form-label">PAN</label><input name="pan" class="form-control" maxlength="20"></div>
        <div class="col-md-2"><label class="form-label">Credit Limit</label><input name="credit_limit" type="number" min="0" step="0.01" value="0" class="form-control"></div>
        <div class="col-md-2"><label class="form-label">Opening Balance</label><input name="opening_balance" type="number" min="0" step="0.01" value="0" class="form-control"></div>
        <div class="col-md-3"><label class="form-label">Balance Type</label><select name="balance_type" class="form-select"><option value="{{ $partyType==='customers'?'receivable':'payable' }}">{{ $partyType==='customers'?'Receivable':'Payable' }}</option><option value="{{ $partyType==='customers'?'payable':'receivable' }}">{{ $partyType==='customers'?'Payable':'Receivable' }}</option></select></div>
        <div class="col-md-9"><label class="form-label">Billing Address</label><input name="address" class="form-control" maxlength="1000"></div>
        <div class="col-md-4"><label class="form-label">City</label><input name="city" class="form-control" maxlength="100"></div>
        <div class="col-md-4"><label class="form-label">State</label><input name="state" class="form-control" maxlength="100"></div>
        <div class="col-md-4"><label class="form-label">Postal Code</label><input name="postal_code" class="form-control" maxlength="20"></div>
        <div class="col-12"><input type="hidden" name="is_active" value="0"><div class="form-check form-switch"><input id="party-active" name="is_active" type="checkbox" value="1" class="form-check-input" checked><label class="form-check-label" for="party-active">Active party</label></div></div>
    </div></div>
    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-success"><i class="ri-save-line me-1"></i>Save Party</button></div>
</form></div></div></div>
@endsection
@push('scripts')
<script>
$(function () {
    $.ajaxSetup({headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content}});
    const base = @json(route('admin.parties.index', $partyType));
    const transactionsSuffix = '/transactions';
    const modal = new bootstrap.Modal('#party-modal');
    let selectedId = null;
    let selectedParty = null;
    let transactionTable = null;
    let profileRequest = null;
    const money = value => 'Rs. '+Number(value || 0).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    const safe = value => $('<div>').text(value || '').html();

    const partyTable = initializeDataTable({
        selector: '#parties-table', url: base,
        filters: () => ({group_id: $('#party-group-filter').val(), status: $('#party-status-filter').val()}),
        pageLength: 10,
        columns: [
            {data: null, name: 'name', render: function (data, type, row) {
                if (type !== 'display') return row.name;
                const meta = [row.code, row.mobile].filter(Boolean).map(safe).join(' · ');
                return '<div class="party-list-name">'+safe(row.name)+'</div><div class="party-list-meta">'+meta+'</div>';
            }},
            {data: 'current_balance', name: 'current_balance', orderable: false, searchable: false, render: function (value, type) {
                if (type !== 'display') return value;
                return '<span class="party-list-balance '+(Number(value) > 0 ? 'has-balance' : '')+'">'+money(value)+'</span>';
            }}
        ],
        order: [[0, 'asc']]
    });

    function selectParty(id) {
        if (!id) return;
        selectedId = Number(id);
        $('#parties-table tbody tr').removeClass('is-selected').filter(function () { return Number(partyTable.row(this).data()?.id) === selectedId; }).addClass('is-selected');
        $('#party-empty-state').addClass('d-none'); $('#party-detail-content').removeClass('d-none').addClass('is-loading');
        if (profileRequest) profileRequest.abort();
        profileRequest = $.get(base+'/'+selectedId).done(renderProfile).fail(function (xhr) { if (xhr.statusText !== 'abort') handleAjaxError(xhr); }).always(() => $('#party-detail-content').removeClass('is-loading'));
    }

    function renderProfile(response) {
        selectedParty = response.data.party;
        const summary = response.data.summary;
        const address = (selectedParty.addresses || []).find(item => item.is_default) || (selectedParty.addresses || [])[0] || {};
        const addressText = [address.address, address.city, address.state, address.postal_code].filter(Boolean).join(', ') || '-';
        const initials = String(selectedParty.name || 'P').split(/\s+/).slice(0, 2).map(word => word[0]).join('').toUpperCase();
        $('#party-avatar').text(initials); $('#party-name').text(selectedParty.name); $('#party-code').text(selectedParty.code);
        $('#party-group').text(selectedParty.group?.name || 'Ungrouped'); $('#party-mobile').text(selectedParty.mobile || '-'); $('#party-email').text(selectedParty.email || '-'); $('#party-gstin').text(selectedParty.gstin || '-'); $('#party-address').text(addressText);
        $('#party-status').html(selectedParty.is_active ? '<span class="badge bg-success-subtle text-success">Active</span>' : '<span class="badge bg-secondary-subtle text-secondary">Inactive</span>');
        $('#party-outstanding').text(money(summary.outstanding)); $('#party-business').text(money(summary.total_business)); $('#party-payments').text(money(summary.payments)); $('#party-credit').text(money(summary.credit_available)); $('#party-transaction-count').text(summary.transactions);
        $('#party-balance-label').text(@json($partyType === 'customers' ? 'Receivable' : 'Payable'));
        $('#party-whatsapp').attr('href', selectedParty.mobile ? 'https://wa.me/'+String(selectedParty.mobile).replace(/\D/g, '') : '#').toggleClass('disabled', !selectedParty.mobile);
        $('#party-mail').attr('href', selectedParty.email ? 'mailto:'+encodeURIComponent(selectedParty.email) : '#').toggleClass('disabled', !selectedParty.email);
        $('#edit-selected-party').toggleClass('d-none', !response.data.permissions.update); $('#delete-selected-party').toggleClass('d-none', !response.data.permissions.delete);
        loadTransactions();
    }

    function loadTransactions() {
        const url = base+'/'+selectedId+transactionsSuffix;
        if (transactionTable) { transactionTable.ajax.url(url).load(); return; }
        transactionTable = initializeDataTable({selector: '#party-transactions-table', url: url, filters: () => ({status: $('#transaction-status-filter').val(), from_date: $('#transaction-from-filter').val(), to_date: $('#transaction-to-filter').val()}), columns: [
            {data: 'DT_RowIndex', orderable: false, searchable: false}, {data: 'source_type'}, {data: 'number'}, {data: 'transaction_date'},
            {data: 'total'}, {data: 'balance'}, {data: 'due_date'}, {data: 'status', orderable: false, searchable: false}, {data: 'action', orderable: false, searchable: false}
        ], order: [[3, 'desc']]});
    }

    function resetForm() {
        const form = document.getElementById('party-form'); form.reset(); form.action = base; form._method.value = 'POST'; $('#party-active').prop('checked', true);
        $('[name="balance_type"]').val(@json($partyType === 'customers' ? 'receivable' : 'payable'));
        $('#party-form .is-invalid').removeClass('is-invalid'); $('#party-form [data-ajax-error]').remove();
    }
    function editParty() {
        if (!selectedParty) return; resetForm(); const form = document.getElementById('party-form'); form.action = base+'/'+selectedParty.id; form._method.value = 'PUT';
        ['name','code','group_id','mobile','email','gstin','pan','credit_limit','opening_balance','balance_type'].forEach(key => $('[name="'+key+'"]').val(selectedParty[key] ?? ''));
        const address = (selectedParty.addresses || []).find(item => item.is_default) || (selectedParty.addresses || [])[0] || {};
        ['address','city','state','postal_code'].forEach(key => $('[name="'+key+'"]').val(address[key] ?? ''));
        $('#party-active').prop('checked', !!selectedParty.is_active); modal.show();
    }

    $('#parties-table tbody').on('click', 'tr', function () { const data = partyTable.row(this).data(); if (data) selectParty(data.id); });
    $('#parties-table').on('draw.dt', function () {
        if (selectedId) $('#parties-table tbody tr').filter(function () { return Number(partyTable.row(this).data()?.id) === selectedId; }).addClass('is-selected');
        if (!selectedId) { const first = partyTable.rows({page: 'current'}).data()[0]; if (first) selectParty(first.id); }
    });
    let searchTimer; $('#party-search').on('input', function () { clearTimeout(searchTimer); const value = this.value; searchTimer = setTimeout(() => partyTable.search(value).draw(), 250); });
    $('#party-group-filter,#party-status-filter').on('change', () => { selectedId = null; partyTable.ajax.reload(); });
    $('#reset-party-filters').on('click', () => { $('#party-group-filter,#party-status-filter').val(''); $('#party-search').val(''); selectedId = null; partyTable.search('').ajax.reload(); });
    $('#transaction-status-filter,#transaction-from-filter,#transaction-to-filter').on('change', () => transactionTable?.ajax.reload());
    $('#reset-transaction-filters').on('click', () => { $('#transaction-status-filter,#transaction-from-filter,#transaction-to-filter').val(''); transactionTable?.ajax.reload(); });
    $('#add-party').on('click', () => { resetForm(); modal.show(); }); $('#edit-selected-party').on('click', editParty);

    $('#party-form').validate({rules: {name: {required: true, minlength: 2, maxlength: 190}, code: {required: true, maxlength: 60}, mobile: {minlength: 7, maxlength: 30}, email: {email: true}, credit_limit: {number: true, min: 0}, opening_balance: {number: true, min: 0}}, errorElement: 'span', errorClass: 'invalid-feedback', highlight: element => $(element).addClass('is-invalid'), unhighlight: element => $(element).removeClass('is-invalid'), submitHandler: form => submitFormUsingAjax(form, {reset: false, onSuccess: function (response) { modal.hide(); selectedId = Number(response.data.id); partyTable.ajax.reload(() => selectParty(selectedId), false); }})});
    $('#delete-selected-party').on('click', function () { if (!selectedId) return; Swal.fire({title: 'Delete '+(selectedParty?.name || 'party')+'?', text: 'This cannot be undone.', icon: 'warning', showCancelButton: true, confirmButtonText: 'Delete'}).then(result => { if (!result.isConfirmed) return; $.ajax({url: base+'/'+selectedId, type: 'DELETE'}).done(response => { Swal.fire('Deleted', response.message, 'success'); selectedId = null; selectedParty = null; $('#party-detail-content').addClass('d-none'); $('#party-empty-state').removeClass('d-none'); partyTable.ajax.reload(null, false); }).fail(xhr => Swal.fire('Unable to delete', xhr.responseJSON?.message || 'Please try again.', 'error')); }); });
    $('#party-pdf').on('click', () => location.href = @json(route('admin.parties.pdf', $partyType))+'?status='+($('#party-status-filter').val() || '')+'&group_id='+($('#party-group-filter').val() || '')+'&search='+encodeURIComponent($('#party-search').val() || ''));
});
</script>
@endpush
