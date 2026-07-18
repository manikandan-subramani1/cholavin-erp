(function (window, $) {
    'use strict';
    if (!$ || !$('#locations-module').length) return;
    var module = $('#locations-module');
    var shopModal = bootstrap.Modal.getOrCreateInstance($('#shop-modal').get(0));
    var godownModal = bootstrap.Modal.getOrCreateInstance($('#godown-modal').get(0));
    if (window.CholavinShell) {
        window.CholavinShell.setQuickActions([
            {label: 'Add Shop', icon: 'ri-store-2-line', target: '#add-shop', variant: 'primary'},
            {label: 'Add Godown', icon: 'ri-building-4-line', target: '#add-godown'},
            {label: 'Users', icon: 'ri-team-line', url: module.data('users-url')},
            {label: 'Stock', icon: 'ri-stack-line', url: module.data('stock-url')}
        ]);
    }

    var filters = function (entity) { return {entity: entity, status: $('#location-status-filter').val(), shop_id: $('#location-shop-filter').val()}; };
    var shops = initializeDataTable({selector: '#shops-table', url: module.data('index-url'), filters: function () { return filters('shops'); }, columns: [
        {data: 'DT_RowIndex'}, {data: 'name'}, {data: 'code'}, {data: 'linked_godowns_count', searchable: false}, {data: 'users_count', searchable: false}, {data: 'record_status', name: 'is_active'}, {data: 'action'}
    ], order: [[1, 'asc']]});
    var godowns = initializeDataTable({selector: '#godowns-table', url: module.data('index-url'), filters: function () { return filters('godowns'); }, columns: [
        {data: 'DT_RowIndex'}, {data: 'name'}, {data: 'code'}, {data: 'shop_names', orderable: false, searchable: false}, {data: 'users_count', searchable: false}, {data: 'record_status', name: 'is_active'}, {data: 'action'}
    ], order: [[1, 'asc']]});
    window.activeDataTable = godowns;

    $('#godown-shops').select2({width: '100%', dropdownParent: $('#godown-modal')});
    function reset(form, storeUrl, method, title, statusWrap) { form.trigger('reset'); form.attr('action', storeUrl); method.val('POST'); $(title).text(title.indexOf('shop') >= 0 ? 'Add Shop' : 'Add Godown'); $(statusWrap).addClass('d-none'); form.validate().resetForm(); form.find('.is-invalid').removeClass('is-invalid'); }
    var shopForm = $('#shop-form'); var godownForm = $('#godown-form');
    shopForm.validate({rules: {name: {required: true, minlength: 2, maxlength: 150}, code: {required: true, maxlength: 50}, address: {maxlength: 1000}}, errorElement: 'span', errorClass: 'error text-danger', highlight: function (el) { $(el).addClass('is-invalid'); }, unhighlight: function (el) { $(el).removeClass('is-invalid'); }, submitHandler: function (el) { submitFormUsingAjax(el, {reset: false, onSuccess: function () { shopModal.hide(); shops.ajax.reload(null, false); godowns.ajax.reload(null, false); }}); }});
    godownForm.validate({ignore: [], rules: {name: {required: true, minlength: 2, maxlength: 150}, code: {required: true, maxlength: 50}, address: {maxlength: 1000}}, errorElement: 'span', errorClass: 'error text-danger', highlight: function (el) { $(el).addClass('is-invalid'); }, unhighlight: function (el) { $(el).removeClass('is-invalid'); }, submitHandler: function (el) { submitFormUsingAjax(el, {reset: false, onSuccess: function () { godownModal.hide(); godowns.ajax.reload(null, false); shops.ajax.reload(null, false); }}); }});
    $('#add-shop').on('click', function () { reset(shopForm, module.data('shop-store-url'), $('#shop-method'), '#shop-modal-title', '#shop-status-wrap'); shopModal.show(); });
    $('#add-godown').on('click', function () { reset(godownForm, module.data('godown-store-url'), $('#godown-method'), '#godown-modal-title', '#godown-status-wrap'); $('#godown-shops').val(null).trigger('change'); godownModal.show(); });
    $(document).on('click', '.edit-shop', function () { var url = $(this).data('url'); CholavinAjax.request({url: url, onSuccess: function (response) { var item = response.data; reset(shopForm, url, $('#shop-method'), '#shop-modal-title', '#shop-status-wrap'); shopForm.attr('action', url); $('#shop-method').val('PUT'); $('#shop-modal-title').text('Edit Shop'); $('#shop-status-wrap').removeClass('d-none'); $('#shop-name').val(item.name); $('#shop-code').val(item.code); $('#shop-address').val(item.address); $('#shop-active').prop('checked', !!item.is_active); shopModal.show(); }}); });
    $(document).on('click', '.edit-godown', function () { var url = $(this).data('url'); CholavinAjax.request({url: url, onSuccess: function (response) { var item = response.data; reset(godownForm, url, $('#godown-method'), '#godown-modal-title', '#godown-status-wrap'); godownForm.attr('action', url); $('#godown-method').val('PUT'); $('#godown-modal-title').text('Edit Godown'); $('#godown-status-wrap').removeClass('d-none'); $('#godown-name').val(item.name); $('#godown-code').val(item.code); $('#godown-address').val(item.address); $('#godown-active').prop('checked', !!item.is_active); $('#godown-shops').val(item.shop_ids.map(String)).trigger('change'); godownModal.show(); }}); });
    $(document).on('click', '.delete-location', function () { var url = $(this).data('url'); Swal.fire({title: 'Delete this location?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Delete'}).then(function (result) { if (result.isConfirmed) CholavinAjax.request({url: url, method: 'DELETE', onSuccess: function (response) { toastr.success(response.message); shops.ajax.reload(null, false); godowns.ajax.reload(null, false); }}); }); });
    var timer; $('#location-search').on('input', function () { var value = $(this).val(); clearTimeout(timer); timer = setTimeout(function () { shops.search(value).draw(); godowns.search(value).draw(); }, 300); });
    $('#location-status-filter,#location-shop-filter').on('change', function () { shops.ajax.reload(null, false); godowns.ajax.reload(null, false); });
    $('#reset-location-filters').on('click', function () { $('#location-filters').trigger('reset'); shops.search('').ajax.reload(null, false); godowns.search('').ajax.reload(null, false); });
})(window, window.jQuery);
