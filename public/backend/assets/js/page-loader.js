(function (window, $) {
    'use strict';

    if (!$) return;

    var $loader = $('#erp-page-loader');
    if (!$loader.length) return;

    var minimumVisibleTime = 350;
    var fallbackTimer = null;
    var shownAt = window.performance.now();

    function clearFallback() {
        if (!fallbackTimer) return;
        window.clearTimeout(fallbackTimer);
        fallbackTimer = null;
    }

    function show(withFallback) {
        clearFallback();
        shownAt = window.performance.now();
        $loader.removeClass('is-hidden').attr('aria-hidden', 'false');
        $('html').addClass('erp-page-is-loading');

        if (withFallback) fallbackTimer = window.setTimeout(hide, 8000);
    }

    function hide() {
        clearFallback();
        var elapsed = window.performance.now() - shownAt;
        var delay = Math.max(0, minimumVisibleTime - elapsed);

        window.setTimeout(function () {
            $loader.addClass('is-hidden').attr('aria-hidden', 'true');
            $('html').removeClass('erp-page-is-loading');
        }, delay);
    }

    function isInternalNavigation($link, event) {
        if (!$link.length || event.isDefaultPrevented() || event.which !== 1) return false;
        if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return false;
        if ($link.attr('target') && $link.attr('target') !== '_self') return false;
        if ($link.is('[download], [data-bs-toggle], [data-toggle]')) return false;

        var href = $link.attr('href') || '';
        if (!href || href.indexOf('#') === 0 || href.indexOf('javascript:') === 0) return false;

        var target = new URL($link.prop('href'), window.location.href);
        if (target.origin !== window.location.origin) return false;

        return target.pathname !== window.location.pathname || target.search !== window.location.search;
    }

    $(document)
        .on('click.cholavinLoader', 'a[href]', function (event) {
            if (!isInternalNavigation($(this), event)) return;
            window.setTimeout(function () {
                if (!event.isDefaultPrevented()) show(true);
            }, 0);
        })
        .on('submit.cholavinLoader', 'form', function (event) {
            window.setTimeout(function () {
                if (!event.isDefaultPrevented()) show(true);
            }, 0);
        });

    $(window)
        .on('beforeunload.cholavinLoader', function () { show(false); })
        .on('load.cholavinLoader pageshow.cholavinLoader', hide);

    window.CholavinPageLoader = {show: show, hide: hide};
})(window, window.jQuery);
