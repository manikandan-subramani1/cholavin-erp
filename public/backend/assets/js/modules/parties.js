(function ($) {
    'use strict';

    var $root = $('#party-module');
    if (!$root.length || !$.fn.DataTable) return;

    var base = $root.data('base-url');
    var modal = bootstrap.Modal.getOrCreateInstance($('#party-modal').get(0));
    var detailModal = bootstrap.Modal.getOrCreateInstance($('#party-detail-modal').get(0));
    var selectedId = null;
    var selectedParty = null;
    var transactionTable = null;
    var profileRequest = null;
    var initialSearch = new URLSearchParams(window.location.search).get('search') || '';

    function money(value) {
        return '₹' + Number(value || 0).toLocaleString('en-IN', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function reloadMetrics() {
        CholavinAjax.request({
            url: base + '/summary-metrics',
            showLoader: false,
            onSuccess: function (response) {
                $.each(response.data || {}, function (key, value) {
                    var formatted = ['outstanding', 'overdue', 'business_month', 'payments_month'].includes(key) ? money(value) : Number(value || 0).toLocaleString('en-IN');
                    $('[data-party-metric="' + key + '"]').text(formatted);
                });
            }
        });
    }

    function safe(value) {
        return $('<div>').text(value || '').html();
    }

    var partyTable = initializeDataTable({
        selector: '#parties-table',
        url: base,
        filters: function () {
            return {
                group_id: $('#party-group-filter').val(),
                status: $('#party-status-filter').val()
            };
        },
        pageLength: 10,
        columns: [
            {
                data: null,
                name: 'name',
                render: function (data, type, row) {
                    if (type !== 'display') return row.name;
                    var meta = [row.code, row.mobile].filter(Boolean).map(safe).join(' · ');
                    return '<div class="party-list-name">' + safe(row.name) + '</div>' +
                        '<div class="party-list-meta">' + meta + '</div>';
                }
            },
            {data: 'group.name', defaultContent: 'Ungrouped', orderable: false, searchable: false},
            {data: 'mobile', defaultContent: '—'},
            {data: 'gstin', defaultContent: '—'},
            {data: 'address_text', orderable: false, searchable: false},
            {
                data: 'current_balance',
                name: 'current_balance',
                orderable: false,
                searchable: false,
                render: function (value, type) {
                    if (type !== 'display') return value;
                    return '<span class="party-list-balance ' + (Number(value) > 0 ? 'has-balance' : '') + '">' + money(value) + '</span>';
                }
            },
            {data: 'is_active', orderable: false, searchable: false}
        ],
        order: [[0, 'asc']]
    });

    function loadTransactions() {
        var url = base + '/' + selectedId + '/transactions';
        if (transactionTable) {
            transactionTable.ajax.url(url).load(null, false);
            return;
        }

        transactionTable = initializeDataTable({
            selector: '#party-transactions-table',
            url: url,
            filters: function () {
                return {
                    status: $('#transaction-status-filter').val(),
                    from_date: $('#transaction-from-filter').val(),
                    to_date: $('#transaction-to-filter').val()
                };
            },
            columns: [
                {data: 'DT_RowIndex'},
                {data: 'source_type'},
                {data: 'number'},
                {data: 'transaction_date'},
                {data: 'total'},
                {data: 'balance'},
                {data: 'due_date'},
                {data: 'status', orderable: false, searchable: false},
                {data: 'action', orderable: false, searchable: false}
            ],
            order: [[3, 'desc']]
        });
    }

    function renderProfile(response) {
        selectedParty = response.data.party;
        var summary = response.data.summary;
        var addresses = selectedParty.addresses || [];
        var address = addresses.find(function (item) { return item.is_default; }) || addresses[0] || {};
        var addressText = [address.address, address.city, address.state, address.postal_code].filter(Boolean).join(', ') || '-';
        var initials = String(selectedParty.name || 'P').split(/\s+/).slice(0, 2).map(function (word) { return word[0]; }).join('').toUpperCase();

        $('#party-avatar').text(initials);
        $('#party-name').text(selectedParty.name);
        $('#party-code').text(selectedParty.code);
        $('#party-group').text(selectedParty.group ? selectedParty.group.name : 'Ungrouped');
        $('#party-mobile').text(selectedParty.mobile || '-');
        $('#party-email').text(selectedParty.email || '-');
        $('#party-gstin').text(selectedParty.gstin || '-');
        $('#party-address').text(addressText);
        $('#party-status').html(selectedParty.is_active
            ? '<span class="badge bg-success-subtle text-success">Active</span>'
            : '<span class="badge bg-secondary-subtle text-secondary">Inactive</span>');
        $('#party-outstanding').text(money(summary.outstanding));
        $('#party-business').text(money(summary.total_business));
        $('#party-payments').text(money(summary.payments));
        $('#party-credit').text(money(summary.credit_available));
        $('#party-transaction-count').text(summary.transactions);
        $('#party-balance-label').text($root.data('balance-label'));
        $('#party-whatsapp').attr('href', selectedParty.mobile ? 'https://wa.me/' + String(selectedParty.mobile).replace(/\D/g, '') : '#').toggleClass('disabled', !selectedParty.mobile);
        $('#party-mail').attr('href', selectedParty.email ? 'mailto:' + encodeURIComponent(selectedParty.email) : '#').toggleClass('disabled', !selectedParty.email);
        $('#edit-selected-party').toggleClass('d-none', !response.data.permissions.update);
        $('#delete-selected-party').toggleClass('d-none', !response.data.permissions.delete);
        loadTransactions();
    }

    function selectParty(id) {
        if (!id) return;
        selectedId = Number(id);
        $('#parties-table tbody tr').removeClass('is-selected').filter(function () {
            var row = partyTable.row(this).data();
            return row && Number(row.id) === selectedId;
        }).addClass('is-selected');
        $('#party-empty-state').addClass('d-none');
        $('#party-detail-content').removeClass('d-none').addClass('is-loading');
        detailModal.show();
        if (profileRequest) profileRequest.abort();
        profileRequest = CholavinAjax.request({
            url: base + '/' + selectedId,
            showLoader: false,
            onSuccess: renderProfile,
            onComplete: function () { $('#party-detail-content').removeClass('is-loading'); }
        });
    }

    function resetForm() {
        var $form = $('#party-form');
        $form.trigger('reset').attr('action', base);
        $form.find('[name="_method"]').val('POST');
        $('#party-active').prop('checked', true);
        $form.find('[name="balance_type"]').val($root.data('balance-type'));
        $form.find('.is-invalid').removeClass('is-invalid');
        $form.find('[data-ajax-error]').remove();
    }

    function editParty() {
        if (!selectedParty) return;
        resetForm();
        var $form = $('#party-form');
        $form.attr('action', base + '/' + selectedParty.id);
        $form.find('[name="_method"]').val('PUT');
        ['name', 'code', 'group_id', 'mobile', 'email', 'gstin', 'pan', 'credit_limit', 'opening_balance', 'balance_type'].forEach(function (key) {
            $form.find('[name="' + key + '"]').val(selectedParty[key] == null ? '' : selectedParty[key]);
        });
        var addresses = selectedParty.addresses || [];
        var address = addresses.find(function (item) { return item.is_default; }) || addresses[0] || {};
        ['address', 'city', 'state', 'postal_code'].forEach(function (key) {
            $form.find('[name="' + key + '"]').val(address[key] == null ? '' : address[key]);
        });
        $('#party-active').prop('checked', !!selectedParty.is_active);
        detailModal.hide();
        modal.show();
    }

    $('#parties-table tbody').off('.partyModule').on('click.partyModule', 'tr', function () {
        var data = partyTable.row(this).data();
        if (data) selectParty(data.id);
    });
    $('#parties-table').off('.partyModule').on('draw.dt.partyModule', function () {
        if (selectedId) {
            $('#parties-table tbody tr').filter(function () {
                var row = partyTable.row(this).data();
                return row && Number(row.id) === selectedId;
            }).addClass('is-selected');
        }
    });

    var searchTimer;
    if (initialSearch) {
        $('#party-search').val(initialSearch);
        partyTable.search(initialSearch).draw();
    }
    $('#party-search').off('.partyModule').on('input.partyModule', function () {
        clearTimeout(searchTimer);
        var value = $(this).val();
        searchTimer = setTimeout(function () { partyTable.search(value).draw(); }, 250);
    });
    $('#party-group-filter,#party-status-filter').off('.partyModule').on('change.partyModule', function () {
        selectedId = null;
        partyTable.ajax.reload(null, false);
    });
    $('#reset-party-filters').off('.partyModule').on('click.partyModule', function () {
        $('#party-group-filter,#party-status-filter').val('');
        $('#party-search').val('');
        selectedId = null;
        partyTable.search('').ajax.reload(null, false);
    });
    $('#transaction-status-filter,#transaction-from-filter,#transaction-to-filter').off('.partyModule').on('change.partyModule', function () {
        if (transactionTable) transactionTable.ajax.reload(null, false);
    });
    $('#reset-transaction-filters').off('.partyModule').on('click.partyModule', function () {
        $('#transaction-status-filter,#transaction-from-filter,#transaction-to-filter').val('');
        if (transactionTable) transactionTable.ajax.reload(null, false);
    });
    $('#add-party').off('.partyModule').on('click.partyModule', function () { resetForm(); modal.show(); });
    $('#edit-selected-party').off('.partyModule').on('click.partyModule', editParty);

    $('#party-form').validate({
        ignore: [],
        rules: {
            name: {required: true, minlength: 2, maxlength: 190},
            code: {required: true, maxlength: 60},
            mobile: {minlength: 7, maxlength: 30},
            email: {email: true},
            credit_limit: {number: true, min: 0},
            opening_balance: {number: true, min: 0}
        },
        errorElement: 'span',
        errorClass: 'invalid-feedback',
        highlight: function (element) { $(element).addClass('is-invalid').closest('.form-group').addClass('has-error'); },
        unhighlight: function (element) { $(element).removeClass('is-invalid').closest('.form-group').removeClass('has-error'); },
        submitHandler: function (form) {
            submitFormUsingAjax(form, {
                reset: false,
                onSuccess: function (response) {
                    modal.hide();
                    selectedId = Number(response.data.id);
                    partyTable.ajax.reload(function () { selectParty(selectedId); }, false);
                    reloadMetrics();
                }
            });
        }
    });

    $('#delete-selected-party').off('.partyModule').on('click.partyModule', function () {
        if (!selectedId) return;
        Swal.fire({
            title: 'Delete ' + (selectedParty ? selectedParty.name : 'party') + '?',
            text: 'This cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Delete'
        }).then(function (result) {
            if (!result.isConfirmed) return;
            CholavinAjax.request({
                url: base + '/' + selectedId,
                method: 'DELETE',
                disableButton: '#delete-selected-party',
                onSuccess: function (response) {
                    toastr.success(response.message);
                    selectedId = null;
                    selectedParty = null;
                    detailModal.hide();
                    $('#party-detail-content').addClass('d-none');
                    $('#party-empty-state').removeClass('d-none');
                    partyTable.ajax.reload(null, false);
                    reloadMetrics();
                }
            });
        });
    });

    $('#party-pdf').off('.partyModule').on('click.partyModule', function () {
        var params = new URLSearchParams({
            status: $('#party-status-filter').val() || '',
            group_id: $('#party-group-filter').val() || '',
            search: $('#party-search').val() || ''
        });
        window.location.assign($root.data('pdf-url') + '?' + params.toString());
    });
})(window.jQuery);
