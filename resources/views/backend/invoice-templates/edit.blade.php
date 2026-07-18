@extends('backend.layouts.app')

@php
    $module = json_decode('{"title":"Invoice Templates","group":"Administration","singular":"Invoice Template","slug":"invoice-templates","icon":"ri-layout-4-line"}', true);
    $editing = true;
@endphp

@section('title', ($editing ? 'Edit ' : 'Create ') . $module['singular'] . ' | Cholavin ERP')

@section('content')
<div class="card erp-panel">
    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div><span class="erp-eyebrow">{{ $module['group'] }}</span><h4 class="mb-0">{{ $editing ? 'Edit' : 'Create' }} {{ $module['singular'] }}</h4></div>
        <a href="{{ route('admin.invoice-templates.index') }}" class="btn btn-secondary"><i class="ri-arrow-left-line me-1"></i>Back</a>
    </div>
    <form action="{{ route('admin.invoice-templates.update', $recordId) }}" method="POST" id="form-validate" data-index-url="{{ route('admin.'.$module['slug'].'.index') }}">
        @csrf
        @if($editing) @method('PUT') @endif
        <div class="card-body">
            <div class="row gy-4">
                <div class="col-xxl-3 col-md-6">
                    <div class="form-group">
                        <label class="form-label" for="name">Template Name <span class="text-danger">*</span></label>
                        <input class="form-control reference-master-input name-field @error('name') is-invalid @enderror" id="name" name="name" type="text" value="{{ old('name', $values['name'] ?? '') }}" placeholder="Retail Invoice" maxlength="190" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-xxl-3 col-md-6">
                    <div class="form-group">
                        <label class="form-label" for="template_type">Template Type <span class="text-danger">*</span></label>
                        <select class="form-select reference-master-input template-type-field @error('template_type') is-invalid @enderror" id="template_type" name="template_type" required>
                            <option value="">-- Select --</option>
                            <option value="Sales" @selected((string) old('template_type', $values['template_type'] ?? '') === 'Sales')>Sales</option>
                            <option value="Purchase" @selected((string) old('template_type', $values['template_type'] ?? '') === 'Purchase')>Purchase</option>
                            <option value="POS" @selected((string) old('template_type', $values['template_type'] ?? '') === 'POS')>POS</option>
                            <option value="Delivery" @selected((string) old('template_type', $values['template_type'] ?? '') === 'Delivery')>Delivery</option>
                        </select>
                        @error('template_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-xxl-3 col-md-6">
                    <div class="form-group">
                        <label class="form-label" for="paper_size">Paper Size <span class="text-danger">*</span></label>
                        <select class="form-select reference-master-input paper-size-field @error('paper_size') is-invalid @enderror" id="paper_size" name="paper_size" required>
                            <option value="">-- Select --</option>
                            <option value="A4" @selected((string) old('paper_size', $values['paper_size'] ?? '') === 'A4')>A4</option>
                            <option value="A5" @selected((string) old('paper_size', $values['paper_size'] ?? '') === 'A5')>A5</option>
                            <option value="Thermal 80mm" @selected((string) old('paper_size', $values['paper_size'] ?? '') === 'Thermal 80mm')>Thermal 80mm</option>
                            <option value="Thermal 58mm" @selected((string) old('paper_size', $values['paper_size'] ?? '') === 'Thermal 58mm')>Thermal 58mm</option>
                        </select>
                        @error('paper_size')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-xxl-3 col-md-6">
                    <div class="form-group">
                        <label class="form-label" for="header_text">Header Text</label>
                        <input class="form-control reference-master-input header-text-field @error('header_text') is-invalid @enderror" id="header_text" name="header_text" type="text" value="{{ old('header_text', $values['header_text'] ?? '') }}" placeholder="Invoice header" maxlength="190">
                        @error('header_text')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-12">
                    <div class="form-group">
                        <label class="form-label" for="footer_text">Footer Text</label>
                        <textarea class="form-control reference-master-input footer-text-field @error('footer_text') is-invalid @enderror" id="footer_text" name="footer_text" rows="3" maxlength="2000" placeholder="Terms and footer">{{ old('footer_text', $values['footer_text'] ?? '') }}</textarea>
                        @error('footer_text')
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
                <a href="{{ route('admin.invoice-templates.index') }}" class="btn btn-secondary">Cancel</a>
                <button class="btn btn-primary" type="submit"><i class="ri-save-3-line me-1"></i>{{ $editing ? 'Update' : 'Save' }} {{ $module['singular'] }}</button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="{{ asset('backend/assets/js/modules/reference-master-form.js') }}?v={{ filemtime(public_path('backend/assets/js/modules/reference-master-form.js')) }}"></script>
@endpush
