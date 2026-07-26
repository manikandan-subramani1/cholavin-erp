@extends('backend.layouts.app')

@section('title', $module['title'].' | Cholavin ERP')

@php
    $kpiValues = ['₹ 1,24,580', '320', '₹ 8,65,230', '48', '12'];
    $rows = [
        ['name' => 'Cholavin Premium Ponni Rice', 'code' => 'ITEM-0001', 'value' => '₹ 1,550', 'status' => 'Active'],
        ['name' => 'Sri Balaji Traders', 'code' => 'CUS-0001', 'value' => '₹ 45,690', 'status' => 'Active'],
        ['name' => 'Main Godown → Namakkal Shop', 'code' => 'TRF-00025', 'value' => '50 Bags', 'status' => 'Pending'],
        ['name' => 'INV-2025-00125', 'code' => '31 May 2025', 'value' => '₹ 6,990', 'status' => 'Paid'],
        ['name' => 'Karthik Enterprises', 'code' => 'SUP-0004', 'value' => '₹ 25,000', 'status' => 'Due'],
    ];
@endphp

<div class="cholavin-page-head">
    <div><span class="cholavin-eyebrow">Module {{ $module['number'] }} · {{ $module['group'] }}</span><h1>{{ $module['title'] }}</h1><p>{{ $module['description'] }}</p></div>
    <div class="d-flex flex-wrap gap-2"><button type="button" class="cholavin-outline-button" data-toast="Export will be connected to the module backend."><i class="ri-download-2-line"></i> Export</button><button type="button" class="cholavin-brand-button" data-bs-toggle="modal" data-bs-target="#workspaceFormModal"><i class="ri-add-line"></i> New {{ $module['title'] }}</button></div>
</div>

<div class="cholavin-kpi-grid" style="grid-template-columns:repeat({{ min(count($module['kpis']), 5) }}, minmax(0,1fr));">
    @foreach($module['kpis'] as $index => $kpi)<div class="cholavin-kpi"><span class="cholavin-kpi-icon"><i class="{{ $module['icon'] }}"></i></span><small>{{ $kpi }}</small><strong>{{ $kpiValues[$index % count($kpiValues)] }}</strong><em>{{ $index % 3 === 2 ? 'Needs attention' : '↑ 8.6% vs last period' }}</em></div>@endforeach
</div>

<div class="cholavin-tabs" role="tablist">@foreach($module['tabs'] as $index => $tab)<button class="cholavin-tab {{ $index === 0 ? 'active' : '' }}" type="button" role="tab">{{ $tab }}</button>@endforeach</div>

<div class="cholavin-filterbar">
    <label class="cholavin-search"><i class="ri-search-line"></i><input type="search" data-smart-search placeholder="Search {{ strtolower($module['title']) }}..." aria-label="Search {{ $module['title'] }}"></label>
    <select aria-label="Status filter"><option>All Status</option><option>Active</option><option>Pending</option><option>Completed</option></select>
    <select aria-label="Period filter"><option>This Month</option><option>This Week</option><option>Last 30 Days</option></select>
    <button type="button" class="cholavin-outline-button ms-auto" data-toast="Smart filters are ready for AJAX wiring."><i class="ri-equalizer-2-line"></i> More filters</button>
</div>

