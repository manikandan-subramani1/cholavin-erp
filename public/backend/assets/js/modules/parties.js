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

    function visitUrl(url) {
        if (!url) return;
        if (window.CholavinNavigation) window.CholavinNavigation.visit(url);
        else window.location.assign(url);
    }

    function drawerSkeleton() {
        return '<div class="party-drawer-loading"><span class="spinner-border text-primary"></span><strong>Loading profile...</strong><small>Fetching scoped party details.</small></div>';
    }

    function partyInitials(party) {
        return String(party.name || 'P').split(/\s+/).slice(0, 2).map(function (word) { return word[0]; }).join('').toUpperCase();
    }

    function partyAddress(party) {
        var addresses = party.addresses || [];
        var address = addresses.find(function (item) { return item.is_default; }) || addresses[0] || {};
        return [address.address, address.city, address.state, address.postal_code].filter(Boolean).join(', ') || '-';
    }

    function openPartyDrawer(response) {
        if (!window.CholavinShell) return false;

        var party = response.data.party;
        var summary = response.data.summary;
        var permissions = response.data.permissions || {};
        var typeLabel = $root.data('party-label') || 'Party';
        var paymentLabel = $root.data('balance-type') === 'receivable' ? 'Receive Payment' : 'Make Payment';
        var documentLabel = $root.data('balance-type') === 'receivable' ? 'New Sale' : 'New Purchase';
        var whatsapp = party.mobile ? 'https://wa.me/' + String(party.mobile).replace(/\D/g, '') : '#';
        var email = party.email ? 'mailto:' + encodeURIComponent(party.email) : '#';
        var html = ''
            + '<div class="party-drawer-profile">'
            + '<div class="party-drawer-hero"><div class="party-avatar">' + safe(partyInitials(party)) + '</div><div><span class="erp-eyebrow">' + safe(typeLabel) + ' profile</span><h4>' + safe(party.name) + '</h4><p>' + safe(party.code) + ' &middot; ' + safe(party.group ? party.group.name : 'Ungrouped') + '</p></div></div>'
            + '<div class="party-drawer-status">' + (party.is_active ? '<span class="badge bg-success-subtle text-success">Active</span>' : '<span class="badge bg-secondary-subtle text-secondary">Inactive</span>') + '</div>'
            + '<div class="party-drawer-kpis">'
            + '<article><small>Outstanding</small><strong>' + money(summary.outstanding) + '</strong><span>' + safe($root.data('balance-label')) + '</span></article>'
            + '<article><small>Total Business</small><strong>' + money(summary.total_business) + '</strong><span>Posted transactions</span></article>'
            + '<article><small>Payments</small><strong>' + money(summary.payments) + '</strong><span>' + Number(summary.transactions || 0).toLocaleString('en-IN') + ' entries</span></article>'
            + '<article><small>Credit Available</small><strong>' + money(summary.credit_available) + '</strong><span>Limit balance</span></article>'
            + '</div>'
            + '<div class="party-drawer-tabs"><button class="is-active" type="button">Overview</button><button type="button">Ledger</button><button type="button">Documents</button><button type="button">Activity</button></div>'
            + '<div class="party-drawer-contact">'
            + '<span><i class="ri-phone-line"></i><strong>Mobile</strong><small>' + safe(party.mobile || '-') + '</small></span>'
            + '<span><i class="ri-mail-line"></i><strong>Email</strong><small>' + safe(party.email || '-') + '</small></span>'
            + '<span><i class="ri-government-line"></i><strong>GSTIN</strong><small>' + safe(party.gstin || '-') + '</small></span>'
            + '<span><i class="ri-map-pin-line"></i><strong>Address</strong><small>' + safe(partyAddress(party)) + '</small></span>'
            + '</div>'
            + '<div class="party-drawer-actions">'
            + '<a class="btn btn-soft-success" href="' + whatsapp + '" target="_blank" rel="noopener"><i class="ri-whatsapp-line"></i>WhatsApp</a>'
            + '<a class="btn btn-soft-info" href="' + email + '"><i class="ri-mail-send-line"></i>Email</a>'
            + '<button class="btn btn-primary" type="button" data-party-drawer-action="ledger"><i class="ri-file-list-3-line"></i>Open Ledger</button>'
            + '<a class="btn btn-secondary" href="' + safe($root.data('payment-url')) + '"><i class="ri-hand-coin-line"></i>' + safe(paymentLabel) + '</a>'
            + '<a class="btn btn-secondary" href="' + safe($root.data('document-url')) + '"><i class="ri-add-line"></i>' + safe(documentLabel) + '</a>'
            + (permissions.update ? '<button class="btn btn-soft-primary" type="button" data-party-drawer-action="edit"><i class="ri-edit-line"></i>Edit</button>' : '')
            + (permissions.delete ? '<button class="btn btn-soft-danger" type="button" data-party-drawer-action="delete"><i class="ri-delete-bin-line"></i>Delete</button>' : '')
            + '</div></div>';

        window.CholavinShell.openDrawer({title: party.name || typeLabel + ' Details', html: html});
        return true;
    }

    function openWorkspaceQuickActions() {
        if (!window.CholavinShell) return;
        var actions = [];
        if ($('#add-party').length) actions.push({label: 'Add ' + ($root.data('party-label') || 'Party'), icon: 'ri-user-add-line', handler: function () { $('#add-party').trigger('click'); }});
        if ($('#party-pdf').length) actions.push({label: 'Export PDF', icon: 'ri-file-pdf-2-line', handler: function () { $('#party-pdf').trigger('click'); }});
        actions.push({label: $root.data('balance-type') === 'receivable' ? 'Payment In' : 'Payment Out', icon: 'ri-hand-coin-line', handler: function () { visitUrl($root.data('payment-url')); }});
        actions.push({label: $root.data('balance-type') === 'receivable' ? 'New Sale' : 'New Purchase', icon: 'ri-add-circle-line', handler: function () { visitUrl($root.data('document-url')); }});
        window.CholavinShell.setQuickActions(actions);
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
            {data: 'is_active', orderable: false, searchable: false},
            {
                data: null,
                orderable: false,
                searchable: false,
                render: function (data, type, row) {
                    if (type !== 'display') return row.id;
                    return '<div class="party-row-actions">'
                        + '<button type="button" class="btn btn-sm btn-soft-primary" data-party-action="view" data-party-id="' + row.id + '" title="View profile"><i class="ri-eye-line"></i></button>'
                        + '<button type="button" class="btn btn-sm btn-soft-success" data-party-action="payment" data-party-id="' + row.id + '" title="Payment"><i class="ri-hand-coin-line"></i></button>'
                        + '</div>';
                }
            }
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
        var addressText = partyAddress(selectedParty);
        var initials = partyInitials(selectedParty);

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
        if (window.CholavinShell) {
            window.CholavinShell.openDrawer({title: 'Loading profile', html: drawerSkeleton()});
        } else {
            detailModal.show();
        }
        if (profileRequest) profileRequest.abort();
        profileRequest = CholavinAjax.request({
            url: base + '/' + selectedId,
            showLoader: false,
            onSuccess: function (response) {
                renderProfile(response);
                openPartyDrawer(response);
            },
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
        if (window.CholavinShell) window.CholavinShell.closeDrawer();
        modal.show();
    }

    $('#parties-table tbody').off('.partyModule')
        .on('click.partyModule', '[data-party-action]', function (event) {
            event.preventDefault();
            event.stopPropagation();
            var id = $(this).data('party-id');
            var action = $(this).data('party-action');
            if (action === 'payment') {
                visitUrl($root.data('payment-url'));
                return;
            }
            selectParty(id);
        })
        .on('click.partyModule', 'tr', function () {
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

    openWorkspaceQuickActions();

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

    $(document).off('click.partyDrawer').on('click.partyDrawer', '[data-party-drawer-action]', function () {
        var action = $(this).data('party-drawer-action');
        if (action === 'ledger') {
            detailModal.show();
            return;
        }
        if (action === 'edit') {
            editParty();
            return;
        }
        if (action === 'delete') {
            $('#delete-selected-party').trigger('click');
        }
    });

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
                    if (window.CholavinShell) window.CholavinShell.closeDrawer();
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
