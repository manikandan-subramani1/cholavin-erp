(function (window, $) {
    'use strict';
    if (!$ || !$('#report-module').length) return;

    var module = $('#report-module');
    function filters() { return {from_date: $('#report-from').val(), to_date: $('#report-to').val()}; }
    function money(value) { return '\u20B9' + Number(value || 0).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}); }
    function iso(date) { return date.toISOString().slice(0, 10); }
    function visitUrl(url) { if (!url) return; if (window.CholavinNavigation) window.CholavinNavigation.visit(url); else window.location.href = url; }

    function setPreset(preset) {
        var today = new Date();
        var from = new Date(today.getFullYear(), today.getMonth(), today.getDate());
        var to = new Date(from);
        if (preset === 'yesterday') { from.setDate(from.getDate() - 1); to.setDate(to.getDate() - 1); }
        if (preset === 'week') { from.setDate(from.getDate() - from.getDay()); }
        if (preset === 'month') { from = new Date(today.getFullYear(), today.getMonth(), 1); }
        if (preset === 'last-month') { from = new Date(today.getFullYear(), today.getMonth() - 1, 1); to = new Date(today.getFullYear(), today.getMonth(), 0); }
        if (preset === 'financial-year') { from = new Date(today.getMonth() >= 3 ? today.getFullYear() : today.getFullYear() - 1, 3, 1); }
        $('#report-from').val(iso(from));
        $('#report-to').val(iso(to));
        table.ajax.reload(null, false);
    }

    function installQuickActions() {
        if (!window.CholavinShell) return;
        window.CholavinShell.setQuickActions([
            {label: 'This Month', icon: 'ri-calendar-event-line', target: '[data-report-preset="month"]', variant: 'primary'},
            {label: 'FY', icon: 'ri-calendar-check-line', target: '[data-report-preset="financial-year"]'},
            {label: 'Finance', icon: 'ri-line-chart-line', url: module.data('finance-url')},
            {label: 'Export PDF', icon: 'ri-file-pdf-2-line', target: '#report-pdf'}
        ]);
    }

    var table = initializeDataTable({selector: '#report-table', url: module.data('index-url'), filters: filters, columns: [{data: 'DT_RowIndex'}, {data: 'report_date'}, {data: 'reference'}, {data: 'party'}, {data: 'description'}, {data: 'debit'}, {data: 'credit'}, {data: 'amount'}, {data: 'status'}], order: [[1, 'desc']]});
    $('#report-table').on('xhr.dt.reportSummary', function (event, settings, json) { $.each((json && json.summary) || {}, function (key, value) { $('[data-report-summary="' + key + '"]').text(key === 'records' ? Number(value || 0).toLocaleString('en-IN') : money(value)); }); });
    var timer;
    $('#report-search').on('input', function () { var value = $(this).val(); clearTimeout(timer); timer = setTimeout(function () { table.search(value).draw(); }, 300); });
    $('#report-from,#report-to').on('change', function () { table.ajax.reload(null, false); });
    $('#report-reset').on('click', function () { $('#report-filters').trigger('reset'); table.search('').ajax.reload(null, false); });
    $('[data-report-preset]').on('click', function () { setPreset($(this).data('report-preset')); });
    $('[data-report-action="finance"]').on('click', function (event) { event.preventDefault(); visitUrl(module.data('finance-url')); });
    $('#report-pdf').on('click', function () { var params = filters(); params.search = $('#report-search').val(); window.location.href = module.data('pdf-url') + '?' + new URLSearchParams(params).toString(); });
    installQuickActions();
})(window, window.jQuery);
