(function (window, $) {
    'use strict';
    if (!$ || !$('#stock-module').length) return;

    var module = $('#stock-module');

    function escapeHtml(value) {
        return $('<div>').text(value == null || value === '' ? '-' : value).html();
    }

    function money(value) {
        return '\u20B9' + Number(value || 0).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }

    function filters() {
        return {
            product_id: $('#stock-product-filter').val(),
            godown_id: $('#stock-godown-filter').val(),
            low_stock: $('#low-stock-only').is(':checked') ? 1 : ''
        };
    }

    function visitUrl(url) {
        if (!url) return;
        if (window.CholavinNavigation) window.CholavinNavigation.visit(url);
        else window.location.href = url;
    }

    function openStockDrawer(row) {
        if (!window.CholavinShell || !row) return;
        var quantity = Number(String(row.quantity || '0').replace(/,/g, '')) || 0;
        var averageCost = Number(String(row.average_cost || '0').replace(/,/g, '')) || 0;
        var html = '' +
            '<div class="erp-details-hero erp-stock-details-hero">' +
                '<div class="erp-details-avatar"><i class="ri-stack-line"></i></div>' +
                '<div><span class="erp-eyebrow">Stock balance</span><h5>' + escapeHtml(row.product_name) + '</h5>' +
                '<p>' + escapeHtml(row.godown_name) + ' / Batch ' + escapeHtml(row.batch_number) + '</p></div>' +
            '</div>' +
            '<div class="erp-details-kpis erp-details-kpis-3">' +
                '<article><small>Quantity</small><strong>' + escapeHtml(row.quantity) + '</strong><em>On hand</em></article>' +
                '<article><small>Avg Cost</small><strong>' + money(averageCost) + '</strong><em>Weighted</em></article>' +
                '<article><small>Value</small><strong>' + money(quantity * averageCost) + '</strong><em>Stock value</em></article>' +
            '</div>' +
            '<div class="erp-details-tabs"><button class="active">Overview</button><button>History</button><button>Audit</button></div>' +
            '<dl class="erp-details-meta"><dt>SKU</dt><dd>' + escapeHtml(row.sku) + '</dd><dt>Expiry</dt><dd>' + escapeHtml(row.expiry_date) + '</dd><dt>Status</dt><dd>' + (row.record_status || '-') + '</dd></dl>' +
            '<div class="erp-details-actions">' +
                '<button type="button" class="btn btn-primary" data-stock-details-action="transfer"><i class="ri-arrow-left-right-line me-1"></i>Transfer</button>' +
                '<button type="button" class="btn btn-secondary" data-stock-details-action="adjust"><i class="ri-equalizer-line me-1"></i>Adjust</button>' +
                '<button type="button" class="btn btn-secondary" data-stock-details-action="damage"><i class="ri-error-warning-line me-1"></i>Damage Entry</button>' +
            '</div>';
        window.CholavinShell.openDrawer({title: row.product_name || 'Stock Details', html: html});
    }

    function installQuickActions() {
        if (!window.CholavinShell) return;
        window.CholavinShell.setQuickActions([
            {label: 'Transfer', icon: 'ri-arrow-left-right-line', url: module.data('transfer-url'), variant: 'primary'},
            {label: 'Adjust Stock', icon: 'ri-equalizer-line', url: module.data('adjustment-url')},
            {label: 'Damage Entry', icon: 'ri-error-warning-line', url: module.data('damage-url')},
            {label: 'Export PDF', icon: 'ri-file-pdf-2-line', target: '#stock-pdf'}
        ]);
    }

    var table = initializeDataTable({
        selector: '#stock-table',
        url: module.data('index-url'),
        filters: filters,
        columns: [
            {data: 'DT_RowIndex'},
            {data: 'product_name', name: 'product.name'},
            {data: 'sku', name: 'product.sku'},
            {data: 'godown_name', name: 'godown.name'},
            {data: 'batch_number'},
            {data: 'expiry_date'},
            {data: 'quantity'},
            {data: 'average_cost'},
            {data: 'value', orderable: false, searchable: false},
            {data: 'record_status', orderable: false, searchable: false},
            {data: 'actions', orderable: false, searchable: false}
        ],
        order: [[1, 'asc']]
    });

    $('#stock-table').on('xhr.dt.stockSummary', function (event, settings, json) {
        $.each((json && json.summary) || {}, function (key, value) {
            var formatted = key === 'value'
                ? money(value)
                : Number(value || 0).toLocaleString('en-IN', {maximumFractionDigits: key === 'quantity' ? 3 : 0});
            $('[data-stock-summary="' + key + '"]').text(formatted);
        });
    });

    var timer;
    $('#stock-search').on('input', function () {
        var value = $(this).val();
        clearTimeout(timer);
        timer = setTimeout(function () { table.search(value).draw(); }, 300);
    });

    $('#stock-product-filter,#stock-godown-filter,#low-stock-only').on('change', function () { table.ajax.reload(null, false); });
    $('#reset-stock-filters').on('click', function () { $('#stock-filters').trigger('reset'); table.search('').ajax.reload(null, false); });
    $('#stock-pdf').on('click', function () { var params = filters(); params.search = $('#stock-search').val(); window.location.href = module.data('pdf-url') + '?' + new URLSearchParams(params).toString(); });
    $('#stock-table').on('click', '[data-stock-action="view"]', function (event) { event.preventDefault(); event.stopPropagation(); openStockDrawer(table.row($(this).closest('tr')).data()); });
    $('#stock-table').on('click', 'tbody tr', function () { openStockDrawer(table.row(this).data()); });
    $(document).on('click', '[data-stock-details-action="transfer"]', function () { visitUrl(module.data('transfer-url')); });
    $(document).on('click', '[data-stock-details-action="adjust"]', function () { visitUrl(module.data('adjustment-url')); });
    $(document).on('click', '[data-stock-details-action="damage"]', function () { visitUrl(module.data('damage-url')); });

    installQuickActions();
})(window, window.jQuery);
