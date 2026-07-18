(function (window, $) {
    'use strict';

    if (!$) return;

    function csrfToken() {
        return $('meta[name="csrf-token"]').attr('content') || '';
    }

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': csrfToken(),
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    });

    $(function () {
        var token = csrfToken();

        $('form').each(function () {
            var $form = $(this);
            var method = ($form.attr('method') || 'GET').toUpperCase();
            if (method === 'GET' || $form.find('input[name="_token"]').length) return;

            $('<input>', {type: 'hidden', name: '_token', value: token}).appendTo($form);
        });

        enhanceIndexPages();
        window.setTimeout(enhanceIndexPages, 0);
    });

    function enhanceIndexPages() {
        var $container = $('.page-content .container-fluid').first();
        if (!$container.length || $container.find('.party-workspace').length) return;

        var $tables = $container.find('.card table.table').filter(function () {
            var $table = $(this);
            return !$table.closest('.modal').length
                && !$table.closest('.receipt-paper').length
                && !$table.hasClass('erp-dashboard-table');
        });
        if (!$tables.length) return;

        $container.addClass('erp-index-page');
        var $toolbar = $container.find('.page-title-box').first();
        if ($toolbar.length) {
            $toolbar.addClass('erp-index-toolbar');
            var $heading = $toolbar.find('h1, h2, h3, h4').first();
            if ($heading.length && !$toolbar.find('.erp-eyebrow').length) {
                $('<span>', {class: 'erp-eyebrow', text: 'Cholavin ERP'}).insertBefore($heading);
            }
        }

        $tables.each(function () {
            var $table = $(this).addClass('erp-index-table');
            $table.closest('.card-body').addClass('erp-index-table-body');
            $table.closest('.card').addClass('erp-index-card');
        });

        $container.find('.card').each(function () {
            var $card = $(this);
            if ($card.hasClass('erp-index-card')) return;
            if ($card.find('form, select, input[type="date"], input[type="search"]').length) {
                $card.addClass('erp-index-filter-card');
            }
        });

        $container.find('.dataTables_filter input').each(function () {
            var $input = $(this);
            $input.attr({
                placeholder: $input.attr('placeholder') || 'Search records...',
                'aria-label': 'Search table records'
            });
        });
    }

    $(document).on('init.dt.erp-index draw.dt.erp-index', function () {
        window.setTimeout(enhanceIndexPages, 0);
    });

    function notify(type, message) {
        if (window.toastr && typeof window.toastr[type] === 'function') {
            window.toastr[type](message);
            return;
        }

        if (type === 'error') {
            console.error(message);
        }
    }

    function responseSucceeded(response) {
        return response && (response.success === true || response.status === true);
    }

    function responseRedirect(response) {
        return response && (response.redirect || (response.data && response.data.redirect)) || null;
    }

    function showLoader() {
        if (window.CholavinPageLoader) window.CholavinPageLoader.show(true);
    }

    function hideLoader() {
        if (window.CholavinPageLoader) window.CholavinPageLoader.hide();
    }

    function startButton(button) {
        var $ = window.jQuery;
        var $button = button && button.jquery ? button : $(button);
        if (!$button.length || $button.data('cholavin-loading')) return;
        $button.data('cholavin-loading', true);
        $button.data('cholavin-original-html', $button.html());
        $button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span> Processing...');
    }

    function stopButton(button) {
        var $ = window.jQuery;
        var $button = button && button.jquery ? button : $(button);
        if (!$button.length) return;
        var original = $button.data('cholavin-original-html');
        $button.prop('disabled', false);
        if (original !== undefined) $button.html(original);
        $button.removeData('cholavin-loading cholavin-original-html');
    }

    window.AppLoader = {show: showLoader, hide: hideLoader};
    window.ButtonLoader = {start: startButton, stop: stopButton};

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
                var $feedback = $('<span class="invalid-feedback" data-ajax-error></span>').text(messages[0]);
                var $select2 = $input.next('.select2');
                ($select2.length ? $feedback.insertAfter($select2) : $feedback.insertAfter($input));
            });
        }

        notify('error', response.message || 'Something went wrong. Please try again.');
    };

    window.AjaxErrorHandler = {handle: window.handleAjaxError};

    window.CholavinAjax = {
        request: function (options) {
            var $ = window.jQuery;
            if (!$) throw new Error('jQuery is required for AJAX requests.');

            var settings = $.extend({
                method: 'GET',
                data: {},
                processData: true,
                contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
                showLoader: true,
                disableButton: null,
                form: null
            }, options || {});

            if (settings.showLoader) showLoader();
            if (settings.disableButton) startButton(settings.disableButton);

            return $.ajax({
                url: settings.url,
                type: settings.method,
                data: settings.data,
                processData: settings.processData,
                contentType: settings.contentType,
                headers: {
                    'X-CSRF-TOKEN': csrfToken(),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            }).done(function (response) {
                if (!responseSucceeded(response)) {
                    notify('error', response.message || 'The operation could not be completed.');
                    return;
                }
                if (typeof settings.onSuccess === 'function') settings.onSuccess(response);
            }).fail(function (xhr) {
                if (xhr.statusText === 'abort') return;
                window.handleAjaxError(xhr, settings.form);
                if (typeof settings.onError === 'function') settings.onError(xhr);
            }).always(function () {
                if (settings.showLoader) hideLoader();
                if (settings.disableButton) stopButton(settings.disableButton);
                if (typeof settings.onComplete === 'function') settings.onComplete();
            });
        }
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

        return window.CholavinAjax.request({
            url: $form.attr('action'),
            method: ($form.attr('method') || 'POST').toUpperCase(),
            data: new FormData(form),
            processData: false,
            contentType: false,
            form: $form,
            disableButton: $button,
            onSuccess: function (response) {
                if (options.reset !== false) form.reset();
                $form.find('.is-invalid').removeClass('is-invalid');
                if (options.modal) $(options.modal).modal('hide');
                if (options.table) window.reloadDataTable(options.table);
                if (response.refresh && response.refresh.datatable && window.activeDataTable) {
                    window.activeDataTable.ajax.reload(null, false);
                }
                notify('success', response.message);
                if (typeof options.onSuccess === 'function') options.onSuccess(response);
                if (!options.onSuccess) {
                    var redirect = responseRedirect(response);
                    if (redirect && window.CholavinNavigation) window.CholavinNavigation.visit(redirect);
                }
            }
        });
    };

    if (window.jQuery) {
        window.jQuery(document).on('submit.cholavin-ajax', '.ajax-form', function (event) {
            event.preventDefault();
            var form = this;
            var $form = window.jQuery(form);
            if ($form.data('ajax-submitting')) return;
            if (typeof $form.valid === 'function' && !$form.valid()) return;

            $form.data('ajax-submitting', true);
            window.submitFormUsingAjax(form, {
                reset: $form.data('reset') !== false,
                modal: $form.data('modal'),
                table: $form.data('table'),
                onSuccess: function (response) {
                    var redirect = responseRedirect(response);
                    if (redirect && window.CholavinNavigation) {
                        window.CholavinNavigation.visit(redirect);
                    }
                    $form.trigger('cholavin:success', [response]);
                }
            }).always(function () {
                $form.removeData('ajax-submitting');
            });
        });
    }

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

        var table = $(options.selector).DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            stateSave: options.stateSave !== false,
            searchDelay: options.searchDelay || 500,
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
        window.activeDataTable = table;
        return table;
    };
})(window, window.jQuery);
