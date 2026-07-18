@extends('backend.layouts.app')

@php
    $module = json_decode('{"title":"Invoice Sequences","group":"Accounting","singular":"Invoice Sequence","slug":"invoice-sequences","icon":"ri-sort-number-asc"}', true);
    $editing = true;
@endphp

@section('title', ($editing ? 'Edit ' : 'Create ') . $module['singular'] . ' | Cholavin ERP')

@section('content')
<div class="card erp-panel">
    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div><span class="erp-eyebrow">{{ $module['group'] }}</span><h4 class="mb-0">{{ $editing ? 'Edit' : 'Create' }} {{ $module['singular'] }}</h4></div>
        <a href="{{ route('admin.invoice-sequences.index') }}" class="btn btn-secondary"><i class="ri-arrow-left-line me-1"></i>Back</a>
    </div>
    <form action="{{ route('admin.invoice-sequences.update', $recordId) }}" method="POST" id="form-validate" data-index-url="{{ route('admin.'.$module['slug'].'.index') }}">
        @csrf
        @if($editing) @method('PUT') @endif
        <div class="card-body">
            <div class="row gy-4">
                <div class="col-xxl-3 col-md-6">
                    <div class="form-group">
                        <label class="form-label" for="name">Sequence Name <span class="text-danger">*</span></label>
                        <input class="form-control reference-master-input name-field @error('name') is-invalid @enderror" id="name" name="name" type="text" value="{{ old('name', $values['name'] ?? '') }}" placeholder="Sales Invoice Chennai" maxlength="190" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-xxl-3 col-md-6">
                    <div class="form-group">
                        <label class="form-label" for="prefix">Prefix <span class="text-danger">*</span></label>
                        <input class="form-control reference-master-input prefix-field @error('prefix') is-invalid @enderror" id="prefix" name="prefix" type="text" value="{{ old('prefix', $values['prefix'] ?? '') }}" placeholder="CHN-SI-" maxlength="190" required>
                        @error('prefix')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-xxl-3 col-md-6">
                    <div class="form-group">
                        <label class="form-label" for="next_number">Next Number <span class="text-danger">*</span></label>
                        <input class="form-control reference-master-input next-number-field @error('next_number') is-invalid @enderror" id="next_number" name="next_number" type="number" value="{{ old('next_number', $values['next_number'] ?? '') }}" placeholder="1001" min="0" step="any" required>
                        @error('next_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-xxl-3 col-md-6">
                    <div class="form-group">
                        <label class="form-label" for="padding">Padding <span class="text-danger">*</span></label>
                        <input class="form-control reference-master-input padding-field @error('padding') is-invalid @enderror" id="padding" name="padding" type="number" value="{{ old('padding', $values['padding'] ?? '') }}" placeholder="4" min="0" step="any" required>
                        @error('padding')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-xxl-3 col-md-6">
                    <div class="form-group">
                        <label class="form-label" for="reset_period">Reset Period <span class="text-danger">*</span></label>
                        <select class="form-select reference-master-input reset-period-field @error('reset_period') is-invalid @enderror" id="reset_period" name="reset_period" required>
                            <option value="">-- Select --</option>
                            <option value="Never" @selected((string) old('reset_period', $values['reset_period'] ?? '') === 'Never')>Never</option>
                            <option value="Monthly" @selected((string) old('reset_period', $values['reset_period'] ?? '') === 'Monthly')>Monthly</option>
                            <option value="Financial Year" @selected((string) old('reset_period', $values['reset_period'] ?? '') === 'Financial Year')>Financial Year</option>
                        </select>
                        @error('reset_period')
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
                <a href="{{ route('admin.invoice-sequences.index') }}" class="btn btn-secondary">Cancel</a>
                <button class="btn btn-primary" type="submit"><i class="ri-save-3-line me-1"></i>{{ $editing ? 'Update' : 'Save' }} {{ $module['singular'] }}</button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="{{ asset('backend/assets/js/modules/reference-master-form.js') }}?v={{ filemtime(public_path('backend/assets/js/modules/reference-master-form.js')) }}"></script>
@endpush
