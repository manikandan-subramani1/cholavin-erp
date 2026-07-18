@extends('backend.layouts.app')

@php
    $module = json_decode('{"title":"Price Lists","group":"Items","singular":"Price List","slug":"price-lists","icon":"ri-money-rupee-circle-line"}', true);
    $editing = true;
@endphp

@section('title', ($editing ? 'Edit ' : 'Create ') . $module['singular'] . ' | Cholavin ERP')

@section('content')
<div class="card erp-panel">
    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div><span class="erp-eyebrow">{{ $module['group'] }}</span><h4 class="mb-0">{{ $editing ? 'Edit' : 'Create' }} {{ $module['singular'] }}</h4></div>
        <a href="{{ route('admin.price-lists.index') }}" class="btn btn-secondary"><i class="ri-arrow-left-line me-1"></i>Back</a>
    </div>
    <form action="{{ route('admin.price-lists.update', $recordId) }}" method="POST" id="form-validate" data-index-url="{{ route('admin.'.$module['slug'].'.index') }}">
        @csrf
        @if($editing) @method('PUT') @endif
        <div class="card-body">
            <div class="row gy-4">
                <div class="col-xxl-3 col-md-6">
                    <div class="form-group">
                        <label class="form-label" for="name">Price List Name <span class="text-danger">*</span></label>
                        <input class="form-control reference-master-input name-field @error('name') is-invalid @enderror" id="name" name="name" type="text" value="{{ old('name', $values['name'] ?? '') }}" placeholder="Retail Price" maxlength="190" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-xxl-3 col-md-6">
                    <div class="form-group">
                        <label class="form-label" for="customer_type">Customer Type <span class="text-danger">*</span></label>
                        <select class="form-select reference-master-input customer-type-field @error('customer_type') is-invalid @enderror" id="customer_type" name="customer_type" required>
                            <option value="">-- Select --</option>
                            <option value="Retail" @selected((string) old('customer_type', $values['customer_type'] ?? '') === 'Retail')>Retail</option>
                            <option value="Wholesale" @selected((string) old('customer_type', $values['customer_type'] ?? '') === 'Wholesale')>Wholesale</option>
                            <option value="Distributor" @selected((string) old('customer_type', $values['customer_type'] ?? '') === 'Distributor')>Distributor</option>
                        </select>
                        @error('customer_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-xxl-3 col-md-6">
                    <div class="form-group">
                        <label class="form-label" for="effective_from">Effective From <span class="text-danger">*</span></label>
                        <input class="form-control reference-master-input effective-from-field @error('effective_from') is-invalid @enderror" id="effective_from" name="effective_from" type="date" value="{{ old('effective_from', $values['effective_from'] ?? '') }}" placeholder="" maxlength="190" required>
                        @error('effective_from')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-xxl-3 col-md-6">
                    <div class="form-group">
                        <label class="form-label" for="effective_to">Effective To <span class="text-danger">*</span></label>
                        <input class="form-control reference-master-input effective-to-field @error('effective_to') is-invalid @enderror" id="effective_to" name="effective_to" type="date" value="{{ old('effective_to', $values['effective_to'] ?? '') }}" placeholder="" maxlength="190" required>
                        @error('effective_to')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-xxl-3 col-md-6">
                    <div class="form-group">
                        <label class="form-label" for="margin_percent">Margin % <span class="text-danger">*</span></label>
                        <input class="form-control reference-master-input margin-percent-field @error('margin_percent') is-invalid @enderror" id="margin_percent" name="margin_percent" type="number" value="{{ old('margin_percent', $values['margin_percent'] ?? '') }}" placeholder="10" min="0" step="any" required>
                        @error('margin_percent')
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
                <a href="{{ route('admin.price-lists.index') }}" class="btn btn-secondary">Cancel</a>
                <button class="btn btn-primary" type="submit"><i class="ri-save-3-line me-1"></i>{{ $editing ? 'Update' : 'Save' }} {{ $module['singular'] }}</button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="{{ asset('backend/assets/js/modules/reference-master-form.js') }}?v={{ filemtime(public_path('backend/assets/js/modules/reference-master-form.js')) }}"></script>
@endpush
