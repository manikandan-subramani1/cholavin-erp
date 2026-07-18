(function (window, $) {
    'use strict';
    if (!$ || !$('#stock-transfer-module').length) return;
    var module = $('#stock-transfer-module'); var form = $('#transfer-form'); var modal = bootstrap.Modal.getOrCreateInstance($('#transfer-modal').get(0));
    function filters() { return {status: $('#transfer-status-filter').val(), godown_id: $('#transfer-godown-filter').val(), from_date: $('#transfer-from-filter').val(), to_date: $('#transfer-to-filter').val()}; }
    var table = initializeDataTable({selector: '#transfers-table', url: module.data('index-url'), filters: filters, columns: [{data: 'DT_RowIndex'}, {data: 'number'}, {data: 'transfer_date'}, {data: 'route', orderable: false, searchable: false}, {data: 'items_count', searchable: false}, {data: 'record_status', name: 'status'}], order: [[2, 'desc']]});
    function reindex() { $('#transfer-items .transfer-item').each(function (index) { $(this).find('[data-field]').each(function () { var $field = $(this); $field.attr('name', 'items[' + index + '][' + $field.data('field') + ']'); }); }); }
    function addItem() { var item = $($('#transfer-item-template').html()); $('#transfer-items').append(item); item.find('.transfer-product').select2({width: '100%', dropdownParent: $('#transfer-modal')}); reindex(); }
    $('#add-transfer-item').on('click', addItem); $(document).on('click', '.remove-transfer-item', function () { if ($('#transfer-items .transfer-item').length === 1) return; $(this).closest('.transfer-item').remove(); reindex(); });
    form.validate({ignore: [], rules: {from_godown_id: {required: true}, to_godown_id: {required: true, notEqualTo: '[name="from_godown_id"]'}, transfer_date: {required: true, date: true}, status: {required: true}, notes: {maxlength: 2000}}, errorElement: 'span', errorClass: 'error text-danger', highlight: function (el) { $(el).addClass('is-invalid'); }, unhighlight: function (el) { $(el).removeClass('is-invalid'); }, submitHandler: function (el) { submitFormUsingAjax(el, {reset: true, onSuccess: function () { modal.hide(); table.ajax.reload(null, false); $('#transfer-items').empty(); addItem(); }}); }});
    $.validator.addMethod('notEqualTo', function (value, element, selector) { return value !== $(selector).val(); }, 'Source and destination must be different.');
    $('#add-transfer').on('click', function () { form.trigger('reset'); $('#transfer-items').empty(); addItem(); modal.show(); });
    var timer; $('#transfer-search').on('input', function () { var value = $(this).val(); clearTimeout(timer); timer = setTimeout(function () { table.search(value).draw(); }, 300); });
    $('#transfer-status-filter,#transfer-godown-filter,#transfer-from-filter,#transfer-to-filter').on('change', function () { table.ajax.reload(null, false); });
    $('#reset-transfer-filters').on('click', function () { $('#transfer-filters').trigger('reset'); table.search('').ajax.reload(null, false); });
    addItem();
})(window, window.jQuery);
