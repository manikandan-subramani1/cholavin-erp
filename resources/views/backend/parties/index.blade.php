@extends('backend.layouts.app')
@section('title', $title . ' | Cholavin ERP')
@push('styles')
@endpush
@section('content')
    <div id="party-module" data-base-url="{{ route('admin.parties.index', $partyType) }}"
        data-pdf-url="{{ route('admin.parties.pdf', $partyType) }}"
        data-balance-label="{{ $partyType === 'customers' ? 'Receivable' : 'Payable' }}"
        data-balance-type="{{ $partyType === 'customers' ? 'receivable' : 'payable' }}"
        data-party-label="{{ str($title)->singular() }}"
        data-payment-url="{{ route('admin.payments.index', ['type' => $partyType === 'customers' ? 'customer_collection' : 'supplier_payment']) }}"
        data-document-url="{{ route('admin.documents.index', $partyType === 'customers' ? 'sales-invoices' : 'purchase-bills') }}">
    <div class="party-page-toolbar">
        <div>
            <span class="erp-eyebrow">Party Management</span>
            <div class="d-flex align-items-center gap-2">
                <h4 class="mb-0">{{ $title }}</h4><span
                    class="party-live-context">{{ $headerShops->firstWhere('id', $activeShopId)?->name ?? 'Select shop' }}</span>
            </div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <div class="btn-group party-type-switch" role="group">
                <a href="{{ route('admin.parties.index', 'customers') }}"
                    class="btn {{ $partyType === 'customers' ? 'btn-primary' : 'btn-secondary' }}">Customers</a>
                <a href="{{ route('admin.parties.index', 'suppliers') }}"
                    class="btn {{ $partyType === 'suppliers' ? 'btn-primary' : 'btn-secondary' }}">Suppliers</a>
            </div>
            @can($partyType . '.export')
                <button id="party-pdf" class="btn btn-secondary"><i class="ri-file-pdf-2-line me-1"></i>PDF</button>
            @endcan
            @can($partyType . '.create')
                <button id="add-party" class="btn btn-primary"><i class="ri-user-add-line me-1"></i>Add
                    {{ str($title)->singular() }}</button>
            @endcan
        </div>
    </div>

    <div class="erp-party-kpis" aria-label="{{ $title }} summary">
        <article><span class="erp-party-kpi-icon is-total"><i class="ri-group-line"></i></span><div><small>Total {{ strtolower($title) }}</small><strong data-party-metric="total">{{ number_format($workspaceMetrics['total']) }}</strong><em>All records</em></div></article>
        <article><span class="erp-party-kpi-icon is-active"><i class="ri-user-follow-line"></i></span><div><small>Active</small><strong data-party-metric="active">{{ number_format($workspaceMetrics['active']) }}</strong><em>Available for transactions</em></div></article>
        <article><span class="erp-party-kpi-icon is-balance"><i class="ri-wallet-3-line"></i></span><div><small>Total {{ $partyType === 'customers' ? 'receivable' : 'payable' }}</small><strong data-party-metric="outstanding">₹{{ number_format($workspaceMetrics['outstanding'], 2) }}</strong><em>Current outstanding</em></div></article>
        <article><span class="erp-party-kpi-icon is-overdue"><i class="ri-alarm-warning-line"></i></span><div><small>Overdue amount</small><strong data-party-metric="overdue">₹{{ number_format($workspaceMetrics['overdue'], 2) }}</strong><em>Past due date</em></div></article>
        <article><span class="erp-party-kpi-icon is-business"><i class="ri-line-chart-line"></i></span><div><small>{{ $partyType === 'customers' ? 'Sales' : 'Purchases' }} this month</small><strong data-party-metric="business_month">₹{{ number_format($workspaceMetrics['business_month'], 2) }}</strong><em>Posted documents</em></div></article>
        <article><span class="erp-party-kpi-icon is-payment"><i class="ri-hand-coin-line"></i></span><div><small>{{ $partyType === 'customers' ? 'Collections' : 'Payments' }} this month</small><strong data-party-metric="payments_month">₹{{ number_format($workspaceMetrics['payments_month'], 2) }}</strong><em>Recorded payments</em></div></article>
    </div>

    <div class="party-workspace">
        <aside class="party-list-panel">
            <div class="party-list-tools">
                <div class="party-search"><i class="ri-search-line"></i><input id="party-search" type="search"
                        placeholder="Search name, code or mobile" autocomplete="off"></div>
                <button class="party-filter-toggle" type="button" data-bs-toggle="collapse"
                    data-bs-target="#party-compact-filters" aria-label="Party filters"><i
                        class="ri-filter-3-line"></i></button>
            </div>
            <div class="collapse" id="party-compact-filters">
                <div class="party-compact-filters">
                    <select id="party-group-filter" class="form-select form-select-sm">
                        <option value="">All groups</option>
                        @foreach ($groups as $group)
                            <option value="{{ $group->id }}">{{ $group->name }}</option>
                        @endforeach
                    </select>
                    <select id="party-status-filter" class="form-select form-select-sm">
                        <option value="">All status</option>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                    <button id="reset-party-filters" class="btn btn-sm btn-secondary" type="button">Reset</button>
                </div>
            </div>
            <div class="party-list-table-wrap">
                <table id="parties-table" class="table table-hover align-middle w-100">
                    <thead>
                        <tr>
                            <th>Party</th>
                            <th>Group</th>
                            <th>Mobile</th>
                            <th>GSTIN</th>
                            <th>Location</th>
                            <th>Outstanding</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </aside>

        <div id="party-detail-modal" class="modal fade erp-form-modal" tabindex="-1" aria-labelledby="party-detail-title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-xl modal-fullscreen-sm-down"><div class="modal-content">
        <div class="modal-header"><div><span class="erp-eyebrow">{{ str($title)->singular() }} workspace</span><h5 id="party-detail-title" class="modal-title">Profile and transactions</h5></div><button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button></div>
        <div class="modal-body p-0">
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
                            <div class="d-flex align-items-center flex-wrap gap-2">
                                <h4 id="party-name" class="mb-0"></h4><span id="party-status"></span>
                            </div>
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
                        <a id="party-whatsapp" class="btn btn-soft-success" target="_blank" rel="noopener"
                            title="WhatsApp"><i class="ri-whatsapp-line"></i></a>
                        <a id="party-mail" class="btn btn-soft-info" title="Email"><i class="ri-mail-send-line"></i></a>
                        <button id="edit-selected-party" class="btn btn-soft-primary" type="button"><i
                                class="ri-edit-line"></i><span>Edit</span></button>
                        <button id="delete-selected-party" class="btn btn-soft-danger" type="button"><i
                                class="ri-delete-bin-line"></i></button>
                    </div>
                </article>

                <div class="party-summary-grid">
                    <div class="party-summary-card party-summary-primary"><span>Outstanding</span><strong
                            id="party-outstanding">? 0.00</strong><small id="party-balance-label">Receivable</small>
                    </div>
                    <div class="party-summary-card"><span>Total Business</span><strong id="party-business">?
                            0.00</strong><small>Posted transactions</small></div>
                    <div class="party-summary-card">
                        <span>{{ $partyType === 'customers' ? 'Collections' : 'Payments' }}</span><strong
                            id="party-payments">? 0.00</strong><small>Recorded payments</small></div>
                    <div class="party-summary-card"><span>Credit Available</span><strong id="party-credit">?
                            0.00</strong><small><span id="party-transaction-count">0</span> transactions</small></div>
                </div>

                <article class="party-transactions-card">
                    <div class="party-transactions-header">
                        <div>
                            <h5>Transactions</h5>
                            <p>Invoices, returns, notes, and payments for this party</p>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            @if ($partyType === 'customers')
                                @can('sales-invoices.create')
                                    <a href="{{ route('admin.documents.index', 'sales-invoices') }}"
                                        class="btn btn-primary btn-sm"><i class="ri-add-line"></i>Add Sale</a>
                                @endcan
                            @else
                                @can('purchase-bills.create')
                                    <a href="{{ route('admin.documents.index', 'purchase-bills') }}"
                                        class="btn btn-primary btn-sm"><i class="ri-add-line"></i>Add Purchase</a>
                                @endcan
                            @endif
                            @can('payments.create')
                                <a href="{{ route('admin.payments.index', ['type' => $partyType === 'customers' ? 'customer_collection' : 'supplier_payment']) }}" class="btn btn-secondary btn-sm"><i
                                        class="ri-money-rupee-circle-line"></i>{{ $partyType === 'customers' ? 'Receive Payment' : 'Make Payment' }}</a>
                            @endcan
                        </div>
                    </div>
                    <div class="party-transaction-filters">
                        <select id="transaction-status-filter" class="form-select form-select-sm">
                            <option value="">All status</option>
                            <option value="draft">Draft</option>
                            <option value="posted">Posted</option>
                        </select>
                        <input id="transaction-from-filter" type="date" class="form-control form-control-sm"
                            aria-label="Transactions from date">
                        <input id="transaction-to-filter" type="date" class="form-control form-control-sm"
                            aria-label="Transactions to date">
                        <button id="reset-transaction-filters" class="btn btn-sm btn-secondary"
                            type="button">Reset</button>
                    </div>
                    <div class="table-responsive">
                        <table id="party-transactions-table" class="table align-middle w-100">
                            <thead>
                                <tr>
                                    <th>S.No</th>
                                    <th>Type</th>
                                    <th>Number</th>
                                    <th>Date</th>
                                    <th>Total</th>
                                    <th>Balance</th>
                                    <th>Due Date</th>
                                    <th>Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </article>
            </div>
        </section>
        </div></div></div></div>
    </div>

    <div id="party-modal" class="modal fade erp-form-modal" tabindex="-1" aria-labelledby="party-modal-title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl modal-fullscreen-sm-down">
            <div class="modal-content">
                <form id="party-form" method="POST">@csrf
                    <div class="modal-header">
                        <div><span class="erp-eyebrow">Party Management</span>
                            <h5 id="party-modal-title" class="modal-title mb-0">{{ str($title)->singular() }} Details</h5>
                        </div><button class="btn-close" type="button" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body"><input type="hidden" name="_method" value="POST">
                        <div class="row g-3">
                            <div class="col-md-4"><label class="form-label">Name <span
                                        class="text-danger">*</span></label><input name="name" class="form-control"
                                    required maxlength="190"></div>
                            <div class="col-md-2"><label class="form-label">Code <span
                                        class="text-danger">*</span></label><input name="code"
                                    class="form-control text-uppercase" required maxlength="60"></div>
                            <div class="col-md-3"><label class="form-label">Group</label><select name="group_id"
                                    class="form-select">
                                    <option value="">None</option>
                                    @foreach ($groups as $group)
                                        <option value="{{ $group->id }}">{{ $group->name }}</option>
                                    @endforeach
                                </select></div>
                            <div class="col-md-3"><label class="form-label">Mobile</label><input name="mobile"
                                    class="form-control" minlength="7" maxlength="30"></div>
                            <div class="col-md-4"><label class="form-label">Email</label><input name="email"
                                    type="email" class="form-control"></div>
                            <div class="col-md-2"><label class="form-label">GSTIN</label><input name="gstin"
                                    class="form-control" maxlength="30"></div>
                            <div class="col-md-2"><label class="form-label">PAN</label><input name="pan"
                                    class="form-control" maxlength="20"></div>
                            <div class="col-md-2"><label class="form-label">Credit Limit</label><input
                                    name="credit_limit" type="number" min="0" step="0.01" value="0"
                                    class="form-control"></div>
                            <div class="col-md-2"><label class="form-label">Opening Balance</label><input
                                    name="opening_balance" type="number" min="0" step="0.01" value="0"
                                    class="form-control"></div>
                            <div class="col-md-3"><label class="form-label">Balance Type</label><select
                                    name="balance_type" class="form-select">
                                    <option value="{{ $partyType === 'customers' ? 'receivable' : 'payable' }}">
                                        {{ $partyType === 'customers' ? 'Receivable' : 'Payable' }}</option>
                                    <option value="{{ $partyType === 'customers' ? 'payable' : 'receivable' }}">
                                        {{ $partyType === 'customers' ? 'Payable' : 'Receivable' }}</option>
                                </select></div>
                            <div class="col-md-9"><label class="form-label">Billing Address</label><input name="address"
                                    class="form-control" maxlength="1000"></div>
                            <div class="col-md-4"><label class="form-label">City</label><input name="city"
                                    class="form-control" maxlength="100"></div>
                            <div class="col-md-4"><label class="form-label">State</label><input name="state"
                                    class="form-control" maxlength="100"></div>
                            <div class="col-md-4"><label class="form-label">Postal Code</label><input name="postal_code"
                                    class="form-control" maxlength="20"></div>
                            <div class="col-12"><input type="hidden" name="is_active" value="0">
                                <div class="form-check form-switch"><input id="party-active" name="is_active"
                                        type="checkbox" value="1" class="form-check-input" checked><label
                                        class="form-check-label" for="party-active">Active party</label></div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-success"><i
                                class="ri-save-line me-1"></i>Save Party</button></div>
                </form>
            </div>
        </div>
    </div>
    </div>
@endsection
@push('scripts')
    <script src="{{ asset('backend/assets/js/modules/parties.js') }}?v={{ filemtime(public_path('backend/assets/js/modules/parties.js')) }}"></script>
@endpush
