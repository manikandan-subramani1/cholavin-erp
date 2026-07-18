@extends('backend.layouts.app')

@php
    $module = json_decode('{"title":"Drivers","group":"Delivery","singular":"Driver","slug":"drivers","icon":"ri-steering-2-line"}', true);
    $editing = true;
@endphp

@section('title', ($editing ? 'Edit ' : 'Create ') . $module['singular'] . ' | Cholavin ERP')

@section('content')
<div class="card erp-panel">
    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div><span class="erp-eyebrow">{{ $module['group'] }}</span><h4 class="mb-0">{{ $editing ? 'Edit' : 'Create' }} {{ $module['singular'] }}</h4></div>
        <a href="{{ route('admin.drivers.index') }}" class="btn btn-secondary"><i class="ri-arrow-left-line me-1"></i>Back</a>
    </div>
    <form action="{{ route('admin.drivers.update', $recordId) }}" method="POST" id="form-validate" data-index-url="{{ route('admin.'.$module['slug'].'.index') }}">
        @csrf
        @if($editing) @method('PUT') @endif
        <div class="card-body">
            <div class="row gy-4">
                <div class="col-xxl-3 col-md-6">
                    <div class="form-group">
                        <label class="form-label" for="name">Driver Name <span class="text-danger">*</span></label>
                        <input class="form-control reference-master-input name-field @error('name') is-invalid @enderror" id="name" name="name" type="text" value="{{ old('name', $values['name'] ?? '') }}" placeholder="Driver name" maxlength="190" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-xxl-3 col-md-6">
                    <div class="form-group">
                        <label class="form-label" for="mobile">Mobile Number <span class="text-danger">*</span></label>
                        <input class="form-control reference-master-input mobile-field @error('mobile') is-invalid @enderror" id="mobile" name="mobile" type="text" value="{{ old('mobile', $values['mobile'] ?? '') }}" placeholder="Mobile" maxlength="190" required>
                        @error('mobile')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-xxl-3 col-md-6">
                    <div class="form-group">
                        <label class="form-label" for="license_number">License Number <span class="text-danger">*</span></label>
                        <input class="form-control reference-master-input license-number-field @error('license_number') is-invalid @enderror" id="license_number" name="license_number" type="text" value="{{ old('license_number', $values['license_number'] ?? '') }}" placeholder="DL number" maxlength="190" required>
                        @error('license_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-xxl-3 col-md-6">
                    <div class="form-group">
                        <label class="form-label" for="license_expiry">License Expiry <span class="text-danger">*</span></label>
                        <input class="form-control reference-master-input license-expiry-field @error('license_expiry') is-invalid @enderror" id="license_expiry" name="license_expiry" type="date" value="{{ old('license_expiry', $values['license_expiry'] ?? '') }}" placeholder="" maxlength="190" required>
                        @error('license_expiry')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-xxl-3 col-md-6">
                    <div class="form-group">
                        <label class="form-label" for="assigned_vehicle">Assigned Vehicle <span class="text-danger">*</span></label>
                        <input class="form-control reference-master-input assigned-vehicle-field @error('assigned_vehicle') is-invalid @enderror" id="assigned_vehicle" name="assigned_vehicle" type="text" value="{{ old('assigned_vehicle', $values['assigned_vehicle'] ?? '') }}" placeholder="Vehicle number" maxlength="190" required>
                        @error('assigned_vehicle')
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
                            <div class="form-check form-check-inline mb-0">
                                <input class="form-check-input reference-master-input status-field @error('status') is-invalid @enderror" id="status_on_leave" name="status" type="radio" value="On Leave" @checked((string) old('status', $values['status'] ?? 'Active') === 'On Leave')>
                                <label class="form-check-label" for="status_on_leave">On Leave</label>
                            </div>
                        </div>
                        @error('status')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            <div class="mt-4 d-flex justify-content-end gap-2">
                <a href="{{ route('admin.drivers.index') }}" class="btn btn-secondary">Cancel</a>
                <button class="btn btn-primary" type="submit"><i class="ri-save-3-line me-1"></i>{{ $editing ? 'Update' : 'Save' }} {{ $module['singular'] }}</button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="{{ asset('backend/assets/js/modules/reference-master-form.js') }}?v={{ filemtime(public_path('backend/assets/js/modules/reference-master-form.js')) }}"></script>
@endpush
