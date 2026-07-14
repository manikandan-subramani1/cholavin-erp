(function (window) {
    'use strict';

    function csrfToken() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.content : '';
    }

    if (window.jQuery) {
        window.jQuery.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': csrfToken(),
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        var token = csrfToken();

        document.querySelectorAll('form').forEach(function (form) {
            var method = (form.getAttribute('method') || 'GET').toUpperCase();
            if (method === 'GET' || form.querySelector('input[name="_token"]')) return;

            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = '_token';
            input.value = token;
            form.appendChild(input);
        });

        enhanceIndexPages();
        window.setTimeout(enhanceIndexPages, 0);
    });

    function enhanceIndexPages() {
        var container = document.querySelector('.page-content .container-fluid');
        if (!container || container.querySelector('.party-workspace')) return;

        var tables = Array.prototype.filter.call(container.querySelectorAll('.card table.table'), function (table) {
            return !table.closest('.modal')
                && !table.closest('.receipt-paper')
                && !table.classList.contains('erp-dashboard-table');
        });
        if (!tables.length) return;

        container.classList.add('erp-index-page');
        var toolbar = container.querySelector('.page-title-box');
        if (toolbar) {
            toolbar.classList.add('erp-index-toolbar');
            var heading = toolbar.querySelector('h1, h2, h3, h4');
            if (heading && !toolbar.querySelector('.erp-eyebrow')) {
                var eyebrow = document.createElement('span');
                eyebrow.className = 'erp-eyebrow';
                eyebrow.textContent = 'Cholavin ERP';
                heading.parentNode.insertBefore(eyebrow, heading);
            }
        }

        tables.forEach(function (table) {
            table.classList.add('erp-index-table');
            var body = table.closest('.card-body');
            var card = table.closest('.card');
            if (body) body.classList.add('erp-index-table-body');
            if (card) card.classList.add('erp-index-card');
        });

        Array.prototype.forEach.call(container.querySelectorAll('.card'), function (card) {
            if (card.classList.contains('erp-index-card')) return;
            if (card.querySelector('form, select, input[type="date"], input[type="search"]')) {
                card.classList.add('erp-index-filter-card');
            }
        });

        Array.prototype.forEach.call(container.querySelectorAll('.dataTables_filter input'), function (input) {
            input.placeholder = input.placeholder || 'Search records...';
            input.setAttribute('aria-label', 'Search table records');
        });
    }

    if (window.jQuery) {
        window.jQuery(document).on('init.dt.erp-index draw.dt.erp-index', function () {
            window.setTimeout(enhanceIndexPages, 0);
        });
    }

    function notify(type, message) {
        if (window.toastr && typeof window.toastr[type] === 'function') {
            window.toastr[type](message);
            return;
        }

        if (type === 'error') {
            console.error(message);
        }
    }

    if (window.jQuery && window.jQuery.fn.dataTable) {
        window.jQuery.fn.dataTable.ext.errMode = 'none';
        window.jQuery.extend(true, window.jQuery.fn.dataTable.defaults, {
            autoWidth: false,
            language: {
                search: '',
                searchPlaceholder: 'Search records...',
                lengthMenu: 'Show _MENU_',
                info: 'Showing _START_ to _END_ of _TOTAL_ records',
                infoEmpty: 'No records available',
                emptyTable: 'No records found.',
                zeroRecords: 'No matching records found.',
                processing: 'Loading records...'
            }
        });
        window.jQuery(document).off('error.dt.erp').on('error.dt.erp', function () {
            notify('error', 'Unable to load table data. Please try again.');
        });
    }

    window.handleAjaxError = function (xhr, form) {
        var $ = window.jQuery;
        var $form = form && form.jquery ? form : (form && $ ? $(form) : null);

        if ($form) {
            $form.find('.is-invalid').removeClass('is-invalid');
            $form.find('.invalid-feedback[data-ajax-error]').remove();
        }

        var response = xhr.responseJSON || {};
        if (xhr.status === 419) {
            notify('error', 'Your secure session expired. Refresh this page and submit again.');
            return;
        }

        if (xhr.status === 422 && $form && response.errors) {
            $.each(response.errors, function (field, messages) {
                var normalized = field.replace(/\./g, '\\.');
                var $input = $form.find('[name="' + normalized + '"], [name="' + normalized + '[]"]').first();
                $input.addClass('is-invalid');
                $('<span class="invalid-feedback" data-ajax-error></span>').text(messages[0]).insertAfter($input);
            });
        }

        notify('error', response.message || 'Something went wrong. Please try again.');
    };

    window.reloadDataTable = function (selector) {
        var $ = window.jQuery;
        if ($ && $.fn.DataTable && $.fn.DataTable.isDataTable(selector)) {
            $(selector).DataTable().ajax.reload(null, false);
        }
    };

    window.submitFormUsingAjax = function (form, options) {
        var $ = window.jQuery;
        if (!$) {
            throw new Error('jQuery is required for AJAX form submission.');
        }

        options = options || {};
        var $form = $(form);
        var $button = $form.find('[type="submit"]').first();
        var original = $button.html();

        return $.ajax({
            url: $form.attr('action'),
            type: ($form.attr('method') || 'POST').toUpperCase(),
            data: new FormData(form),
            processData: false,
            contentType: false,
            headers: {'X-CSRF-TOKEN': csrfToken()},
            beforeSend: function () {
                $button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Processing...');
            },
            success: function (response) {
                if (!response.status) {
                    notify('error', response.message || 'The operation could not be completed.');
                    return;
                }

                if (options.reset !== false) form.reset();
                $form.find('.is-invalid').removeClass('is-invalid');
                if (options.modal) $(options.modal).modal('hide');
                if (options.table) window.reloadDataTable(options.table);
                notify('success', response.message);
                if (typeof options.onSuccess === 'function') options.onSuccess(response);
            },
            error: function (xhr) { window.handleAjaxError(xhr, $form); },
            complete: function () { $button.prop('disabled', false).html(original); }
        });
    };

    window.initializeDataTable = function (options) {
        var $ = window.jQuery;
        if (!$ || !$.fn.DataTable) {
            throw new Error('jQuery DataTables is required.');
        }

        $.fn.dataTable.ext.errMode = 'none';

        var columns = (options.columns || []).map(function (column) {
            var normalized = Object.assign({}, column);
            if (normalized.data === 'DT_RowIndex' || normalized.data === 'action') {
                normalized.orderable = false;
                normalized.searchable = false;
            }

            return normalized;
        });
        var initialOrder = Array.isArray(options.order) ? options.order : [];

        return $(options.selector).DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            searching: true,
            ordering: true,
            pageLength: options.pageLength || 10,
            lengthMenu: [10, 25, 50, 100],
            ajax: {
                url: options.url,
                data: function (data) {
                    if (typeof options.filters === 'function') Object.assign(data, options.filters());
                },
                error: function () { notify('error', 'Unable to load table data.'); }
            },
            columns: columns,
            order: initialOrder,
            language: {
                search: '',
                searchPlaceholder: 'Search records...',
                lengthMenu: 'Show _MENU_',
                info: 'Showing _START_ to _END_ of _TOTAL_ records',
                infoEmpty: 'No records available',
                processing: 'Loading records...',
                emptyTable: 'No records found.',
                zeroRecords: 'No matching records found.'
            }
        });
    };
})(window);
