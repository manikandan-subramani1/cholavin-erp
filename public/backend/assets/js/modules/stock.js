(function (window, document, $) {
    'use strict';
    if (!$ || !$('#stock-module').length) return;
    var module = $('#stock-module');
    function filters() { return {product_id: $('#stock-product-filter').val(), godown_id: $('#stock-godown-filter').val(), low_stock: $('#low-stock-only').is(':checked') ? 1 : ''}; }
    var table = initializeDataTable({selector: '#stock-table', url: module.data('index-url'), filters: filters, columns: [
        {data: 'DT_RowIndex'}, {data: 'product_name', name: 'product.name'}, {data: 'sku', name: 'product.sku'}, {data: 'godown_name', name: 'godown.name'},
        {data: 'batch_number'}, {data: 'expiry_date'}, {data: 'quantity'}, {data: 'average_cost'}, {data: 'value', orderable: false, searchable: false}, {data: 'record_status', orderable: false, searchable: false}
    ], order: [[1, 'asc']]});
    $('#stock-table').on('xhr.dt.stockSummary', function (event, settings, json) {
        $.each((json && json.summary) || {}, function (key, value) {
            var formatted = key === 'value'
                ? '₹' + Number(value || 0).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})
                : Number(value || 0).toLocaleString('en-IN', {maximumFractionDigits: key === 'quantity' ? 3 : 0});
            $('[data-stock-summary="' + key + '"]').text(formatted);
        });
    });
    var timer; $('#stock-search').on('input', function () { var value = $(this).val(); clearTimeout(timer); timer = setTimeout(function () { table.search(value).draw(); }, 300); });
    $('#stock-product-filter,#stock-godown-filter,#low-stock-only').on('change', function () { table.ajax.reload(null, false); });
    $('#reset-stock-filters').on('click', function () { $('#stock-filters').trigger('reset'); table.ajax.reload(null, false); });
    $('#stock-pdf').on('click', function () { var params = filters(); params.search = $('#stock-search').val(); window.location.href = module.data('pdf-url') + '?' + new URLSearchParams(params).toString(); });
})(window, document, window.jQuery);
