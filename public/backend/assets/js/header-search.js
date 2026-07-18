(function (window, $) {
    'use strict';

    if (!$) return;

    $(function () {
        var $form = $('#global-search-form');
        var $input = $('#search-options');
        var $dropdown = $('#search-dropdown');
        var $results = $('#global-search-results');
        var $close = $('#search-close-options');

        if (!$form.length || !$input.length || !$dropdown.length || !$results.length) return;

        var timer = null;
        var request = null;
        var cache = new Map();

        function escapeHtml(value) {
            return $('<div>').text(value == null ? '' : String(value)).html();
        }

        function state(icon, message) {
            $results.html(
                '<div class="global-search-state">' +
                '<i class="' + icon + '" aria-hidden="true"></i>' +
                '<span>' + escapeHtml(message) + '</span>' +
                '</div>'
            );
        }

        function show() {
            $dropdown.addClass('show');
            $close.removeClass('d-none');
        }

        function hide() {
            $dropdown.removeClass('show');
        }

        function render(payload) {
            var groups = payload && Array.isArray(payload.groups) ? payload.groups : [];

            if (!groups.length) {
                state('ri-search-eye-line', 'No matching records found.');
                return;
            }

            $results.html(groups.map(function (group) {
                var items = (group.items || []).map(function (item) {
                    return '<a class="global-search-item" href="' + escapeHtml(item.url) + '">' +
                        '<span class="global-search-icon"><i class="' + escapeHtml(item.icon || 'ri-search-line') + '"></i></span>' +
                        '<span class="global-search-copy"><strong>' + escapeHtml(item.title) + '</strong>' +
                        '<small>' + escapeHtml(item.meta || '') + '</small></span>' +
                        '<i class="ri-arrow-right-up-line global-search-arrow" aria-hidden="true"></i>' +
                        '</a>';
                }).join('');

                return '<section class="global-search-group" data-search-group="' + escapeHtml(group.key) + '">' +
                    '<div class="global-search-heading"><span>' + escapeHtml(group.label) + '</span>' +
                    '<span class="badge bg-light text-muted">' + (group.items || []).length + '</span></div>' +
                    items + '</section>';
            }).join(''));
        }

        function search() {
            var query = $.trim($input.val());
            var cacheKey = query.toLowerCase();

            if (query.length < 2) {
                if (request) request.abort();
                state('ri-search-line', 'Enter at least 2 characters to search.');
                query.length ? show() : hide();
                return;
            }

            show();

            if (cache.has(cacheKey)) {
                render(cache.get(cacheKey));
                return;
            }

            if (request) request.abort();
            state('ri-loader-4-line global-search-spinner', 'Searching ERP records...');

            var activeRequest = window.CholavinAjax.request({
                url: $form.data('search-url'),
                method: 'GET',
                data: {q: query},
                showLoader: false,
                onSuccess: function (response) {
                    var payload = response.data || {groups: []};
                    cache.set(cacheKey, payload);
                    render(payload);
                },
                onError: function (xhr) {
                    if (xhr.statusText !== 'abort') {
                        state('ri-error-warning-line', xhr.responseJSON?.message || 'Search is temporarily unavailable.');
                    }
                },
                onComplete: function () {
                    if (request === activeRequest) request = null;
                }
            });
            request = activeRequest;
        }

        $input
            .on('input.erpSearch', function () {
                window.clearTimeout(timer);
                timer = window.setTimeout(search, 300);
            })
            .on('focus.erpSearch', function () {
                if ($.trim($input.val()).length >= 2) search();
            })
            .on('keyup.erpSearch', function (event) {
                event.stopImmediatePropagation();
            })
            .on('keydown.erpSearch', function (event) {
                if (event.key === 'Escape') hide();
            });

        $form.on('submit.erpSearch', function (event) {
            event.preventDefault();
            $results.find('.global-search-item').first().trigger('click');
        });

        $close.on('click.erpSearch', function () {
            window.clearTimeout(timer);
            if (request) request.abort();
            cache.clear();
            state('ri-search-line', 'Enter at least 2 characters to search.');
        });

        $(window).on('business-context:changed.erpSearch', function () {
            cache.clear();
            if ($.trim($input.val()).length >= 2) search();
        });
    });
})(window, window.jQuery);
