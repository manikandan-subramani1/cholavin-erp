(function (window, $) {
    'use strict';
    if (!$ || !$('#users-module').length) return;

    var module = $('#users-module');
    var form = $('#user-form');
    var modal = bootstrap.Modal.getOrCreateInstance($('#user-modal').get(0));
    var allGodownOptions = $('#user-godowns option').clone();
    function visitUrl(url) { if (!url) return; if (window.CholavinNavigation) window.CholavinNavigation.visit(url); else window.location.href = url; }
    if (window.CholavinShell) {
        window.CholavinShell.setQuickActions([
            {label: 'Add User', icon: 'ri-user-add-line', target: '#add-user', variant: 'primary'},
            {label: 'Roles', icon: 'ri-shield-user-line', url: module.data('roles-url')},
            {label: 'Locations', icon: 'ri-store-2-line', url: module.data('locations-url')},
            {label: 'Activity', icon: 'ri-history-line', url: module.data('activity-url')}
        ]);
    }

    var table = initializeDataTable({
        selector: '#users-table',
        url: module.data('index-url'),
        filters: function () { return {role_id: $('#user-role-filter').val(), shop_id: $('#user-shop-filter').val(), status: $('#user-status-filter').val()}; },
        columns: [
            {data: 'DT_RowIndex'}, {data: 'identity', name: 'name'}, {data: 'role_name', name: 'role.name'},
            {data: 'locations', orderable: false, searchable: false}, {data: 'record_status', name: 'is_active'}, {data: 'action'}
        ],
        order: [[1, 'asc']]
    });

    function filterGodowns(selected) {
        var shopIds = ($('#user-shops').val() || []).map(String);
        var current = (selected || $('#user-godowns').val() || []).map(String);
        $('#user-godowns').empty();
        allGodownOptions.each(function () {
            var linked = String($(this).data('shop-ids') || '').split(',').filter(Boolean);
            if (linked.some(function (id) { return shopIds.includes(id); })) $('#user-godowns').append($(this).clone());
        });
        $('#user-godowns').val(current.filter(function (id) { return $('#user-godowns option[value="' + id + '"]').length; })).trigger('change.select2');
    }

    function resetForm() {
        form.trigger('reset');
        form.attr('action', module.data('store-url'));
        $('#user-method').val('POST');
        $('#user-modal-title').text('Add User');
        $('.create-required').removeClass('d-none');
        $('.create-password-note').removeClass('d-none');
        $('.edit-password-note').addClass('d-none');
        $('#user-password').rules('add', {required: true});
        $('#user-shops,#user-godowns,#user-financial-years').val(null).trigger('change');
        filterGodowns([]);
        form.find('.is-invalid').removeClass('is-invalid');
        form.validate().resetForm();
    }

    $('#user-shops,#user-godowns,#user-financial-years').select2({width: '100%', dropdownParent: $('#user-modal')});
    $('#user-shops').on('change', function () { filterGodowns(); });

    form.validate({
        ignore: [],
        rules: {
            name: {required: true, minlength: 2, maxlength: 150}, username: {required: true, maxlength: 100},
            email: {required: true, email: true, maxlength: 190}, mobile: {maxlength: 30},
            password: {required: true, minlength: 8, maxlength: 255}, role_id: {required: true}
        },
        errorElement: 'span', errorClass: 'error text-danger',
        highlight: function (element) { $(element).addClass('is-invalid').closest('.form-group').addClass('has-error'); },
        unhighlight: function (element) { $(element).removeClass('is-invalid').closest('.form-group').removeClass('has-error'); },
        submitHandler: function (element) {
            submitFormUsingAjax(element, {reset: false, onSuccess: function () { modal.hide(); table.ajax.reload(null, false); resetForm(); }});
        }
    });

    $('#add-user').on('click', function () { resetForm(); modal.show(); });
    $(document).on('click', '.edit-user', function () {
        var url = $(this).data('url');
        CholavinAjax.request({url: url, onSuccess: function (response) {
            var user = response.data;
            resetForm();
            form.attr('action', url);
            $('#user-method').val('PUT');
            $('#user-modal-title').text('Edit User');
            $('.create-required').addClass('d-none');
            $('.create-password-note').addClass('d-none');
            $('.edit-password-note').removeClass('d-none');
            $('#user-password').rules('remove', 'required');
            $('#user-name').val(user.name); $('#user-username').val(user.username); $('#user-email').val(user.email);
            $('#user-mobile').val(user.mobile); $('#user-role').val(user.role_id); $('#user-active').prop('checked', !!user.is_active);
            $('#user-shops').val(user.shops.map(String)).trigger('change.select2');
            filterGodowns(user.godowns.map(String));
            $('#user-financial-years').val(user.financial_years.map(String)).trigger('change.select2');
            modal.show();
        }});
    });
    $(document).on('click', '.delete-user', function () {
        var url = $(this).data('url');
        Swal.fire({title: 'Deactivate this user?', text: 'The user will immediately lose access.', icon: 'warning', showCancelButton: true, confirmButtonText: 'Deactivate'}).then(function (result) {
            if (!result.isConfirmed) return;
            CholavinAjax.request({url: url, method: 'DELETE', onSuccess: function (response) { toastr.success(response.message); table.ajax.reload(null, false); }});
        });
    });

    var searchTimer;
    $('#user-search').on('input', function () { var value = $(this).val(); clearTimeout(searchTimer); searchTimer = setTimeout(function () { table.search(value).draw(); }, 300); });
    $('#user-role-filter,#user-shop-filter,#user-status-filter').on('change', function () { table.ajax.reload(null, false); });
    $('#reset-user-filters').on('click', function () { $('#user-filters').trigger('reset'); table.search('').ajax.reload(null, false); });
})(window, window.jQuery);
