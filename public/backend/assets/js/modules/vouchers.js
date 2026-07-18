(function (window, $) {
    'use strict';
    if (!$ || !$('#vouchers-module').length) return;

    var module = $('#vouchers-module');
    var form = $('#voucher-form');
    var modal = bootstrap.Modal.getOrCreateInstance($('#voucher-modal').get(0));

    function escapeHtml(value) {
        return $('<div>').text(value == null || value === '' ? '-' : value).html();
    }

    function money(value) {
        return '\u20B9' + Number(String(value || '0').replace(/,/g, '') || 0).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }

    function filters() {
        return {type: $('#voucher-type-filter').val(), from_date: $('#voucher-from-filter').val(), to_date: $('#voucher-to-filter').val()};
    }

    function visitUrl(url) {
        if (!url) return;
        if (window.CholavinNavigation) window.CholavinNavigation.visit(url);
        else window.location.href = url;
    }

    function openVoucherDrawer(row) {
        if (!window.CholavinShell || !row) return;
        var html = '' +
            '<div class="erp-drawer-hero erp-voucher-drawer-hero">' +
                '<div class="erp-drawer-avatar"><i class="ri-file-list-3-line"></i></div>' +
                '<div><span class="erp-eyebrow">Journal voucher</span><h5>' + escapeHtml(row.number) + '</h5><p>' + escapeHtml(row.type) + ' / ' + escapeHtml(row.reference_number) + '</p></div>' +
            '</div>' +
            '<div class="erp-drawer-kpis erp-drawer-kpis-3">' +
                '<article><small>Date</small><strong>' + escapeHtml(row.voucher_date) + '</strong><em>Voucher date</em></article>' +
                '<article><small>Lines</small><strong>' + escapeHtml(row.lines_count) + '</strong><em>Ledger rows</em></article>' +
                '<article><small>Total</small><strong>' + money(row.total_debit) + '</strong><em>Balanced value</em></article>' +
            '</div>' +
            '<div class="erp-drawer-tabs"><button class="active">Overview</button><button>Ledger Lines</button><button>Attachments</button><button>Audit</button></div>' +
            '<dl class="erp-drawer-meta"><dt>Reference</dt><dd>' + escapeHtml(row.reference_number) + '</dd><dt>Voucher Type</dt><dd>' + escapeHtml(row.type) + '</dd><dt>Rule</dt><dd>Debit and credit must balance before posting.</dd></dl>' +
            '<div class="erp-drawer-actions"><button type="button" class="btn btn-primary" data-voucher-drawer-action="new"><i class="ri-add-line me-1"></i>New Voucher</button><button type="button" class="btn btn-secondary" data-voucher-drawer-action="payments"><i class="ri-wallet-3-line me-1"></i>Payments</button><button type="button" class="btn btn-secondary" data-voucher-drawer-action="finance"><i class="ri-line-chart-line me-1"></i>Finance</button></div>';
        window.CholavinShell.openDrawer({title: row.number || 'Voucher Details', html: html});
    }

    function installQuickActions() {
        if (!window.CholavinShell) return;
        window.CholavinShell.setQuickActions([
            {label: 'New Voucher', icon: 'ri-add-circle-line', target: '#add-voucher', variant: 'primary'},
            {label: 'Payments', icon: 'ri-wallet-3-line', url: module.data('payment-url')},
            {label: 'Finance', icon: 'ri-line-chart-line', url: module.data('finance-url')},
            {label: 'Export PDF', icon: 'ri-file-pdf-2-line', target: '#vouchers-pdf'}
        ]);
    }

    var table = initializeDataTable({
        selector: '#vouchers-table',
        url: module.data('index-url'),
        filters: filters,
        columns: [
            {data: 'DT_RowIndex'},
            {data: 'number'},
            {data: 'voucher_date'},
            {data: 'type'},
            {data: 'reference_number', defaultContent: '-'},
            {data: 'lines_count'},
            {data: 'total_debit'},
            {data: 'actions', orderable: false, searchable: false}
        ],
        order: [[2, 'desc']]
    });

    function reindex() { $('#voucher-lines .voucher-line').each(function (index) { $(this).find('[data-field]').each(function () { var field = $(this); field.attr('name', 'lines[' + index + '][' + field.data('field') + ']'); }); }); }
    function totals() { var debit = 0; var credit = 0; $('.voucher-debit').each(function () { debit += parseFloat($(this).val()) || 0; }); $('.voucher-credit').each(function () { credit += parseFloat($(this).val()) || 0; }); $('#voucher-debit-total').text(debit.toFixed(2)); $('#voucher-credit-total').text(credit.toFixed(2)); $('#voucher-difference').text(Math.abs(debit - credit).toFixed(2)).toggleClass('text-danger', Math.abs(debit - credit) > 0.009); return debit > 0 && Math.abs(debit - credit) <= 0.009; }
    function addLine() { $('#voucher-lines').append($('#voucher-line-template').html()); reindex(); }

    $.validator.addMethod('balancedVoucher', function () { return totals(); }, 'Debit and credit totals must be equal and greater than zero.');
    form.validate({ignore: [], rules: {type: {required: true}, voucher_date: {required: true, date: true}, reference_number: {maxlength: 100}, narration: {maxlength: 2000}}, errorElement: 'span', errorClass: 'error text-danger', submitHandler: function (el) { if (!totals()) { toastr.error('Debit and credit totals must balance.'); return; } submitFormUsingAjax(el, {reset: true, onSuccess: function () { modal.hide(); table.ajax.reload(null, false); }}); }});
    form.on('input', '.voucher-debit,.voucher-credit', totals);
    $('#add-voucher-line').on('click', addLine);
    $(document).on('click', '.remove-voucher-line', function () { if ($('#voucher-lines .voucher-line').length <= 2) return; $(this).closest('.voucher-line').remove(); reindex(); totals(); });
    $('#add-voucher').on('click', function () { form.trigger('reset'); $('#voucher-lines').empty(); addLine(); addLine(); totals(); modal.show(); });

    var timer;
    $('#voucher-search').on('input', function () { var value = $(this).val(); clearTimeout(timer); timer = setTimeout(function () { table.search(value).draw(); }, 300); });
    $('#voucher-type-filter,#voucher-from-filter,#voucher-to-filter').on('change', function () { table.ajax.reload(null, false); });
    $('#reset-voucher-filters').on('click', function () { $('#voucher-filters').trigger('reset'); table.search('').ajax.reload(null, false); });
    $('#vouchers-pdf').on('click', function () { var params = filters(); params.search = $('#voucher-search').val(); window.location.href = module.data('pdf-url') + '?' + new URLSearchParams(params).toString(); });
    $('#vouchers-table').on('click', '[data-voucher-action="view"]', function (event) { event.preventDefault(); event.stopPropagation(); openVoucherDrawer(table.row($(this).closest('tr')).data()); });
    $('#vouchers-table').on('click', 'tbody tr', function () { openVoucherDrawer(table.row(this).data()); });
    $(document).on('click', '[data-voucher-drawer-action="new"]', function () { $('#add-voucher').trigger('click'); });
    $(document).on('click', '[data-voucher-drawer-action="payments"]', function () { visitUrl(module.data('payment-url')); });
    $(document).on('click', '[data-voucher-drawer-action="finance"]', function () { visitUrl(module.data('finance-url')); });

    addLine();
    addLine();
    installQuickActions();
})(window, window.jQuery);
