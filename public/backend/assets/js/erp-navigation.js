(function (window, $) {
    'use strict';

    if (!$) return;

    var contentSelector = '.page-content .container-fluid';
    var currentRequest = null;
    var pageEventHandlers = [];

    function loader(show) {
        if (!window.CholavinPageLoader) return;
        show ? window.CholavinPageLoader.show(true) : window.CholavinPageLoader.hide();
    }

    function notify(type, message) {
        if (window.toastr && typeof window.toastr[type] === 'function') {
            window.toastr[type](message);
        }
    }

    function eventSnapshot(target) {
        if (!$._data) return [];
        var events = $._data(target, 'events') || {};

        return Object.keys(events).flatMap(function (type) {
            return events[type].map(function (handler) {
                return {target: target, type: type, guid: handler.guid, selector: handler.selector, handler: handler.handler};
            });
        });
    }

    function rememberPageHandlers(before) {
        if (!$._data) return;
        var known = new Set(before.map(function (item) {
            return item.type + ':' + item.guid + ':' + (item.selector || '');
        }));

        pageEventHandlers = eventSnapshot(document).concat(eventSnapshot(window)).filter(function (item) {
            return !known.has(item.type + ':' + item.guid + ':' + (item.selector || ''));
        });
    }

    function cleanupPage() {
        $(document).trigger('cholavin:page-before-unload');

        $.each(pageEventHandlers, function (_, item) {
            $(item.target).off(item.type, item.selector, item.handler);
        });
        pageEventHandlers = [];

        if ($.fn.DataTable) {
            $.each($.fn.dataTable.tables(), function (_, table) {
                if ($.fn.DataTable.isDataTable(table)) $(table).DataTable().destroy(true);
            });
        }

        $('.modal-backdrop, .offcanvas-backdrop').remove();
        $('body').removeClass('modal-open offcanvas-backdrop');
    }

    function installStyles(sourceDocument) {
        $('[data-erp-page-style]').remove();
        var $template = $(sourceDocument).find('#erp-page-styles').first();
        if (!$template.length) return;

        $($template.prop('content')).find('style, link[rel="stylesheet"]').each(function () {
            $(this).clone().attr('data-erp-page-style', 'true').appendTo('head');
        });
    }

    function executeScripts(sourceDocument) {
        var $template = $(sourceDocument).find('#erp-page-scripts').first();
        if (!$template.length) return $.Deferred().resolve().promise();

        var scripts = $($template.prop('content')).find('script').toArray();
        var before = eventSnapshot(document).concat(eventSnapshot(window));
        var sequence = $.Deferred().resolve().promise();

        $.each(scripts, function (index, source) {
            sequence = sequence.then(function () {
                if (source.src) {
                    var deferred = $.Deferred();
                    var absolute = new URL(source.src, window.location.href).href;
                    $('<script>', {src: absolute})
                        .one('load', function () { $(this).remove(); deferred.resolve(); })
                        .one('error', function () { $(this).remove(); deferred.reject(new Error('Unable to load page script.')); })
                        .appendTo('body');
                    return deferred.promise();
                }

                if ($.trim(source.textContent)) {
                    Function(source.textContent + '\n//# sourceURL=cholavin-page-' + index + '.js').call(window);
                }

                return undefined;
            });
        });

        return sequence.then(function () {
            var deferred = $.Deferred();
            window.setTimeout(function () {
                rememberPageHandlers(before);
                deferred.resolve();
            }, 0);
            return deferred.promise();
        });
    }

    function updateMenu(url) {
        $('#navbar-nav a.nav-link').each(function () {
            var $link = $(this);
            if ($link.data('bs-toggle')) return;

            var active = false;
            try {
                active = new URL($link.attr('href'), window.location.href).pathname === url.pathname;
            } catch (error) {}

            $link.toggleClass('active', active);
        });
    }

    function render(html, url, push) {
        var parsed = new DOMParser().parseFromString(html, 'text/html');
        var $parsed = $(parsed);
        var $incoming = $parsed.find(contentSelector).first();
        var $current = $(contentSelector).first();

        if (!$incoming.length || !$current.length || !$parsed.find('#erp-page-scripts').length) {
            window.location.assign(url.href);
            return $.Deferred().resolve().promise();
        }

        cleanupPage();
        installStyles(parsed);
        $current.empty().append($incoming.contents());
        document.title = parsed.title || document.title;

        if (push) window.history.pushState({cholavin: true}, '', url.href);
        updateMenu(url);
        $(window).scrollTop(0);

        return executeScripts(parsed).then(function () {
            $(document).trigger('cholavin:page-loaded', [{url: url.href}]);
        });
    }

    function visit(target, options) {
        options = options || {};
        var url = new URL(target, window.location.href);

        if (currentRequest) currentRequest.abort();
        loader(true);

        var request = $.ajax({
            url: url.href,
            type: 'GET',
            dataType: 'html',
            headers: {'Accept': 'text/html', 'X-Cholavin-Navigation': '1'}
        });
        currentRequest = request;

        request.done(function (html) {
            render(html, url, options.push !== false);
        }).fail(function (xhr, status) {
            if (status !== 'abort') {
                notify('error', xhr.status === 403
                    ? 'You do not have permission to open this module.'
                    : 'Unable to load this module.');
            }
        }).always(function () {
            if (currentRequest === request) currentRequest = null;
            loader(false);
        });

        return request;
    }

    function navigable($link, event) {
        if (!$link.length || event.isDefaultPrevented() || event.which !== 1 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return false;
        if ($link.attr('target') && $link.attr('target') !== '_self') return false;
        if ($link.is('[download], [data-no-ajax], .no-ajax, [data-bs-toggle], [data-toggle]')) return false;

        var href = $link.attr('href') || '';
        if (!href || href.charAt(0) === '#' || href.indexOf('javascript:') === 0) return false;

        var url = new URL($link.prop('href'), window.location.href);
        if (url.origin !== window.location.origin || url.pathname.indexOf('/admin/') !== 0) return false;
        return !/(\/pdf|\/print|\/export\/|\/backups\/)/.test(url.pathname);
    }

    $(document).on('click.cholavinNavigation', 'a[href]', function (event) {
        var $link = $(this);
        if (!navigable($link, event)) return;
        event.preventDefault();
        visit($link.prop('href'));
    });

    $(window).on('popstate.cholavinNavigation', function () {
        visit(window.location.href, {push: false});
    });

    $(function () {
        installStyles(document);
        executeScripts(document).then(function () {
            $(document).trigger('cholavin:page-loaded', [{url: window.location.href}]);
        });
    });

    window.CholavinNavigation = {visit: visit};
})(window, window.jQuery);
