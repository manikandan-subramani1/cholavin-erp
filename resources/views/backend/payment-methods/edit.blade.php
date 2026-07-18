@extends('backend.layouts.app')

@php
    $module = json_decode('{"title":"Payment Methods","group":"Cash, Bank & Assets","singular":"Payment Method","slug":"payment-methods","icon":"ri-bank-card-line"}', true);
    $editing = true;
@endphp

@section('title', ($editing ? 'Edit ' : 'Create ') . $module['singular'] . ' | Cholavin ERP')

@section('content')
<div class="card erp-panel">
    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div><span class="erp-eyebrow">{{ $module['group'] }}</span><h4 class="mb-0">{{ $editing ? 'Edit' : 'Create' }} {{ $module['singular'] }}</h4></div>
        <a href="{{ route('admin.payment-methods.index') }}" class="btn btn-secondary"><i class="ri-arrow-left-line me-1"></i>Back</a>
    </div>
    <form action="{{ route('admin.payment-methods.update', $recordId) }}" method="POST" id="form-validate" data-index-url="{{ route('admin.'.$module['slug'].'.index') }}">
        @csrf
        @if($editing) @method('PUT') @endif
        <div class="card-body">
            <div class="row gy-4">
                <div class="col-xxl-3 col-md-6">
                    <div class="form-group">
                        <label class="form-label" for="name">Method Name <span class="text-danger">*</span></label>
                        <input class="form-control reference-master-input name-field @error('name') is-invalid @enderror" id="name" name="name" type="text" value="{{ old('name', $values['name'] ?? '') }}" placeholder="UPI" maxlength="190" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-xxl-3 col-md-6">
                    <div class="form-group">
                        <label class="form-label" for="code">Code <span class="text-danger">*</span></label>
                        <input class="form-control reference-master-input code-field @error('code') is-invalid @enderror" id="code" name="code" type="text" value="{{ old('code', $values['code'] ?? '') }}" placeholder="UPI" maxlength="190" required>
                        @error('code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-xxl-3 col-md-6">
                    <div class="form-group">
                        <label class="form-label" for="settlement_account">Settlement Account <span class="text-danger">*</span></label>
                        <input class="form-control reference-master-input settlement-account-field @error('settlement_account') is-invalid @enderror" id="settlement_account" name="settlement_account" type="text" value="{{ old('settlement_account', $values['settlement_account'] ?? '') }}" placeholder="Default bank/cash account" maxlength="190" required>
                        @error('settlement_account')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-xxl-3 col-md-6">
                    <div class="form-group">
                        <label class="form-label" for="charges_percent">Charges % <span class="text-danger">*</span></label>
                        <input class="form-control reference-master-input charges-percent-field @error('charges_percent') is-invalid @enderror" id="charges_percent" name="charges_percent" type="number" value="{{ old('charges_percent', $values['charges_percent'] ?? '') }}" placeholder="0" min="0" step="any" required>
                        @error('charges_percent')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-xxl-3 col-md-6">
                    <div class="form-group">
                        <div id="status-label" class="form-label mb-0">Status <span class="text-danger">*</span></div>
                        <div class="reference-status-options d-flex flex-wrap align-items-center gap-3 pt-2" role="radiogroup" aria-labelledby="status-label">
                            <div class="form-check form-check-inline mb-0">
                                <input class="form-check-input reference-master-input status-field @error('status') is-invalid @enderror" id="status_active" name="status" type="radio" value="Active" @checked((string) old('status', $values['status'] ?? 'Active') === 'Active') required>
                                <label class="form-check-label" for="status_active">Active</label>
                            </div>
                            <div class="form-check form-check-inline mb-0">
                                <input class="form-check-input reference-master-input status-field @error('status') is-invalid @enderror" id="status_inactive" name="status" type="radio" value="Inactive" @checked((string) old('status', $values['status'] ?? 'Active') === 'Inactive')>
                                <label class="form-check-label" for="status_inactive">Inactive</label>
                            </div>
                        </div>
                        @error('status')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-12">
                    <div class="form-group">
                        <label class="form-label" for="notes">Notes</label>
                        <textarea class="form-control reference-master-input notes-field @error('notes') is-invalid @enderror" id="notes" name="notes" rows="3" maxlength="2000" placeholder="Usage notes">{{ old('notes', $values['notes'] ?? '') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            <div class="mt-4 d-flex justify-content-end gap-2">
                <a href="{{ route('admin.payment-methods.index') }}" class="btn btn-secondary">Cancel</a>
                <button class="btn btn-primary" type="submit"><i class="ri-save-3-line me-1"></i>{{ $editing ? 'Update' : 'Save' }} {{ $module['singular'] }}</button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="{{ asset('backend/assets/js/modules/reference-master-form.js') }}?v={{ filemtime(public_path('backend/assets/js/modules/reference-master-form.js')) }}"></script>
@endpush
