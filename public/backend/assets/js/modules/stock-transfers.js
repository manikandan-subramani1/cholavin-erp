(function (window, $) {
    'use strict';
    if (!$ || !$('#stock-transfer-module').length) return;

    var module = $('#stock-transfer-module');
    var form = $('#transfer-form');
    var modal = bootstrap.Modal.getOrCreateInstance($('#transfer-modal').get(0));

    function escapeHtml(value) {
        return $('<div>').text(value == null || value === '' ? '-' : value).html();
    }

    function filters() {
        return {
            status: $('#transfer-status-filter').val(),
            godown_id: $('#transfer-godown-filter').val(),
            from_date: $('#transfer-from-filter').val(),
            to_date: $('#transfer-to-filter').val()
        };
    }

    function visitUrl(url) {
        if (!url) return;
        if (window.CholavinNavigation) window.CholavinNavigation.visit(url);
        else window.location.href = url;
    }

    function openTransferDrawer(row) {
        if (!window.CholavinShell || !row) return;
        var html = '' +
            '<div class="erp-details-hero erp-transfer-details-hero">' +
                '<div class="erp-details-avatar"><i class="ri-arrow-left-right-line"></i></div>' +
                '<div><span class="erp-eyebrow">Transfer route</span><h5>' + escapeHtml(row.number) + '</h5><p>' + escapeHtml(row.route) + '</p></div>' +
            '</div>' +
            '<div class="erp-details-kpis erp-details-kpis-3">' +
                '<article><small>Date</small><strong>' + escapeHtml(row.transfer_date) + '</strong><em>Requested</em></article>' +
                '<article><small>Items</small><strong>' + escapeHtml(row.items_count) + '</strong><em>Lines</em></article>' +
                '<article><small>Status</small><strong>' + (row.record_status || '-') + '</strong><em>Workflow</em></article>' +
            '</div>' +
            '<div class="erp-details-tabs"><button class="active">Overview</button><button>Approval</button><button>Dispatch</button><button>Receipt</button><button>Audit</button></div>' +
            '<ol class="erp-transfer-flow"><li>Draft</li><li>Requested</li><li>Approved</li><li>Dispatched</li><li>Received</li></ol>' +
            '<div class="erp-details-actions"><button type="button" class="btn btn-primary" data-transfer-details-action="new"><i class="ri-add-line me-1"></i>New Transfer</button><button type="button" class="btn btn-secondary" data-transfer-details-action="stock"><i class="ri-stack-line me-1"></i>Stock Overview</button></div>';
        window.CholavinShell.openDrawer({title: row.number || 'Transfer Details', html: html});
    }

    function installQuickActions() {
        if (!window.CholavinShell) return;
        window.CholavinShell.setQuickActions([
            {label: 'New Transfer', icon: 'ri-arrow-left-right-line', target: '#add-transfer', variant: 'primary'},
            {label: 'Stock Overview', icon: 'ri-stack-line', url: module.data('stock-url')},
            {label: 'Drafts', icon: 'ri-draft-line', target: '#transfer-status-filter', value: 'draft'},
            {label: 'Completed', icon: 'ri-checkbox-circle-line', target: '#transfer-status-filter', value: 'completed'}
        ]);
    }

    var table = initializeDataTable({
        selector: '#transfers-table',
        url: module.data('index-url'),
        filters: filters,
        columns: [
            {data: 'DT_RowIndex'},
            {data: 'number'},
            {data: 'transfer_date'},
            {data: 'route', orderable: false, searchable: false},
            {data: 'items_count', searchable: false},
            {data: 'record_status', name: 'status'},
            {data: 'actions', orderable: false, searchable: false}
        ],
        order: [[2, 'desc']]
    });

    function reindex() { $('#transfer-items .transfer-item').each(function (index) { $(this).find('[data-field]').each(function () { var field = $(this); field.attr('name', 'items[' + index + '][' + field.data('field') + ']'); }); }); }
    function addItem() { var item = $($('#transfer-item-template').html()); $('#transfer-items').append(item); item.find('.transfer-product').select2({width: '100%', dropdownParent: $('#transfer-modal')}); reindex(); }

    $.validator.addMethod('notEqualTo', function (value, element, selector) { return value !== $(selector).val(); }, 'Source and destination must be different.');
    form.validate({ignore: [], rules: {from_godown_id: {required: true}, to_godown_id: {required: true, notEqualTo: '[name="from_godown_id"]'}, transfer_date: {required: true, date: true}, status: {required: true}, notes: {maxlength: 2000}}, errorElement: 'span', errorClass: 'error text-danger', highlight: function (el) { $(el).addClass('is-invalid'); }, unhighlight: function (el) { $(el).removeClass('is-invalid'); }, submitHandler: function (el) { submitFormUsingAjax(el, {reset: true, onSuccess: function () { modal.hide(); table.ajax.reload(null, false); $('#transfer-items').empty(); addItem(); }}); }});

    $('#add-transfer-item').on('click', addItem);
    $(document).on('click', '.remove-transfer-item', function () { if ($('#transfer-items .transfer-item').length === 1) return; $(this).closest('.transfer-item').remove(); reindex(); });
    $('#add-transfer').on('click', function () { form.trigger('reset'); $('#transfer-items').empty(); addItem(); modal.show(); });

    var timer;
    $('#transfer-search').on('input', function () { var value = $(this).val(); clearTimeout(timer); timer = setTimeout(function () { table.search(value).draw(); }, 300); });
    $('#transfer-status-filter,#transfer-godown-filter,#transfer-from-filter,#transfer-to-filter').on('change', function () { table.ajax.reload(null, false); });
    $('#reset-transfer-filters').on('click', function () { $('#transfer-filters').trigger('reset'); table.search('').ajax.reload(null, false); });
    $('#transfers-table').on('click', '[data-transfer-action="view"]', function (event) { event.preventDefault(); event.stopPropagation(); openTransferDrawer(table.row($(this).closest('tr')).data()); });
    $('#transfers-table').on('click', 'tbody tr', function () { openTransferDrawer(table.row(this).data()); });
    $(document).on('click', '[data-transfer-details-action="new"]', function () { $('#add-transfer').trigger('click'); });
    $(document).on('click', '[data-transfer-details-action="stock"]', function () { visitUrl(module.data('stock-url')); });

    addItem();
    installQuickActions();
})(window, window.jQuery);
