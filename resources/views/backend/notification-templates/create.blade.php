@extends('backend.layouts.app')

@php
    $module = json_decode('{"title":"Notification Templates","group":"Grow Your Business","singular":"Notification Template","slug":"notification-templates","icon":"ri-mail-settings-line"}', true);
    $editing = false;
@endphp

@section('title', ($editing ? 'Edit ' : 'Create ') . $module['singular'] . ' | Cholavin ERP')

@section('content')
<div class="card erp-panel">
    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div><span class="erp-eyebrow">{{ $module['group'] }}</span><h4 class="mb-0">{{ $editing ? 'Edit' : 'Create' }} {{ $module['singular'] }}</h4></div>
        <a href="{{ route('admin.notification-templates.index') }}" class="btn btn-secondary"><i class="ri-arrow-left-line me-1"></i>Back</a>
    </div>
    <form action="{{ route('admin.notification-templates.store') }}" method="POST" id="form-validate" data-index-url="{{ route('admin.'.$module['slug'].'.index') }}">
        @csrf
        @if($editing) @method('PUT') @endif
        <div class="card-body">
            <div class="row gy-4">
                <div class="col-xxl-3 col-md-6">
                    <div class="form-group">
                        <label class="form-label" for="name">Template Name <span class="text-danger">*</span></label>
                        <input class="form-control reference-master-input name-field @error('name') is-invalid @enderror" id="name" name="name" type="text" value="{{ old('name', $values['name'] ?? '') }}" placeholder="Payment Reminder" maxlength="190" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-xxl-3 col-md-6">
                    <div class="form-group">
                        <label class="form-label" for="channel">Channel <span class="text-danger">*</span></label>
                        <select class="form-select reference-master-input channel-field @error('channel') is-invalid @enderror" id="channel" name="channel" required>
                            <option value="">-- Select --</option>
                            <option value="WhatsApp" @selected((string) old('channel', $values['channel'] ?? '') === 'WhatsApp')>WhatsApp</option>
                            <option value="SMS" @selected((string) old('channel', $values['channel'] ?? '') === 'SMS')>SMS</option>
                            <option value="Email" @selected((string) old('channel', $values['channel'] ?? '') === 'Email')>Email</option>
                            <option value="In App" @selected((string) old('channel', $values['channel'] ?? '') === 'In App')>In App</option>
                        </select>
                        @error('channel')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-xxl-3 col-md-6">
                    <div class="form-group">
                        <label class="form-label" for="trigger_event">Trigger Event <span class="text-danger">*</span></label>
                        <input class="form-control reference-master-input trigger-event-field @error('trigger_event') is-invalid @enderror" id="trigger_event" name="trigger_event" type="text" value="{{ old('trigger_event', $values['trigger_event'] ?? '') }}" placeholder="Invoice due" maxlength="190" required>
                        @error('trigger_event')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-xxl-3 col-md-6">
                    <div class="form-group">
                        <label class="form-label" for="subject">Subject</label>
                        <input class="form-control reference-master-input subject-field @error('subject') is-invalid @enderror" id="subject" name="subject" type="text" value="{{ old('subject', $values['subject'] ?? '') }}" placeholder="Message subject" maxlength="190">
                        @error('subject')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-12">
                    <div class="form-group">
                        <label class="form-label" for="message_body">Message Body <span class="text-danger">*</span></label>
                        <textarea class="form-control reference-master-input message-body-field @error('message_body') is-invalid @enderror" id="message_body" name="message_body" rows="3" maxlength="2000" placeholder="Message content" required>{{ old('message_body', $values['message_body'] ?? '') }}</textarea>
                        @error('message_body')
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
                <a href="{{ route('admin.notification-templates.index') }}" class="btn btn-secondary">Cancel</a>
                <button class="btn btn-primary" type="submit"><i class="ri-save-3-line me-1"></i>{{ $editing ? 'Update' : 'Save' }} {{ $module['singular'] }}</button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="{{ asset('backend/assets/js/modules/reference-master-form.js') }}?v={{ filemtime(public_path('backend/assets/js/modules/reference-master-form.js')) }}"></script>
@endpush
