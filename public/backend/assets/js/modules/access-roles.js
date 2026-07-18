(function (window, $) {
    'use strict';
    if (!$ || !$('#roles-module').length) return;
    var module = $('#roles-module');
    var form = $('#role-form');
    var modal = bootstrap.Modal.getOrCreateInstance($('#role-modal').get(0));
    var table = initializeDataTable({selector: '#roles-table', url: module.data('index-url'), filters: function () { return {status: $('#role-status-filter').val()}; }, columns: [
        {data: 'DT_RowIndex'}, {data: 'name'}, {data: 'role_type', orderable: false, searchable: false},
        {data: 'permissions_count', searchable: false}, {data: 'users_count', searchable: false}, {data: 'record_status', name: 'is_active'}, {data: 'action'}
    ], order: [[1, 'asc']]});

    function resetForm() {
        form.trigger('reset'); form.attr('action', module.data('store-url')); $('#role-method').val('POST'); $('#role-modal-title').text('Add Role');
        $('#role-status-wrap').addClass('d-none'); $('.role-permission').prop('checked', false); form.validate().resetForm(); form.find('.is-invalid').removeClass('is-invalid');
    }
    form.validate({ignore: [], rules: {name: {required: true, minlength: 2, maxlength: 100}}, errorElement: 'span', errorClass: 'error text-danger', highlight: function (el) { $(el).addClass('is-invalid'); }, unhighlight: function (el) { $(el).removeClass('is-invalid'); }, submitHandler: function (el) {
        submitFormUsingAjax(el, {reset: false, onSuccess: function () { modal.hide(); table.ajax.reload(null, false); resetForm(); }});
    }});
    $('#add-role').on('click', function () { resetForm(); modal.show(); });
    $('#toggle-role-permissions').on('click', function () { var checked = $('.role-permission:checked').length !== $('.role-permission').length; $('.role-permission').prop('checked', checked); $(this).text(checked ? 'Clear all' : 'Select all'); });
    $(document).on('click', '.edit-role', function () { var url = $(this).data('url'); CholavinAjax.request({url: url, onSuccess: function (response) {
        resetForm(); var role = response.data; form.attr('action', url); $('#role-method').val('PUT'); $('#role-modal-title').text('Edit Role'); $('#role-status-wrap').removeClass('d-none');
        $('#role-name').val(role.name); $('#role-active').prop('checked', !!role.is_active); role.permissions.forEach(function (id) { $('.role-permission[value="' + id + '"]').prop('checked', true); }); modal.show();
    }}); });
    $(document).on('click', '.delete-role', function () { var url = $(this).data('url'); Swal.fire({title: 'Delete this role?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Delete'}).then(function (result) { if (result.isConfirmed) CholavinAjax.request({url: url, method: 'DELETE', onSuccess: function (response) { toastr.success(response.message); table.ajax.reload(null, false); }}); }); });
    var timer; $('#role-search').on('input', function () { var value = $(this).val(); clearTimeout(timer); timer = setTimeout(function () { table.search(value).draw(); }, 300); });
    $('#role-status-filter').on('change', function () { table.ajax.reload(null, false); });
    $('#reset-role-filters').on('click', function () { $('#role-filters').trigger('reset'); table.search('').ajax.reload(null, false); });
})(window, window.jQuery);
