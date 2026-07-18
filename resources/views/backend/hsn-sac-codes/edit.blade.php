@extends('backend.layouts.app')

@php
    $module = json_decode('{"title":"HSN / SAC Codes","group":"Items","singular":"HSN / SAC Code","slug":"hsn-sac-codes","icon":"ri-barcode-box-line"}', true);
    $editing = true;
@endphp

@section('title', ($editing ? 'Edit ' : 'Create ') . $module['singular'] . ' | Cholavin ERP')

@section('content')
<div class="card erp-panel">
    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div><span class="erp-eyebrow">{{ $module['group'] }}</span><h4 class="mb-0">{{ $editing ? 'Edit' : 'Create' }} {{ $module['singular'] }}</h4></div>
        <a href="{{ route('admin.hsn-sac-codes.index') }}" class="btn btn-secondary"><i class="ri-arrow-left-line me-1"></i>Back</a>
    </div>
    <form action="{{ route('admin.hsn-sac-codes.update', $recordId) }}" method="POST" id="form-validate" data-index-url="{{ route('admin.'.$module['slug'].'.index') }}">
        @csrf
        @if($editing) @method('PUT') @endif
        <div class="card-body">
            <div class="row gy-4">
                <div class="col-xxl-3 col-md-6">
                    <div class="form-group">
                        <label class="form-label" for="code">HSN / SAC Code <span class="text-danger">*</span></label>
                        <input class="form-control reference-master-input code-field @error('code') is-invalid @enderror" id="code" name="code" type="text" value="{{ old('code', $values['code'] ?? '') }}" placeholder="1006" maxlength="190" required>
                        @error('code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-12">
                    <div class="form-group">
                        <label class="form-label" for="description">Description</label>
                        <textarea class="form-control reference-master-input description-field @error('description') is-invalid @enderror" id="description" name="description" rows="3" maxlength="2000" placeholder="Rice description">{{ old('description', $values['description'] ?? '') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-xxl-3 col-md-6">
                    <div class="form-group">
                        <label class="form-label" for="tax_rate">Tax Rate <span class="text-danger">*</span></label>
                        <select class="form-select reference-master-input tax-rate-field @error('tax_rate') is-invalid @enderror" id="tax_rate" name="tax_rate" required>
                            <option value="">-- Select --</option>
                            <option value="0%" @selected((string) old('tax_rate', $values['tax_rate'] ?? '') === '0%')>0%</option>
                            <option value="5%" @selected((string) old('tax_rate', $values['tax_rate'] ?? '') === '5%')>5%</option>
                            <option value="12%" @selected((string) old('tax_rate', $values['tax_rate'] ?? '') === '12%')>12%</option>
                            <option value="18%" @selected((string) old('tax_rate', $values['tax_rate'] ?? '') === '18%')>18%</option>
                            <option value="28%" @selected((string) old('tax_rate', $values['tax_rate'] ?? '') === '28%')>28%</option>
                        </select>
                        @error('tax_rate')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-xxl-3 col-md-6">
                    <div class="form-group">
                        <label class="form-label" for="type">Type <span class="text-danger">*</span></label>
                        <select class="form-select reference-master-input type-field @error('type') is-invalid @enderror" id="type" name="type" required>
                            <option value="">-- Select --</option>
                            <option value="Goods" @selected((string) old('type', $values['type'] ?? '') === 'Goods')>Goods</option>
                            <option value="Service" @selected((string) old('type', $values['type'] ?? '') === 'Service')>Service</option>
                        </select>
                        @error('type')
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
                <a href="{{ route('admin.hsn-sac-codes.index') }}" class="btn btn-secondary">Cancel</a>
                <button class="btn btn-primary" type="submit"><i class="ri-save-3-line me-1"></i>{{ $editing ? 'Update' : 'Save' }} {{ $module['singular'] }}</button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="{{ asset('backend/assets/js/modules/reference-master-form.js') }}?v={{ filemtime(public_path('backend/assets/js/modules/reference-master-form.js')) }}"></script>
@endpush
