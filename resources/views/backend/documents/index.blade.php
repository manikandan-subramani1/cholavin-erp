@extends('backend.layouts.app')
@section('title', $module['title'].' | Cholavin ERP')
@push('styles')
<style>.document-item-row td{min-width:110px}.document-item-row td:first-child{min-width:230px}</style>
@endpush
@section('content')
<div class="page-title-box d-flex justify-content-between align-items-center">
    <div><span class="erp-eyebrow">{{ $module['group'] }}</span><h4>{{ $module['title'] }}</h4></div>
    <div class="d-flex gap-2">
        @can($moduleKey.'.export')<button id="document-pdf" class="btn btn-secondary">PDF</button>@endcan
        @can($moduleKey.'.create')<button id="add-document" class="btn btn-primary">Add New</button>@endcan
    </div>
</div>
@if($module['stock_effect'] && !$activeGodownId)
    <div class="alert alert-warning">Select a godown in the header before posting this stock-affecting document.</div>
@endif
<div class="card erp-panel mb-3"><div class="card-body"><form id="document-filters" class="row g-3">
    <div class="col-md-2"><label class="form-label">Status</label><select id="document-status-filter" class="form-select"><option value="">All</option><option value="draft">Draft</option><option value="posted">Posted</option></select></div>
    <div class="col-md-3"><label class="form-label">From</label><input id="document-from-filter" type="date" class="form-control"></div>
    <div class="col-md-3"><label class="form-label">To</label><input id="document-to-filter" type="date" class="form-control"></div>
    <div class="col-md-2 align-self-end"><button id="reset-document-filters" type="button" class="btn btn-secondary w-100">Reset</button></div>
</form></div></div>
<div class="card erp-panel"><div class="card-body table-responsive"><table id="documents-table" class="table table-hover w-100">
    <thead><tr><th>S.No</th><th>Number</th><th>Date</th><th>Party</th><th>Godown</th><th>Items</th><th>Total</th><th>Balance</th><th>Status</th><th>Action</th></tr></thead>
</table></div></div>