<div class="cholavin-grid-2">
    <section class="cholavin-panel"><div class="cholavin-panel-head"><div><span class="cholavin-eyebrow">Server-side workspace</span><h2>{{ $module['title'] }} register</h2></div><span class="cholavin-status">AJAX ready</span></div><div class="cholavin-table-wrap"><table class="cholavin-table"><thead><tr><th>Record</th><th>Reference</th><th>Amount / Qty</th><th>Status</th><th>Action</th></tr></thead><tbody>@foreach($rows as $row)<tr data-filter-row><td><strong>{{ $row['name'] }}</strong></td><td>{{ $row['code'] }}</td><td>{{ $row['value'] }}</td><td><span class="cholavin-status {{ in_array($row['status'], ['Pending', 'Due'], true) ? 'pending' : '' }}">{{ $row['status'] }}</span></td><td><button class="cholavin-row-action" type="button" data-drawer-record="{{ $row['name'] }}" aria-label="View {{ $row['name'] }}"><i class="ri-arrow-right-up-line"></i></button></td></tr>@endforeach</tbody></table></div><div class="cholavin-mobile-cards">@foreach($rows as $row)<article class="cholavin-mobile-card" data-filter-row><div class="cholavin-mobile-card-head"><strong>{{ $row['name'] }}</strong><span class="cholavin-status {{ in_array($row['status'], ['Pending', 'Due'], true) ? 'pending' : '' }}">{{ $row['status'] }}</span></div><p>{{ $row['code'] }} · {{ $row['value'] }} <button class="cholavin-row-action float-end" type="button" data-drawer-record="{{ $row['name'] }}"><i class="ri-arrow-right-up-line"></i></button></p></article>@endforeach</div><div class="d-flex justify-content-between align-items-center p-3 border-top"><small class="text-muted">Showing 1 to {{ count($rows) }} of 128 records</small><div class="d-flex gap-1"><button class="cholavin-row-action" type="button" data-toast="Previous page"><i class="ri-arrow-left-s-line"></i></button><button class="cholavin-row-action" type="button" style="background:var(--cholavin-maroon);color:#fff">1</button><button class="cholavin-row-action" type="button" data-toast="Next page"><i class="ri-arrow-right-s-line"></i></button></div></div></section>
    <div class="d-grid gap-3 align-content-start"><section class="cholavin-panel"><div class="cholavin-panel-head"><div><span class="cholavin-eyebrow">Module actions</span><h2>Quick actions</h2></div></div><div class="cholavin-panel-body"><div class="cholavin-action-grid"><button class="cholavin-action" type="button" data-drawer-record="Create {{ $module['title'] }}"><i class="ri-add-box-line"></i><span>Create</span></button><button class="cholavin-action" type="button" data-toast="Edit flow is permission checked."><i class="ri-edit-line"></i><span>Edit</span></button><button class="cholavin-action" type="button" data-toast="Print flow is permission checked."><i class="ri-printer-line"></i><span>Print</span></button><button class="cholavin-action" type="button" data-toast="Status flow is permission checked."><i class="ri-refresh-line"></i><span>Status</span></button><button class="cholavin-action" type="button" data-toast="Audit timeline loaded."><i class="ri-history-line"></i><span>Audit</span></button><button class="cholavin-action" type="button" data-toast="This action will be connected to the rebuilt service."><i class="ri-more-2-line"></i><span>More</span></button></div></div></section><section class="cholavin-panel"><div class="cholavin-panel-head"><div><span class="cholavin-eyebrow">Activity timeline</span><h2>Recent changes</h2></div></div><div class="cholavin-panel-body"><div class="cholavin-timeline"><div class="cholavin-timeline-item"><i class="ri-eye-line"></i><div><strong>Workspace opened</strong><small>Scope checked against user, location, and module.</small></div></div><div class="cholavin-timeline-item"><i class="ri-filter-3-line"></i><div><strong>Filters preserved</strong><small>Search, status, pagination, and mobile cards are ready.</small></div></div><div class="cholavin-timeline-item"><i class="ri-shield-check-line"></i><div><strong>Security boundary active</strong><small>Authentication and dashboard permission middleware applied.</small></div></div></div></div></section></div>
</div>

<section class="cholavin-panel cholavin-section"><div class="cholavin-panel-head"><div><span class="cholavin-eyebrow">Implementation contract</span><h2>AJAX response handling for this module</h2></div></div><div class="cholavin-panel-body"><div class="row g-3"><div class="col-md-6 col-xl-3"><span class="cholavin-status">success: true</span><p class="small text-muted mt-2 mb-0">Toast message and optional refresh instructions.</p></div><div class="col-md-6 col-xl-3"><span class="cholavin-status pending">datatable: true</span><p class="small text-muted mt-2 mb-0">Server-side table reload remains independent.</p></div><div class="col-md-6 col-xl-3"><span class="cholavin-status">drawer: false</span><p class="small text-muted mt-2 mb-0">Drawer state stays open unless requested.</p></div><div class="col-md-6 col-xl-3"><span class="cholavin-status">audit: ready</span><p class="small text-muted mt-2 mb-0">Actions will be logged as services are rebuilt.</p></div></div></div></section>

<div class="modal fade" id="workspaceFormModal" tabindex="-1" aria-labelledby="workspaceFormModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form class="modal-content needs-validation" id="workspace-form" novalidate>
            <div class="modal-header">
                <div>
                    <span class="cholavin-eyebrow">Velzon form modal</span>
                    <h5 class="modal-title" id="workspaceFormModalLabel">New {{ $module['title'] }}</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-soft-primary d-flex gap-2 align-items-start">
                    <i class="ri-information-line fs-18"></i>
                    <span>This standard form popup is ready for the module-specific AJAX endpoint and permission policy.</span>
                </div>
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label" for="workspace-record-name">Name / reference <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="workspace-record-name" name="name" required>
                        <div class="invalid-feedback">Enter a name or reference.</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="workspace-record-status">Status</label>
                        <select class="form-select" id="workspace-record-status" name="status">
                            <option>Draft</option>
                            <option>Active</option>
                            <option>Pending</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="workspace-record-date">Date</label>
                        <input type="date" class="form-control" id="workspace-record-date" name="date" value="{{ now()->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="workspace-record-amount">Amount / quantity</label>
                        <input type="number" class="form-control" id="workspace-record-amount" name="amount" min="0" step="0.01">
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="workspace-record-notes">Notes</label>
                        <textarea class="form-control" id="workspace-record-notes" name="notes" rows="3" placeholder="Add a note for the activity timeline..."></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="ri-save-line me-1"></i> Save {{ $module['title'] }}</button>
            </div>
        </form>
    </div>
</div>
