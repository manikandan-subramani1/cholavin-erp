(function ($) {
    'use strict';
    var $root = $('#enquiries-module');
    if (!$root.length || !$) return;

    var modal = bootstrap.Modal.getOrCreateInstance($('#statusModal').get(0));
    var table = initializeDataTable({
        selector: '#enquiries-table',
        url: $root.data('index-url'),
        order: [[7, 'desc']],
        filters: function () {
            return {
                status: $('#enquiry-status-filter').val(),
                from_date: $('#enquiry-from-filter').val(),
                to_date: $('#enquiry-to-filter').val()
            };
        },
        columns: [
            {data: 'DT_RowIndex'},
            {data: 'name'},
            {data: 'email'},
            {data: 'phone'},
            {data: 'service'},
            {data: 'status'},
            {data: 'reason', defaultContent: '-'},
            {data: 'created_at'},
            {data: 'action'}
        ]
    });

    $('#enquiry-status-filter,#enquiry-from-filter,#enquiry-to-filter').off('.enquiryModule').on('change.enquiryModule', function () {
        table.ajax.reload(null, false);
    });
    $('#reset-enquiry-filters').off('.enquiryModule').on('click.enquiryModule', function () {
        $('#enquiry-filters').trigger('reset');
        table.ajax.reload(null, false);
    });
    $(document).off('click.enquiryModule', '.change-status').on('click.enquiryModule', '.change-status', function () {
        var $button = $(this);
        $('#statusEnquiryId').val($button.data('id'));
        $('#enquiryStatus').val($button.data('status'));
        $('#statusReason').val($button.data('reason') || '');
        modal.show();
    });

    $('#statusForm').validate({
        ignore: [],
        rules: {status: {required: true}, reason: {required: true, maxlength: 1000}},
        errorElement: 'span',
        errorClass: 'invalid-feedback',
        highlight: function (element) { $(element).addClass('is-invalid').closest('.form-group').addClass('has-error'); },
        unhighlight: function (element) { $(element).removeClass('is-invalid').closest('.form-group').removeClass('has-error'); },
        submitHandler: function () {
            var button = $('#statusForm [type="submit"]');
            CholavinAjax.request({
                url: $root.data('status-base-url') + '/' + $('#statusEnquiryId').val() + '/status',
                method: 'PATCH',
                data: {status: $('#enquiryStatus').val(), reason: $('#statusReason').val()},
                form: '#statusForm',
                disableButton: button,
                onSuccess: function (response) {
                    modal.hide();
                    toastr.success(response.message);
                    table.ajax.reload(null, false);
                }
            });
        }
    });
})(window.jQuery);
