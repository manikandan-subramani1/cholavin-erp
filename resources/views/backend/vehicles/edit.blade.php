@extends('backend.layouts.app')

@php
    $module = json_decode('{"title":"Vehicles","group":"Delivery","singular":"Vehicle","slug":"vehicles","icon":"ri-truck-line"}', true);
    $editing = true;
@endphp

@section('title', ($editing ? 'Edit ' : 'Create ') . $module['singular'] . ' | Cholavin ERP')

@section('content')
<div class="card erp-panel">
    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div><span class="erp-eyebrow">{{ $module['group'] }}</span><h4 class="mb-0">{{ $editing ? 'Edit' : 'Create' }} {{ $module['singular'] }}</h4></div>
        <a href="{{ route('admin.vehicles.index') }}" class="btn btn-secondary"><i class="ri-arrow-left-line me-1"></i>Back</a>
    </div>
    <form action="{{ route('admin.vehicles.update', $recordId) }}" method="POST" id="form-validate" data-index-url="{{ route('admin.'.$module['slug'].'.index') }}">
        @csrf
        @if($editing) @method('PUT') @endif
        <div class="card-body">
            <div class="row gy-4">
                <div class="col-xxl-3 col-md-6">
                    <div class="form-group">
                        <label class="form-label" for="vehicle_number">Vehicle Number <span class="text-danger">*</span></label>
                        <input class="form-control reference-master-input vehicle-number-field @error('vehicle_number') is-invalid @enderror" id="vehicle_number" name="vehicle_number" type="text" value="{{ old('vehicle_number', $values['vehicle_number'] ?? '') }}" placeholder="TN 01 AB 1234" maxlength="190" required>
                        @error('vehicle_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-xxl-3 col-md-6">
                    <div class="form-group">
                        <label class="form-label" for="vehicle_type">Vehicle Type <span class="text-danger">*</span></label>
                        <select class="form-select reference-master-input vehicle-type-field @error('vehicle_type') is-invalid @enderror" id="vehicle_type" name="vehicle_type" required>
                            <option value="">-- Select --</option>
                            <option value="Mini Truck" @selected((string) old('vehicle_type', $values['vehicle_type'] ?? '') === 'Mini Truck')>Mini Truck</option>
                            <option value="Truck" @selected((string) old('vehicle_type', $values['vehicle_type'] ?? '') === 'Truck')>Truck</option>
                            <option value="Van" @selected((string) old('vehicle_type', $values['vehicle_type'] ?? '') === 'Van')>Van</option>
                            <option value="Two Wheeler" @selected((string) old('vehicle_type', $values['vehicle_type'] ?? '') === 'Two Wheeler')>Two Wheeler</option>
                        </select>
                        @error('vehicle_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-xxl-3 col-md-6">
                    <div class="form-group">
                        <label class="form-label" for="capacity">Capacity <span class="text-danger">*</span></label>
                        <input class="form-control reference-master-input capacity-field @error('capacity') is-invalid @enderror" id="capacity" name="capacity" type="text" value="{{ old('capacity', $values['capacity'] ?? '') }}" placeholder="2 Ton" maxlength="190" required>
                        @error('capacity')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-xxl-3 col-md-6">
                    <div class="form-group">
                        <label class="form-label" for="owner_name">Owner Name <span class="text-danger">*</span></label>
                        <input class="form-control reference-master-input owner-name-field @error('owner_name') is-invalid @enderror" id="owner_name" name="owner_name" type="text" value="{{ old('owner_name', $values['owner_name'] ?? '') }}" placeholder="Owner" maxlength="190" required>
                        @error('owner_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-xxl-3 col-md-6">
                    <div class="form-group">
                        <label class="form-label" for="insurance_expiry">Insurance Expiry <span class="text-danger">*</span></label>
                        <input class="form-control reference-master-input insurance-expiry-field @error('insurance_expiry') is-invalid @enderror" id="insurance_expiry" name="insurance_expiry" type="date" value="{{ old('insurance_expiry', $values['insurance_expiry'] ?? '') }}" placeholder="" maxlength="190" required>
                        @error('insurance_expiry')
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
                                <input class="form-check-input reference-master-input status-field @error('status') is-invalid @enderror" id="status_service" name="status" type="radio" value="Service" @checked((string) old('status', $values['status'] ?? 'Active') === 'Service')>
                                <label class="form-check-label" for="status_service">Service</label>
                            </div>
                        </div>
                        @error('status')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            <div class="mt-4 d-flex justify-content-end gap-2">
                <a href="{{ route('admin.vehicles.index') }}" class="btn btn-secondary">Cancel</a>
                <button class="btn btn-primary" type="submit"><i class="ri-save-3-line me-1"></i>{{ $editing ? 'Update' : 'Save' }} {{ $module['singular'] }}</button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="{{ asset('backend/assets/js/modules/reference-master-form.js') }}?v={{ filemtime(public_path('backend/assets/js/modules/reference-master-form.js')) }}"></script>
@endpush
