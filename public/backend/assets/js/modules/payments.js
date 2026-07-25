(function (window, $) {
    'use strict';
    if (!$ || !$('#payments-module').length) return;

    var module = $('#payments-module');
    var form = $('#payment-form');
    var modal = bootstrap.Modal.getOrCreateInstance($('#payment-modal').get(0));
    var initialSearch = new URLSearchParams(window.location.search).get('search') || '';

    function escapeHtml(value) {
        return $('<div>').text(value == null || value === '' ? '-' : value).html();
    }

    function money(value) {
        return '\u20B9' + Number(String(value || '0').replace(/,/g, '') || 0).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }

    function typeLabel(value) {
        return String(value || '').replace(/_/g, ' ').replace(/\b\w/g, function (letter) { return letter.toUpperCase(); }) || '-';
    }

    function filters() {
        return {
            type: $('#payment-type-filter').val(),
            party_id: $('#payment-party-filter').val(),
            from_date: $('#payment-from-filter').val(),
            to_date: $('#payment-to-filter').val()
        };
    }

    function visitUrl(url) {
        if (!url) return;
        if (window.CholavinNavigation) window.CholavinNavigation.visit(url);
        else window.location.href = url;
    }

    function openPaymentDrawer(row) {
        if (!window.CholavinShell || !row) return;
        var partyName = row.party && row.party.name ? row.party.name : '-';
        var methodName = row.method && row.method.name ? row.method.name : '-';
        var html = '' +
            '<div class="erp-details-hero erp-payment-details-hero">' +
                '<div class="erp-details-avatar"><i class="ri-wallet-3-line"></i></div>' +
                '<div><span class="erp-eyebrow">Payment transaction</span><h5>' + escapeHtml(row.number) + '</h5><p>' + escapeHtml(typeLabel(row.type)) + ' / ' + escapeHtml(partyName) + '</p></div>' +
            '</div>' +
            '<div class="erp-details-kpis erp-details-kpis-3">' +
                '<article><small>Date</small><strong>' + escapeHtml(row.payment_date) + '</strong><em>Posted</em></article>' +
                '<article><small>Amount</small><strong>' + money(row.amount) + '</strong><em>Cash flow</em></article>' +
                '<article><small>Method</small><strong>' + escapeHtml(methodName) + '</strong><em>Settlement</em></article>' +
            '</div>' +
            '<div class="erp-details-tabs"><button class="active">Overview</button><button>Allocation</button><button>Proof</button><button>Ledger</button><button>Audit</button></div>' +
            '<dl class="erp-details-meta"><dt>Reference</dt><dd>' + escapeHtml(row.reference_number) + '</dd><dt>Party</dt><dd>' + escapeHtml(partyName) + '</dd><dt>Type</dt><dd>' + escapeHtml(typeLabel(row.type)) + '</dd></dl>' +
            '<div class="erp-details-actions"><button type="button" class="btn btn-primary" data-payment-details-action="new"><i class="ri-add-line me-1"></i>New Payment</button><button type="button" class="btn btn-secondary" data-payment-details-action="ledger"><i class="ri-book-open-line me-1"></i>Party Ledger</button><button type="button" class="btn btn-secondary" data-payment-details-action="voucher"><i class="ri-file-list-3-line me-1"></i>Journal</button></div>';
        window.CholavinShell.openDrawer({title: row.number || 'Payment Details', html: html});
    }

    function installQuickActions() {
        if (!window.CholavinShell) return;
        window.CholavinShell.setQuickActions([
            {label: 'Receive', icon: 'ri-arrow-down-circle-line', target: '#payment-type-filter', value: 'customer_collection', variant: 'primary'},
            {label: 'Pay Supplier', icon: 'ri-arrow-up-circle-line', target: '#payment-type-filter', value: 'supplier_payment'},
            {label: 'Expense', icon: 'ri-receipt-line', target: '#payment-type-filter', value: 'expense'},
            {label: 'New Entry', icon: 'ri-wallet-3-line', target: '#add-payment'},
            {label: 'Export PDF', icon: 'ri-file-pdf-2-line', target: '#payments-pdf'}
        ]);
    }

    var table = initializeDataTable({
        selector: '#payments-table',
        url: module.data('index-url'),
        filters: filters,
        columns: [
            {data: 'DT_RowIndex'},
            {data: 'number'},
            {data: 'payment_date'},
            {data: 'type'},
            {data: 'party.name', defaultContent: '-', orderable: false, searchable: false},
            {data: 'method.name', defaultContent: '-', orderable: false, searchable: false},
            {data: 'reference_number', defaultContent: '-'},
            {data: 'amount'},
            {data: 'actions', orderable: false, searchable: false}
        ],
        order: [[2, 'desc']]
    });

    if (initialSearch) { $('#payment-search').val(initialSearch); table.search(initialSearch).draw(); }

    form.validate({ignore: [], rules: {type: {required: true}, payment_date: {required: true, date: true}, amount: {required: true, number: true, min: 0.01}, reference_number: {maxlength: 100}, notes: {maxlength: 1000}}, errorElement: 'span', errorClass: 'error text-danger', highlight: function (el) { $(el).addClass('is-invalid'); }, unhighlight: function (el) { $(el).removeClass('is-invalid'); }, submitHandler: function (el) { submitFormUsingAjax(el, {reset: true, onSuccess: function () { modal.hide(); table.ajax.reload(null, false); }}); }});
    $('#add-payment').on('click', function () { form.trigger('reset'); if (module.data('context')) form.find('[name="type"]').val(module.data('context')); modal.show(); });

    var timer;
    $('#payment-search').on('input', function () { var value = $(this).val(); clearTimeout(timer); timer = setTimeout(function () { table.search(value).draw(); }, 300); });
    $('#payment-type-filter,#payment-party-filter,#payment-from-filter,#payment-to-filter').on('change', function () { table.ajax.reload(null, false); });
    $('#reset-payment-filters').on('click', function () { $('#payment-filters').trigger('reset'); if (module.data('context')) $('#payment-type-filter').val(module.data('context')); table.search('').ajax.reload(null, false); });
    $('#payments-pdf').on('click', function () { var params = filters(); params.search = $('#payment-search').val(); window.location.href = module.data('pdf-url') + '?' + new URLSearchParams(params).toString(); });
    $('#payments-table').on('click', '[data-payment-action="view"]', function (event) { event.preventDefault(); event.stopPropagation(); openPaymentDrawer(table.row($(this).closest('tr')).data()); });
    $('#payments-table').on('click', 'tbody tr', function () { openPaymentDrawer(table.row(this).data()); });
    $(document).on('click', '[data-payment-details-action="new"]', function () { $('#add-payment').trigger('click'); });
    $(document).on('click', '[data-payment-details-action="ledger"]', function () { visitUrl(module.data('ledger-url')); });
    $(document).on('click', '[data-payment-details-action="voucher"]', function () { visitUrl(module.data('voucher-url')); });

    installQuickActions();
})(window, window.jQuery);
