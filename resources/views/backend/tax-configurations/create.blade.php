@extends('backend.layouts.app')

@php
    $module = json_decode('{"title":"Tax Configuration","group":"Accounting","singular":"Tax Configuration","slug":"tax-configurations","icon":"ri-percent-line"}', true);
    $editing = false;
@endphp

@section('title', ($editing ? 'Edit ' : 'Create ') . $module['singular'] . ' | Cholavin ERP')

@section('content')
<div class="card erp-panel">
    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div><span class="erp-eyebrow">{{ $module['group'] }}</span><h4 class="mb-0">{{ $editing ? 'Edit' : 'Create' }} {{ $module['singular'] }}</h4></div>
        <a href="{{ route('admin.tax-configurations.index') }}" class="btn btn-secondary"><i class="ri-arrow-left-line me-1"></i>Back</a>
    </div>
    <form action="{{ route('admin.tax-configurations.store') }}" method="POST" id="form-validate" data-index-url="{{ route('admin.'.$module['slug'].'.index') }}">
        @csrf
        @if($editing) @method('PUT') @endif
        <div class="card-body">
            <div class="row gy-4">
                <div class="col-xxl-3 col-md-6">
                    <div class="form-group">
                        <label class="form-label" for="name">Configuration Name <span class="text-danger">*</span></label>
                        <input class="form-control reference-master-input name-field @error('name') is-invalid @enderror" id="name" name="name" type="text" value="{{ old('name', $values['name'] ?? '') }}" placeholder="GST Regular" maxlength="190" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-xxl-3 col-md-6">
                    <div class="form-group">
                        <label class="form-label" for="tax_type">Tax Type <span class="text-danger">*</span></label>
                        <select class="form-select reference-master-input tax-type-field @error('tax_type') is-invalid @enderror" id="tax_type" name="tax_type" required>
                            <option value="">-- Select --</option>
                            <option value="GST" @selected((string) old('tax_type', $values['tax_type'] ?? '') === 'GST')>GST</option>
                            <option value="IGST" @selected((string) old('tax_type', $values['tax_type'] ?? '') === 'IGST')>IGST</option>
                            <option value="CESS" @selected((string) old('tax_type', $values['tax_type'] ?? '') === 'CESS')>CESS</option>
                            <option value="None" @selected((string) old('tax_type', $values['tax_type'] ?? '') === 'None')>None</option>
                        </select>
                        @error('tax_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-xxl-3 col-md-6">
                    <div class="form-group">
                        <label class="form-label" for="registration_number">Registration Number <span class="text-danger">*</span></label>
                        <input class="form-control reference-master-input registration-number-field @error('registration_number') is-invalid @enderror" id="registration_number" name="registration_number" type="text" value="{{ old('registration_number', $values['registration_number'] ?? '') }}" placeholder="GSTIN" maxlength="190" required>
                        @error('registration_number')
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
                        <label class="form-label" for="default_rate">Default Tax Rate <span class="text-danger">*</span></label>
                        <input class="form-control reference-master-input default-rate-field @error('default_rate') is-invalid @enderror" id="default_rate" name="default_rate" type="number" value="{{ old('default_rate', $values['default_rate'] ?? '') }}" placeholder="18" min="0" step="any" required>
                        @error('default_rate')
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
                <a href="{{ route('admin.tax-configurations.index') }}" class="btn btn-secondary">Cancel</a>
                <button class="btn btn-primary" type="submit"><i class="ri-save-3-line me-1"></i>{{ $editing ? 'Update' : 'Save' }} {{ $module['singular'] }}</button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="{{ asset('backend/assets/js/modules/reference-master-form.js') }}?v={{ filemtime(public_path('backend/assets/js/modules/reference-master-form.js')) }}"></script>
@endpush
