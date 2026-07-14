(function (window, document) {
    'use strict';

    var shell = null;
    var frame = null;
    var entryUrl = window.location.href;
    var messageType = 'cholavin:persistent-fullscreen-exit';

    function fullscreenElement() {
        return document.fullscreenElement || document.webkitFullscreenElement || null;
    }

    function exitFullscreen() {
        var exit = document.exitFullscreen || document.webkitExitFullscreen;
        if (exit && fullscreenElement()) {
            exit.call(document);
        }
    }

    function currentFrameUrl() {
        try {
            return frame?.contentWindow?.location?.href || entryUrl;
        } catch (error) {
            return entryUrl;
        }
    }

    function removeShellAndSynchronizeRoute() {
        if (!shell) return;

        var targetUrl = currentFrameUrl();
        shell.remove();
        shell = null;
        frame = null;
        document.body.classList.remove('fullscreen-enable');

        if (targetUrl !== entryUrl) {
            window.location.assign(targetUrl);
        }
    }

    function enterPersistentFullscreen() {
        if (shell) return;

        entryUrl = window.location.href;
        shell = document.createElement('div');
        shell.id = 'erp-persistent-fullscreen-shell';
        shell.setAttribute('aria-label', 'Cholavin ERP fullscreen workspace');
        var brandLogo = document.querySelector('.erp-brand-logo')?.src
            || '/frontend/assets/img/logo/logo-hm62.png';
        shell.innerHTML = [
            '<div class="erp-fullscreen-loader" role="status">',
            '<div class="loading-container">',
            '<div class="loading" aria-hidden="true"></div>',
            '<div class="loading-icon"><img alt="Cholavin"></div>',
            '<span class="loading-shape shape-one" aria-hidden="true"></span>',
            '<span class="loading-shape shape-two" aria-hidden="true"></span>',
            '<span class="loading-shape shape-three" aria-hidden="true"></span>',
            '</div>',
            '<div class="loading-copy">',
            '<span class="loading-label">Loading</span>',
            '<h2>Cholavin</h2>',
            '</div>',
            '</div>',
            '<iframe class="erp-fullscreen-frame" title="Cholavin ERP workspace"></iframe>'
        ].join('');
        shell.querySelector('.loading-icon img').src = brandLogo;

        frame = shell.querySelector('.erp-fullscreen-frame');
        frame.addEventListener('load', function () {
            shell?.classList.add('is-ready');
        });
        frame.src = entryUrl;
        document.body.appendChild(shell);

        var request = shell.requestFullscreen || shell.webkitRequestFullscreen;
        if (!request) {
            shell.remove();
            shell = null;
            frame = null;
            window.toastr?.error('Fullscreen mode is not supported by this browser.');
            return;
        }

        document.body.classList.add('fullscreen-enable');
        var result = request.call(shell);
        if (result && typeof result.catch === 'function') {
            result.catch(function () {
                removeShellAndSynchronizeRoute();
                window.toastr?.error('The browser could not open fullscreen mode.');
            });
        }
    }

    if (window.self !== window.top) {
        var embeddedToggle = document.querySelector('[data-toggle="fullscreen"]');
        var embeddedIcon = embeddedToggle?.querySelector('i');
        embeddedToggle?.setAttribute('aria-label', 'Exit fullscreen workspace');
        embeddedToggle?.setAttribute('title', 'Exit fullscreen workspace');
        embeddedIcon?.classList.replace('bx-fullscreen', 'bx-exit-fullscreen');
    }

    document.addEventListener('click', function (event) {
        var button = event.target.closest('[data-toggle="fullscreen"]');
        if (!button) return;

        event.preventDefault();
        event.stopPropagation();
        event.stopImmediatePropagation();

        if (window.self !== window.top) {
            window.top.postMessage({type: messageType}, window.location.origin);
            return;
        }

        if (shell || fullscreenElement()) {
            exitFullscreen();
            return;
        }

        enterPersistentFullscreen();
    }, true);

    if (window.self === window.top) {
        window.addEventListener('message', function (event) {
            if (event.origin === window.location.origin && event.data?.type === messageType) {
                exitFullscreen();
            }
        });

        document.addEventListener('fullscreenchange', function () {
            if (!fullscreenElement()) removeShellAndSynchronizeRoute();
        });
        document.addEventListener('webkitfullscreenchange', function () {
            if (!fullscreenElement()) removeShellAndSynchronizeRoute();
        });
    }
})(window, document);
