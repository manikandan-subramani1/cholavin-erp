@php
    $paymentContext = in_array(request('type'), ['customer_collection', 'supplier_payment', 'expense'], true)
        ? request('type')
        : null;
    $paymentHeading = match ($paymentContext) {
        'customer_collection' => 'Payment-In',
        'supplier_payment' => 'Payment-Out',
        'expense' => 'Expense Entries',
        default => 'Receipts and Payments',
    };
@endphp

@extends('backend.layouts.app')
@section('title', $paymentHeading.' | Cholavin ERP')

@section('content')
    <div class="page-title-box d-flex justify-content-between">
        <div>
            <span class="erp-eyebrow">Payments & Accounts</span>
            <h4>{{ $paymentHeading }}</h4>
        </div>
        @can('payments.create')
            <button id="add-payment" class="btn btn-primary">New Entry</button>
        @endcan
    </div>

    <div class="card erp-panel">
        <div class="card-body">
            <table id="payments-table" class="table w-100">
                <thead>
                    <tr>
                        <th>S.No</th><th>Number</th><th>Date</th><th>Type</th><th>Party</th><th>Method</th><th>Reference</th><th>Amount</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    <div id="payment-modal" class="modal fade">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="payment-form" action="{{ route('admin.payments.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5>Payment Entry</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label>Type</label>
                            <select name="type" class="form-select">
                                @foreach(['cash_receipt', 'cash_payment', 'bank_receipt', 'bank_payment', 'supplier_payment', 'customer_collection', 'expense', 'income'] as $type)
                                    <option value="{{ $type }}" @selected($paymentContext === $type)>{{ str($type)->replace('_', ' ')->title() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3"><label>Date</label><input name="payment_date" type="date" value="{{ now()->toDateString() }}" class="form-control" required></div>
                        <div class="mb-3"><label>Party ID (optional)</label><input name="party_id" type="number" class="form-control"></div>
                        <div class="mb-3"><label>Document ID (optional)</label><input name="commercial_document_id" type="number" class="form-control"></div>
                        <div class="mb-3">
                            <label>Method</label>
                            <select name="payment_method_id" class="form-select">
                                <option value="">None</option>
                                @foreach($methods as $method)
                                    <option value="{{ $method->id }}">{{ $method->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3"><label>Amount</label><input name="amount" type="number" min="0.01" step="0.01" class="form-control" required></div>
                        <div class="mb-3"><label>Reference</label><input name="reference_number" class="form-control"></div>
                        <div><label>Notes</label><textarea name="notes" class="form-control"></textarea></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button class="btn btn-success">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(function () {
            const paymentContext = @json($paymentContext);
            const table = initializeDataTable({
                selector: '#payments-table',
                url: @json(route('admin.payments.index', array_filter(['type' => $paymentContext]))),
                columns: [
                    {data: 'DT_RowIndex', orderable: false, searchable: false},
                    {data: 'number'},
                    {data: 'payment_date'},
                    {data: 'type'},
                    {data: 'party.name', defaultContent: '—', orderable: false, searchable: false},
                    {data: 'method.name', defaultContent: '—', orderable: false, searchable: false},
                    {data: 'reference_number', defaultContent: '—'},
                    {data: 'amount'},
                ],
                order: [[2, 'desc']],
            });
            const modal = new bootstrap.Modal('#payment-modal');

            $('#add-payment').on('click', function () {
                if (paymentContext) {
                    $('#payment-form [name="type"]').val(paymentContext);
                }
                modal.show();
            });

            $('#payment-form').validate({
                submitHandler: form => submitFormUsingAjax(form, {
                    table: '#payments-table',
                    onSuccess: () => modal.hide(),
                }),
            });
        });
    </script>
@endpush
