(function ($) {
    'use strict';
    var $root = $('#commercial-documents-module');
    if (!$root.length || !$) return;

    var base = $root.data('base-url');
    var purchase = String($root.data('purchase')) === '1';
    var products = [];
    var initialSearch = new URLSearchParams(window.location.search).get('search') || '';
    var modal = bootstrap.Modal.getOrCreateInstance($('#document-modal').get(0));
    var table = initializeDataTable({
        selector: '#documents-table',
        url: base,
        filters: function () {
            return {
                status: $('#document-status-filter').val(),
                from_date: $('#document-from-filter').val(),
                to_date: $('#document-to-filter').val()
            };
        },
        columns: [
            {data: 'DT_RowIndex'},
            {data: 'number'},
            {data: 'document_date'},
            {data: 'party.name', defaultContent: 'Cash'},
            {data: 'godown.name', defaultContent: '-'},
            {data: 'items_count', searchable: false},
            {data: 'total_amount'},
            {data: 'balance_amount'},
            {data: 'status'},
            {data: 'action'}
        ],
        order: [[2, 'desc']]
    });

    if (initialSearch) {
        $('#document-search').val(initialSearch);
        table.search(initialSearch).draw();
    }

    function escapeHtml(value) {
        return $('<div>').text(value == null ? '' : value).html();
    }

    function loadLookups() {
        var calls = [CholavinAjax.request({
            url: $root.data('product-url'),
            showLoader: false,
            onSuccess: function (response) { products = response.data; }
        })];
        if (String($root.data('party-required')) === '1') {
            calls.push(CholavinAjax.request({
                url: $root.data('party-url'),
                showLoader: false,
                onSuccess: function (response) {
                    var select = $('[name="party_id"]').html('<option value="">Select</option>');
                    response.data.forEach(function (option) { $('<option>', {text: option.text, value: option.id}).appendTo(select); });
                }
            }));
        }
        return $.when.apply($, calls);
    }

    function itemRow(item) {
        item = item || {};
        var index = $('#document-items tr').length;
        var options = ['<option value="">Select product</option>'].concat(products.map(function (product) {
            var selected = Number(item.product_id) === Number(product.id) ? ' selected' : '';
            var rate = purchase ? product.purchase_price : product.sale_price;
            return '<option value="' + Number(product.id) + '" data-rate="' + Number(rate || 0) + '" data-unit="' + escapeHtml(product.unit || '') + '"' + selected + '>' + escapeHtml(product.text) + '</option>';
        })).join('');
        var expiry = item.expiry_date ? String(item.expiry_date).substring(0, 10) : '';
        return '<tr class="document-item-row">' +
            '<td><select name="items[' + index + '][product_id]" class="form-select product-select" required>' + options + '</select></td>' +
            '<td><input name="items[' + index + '][quantity]" type="number" step="0.001" min="0.001" value="' + Number(item.quantity || 1) + '" class="form-control item-qty" required></td>' +
            '<td><input name="items[' + index + '][rate]" type="number" step="0.01" min="0" value="' + Number(item.rate || 0) + '" class="form-control item-rate" required></td>' +
            '<td><input name="items[' + index + '][discount_amount]" type="number" step="0.01" min="0" value="' + Number(item.discount_amount || 0) + '" class="form-control item-discount"></td>' +
            '<td><input name="items[' + index + '][unit]" value="' + escapeHtml(item.unit || '') + '" class="form-control item-unit"></td>' +
            '<td><input name="items[' + index + '][batch_number]" value="' + escapeHtml(item.batch_number || '') + '" class="form-control"></td>' +
            '<td><input name="items[' + index + '][expiry_date]" type="date" value="' + escapeHtml(expiry) + '" class="form-control"></td>' +
            '<td><button type="button" class="btn btn-sm btn-danger remove-item" aria-label="Remove line">&times;</button></td></tr>';
    }

    function total() {
        var value = Number($('[name="expense_amount"]').val() || 0) + Number($('[name="round_off"]').val() || 0);
        $('#document-items tr').each(function () {
            value += Number($(this).find('.item-qty').val() || 0) * Number($(this).find('.item-rate').val() || 0) - Number($(this).find('.item-discount').val() || 0);
        });
        $('#document-estimated-total').text(value.toFixed(2));
    }

    function resetForm() {
        var $form = $('#document-form');
        $form.trigger('reset').attr('action', base);
        $form.find('[name="_method"]').val('POST');
        $('#document-items').empty().append(itemRow());
        $form.validate().resetForm();
        $form.find('.is-invalid').removeClass('is-invalid');
        total();
    }

    $('#add-document').off('.documentModule').on('click.documentModule', function () {
        loadLookups().then(function () { resetForm(); modal.show(); });
    });
    $(document).off('click.documentModule', '.erp-pos-product').on('click.documentModule', '.erp-pos-product', function () {
        var productId = $(this).data('product-id');
        loadLookups().then(function () {
            resetForm();
            $('#document-items .product-select').first().val(String(productId)).trigger('change');
            modal.show();
        });
    });
    $('#pos-product-search').off('.documentModule').on('input.documentModule', function () {
        var term = String($(this).val() || '').toLowerCase();
        $('.erp-pos-product').each(function () { $(this).toggle(String($(this).data('search')).includes(term)); });
    });
    $('#add-document-item').off('.documentModule').on('click.documentModule', function () { $('#document-items').append(itemRow()); });
    $(document).off('click.documentModule', '.remove-item').on('click.documentModule', '.remove-item', function () {
        if ($('#document-items tr').length > 1) $(this).closest('tr').remove();
        total();
    });
    $(document).off('change.documentModule', '.product-select').on('change.documentModule', '.product-select', function () {
        var $option = $(this).find(':selected');
        var row = $(this).closest('tr');
        row.find('.item-rate').val($option.data('rate') || 0);
        row.find('.item-unit').val($option.data('unit') || '');
        total();
    });
    $(document).off('input.documentModule', '.item-qty,.item-rate,.item-discount,[name="expense_amount"],[name="round_off"]')
        .on('input.documentModule', '.item-qty,.item-rate,.item-discount,[name="expense_amount"],[name="round_off"]', total);

    $(document).off('click.documentModule', '.edit-document').on('click.documentModule', '.edit-document', function () {
        var id = $(this).data('id');
        loadLookups().then(function () {
            return CholavinAjax.request({
                url: base + '/' + id,
                onSuccess: function (response) {
                    resetForm();
                    var data = response.data;
                    var $form = $('#document-form');
                    $form.attr('action', base + '/' + id);
                    $form.find('[name="_method"]').val('PUT');
                    ['party_id', 'document_date', 'due_date', 'status', 'reference_number', 'expense_amount', 'round_off', 'notes'].forEach(function (key) {
                        var value = data[key] == null ? '' : data[key];
                        if (key.indexOf('date') !== -1) value = String(value).substring(0, 10);
                        $('[name="' + key + '"]').val(value);
                    });
                    $('#document-items').empty();
                    data.items.forEach(function (item) { $('#document-items').append(itemRow(item)); });
                    total();
                    modal.show();
                }
            });
        });
    });

    $('#document-form').validate({
        ignore: [],
        rules: {
            document_date: {required: true, date: true},
            due_date: {date: true},
            status: {required: true},
            expense_amount: {number: true, min: 0},
            round_off: {number: true, range: [-10, 10]},
            notes: {maxlength: 2000}
        },
        errorElement: 'span',
        errorClass: 'invalid-feedback',
        highlight: function (element) { $(element).addClass('is-invalid').closest('.form-group').addClass('has-error'); },
        unhighlight: function (element) { $(element).removeClass('is-invalid').closest('.form-group').removeClass('has-error'); },
        submitHandler: function (form) {
            submitFormUsingAjax(form, {
                reset: false,
                onSuccess: function () { modal.hide(); table.ajax.reload(null, false); }
            });
        }
    });

    $('#document-status-filter,#document-from-filter,#document-to-filter').off('.documentModule').on('change.documentModule', function () { table.ajax.reload(null, false); });
    var searchTimer;
    $('#document-search').off('.documentModule').on('input.documentModule', function () {
        var value = $(this).val();
        window.clearTimeout(searchTimer);
        searchTimer = window.setTimeout(function () { table.search(value).draw(); }, 300);
    });
    $('#reset-document-filters').off('.documentModule').on('click.documentModule', function () { $('#document-filters').trigger('reset'); table.search('').ajax.reload(null, false); });
    $(document).off('click.documentModule', '.delete-document').on('click.documentModule', '.delete-document', function () {
        var $button = $(this);
        Swal.fire({title: 'Delete draft?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Delete'}).then(function (result) {
            if (!result.isConfirmed) return;
            CholavinAjax.request({
                url: $button.data('url'),
                method: 'DELETE',
                disableButton: $button,
                onSuccess: function (response) { toastr.success(response.message); table.ajax.reload(null, false); }
            });
        });
    });
    $('#document-pdf').off('.documentModule').on('click.documentModule', function () {
        var params = new URLSearchParams({
            status: $('#document-status-filter').val() || '',
            from_date: $('#document-from-filter').val() || '',
            to_date: $('#document-to-filter').val() || '',
            search: $('#document-search').val() || ''
        });
        window.location.assign($root.data('pdf-url') + '?' + params.toString());
    });
})(window.jQuery);
