(function (window, $) {
    'use strict';
    if (!$ || !$('#deliveries-module').length) return;

    var module = $('#deliveries-module');
    var form = $('#delivery-form');
    var modal = bootstrap.Modal.getOrCreateInstance($('#delivery-modal').get(0));

    function escapeHtml(value) { return $('<div>').text(value == null || value === '' ? '-' : value).html(); }
    function money(value) { return '\u20B9' + Number(String(value || '0').replace(/,/g, '') || 0).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}); }
    function filters() { return {status: $('#delivery-status-filter').val(), vehicle_id: $('#delivery-vehicle-filter').val(), driver_id: $('#delivery-driver-filter').val(), from_date: $('#delivery-from-filter').val(), to_date: $('#delivery-to-filter').val()}; }
    function visitUrl(url) { if (!url) return; if (window.CholavinNavigation) window.CholavinNavigation.visit(url); else window.location.href = url; }

    function openDeliveryDrawer(row) {
        if (!window.CholavinShell || !row) return;
        var vehicle = row.vehicle && row.vehicle.name ? row.vehicle.name : '-';
        var driver = row.driver && row.driver.name ? row.driver.name : '-';
        var route = row.route && row.route.name ? row.route.name : '-';
        var html = '' +
            '<div class="erp-details-hero erp-delivery-details-hero"><div class="erp-details-avatar"><i class="ri-truck-line"></i></div><div><span class="erp-eyebrow">Delivery assignment</span><h5>' + escapeHtml(row.document_number) + '</h5><p>' + escapeHtml(row.party_name) + ' / ' + escapeHtml(route) + '</p></div></div>' +
            '<div class="erp-details-kpis erp-details-kpis-3"><article><small>Scheduled</small><strong>' + escapeHtml(row.scheduled_at) + '</strong><em>Planned time</em></article><article><small>Cash</small><strong>' + money(row.cash_collected) + '</strong><em>Collected</em></article><article><small>Expense</small><strong>' + money(row.delivery_expense) + '</strong><em>Route cost</em></article></div>' +
            '<div class="erp-details-tabs"><button class="active">Overview</button><button>Items</button><button>Address</button><button>Driver</button><button>POD</button><button>Activity</button></div>' +
            '<dl class="erp-details-meta"><dt>Vehicle</dt><dd>' + escapeHtml(vehicle) + '</dd><dt>Driver</dt><dd>' + escapeHtml(driver) + '</dd><dt>Status</dt><dd>' + (row.status || '-') + '</dd></dl>' +
            '<div class="erp-details-actions"><button type="button" class="btn btn-primary" data-delivery-details-action="edit" data-url="' + escapeHtml(row.show_url || '') + '"><i class="ri-edit-line me-1"></i>Update</button><button type="button" class="btn btn-secondary" data-delivery-details-action="routes"><i class="ri-road-map-line me-1"></i>Routes</button><button type="button" class="btn btn-secondary" data-delivery-details-action="drivers"><i class="ri-user-location-line me-1"></i>Drivers</button></div>';
        window.CholavinShell.openDrawer({title: row.document_number || 'Delivery Details', html: html});
    }

    function installQuickActions() {
        if (!window.CholavinShell) return;
        window.CholavinShell.setQuickActions([
            {label: 'Assign', icon: 'ri-truck-line', target: '#add-delivery', variant: 'primary'},
            {label: 'Pending', icon: 'ri-time-line', target: '#delivery-status-filter', value: 'pending'},
            {label: 'Out', icon: 'ri-road-map-line', target: '#delivery-status-filter', value: 'out_for_delivery'},
            {label: 'Delivered', icon: 'ri-checkbox-circle-line', target: '#delivery-status-filter', value: 'delivered'},
            {label: 'Export PDF', icon: 'ri-file-pdf-2-line', target: '#deliveries-pdf'}
        ]);
    }

    var table = initializeDataTable({selector: '#deliveries-table', url: module.data('index-url'), filters: filters, columns: [{data: 'DT_RowIndex'}, {data: 'document_number'}, {data: 'party_name', defaultContent: '-'}, {data: 'vehicle.name', defaultContent: '-', orderable: false, searchable: false}, {data: 'driver.name', defaultContent: '-', orderable: false, searchable: false}, {data: 'route.name', defaultContent: '-', orderable: false, searchable: false}, {data: 'scheduled_at', defaultContent: '-'}, {data: 'cash_collected'}, {data: 'delivery_expense'}, {data: 'status'}, {data: 'action'}], order: [[6, 'desc']]});

    function reset() { form.trigger('reset'); form.attr('action', module.data('store-url')); form.find('[name="_method"]').val('POST'); $('#delivery-modal-title').text('Delivery Assignment'); form.validate().resetForm(); }
    function editDelivery(url) { CholavinAjax.request({url: url, onSuccess: function (response) { reset(); var delivery = response.data; form.attr('action', url); form.find('[name="_method"]').val('PUT'); $('#delivery-modal-title').text('Update Delivery'); $.each(['commercial_document_id', 'vehicle_id', 'driver_id', 'route_id', 'status', 'cash_collected', 'delivery_expense', 'notes'], function (index, key) { form.find('[name="' + key + '"]').val(delivery[key] == null ? '' : delivery[key]); }); form.find('[name="scheduled_at"]').val(delivery.scheduled_at ? String(delivery.scheduled_at).substring(0, 16) : ''); modal.show(); }}); }

    form.validate({ignore: [], rules: {commercial_document_id: {required: true}, scheduled_at: {date: true}, status: {required: true}, cash_collected: {number: true, min: 0}, delivery_expense: {number: true, min: 0}, proof: {extension: 'jpg|jpeg|png|webp|pdf'}, notes: {maxlength: 2000}}, errorElement: 'span', errorClass: 'error text-danger', highlight: function (el) { $(el).addClass('is-invalid'); }, unhighlight: function (el) { $(el).removeClass('is-invalid'); }, submitHandler: function (el) { submitFormUsingAjax(el, {reset: false, onSuccess: function () { modal.hide(); table.ajax.reload(null, false); }}); }});
    $('#add-delivery').on('click', function () { reset(); modal.show(); });
    $(document).on('click', '.edit-delivery', function (event) { event.preventDefault(); event.stopPropagation(); editDelivery($(this).data('url')); });
    $(document).on('click', '[data-delivery-details-action="edit"]', function () { editDelivery($(this).data('url')); });
    $(document).on('click', '[data-delivery-details-action="routes"]', function () { visitUrl(module.data('routes-url')); });
    $(document).on('click', '[data-delivery-details-action="drivers"]', function () { visitUrl(module.data('drivers-url')); });

    var timer;
    $('#delivery-search').on('input', function () { var value = $(this).val(); clearTimeout(timer); timer = setTimeout(function () { table.search(value).draw(); }, 300); });
    $('#delivery-status-filter,#delivery-vehicle-filter,#delivery-driver-filter,#delivery-from-filter,#delivery-to-filter').on('change', function () { table.ajax.reload(null, false); });
    $('#reset-delivery-filters').on('click', function () { $('#delivery-filters').trigger('reset'); table.search('').ajax.reload(null, false); });
    $('#deliveries-pdf').on('click', function () { var params = filters(); params.search = $('#delivery-search').val(); window.location.href = module.data('pdf-url') + '?' + new URLSearchParams(params).toString(); });
    $('#deliveries-table').on('click', 'tbody tr', function () { openDeliveryDrawer(table.row(this).data()); });

    installQuickActions();
})(window, window.jQuery);