<div id="document-modal" class="modal fade" tabindex="-1"><div class="modal-dialog modal-fullscreen-xl-down modal-xl"><div class="modal-content">
<form id="document-form" method="POST">
    @csrf
    <div class="modal-header"><h5 class="modal-title">{{ str($module['title'])->singular() }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">@include('backend.documents.'.$moduleKey.'.form')</div>
    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-success">Save Document</button></div>
</form></div></div></div>
@endsection
@push('scripts')
<script>
$(function () {
    $.ajaxSetup({headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content}});
    const base = @json(route('admin.documents.index', $moduleKey));
    const productUrl = @json(route('admin.lookups.products'));
    const partyUrl = @json(route('admin.lookups.parties', ['type' => $module['party_type']]));
    const purchase = {{ $module['group'] === 'Purchases' ? 'true' : 'false' }};
    let products = [];
    const modal = new bootstrap.Modal('#document-modal');
    const table = initializeDataTable({selector: '#documents-table', url: base, filters: () => ({status: $('#document-status-filter').val(), from_date: $('#document-from-filter').val(), to_date: $('#document-to-filter').val()}), columns: [
        {data: 'DT_RowIndex', orderable: false, searchable: false}, {data: 'number'}, {data: 'document_date'},
        {data: 'party.name', defaultContent: 'Cash'}, {data: 'godown.name', defaultContent: '-'}, {data: 'items_count', searchable: false},
        {data: 'total_amount'}, {data: 'balance_amount'}, {data: 'status'}, {data: 'action', orderable: false, searchable: false}
    ], order: [[2, 'desc']]});

    function loadLookups() {
        const calls = [$.get(productUrl).then(response => products = response.data)];
        @if($module['party_type'])
        calls.push($.get(partyUrl).then(response => {
            const select = $('[name="party_id"]').html('<option value="">Select</option>');
            response.data.forEach(option => select.append(new Option(option.text, option.id)));
        }));
        @endif
        return Promise.all(calls);
    }
    function row(item = {}) {
        const index = $('#document-items tr').length;
        const options = ['<option value="">Select product</option>'].concat(products.map(product => `<option value="${product.id}" data-rate="${purchase ? product.purchase_price : product.sale_price}" data-unit="${product.unit || ''}" ${Number(item.product_id) === Number(product.id) ? 'selected' : ''}>${product.text}</option>`)).join('');
        return `<tr class="document-item-row"><td><select name="items[${index}][product_id]" class="form-select product-select" required>${options}</select></td><td><input name="items[${index}][quantity]" type="number" step="0.001" min="0.001" value="${item.quantity || 1}" class="form-control item-qty" required></td><td><input name="items[${index}][rate]" type="number" step="0.01" min="0" value="${item.rate || 0}" class="form-control item-rate" required></td><td><input name="items[${index}][discount_amount]" type="number" step="0.01" min="0" value="${item.discount_amount || 0}" class="form-control item-discount"></td><td><input name="items[${index}][unit]" value="${item.unit || ''}" class="form-control item-unit"></td><td><input name="items[${index}][batch_number]" value="${item.batch_number || ''}" class="form-control"></td><td><input name="items[${index}][expiry_date]" type="date" value="${item.expiry_date ? String(item.expiry_date).substring(0, 10) : ''}" class="form-control"></td><td><button type="button" class="btn btn-sm btn-danger remove-item">&times;</button></td></tr>`;
    }
    function total() {
        let value = Number($('[name="expense_amount"]').val() || 0) + Number($('[name="round_off"]').val() || 0);
        $('#document-items tr').each(function () { value += Number($(this).find('.item-qty').val() || 0) * Number($(this).find('.item-rate').val() || 0) - Number($(this).find('.item-discount').val() || 0); });
        $('#document-estimated-total').text(value.toFixed(2));
    }
    function reset() {
        const form = document.getElementById('document-form'); form.reset(); form.action = base; form._method.value = 'POST';
        $('#document-items').empty().append(row()); total();
    }
    $('#add-document').on('click', () => loadLookups().then(() => { reset(); modal.show(); }));
    $('#add-document-item').on('click', () => $('#document-items').append(row()));
    $(document).on('click', '.remove-item', function () { if ($('#document-items tr').length > 1) $(this).closest('tr').remove(); total(); });
    $(document).on('change', '.product-select', function () { const option = this.selectedOptions[0]; $(this).closest('tr').find('.item-rate').val(option.dataset.rate || 0).end().find('.item-unit').val(option.dataset.unit || ''); total(); });
    $(document).on('input', '.item-qty,.item-rate,.item-discount,[name="expense_amount"],[name="round_off"]', total);
    $(document).on('click', '.edit-document', function () {
        const id = this.dataset.id;
        loadLookups().then(() => $.get(base+'/'+id)).then(response => {
            reset(); const documentData = response.data; const form = document.getElementById('document-form'); form.action = base+'/'+id; form._method.value = 'PUT';
            ['party_id', 'document_date', 'due_date', 'status', 'reference_number', 'expense_amount', 'round_off', 'notes'].forEach(key => $('[name="'+key+'"]').val(documentData[key] !== null && documentData[key] !== undefined ? String(documentData[key]).substring(0, 10) : ''));
            $('#document-items').empty(); documentData.items.forEach(item => $('#document-items').append(row(item))); total(); modal.show();
        });
    });
    $('#document-form').validate({errorElement: 'span', errorClass: 'invalid-feedback', highlight: element => $(element).addClass('is-invalid'), unhighlight: element => $(element).removeClass('is-invalid'), submitHandler: form => submitFormUsingAjax(form, {reset: false, table: '#documents-table', onSuccess: () => modal.hide()})});
    $('#document-status-filter,#document-from-filter,#document-to-filter').on('change', () => table.ajax.reload());
    $('#reset-document-filters').on('click', () => { document.getElementById('document-filters').reset(); table.ajax.reload(); });
    $(document).on('click', '.delete-document', function () { const url = this.dataset.url; Swal.fire({title: 'Delete draft?', icon: 'warning', showCancelButton: true}).then(result => { if (result.isConfirmed) $.ajax({url, type: 'DELETE'}).done(response => { Swal.fire('Deleted', response.message, 'success'); table.ajax.reload(null, false); }).fail(xhr => Swal.fire('Unable', xhr.responseJSON?.message || 'Please try again.', 'error')); }); });
    $('#document-pdf').on('click', () => location.href = @json(route('admin.documents.pdf', $moduleKey))+'?status='+($('#document-status-filter').val() || '')+'&from_date='+($('#document-from-filter').val() || '')+'&to_date='+($('#document-to-filter').val() || ''));
});
</script>
@endpush
