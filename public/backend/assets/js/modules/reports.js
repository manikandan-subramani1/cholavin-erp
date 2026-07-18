(function (window, $) {
    'use strict';

    if (!$ || !$('#report-module').length) return;

    var module = $('#report-module');

    function filters() {
        return {from_date: $('#report-from').val(), to_date: $('#report-to').val()};
    }

    function money(value) {
        return '₹' + Number(value || 0).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }

    var table = initializeDataTable({
        selector: '#report-table',
        url: module.data('index-url'),
        filters: filters,
        columns: [
            {data: 'DT_RowIndex'}, {data: 'report_date'}, {data: 'reference'}, {data: 'party'},
            {data: 'description'}, {data: 'debit'}, {data: 'credit'}, {data: 'amount'}, {data: 'status'}
        ],
        order: [[1, 'desc']]
    });

    $('#report-table').on('xhr.dt.reportSummary', function (event, settings, json) {
        var summary = json && json.summary ? json.summary : {};
        $.each(summary, function (key, value) {
            $('[data-report-summary="' + key + '"]').text(key === 'records' ? Number(value || 0).toLocaleString('en-IN') : money(value));
        });
    });

    var timer;
    $('#report-search').on('input', function () {
        var value = $(this).val();
        clearTimeout(timer);
        timer = setTimeout(function () { table.search(value).draw(); }, 300);
    });
    $('#report-from,#report-to').on('change', function () { table.ajax.reload(null, false); });
    $('#report-reset').on('click', function () { $('#report-filters').trigger('reset'); table.search('').ajax.reload(null, false); });
    $('#report-pdf').on('click', function () {
        var params = filters();
        params.search = $('#report-search').val();
        window.location.href = module.data('pdf-url') + '?' + new URLSearchParams(params).toString();
    });
})(window, window.jQuery);
