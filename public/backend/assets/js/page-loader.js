(function (window, document) {
    'use strict';

    var loader = document.getElementById('erp-page-loader');
    if (!loader) return;

    var minimumVisibleTime = 350;
    var fallbackTimer = null;
    var shownAt = window.performance.now();

    function clearFallback() {
        if (fallbackTimer) {
            window.clearTimeout(fallbackTimer);
            fallbackTimer = null;
        }
    }

    function show(withFallback) {
        clearFallback();
        shownAt = window.performance.now();
        loader.classList.remove('is-hidden');
        loader.setAttribute('aria-hidden', 'false');
        document.documentElement.classList.add('erp-page-is-loading');

        if (withFallback) {
            fallbackTimer = window.setTimeout(hide, 8000);
        }
    }

    function hide() {
        clearFallback();
        var elapsed = window.performance.now() - shownAt;
        var delay = Math.max(0, minimumVisibleTime - elapsed);

        window.setTimeout(function () {
            loader.classList.add('is-hidden');
            loader.setAttribute('aria-hidden', 'true');
            document.documentElement.classList.remove('erp-page-is-loading');
        }, delay);
    }

    function isInternalNavigation(link, event) {
        if (!link || event.defaultPrevented || event.button !== 0) return false;
        if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return false;
        if (link.target && link.target !== '_self') return false;
        if (link.hasAttribute('download') || link.dataset.bsToggle || link.dataset.toggle) return false;

        var href = link.getAttribute('href') || '';
        if (!href || href.startsWith('#') || href.startsWith('javascript:')) return false;

        var target = new URL(link.href, window.location.href);
        if (target.origin !== window.location.origin) return false;

        return target.pathname !== window.location.pathname
            || target.search !== window.location.search;
    }

    document.addEventListener('click', function (event) {
        var link = event.target.closest('a[href]');
        if (!isInternalNavigation(link, event)) return;

        window.setTimeout(function () {
            if (!event.defaultPrevented) show(true);
        }, 0);
    });

    document.addEventListener('submit', function (event) {
        window.setTimeout(function () {
            if (!event.defaultPrevented) show(true);
        }, 0);
    });

    window.addEventListener('beforeunload', function () {
        show(false);
    });
    window.addEventListener('load', hide);
    window.addEventListener('pageshow', hide);

    window.CholavinPageLoader = {show: show, hide: hide};
})(window, document);
