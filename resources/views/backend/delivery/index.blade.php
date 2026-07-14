@extends('backend.layouts.app')
@section('title', 'Delivery Management | Cholavin ERP')
@section('content')
    <div class="page-title-box d-flex justify-content-between">
        <div><span class="erp-eyebrow">Delivery Management</span>
            <h4>Assignments and Status</h4>
        </div>
        @can('deliveries.create')
            <button id="add-delivery" class="btn btn-primary">Assign Delivery</button>
        @endcan
    </div>
    <div class="card mb-3">
        <div class="card-body">
            <div class="row">
                <div class="col-md-3"><label class="form-label">Status</label><select id="delivery-status-filter"
                        class="form-select">
                        <option value="">All</option>
                        @foreach (['pending', 'assigned', 'out_for_delivery', 'delivered', 'failed', 'returned'] as $status)
                            <option value="{{ $status }}">{{ str($status)->replace('_', ' ')->title() }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-body table-responsive">
            <table id="deliveries-table" class="table w-100">
                <thead>
                    <tr>
                        <th>S.No</th>
                        <th>Document</th>
                        <th>Party</th>
                        <th>Vehicle</th>
                        <th>Driver</th>
                        <th>Route</th>
                        <th>Scheduled</th>
                        <th>Cash</th>
                        <th>Expense</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
    <div id="delivery-modal" class="modal fade">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="delivery-form" action="{{ route('admin.deliveries.store') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="_method" value="POST">
                    <div class="modal-header">
                        <h5>Delivery Assignment</h5><button class="btn-close" type="button"
                            data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6"><label class="form-label">Sales Document ID</label><input
                                    name="commercial_document_id" type="number" class="form-control" required></div>
                            <div class="col-md-6"><label class="form-label">Scheduled At</label><input name="scheduled_at"
                                    type="datetime-local" class="form-control"></div>
                            <div class="col-md-4"><label class="form-label">Vehicle</label><select name="vehicle_id"
                                    class="form-select">
                                    <option value="">None</option>
                                    @foreach ($vehicles as $item)
                                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4"><label class="form-label">Driver</label><select name="driver_id"
                                    class="form-select">
                                    <option value="">None</option>
                                    @foreach ($drivers as $item)
                                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4"><label class="form-label">Route</label><select name="route_id"
                                    class="form-select">
                                    <option value="">None</option>
                                    @foreach ($routes as $item)
                                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4"><label class="form-label">Status</label><select name="status"
                                    class="form-select">
                                    @foreach (['pending', 'assigned', 'out_for_delivery', 'delivered', 'failed', 'returned'] as $status)
                                        <option value="{{ $status }}">{{ str($status)->replace('_', ' ')->title() }}
                                        </option>
                                    @endforeach
                                </select></div>
                            <div class="col-md-4"><label class="form-label">Cash Collected</label><input
                                    name="cash_collected" type="number" min="0" step="0.01" value="0"
                                    class="form-control"></div>
                            <div class="col-md-4"><label class="form-label">Delivery Expense</label><input
                                    name="delivery_expense" type="number" min="0" step="0.01" value="0"
                                    class="form-control"></div>
                            <div class="col-md-6"><label class="form-label">Proof of Delivery</label><input name="proof"
                                    type="file" accept="image/jpeg,image/png,image/webp,application/pdf"
                                    class="form-control"><small class="text-muted">JPG, PNG, WebP or PDF; max 5 MB.</small>
                            </div>
                            <div class="col-md-6"><label class="form-label">Notes</label>
                                <textarea name="notes" class="form-control" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">Cancel</button><button class="btn btn-success">Save</button></div>
                </form>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        $(function() {
            const base = @json(route('admin.deliveries.index'));
            const modal = new bootstrap.Modal('#delivery-modal');
            const table = initializeDataTable({
                selector: '#deliveries-table',
                url: base,
                filters: () => ({
                    status: $('#delivery-status-filter').val()
                }),
                columns: [{
                    data: 'DT_RowIndex',
                    orderable: false,
                    searchable: false
                }, {
                    data: 'document_number'
                }, {
                    data: 'party_name',
                    defaultContent: '-'
                }, {
                    data: 'vehicle.name',
                    defaultContent: '-'
                }, {
                    data: 'driver.name',
                    defaultContent: '-'
                }, {
                    data: 'route.name',
                    defaultContent: '-'
                }, {
                    data: 'scheduled_at',
                    defaultContent: '-'
                }, {
                    data: 'cash_collected'
                }, {
                    data: 'delivery_expense'
                }, {
                    data: 'status'
                }, {
                    data: 'action',
                    orderable: false,
                    searchable: false
                }]
            });

            function reset() {
                const form = document.getElementById('delivery-form');
                form.reset();
                form.action = base;
                form._method.value = 'POST';
            }
            $('#add-delivery').on('click', () => {
                reset();
                modal.show();
            });
            $('#delivery-status-filter').on('change', () => table.ajax.reload());
            $(document).on('click', '.edit-delivery', function() {
                $.get(base + '/' + this.dataset.id).then(response => {
                    reset();
                    const data = response.data;
                    const form = document.getElementById('delivery-form');
                    form.action = base + '/' + data.id;
                    form._method.value = 'PUT';
                    ['commercial_document_id', 'vehicle_id', 'driver_id', 'route_id', 'status',
                        'scheduled_at', 'cash_collected', 'delivery_expense', 'notes'
                    ].forEach(key => $('[name="' + key + '"]').val(data[key] !== null && data[
                        key] !== undefined ? String(data[key]).substring(0, 16) : ''));
                    modal.show();
                });
            });
            $('#delivery-form').validate({
                rules: {
                    proof: {
                        extension: 'jpg|jpeg|png|webp|pdf'
                    }
                },
                submitHandler: form => submitFormUsingAjax(form, {
                    reset: false,
                    table: '#deliveries-table',
                    onSuccess: () => modal.hide()
                })
            });
        });
    </script>
@endpush
