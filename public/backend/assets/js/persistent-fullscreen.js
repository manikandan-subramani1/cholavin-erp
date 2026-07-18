(function (window, $) {
    'use strict';

    if (!$) return;

    var $shell = $();
    var $frame = $();
    var entryUrl = window.location.href;
    var messageType = 'cholavin:persistent-fullscreen-exit';

    function fullscreenElement() {
        return document.fullscreenElement || document.webkitFullscreenElement || null;
    }

    function exitFullscreen() {
        var exit = document.exitFullscreen || document.webkitExitFullscreen;
        if (exit && fullscreenElement()) exit.call(document);
    }

    function currentFrameUrl() {
        try {
            return $frame.get(0)?.contentWindow?.location?.href || entryUrl;
        } catch (error) {
            return entryUrl;
        }
    }

    function removeShellAndSynchronizeRoute() {
        if (!$shell.length) return;

        var targetUrl = currentFrameUrl();
        $shell.remove();
        $shell = $();
        $frame = $();
        $('body').removeClass('fullscreen-enable');

        if (targetUrl !== entryUrl) window.location.assign(targetUrl);
    }

    function enterPersistentFullscreen() {
        if ($shell.length) return;

        entryUrl = window.location.href;
        var brandLogo = $('.erp-brand-logo').first().attr('src') || '/frontend/assets/img/logo/logo-hm62.png';

        $shell = $('<div>', {
            id: 'erp-persistent-fullscreen-shell',
            'aria-label': 'Cholavin ERP fullscreen workspace'
        }).html([
            '<div class="erp-fullscreen-loader" role="status">',
            '<div class="loading-container">',
            '<div class="loading" aria-hidden="true"></div>',
            '<div class="loading-icon"><img alt="Cholavin"></div>',
            '<span class="loading-shape shape-one" aria-hidden="true"></span>',
            '<span class="loading-shape shape-two" aria-hidden="true"></span>',
            '<span class="loading-shape shape-three" aria-hidden="true"></span>',
            '</div>',
            '<div class="loading-copy"><span class="loading-label">Loading</span><h2>Cholavin</h2></div>',
            '</div>',
            '<iframe class="erp-fullscreen-frame" title="Cholavin ERP workspace"></iframe>'
        ].join(''));

        $shell.find('.loading-icon img').attr('src', brandLogo);
        $frame = $shell.find('.erp-fullscreen-frame').one('load', function () {
            $shell.addClass('is-ready');
        }).attr('src', entryUrl);
        $shell.appendTo('body');

        var shellElement = $shell.get(0);
        var request = shellElement.requestFullscreen || shellElement.webkitRequestFullscreen;
        if (!request) {
            $shell.remove();
            $shell = $();
            $frame = $();
            window.toastr?.error('Fullscreen mode is not supported by this browser.');
            return;
        }

        $('body').addClass('fullscreen-enable');
        var result = request.call(shellElement);
        if (result && typeof result.catch === 'function') {
            result.catch(function () {
                removeShellAndSynchronizeRoute();
                window.toastr?.error('The browser could not open fullscreen mode.');
            });
        }
    }

    function handleToggle(event) {
        event.preventDefault();
        event.stopPropagation();
        event.stopImmediatePropagation();

        if (window.self !== window.top) {
            window.top.postMessage({type: messageType}, window.location.origin);
        } else if ($shell.length || fullscreenElement()) {
            exitFullscreen();
        } else {
            enterPersistentFullscreen();
        }
    }

    function bindToggles() {
        var $toggles = $('[data-toggle="fullscreen"]');
        $toggles.off('click.cholavinFullscreen').on('click.cholavinFullscreen', handleToggle);

        if (window.self !== window.top) {
            $toggles.attr({
                'aria-label': 'Exit fullscreen workspace',
                title: 'Exit fullscreen workspace'
            }).find('i').removeClass('bx-fullscreen').addClass('bx-exit-fullscreen');
        }
    }

    bindToggles();
    $(document).on('cholavin:page-loaded.cholavinFullscreen', bindToggles);

    if (window.self === window.top) {
        $(window).on('message.cholavinFullscreen', function (event) {
            var original = event.originalEvent;
            if (original.origin === window.location.origin && original.data?.type === messageType) exitFullscreen();
        });

        $(document).on('fullscreenchange.cholavinFullscreen webkitfullscreenchange.cholavinFullscreen', function () {
            if (!fullscreenElement()) removeShellAndSynchronizeRoute();
        });
    }
})(window, window.jQuery);
