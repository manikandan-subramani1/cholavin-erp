@extends('backend.layouts.app')

@php
    $module = json_decode('{"title":"HSN / SAC Codes","group":"Items","singular":"HSN / SAC Code","slug":"hsn-sac-codes","icon":"ri-barcode-box-line"}', true);
    $fields = json_decode('[{"label":"HSN / SAC Code","name":"code","placeholder":"1006","type":"text"},{"label":"Description","name":"description","placeholder":"Rice description","type":"textarea"},{"label":"Tax Rate","name":"tax_rate","options":["0%","5%","12%","18%","28%"],"type":"select"},{"label":"Type","name":"type","options":["Goods","Service"],"type":"select"},{"label":"Status","name":"status","options":["Active","Inactive"],"type":"select"}]', true);
    $editing = true;
@endphp

@section('title', ($editing ? 'Edit ' : 'Create ') . $module['singular'] . ' | Cholavin ERP')

@section('content')
<div class="card erp-panel">
    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div><span class="erp-eyebrow">{{ $module['group'] }}</span><h4 class="mb-0">{{ $editing ? 'Edit' : 'Create' }} {{ $module['singular'] }}</h4></div>
        <a href="{{ route('admin.hsn-sac-codes.index') }}" class="btn btn-secondary"><i class="ri-arrow-left-line me-1"></i>Back</a>
    </div>
    <form action="{{ route('admin.hsn-sac-codes.update', $recordId) }}" method="POST" id="form-validate">
        @csrf
        @if($editing) @method('PUT') @endif
        <div class="card-body">
            <div class="row gy-4">
                @foreach($fields as $field)
                    @php
                        $name = $field['name'];
                        $type = $field['type'] ?? 'text';
                        $value = old($name, $values[$name] ?? ($name === 'status' ? 'Active' : ''));
                        $required = !in_array($name, ['notes', 'description', 'footer_text', 'suffix', 'header_text', 'subject'], true);
                    @endphp
                    <div class="{{ $type === 'textarea' ? 'col-12' : 'col-xxl-3 col-md-6' }}">
                        <div class="form-group">
                            <label class="form-label" for="{{ $name }}">{{ $field['label'] }} @if($required)<span class="text-danger">*</span>@endif</label>
                            @if($type === 'select')
                                <select class="form-select" id="{{ $name }}" name="{{ $name }}">
                                    <option value="">-- Select --</option>
                                    @foreach($field['options'] ?? [] as $option)<option value="{{ $option }}" @selected((string)$value === (string)$option)>{{ $option }}</option>@endforeach
                                </select>
                            @elseif($type === 'textarea')
                                <textarea class="form-control" id="{{ $name }}" name="{{ $name }}" rows="3" placeholder="{{ $field['placeholder'] ?? '' }}">{{ $value }}</textarea>
                            @else
                                <input class="form-control" id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" value="{{ $value }}" placeholder="{{ $field['placeholder'] ?? '' }}">
                            @endif
                            @error($name)<div class="error text-danger">{{ $message }}</div>@enderror
                        </div>
                    </div>
                @endforeach
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
<script>
$(document).ready(function () {
    const rules = {};
    @foreach($fields as $field)
    @php
        $clientRequired = ! in_array($field['name'], ['notes', 'description', 'footer_text', 'suffix', 'header_text', 'subject'], true);
    @endphp
    rules[@json($field['name'])] = {
        required: @json($clientRequired),
        @if(($field['type'] ?? 'text') === 'number') number: true, min: 0, @else maxlength: @json(($field['type'] ?? 'text') === 'textarea' ? 2000 : 190), @endif
    };
    @endforeach

    $('#form-validate').validate({
        ignore: [],
        rules,
        messages: {required: 'This field is required.'},
        errorElement: 'span',
        errorClass: 'error text-danger',
        highlight: function (element) {
            $(element).closest('.form-group').addClass('has-error');
            $(element).addClass('is-invalid');
        },
        unhighlight: function (element) {
            $(element).closest('.form-group').removeClass('has-error');
            $(element).removeClass('is-invalid');
        },
        submitHandler: function (form) {
            submitFormUsingAjax(form, {
                reset: false,
                onSuccess: function (response) {
                    window.location.assign(response.data.redirect);
                }
            });
        }
    });
});
</script>
@endpush
