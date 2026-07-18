@extends('backend.layouts.app')

@php
    $module = json_decode('{"title":"Bank Accounts","group":"Cash, Bank & Assets","singular":"Bank Account","slug":"bank-accounts","icon":"ri-bank-line"}', true);
    $editing = true;
@endphp

@section('title', ($editing ? 'Edit ' : 'Create ') . $module['singular'] . ' | Cholavin ERP')

@section('content')
<div class="card erp-panel">
    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div><span class="erp-eyebrow">{{ $module['group'] }}</span><h4 class="mb-0">{{ $editing ? 'Edit' : 'Create' }} {{ $module['singular'] }}</h4></div>
        <a href="{{ route('admin.bank-accounts.index') }}" class="btn btn-secondary"><i class="ri-arrow-left-line me-1"></i>Back</a>
    </div>
    <form action="{{ route('admin.bank-accounts.update', $recordId) }}" method="POST" id="form-validate" data-index-url="{{ route('admin.'.$module['slug'].'.index') }}">
        @csrf
        @if($editing) @method('PUT') @endif
        <div class="card-body">
            <div class="row gy-4">
                <div class="col-xxl-3 col-md-6">
                    <div class="form-group">
                        <label class="form-label" for="bank_name">Bank Name <span class="text-danger">*</span></label>
                        <input class="form-control reference-master-input bank-name-field @error('bank_name') is-invalid @enderror" id="bank_name" name="bank_name" type="text" value="{{ old('bank_name', $values['bank_name'] ?? '') }}" placeholder="HDFC Bank" maxlength="190" required>
                        @error('bank_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-xxl-3 col-md-6">
                    <div class="form-group">
                        <label class="form-label" for="account_name">Account Name <span class="text-danger">*</span></label>
                        <input class="form-control reference-master-input account-name-field @error('account_name') is-invalid @enderror" id="account_name" name="account_name" type="text" value="{{ old('account_name', $values['account_name'] ?? '') }}" placeholder="Cholavin Foods" maxlength="190" required>
                        @error('account_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-xxl-3 col-md-6">
                    <div class="form-group">
                        <label class="form-label" for="account_number">Account Number <span class="text-danger">*</span></label>
                        <input class="form-control reference-master-input account-number-field @error('account_number') is-invalid @enderror" id="account_number" name="account_number" type="text" value="{{ old('account_number', $values['account_number'] ?? '') }}" placeholder="Enter account number" maxlength="190" required>
                        @error('account_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-xxl-3 col-md-6">
                    <div class="form-group">
                        <label class="form-label" for="ifsc_code">IFSC Code <span class="text-danger">*</span></label>
                        <input class="form-control reference-master-input ifsc-code-field @error('ifsc_code') is-invalid @enderror" id="ifsc_code" name="ifsc_code" type="text" value="{{ old('ifsc_code', $values['ifsc_code'] ?? '') }}" placeholder="HDFC0001234" maxlength="190" required>
                        @error('ifsc_code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-xxl-3 col-md-6">
                    <div class="form-group">
                        <label class="form-label" for="branch">Branch <span class="text-danger">*</span></label>
                        <input class="form-control reference-master-input branch-field @error('branch') is-invalid @enderror" id="branch" name="branch" type="text" value="{{ old('branch', $values['branch'] ?? '') }}" placeholder="Chennai" maxlength="190" required>
                        @error('branch')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-xxl-3 col-md-6">
                    <div class="form-group">
                        <label class="form-label" for="opening_balance">Opening Balance <span class="text-danger">*</span></label>
                        <input class="form-control reference-master-input opening-balance-field @error('opening_balance') is-invalid @enderror" id="opening_balance" name="opening_balance" type="number" value="{{ old('opening_balance', $values['opening_balance'] ?? '') }}" placeholder="0.00" min="0" step="any" required>
                        @error('opening_balance')
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
            </div>
            <div class="mt-4 d-flex justify-content-end gap-2">
                <a href="{{ route('admin.bank-accounts.index') }}" class="btn btn-secondary">Cancel</a>
                <button class="btn btn-primary" type="submit"><i class="ri-save-3-line me-1"></i>{{ $editing ? 'Update' : 'Save' }} {{ $module['singular'] }}</button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="{{ asset('backend/assets/js/modules/reference-master-form.js') }}?v={{ filemtime(public_path('backend/assets/js/modules/reference-master-form.js')) }}"></script>
@endpush
