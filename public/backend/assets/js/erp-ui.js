(function (window, $) {
    'use strict';

    if (!$) return;

    var storageKeys = {
        pinned: 'cholavin.erp.pinned-pages',
        recent: 'cholavin.erp.recent-pages',
        sidebarSize: 'cholavin.erp.sidebar-size'
    };
    var mainSelector = '#app-content, #erp-main-content';

    function readList(type) {
        try {
            var value = JSON.parse(window.localStorage.getItem(storageKeys[type]) || '[]');
            return Array.isArray(value) ? value : [];
        } catch (error) {
            return [];
        }
    }

    function writeList(type, items) {
        window.localStorage.setItem(storageKeys[type], JSON.stringify(items.slice(0, type === 'recent' ? 5 : 6)));
    }

    function currentPage() {
        return {
            url: window.location.pathname + window.location.search,
            title: $.trim(String(document.title || 'Cholavin ERP').split('|')[0]) || 'Cholavin ERP'
        };
    }

    function pageIcon(url) {
        if (url.includes('/documents/sales') || url.includes('/sales/')) return 'ri-receipt-line';
        if (url.includes('/documents/purchase')) return 'ri-shopping-cart-2-line';
        if (url.includes('/parties/')) return 'ri-group-line';
        if (url.includes('/inventory/')) return 'ri-archive-stack-line';
        if (url.includes('/reports/')) return 'ri-bar-chart-box-line';
        if (url.includes('/access/')) return 'ri-shield-user-line';
        return 'ri-file-list-3-line';
    }

    function renderList(type) {
        var items = readList(type);
        var $section = $('#erp-' + type + '-section');
        var $target = $('#erp-' + type + '-pages').empty();

        $section.toggleClass('d-none', !items.length);
        $.each(items, function (_, item) {
            $('<a>', {href: item.url, title: item.title})
                .append($('<i>', {class: pageIcon(item.url)}))
                .append($('<span>').text(item.title))
                .appendTo($target);
        });
    }

    function rememberRecentPage() {
        if (!window.location.pathname.startsWith('/admin/') || window.location.pathname.includes('/login')) return;
        var page = currentPage();
        var items = readList('recent').filter(function (item) { return item.url !== page.url; });
        items.unshift(page);
        writeList('recent', items);
        renderList('recent');
    }

    function pinCurrentPage() {
        var page = currentPage();
        var items = readList('pinned');
        var exists = items.some(function (item) { return item.url === page.url; });

        if (exists) {
            items = items.filter(function (item) { return item.url !== page.url; });
            window.toastr?.info('Page removed from pinned shortcuts.');
        } else {
            items.unshift(page);
            window.toastr?.success('Page pinned to the sidebar.');
        }

        writeList('pinned', items);
        renderList('pinned');
    }

    function toggleQuickCreate() {
        var element = $('#erp-quick-create-toggle').get(0);
        if (element) window.bootstrap.Dropdown.getOrCreateInstance(element).toggle();
    }

    function setDomainClass() {
        var path = window.location.pathname;
        var domain = 'general';
        if (path.includes('/parties/')) domain = path.includes('suppliers') ? 'supplier' : 'customer';
        else if (path.includes('/documents/sales') || path.includes('/sales/')) domain = 'sales';
        else if (path.includes('/documents/purchase')) domain = 'purchase';
        else if (path.includes('/inventory/')) domain = 'inventory';
        else if (path.includes('/accounts/')) domain = 'accounting';
        else if (path.includes('/deliveries')) domain = 'delivery';
        else if (path.includes('/reports/')) domain = 'reports';
        else if (path.includes('/access/') || path.includes('/settings')) domain = 'administration';
        $('body').attr('data-erp-domain', domain);
    }

    function restoreSidebarSize() {
        if (window.innerWidth < 1025) return;

        try {
            var size = window.localStorage.getItem(storageKeys.sidebarSize);
            if (size !== 'lg' && size !== 'sm') return;
            $('html').attr('data-sidebar-size', size);
            window.sessionStorage.setItem('data-sidebar-size', size);
        } catch (error) {
            // Storage can be unavailable in restricted browser sessions.
        }
    }

    function rememberSidebarSize() {
        if (window.innerWidth < 1025) return;

        try {
            var size = $('html').attr('data-sidebar-size') === 'sm' ? 'sm' : 'lg';
            window.localStorage.setItem(storageKeys.sidebarSize, size);
            window.sessionStorage.setItem('data-sidebar-size', size);
        } catch (error) {
            // The visual toggle still works when persistence is unavailable.
        }
    }

    function enhancePage() {
        setDomainClass();
        var $main = $(mainSelector).first();
        $main.find('> .card.erp-panel').first().addClass('erp-page-command-card');
        $main.find('.accordion').has('form').addClass('erp-smart-filter-panel');
        $main.find('.table').addClass('erp-data-table');
        $main.find('.table-responsive').addClass('erp-datatable-shell');
        $main.find('.table-responsive').closest('.card').addClass('erp-table-workspace');
        $main.find('.card').addClass('erp-surface');
        $main.find('input[type="search"]').attr('autocomplete', 'off');
        rememberRecentPage();
    }

    function announce(message) {
        $('#erp-live-region').text('');
        window.setTimeout(function () { $('#erp-live-region').text(message); }, 20);
    }

    function refreshSidebarBadges() {
        var $menu = $('.app-menu.navbar-menu');
        var url = $menu.data('sidebar-badges-url');
        if (!url || !window.CholavinAjax) return;

        window.CholavinAjax.request({
            url: url,
            method: 'GET',
            showLoader: false,
            onSuccess: function (response) {
                var counts = response.data || {};

                $('[data-sidebar-badge]').each(function () {
                    var $badge = $(this);
                    var value = Number(counts[$badge.data('sidebar-badge')] || 0);
                    $badge.text(value > 99 ? '99+' : value).toggleClass('d-none', value < 1);
                });

                $('.erp-notification-dot').toggleClass('d-none', Object.values(counts).every(function (value) {
                    return Number(value || 0) < 1;
                }));
            }
        });
    }

    function updateModuleSummary($table, summary) {
        if (!summary) return;
        var $root = $table.closest('[data-module-summary-root], #app-content, #erp-main-content').find('[data-module-summary-root]').first();
        if (!$root.length) return;

        $.each(summary, function (key, value) {
            var $target = $root.find('[data-summary-key="' + key + '"]');
            var format = $target.data('summary-format');
            var number = Number(value || 0);
            var output = format === 'money'
                ? '₹' + number.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})
                : number.toLocaleString('en-IN', {maximumFractionDigits: format === 'quantity' ? 3 : 0});
            $target.text(output);
        });
    }

    function closeDrawer() {
        $('#erp-right-drawer').removeClass('is-open').attr('aria-hidden', 'true');
        $('body').removeClass('erp-drawer-open');
    }

    function openDrawer(options) {
        options = options || {};
        var $drawer = $('#erp-right-drawer');
        var $body = $drawer.find('[data-erp-drawer-body]');
        if (!$drawer.length) return;

        $('#erp-right-drawer-title').text(options.title || 'Details');
        $body.html(options.html || '');
        $drawer.addClass('is-open').attr('aria-hidden', 'false');
        $('body').addClass('erp-drawer-open');
        $drawer.find('[data-erp-close-drawer]').trigger('focus');
    }

    function closeTimeline() {
        $('#erp-activity-timeline').removeClass('is-open').attr('aria-hidden', 'true');
        $('body').removeClass('erp-timeline-open');
    }

    function openTimeline(items) {
        var $timeline = $('#erp-activity-timeline');
        var $body = $timeline.find('[data-erp-timeline-body]').empty();
        if (!$timeline.length) return;

        $.each(items || [], function (_, item) {
            $('<article>', {class: 'erp-activity-item'})
                .append($('<span>', {class: 'erp-activity-dot'}))
                .append($('<div>')
                    .append($('<strong>').text(item.title || 'Activity'))
                    .append($('<small>').text(item.time || ''))
                    .append($('<p>').text(item.description || '')))
                .appendTo($body);
        });

        $timeline.addClass('is-open').attr('aria-hidden', 'false');
        $('body').addClass('erp-timeline-open');
        $timeline.find('[data-erp-close-timeline]').trigger('focus');
    }

    function setQuickActions(actions) {
        var $bar = $('#erp-quick-action-bar').empty();
        if (!$bar.length) return;

        $.each(actions || [], function (_, action) {
            var $button = $('<button>', {type: 'button', class: 'erp-quick-action-item', title: action.title || action.label || ''})
                .append($('<i>', {class: action.icon || 'ri-flashlight-line'}))
                .append($('<span>').text(action.label || 'Action'));

            if (typeof action.handler === 'function') {
                $button.on('click.erpQuickAction', action.handler);
            } else if (action.url) {
                $button.on('click.erpQuickAction', function () {
                    if (window.CholavinNavigation) window.CholavinNavigation.visit(action.url);
                    else window.location.assign(action.url);
                });
            }

            $button.appendTo($bar);
        });

        $bar.toggleClass('is-open', (actions || []).length > 0).attr('aria-hidden', (actions || []).length > 0 ? 'false' : 'true');
    }
    function calculate(key) {
        var $display = $('#erp-calculator-display');
        var expression = String($display.data('expression') || '');

        if (key === '=') {
            if (!expression || !/^[0-9+\-*/. ()]+$/.test(expression)) return;
            try {
                var result = Function('"use strict"; return (' + expression + ')')();
                if (!Number.isFinite(result)) throw new Error('Invalid result');
                $display.val(String(Math.round((result + Number.EPSILON) * 100000) / 100000)).data('expression', String(result));
            } catch (error) {
                $display.val('Error').data('expression', '');
            }
            return;
        }

        expression += key;
        $display.data('expression', expression).val(expression || '0');
    }

    function shortcutTarget(selector) {
        var $target = $(selector).first();
        if ($target.length) $target.get(0).click();
    }

    $(function () {
        restoreSidebarSize();
        $('<div>', {id: 'erp-live-region', class: 'visually-hidden', 'aria-live': 'polite', 'aria-atomic': 'true'}).appendTo('body');
        renderList('pinned');
        renderList('recent');
        enhancePage();
        refreshSidebarBadges();

        $('[data-erp-close-drawer]').on('click.erpUi', closeDrawer);
        $('[data-erp-close-timeline]').on('click.erpUi', closeTimeline);
        $(document).on('click.erpUi', '[data-erp-drawer-url]', function () {
            var $trigger = $(this);
            window.CholavinAjax.request({
                url: $trigger.data('erp-drawer-url'),
                method: 'GET',
                onSuccess: function (response) {
                    openDrawer({
                        title: $trigger.data('erp-drawer-title'),
                        html: response.data && response.data.html ? response.data.html : ''
                    });
                }
            });
        });
        $('#erp-sidebar-quick-create, #erp-mobile-create').on('click.erpUi', toggleQuickCreate);
        $('#erp-pin-current-page').on('click.erpUi', pinCurrentPage);
        $('#topnav-hamburger-icon').on('click.erpSidebarState', function () {
            window.setTimeout(rememberSidebarSize, 0);
        });
        $('[data-clear-list]').on('click.erpUi', function () {
            var type = $(this).data('clear-list');
            writeList(type, []);
            renderList(type);
        });

        $('#erp-mobile-more').on('click.erpUi', function () { $('#topnav-hamburger-icon').trigger('click'); });
        $('#erp-context-mobile-toggle').on('click.erpUi', function () { $('#erp-context-panel').addClass('is-open'); $('body').addClass('erp-context-open'); });
        $('#erp-context-mobile-close').on('click.erpUi', function () { $('#erp-context-panel').removeClass('is-open'); $('body').removeClass('erp-context-open'); });

        $('[data-calc-key]').on('click.erpUi', function () { calculate(String($(this).data('calc-key'))); });
        $('[data-calc-action="clear"]').on('click.erpUi', function () { $('#erp-calculator-display').val('0').data('expression', ''); });
        $('#erp-print-current').on('click.erpUi', function () { window.print(); });

        $(document)
            .on('xhr.dt.erpModuleSummary', 'table', function (event, settings, json) { updateModuleSummary($(this), json && json.summary); })
            .on('cholavin:page-loaded.erpUi', function () { enhancePage(); refreshSidebarBadges(); announce('Page loaded'); })
            .on('business-context:changed.erpUi', refreshSidebarBadges)
            .on('keydown.erpUi', function (event) {
                var $target = $(event.target);
                var typing = $target.is('input, textarea, select, [contenteditable="true"]');

                if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') {
                    event.preventDefault();
                    $('#search-options').trigger('focus');
                    return;
                }

                if (!typing && event.key === '/') {
                    event.preventDefault();
                    $('#search-options').trigger('focus');
                    return;
                }

                if (!typing && event.altKey) {
                    var key = event.key.toLowerCase();
                    var targets = {n: '#erp-quick-create-toggle', s: '.erp-command-item.is-sales', p: '.erp-command-item.is-purchase', r: '.erp-command-item.is-customer', o: '.erp-command-item.is-accounting'};
                    if (targets[key]) { event.preventDefault(); shortcutTarget(targets[key]); }
                }

                if (!typing && /^F[1-8]$/.test(event.key)) {
                    var shortcut = {F1: '.erp-command-item.is-sales', F2: '.erp-command-item.is-purchase', F3: '.erp-command-item.is-customer', F4: 'a[href*="/parties/customers"]', F5: 'a[href*="/products"]', F6: 'a[href*="/inventory/stock"]', F7: 'a[href*="/reports/"]'};
                    event.preventDefault();
                    if (event.key === 'F8') window.print();
                    else shortcutTarget(shortcut[event.key]);
                }

                if ((event.ctrlKey || event.metaKey) && event.key === 'Enter') {
                    var $form = $('.modal.show form:visible, #app-content form:visible, #erp-main-content form:visible').first();
                    if ($form.length) { event.preventDefault(); $form.trigger('submit'); }
                }
            });

        $(document).ajaxStart(function () { $('body').addClass('erp-ajax-active'); }).ajaxStop(function () { $('body').removeClass('erp-ajax-active'); });
    });
    window.CholavinShell = {openDrawer: openDrawer, closeDrawer: closeDrawer, openTimeline: openTimeline, closeTimeline: closeTimeline, setQuickActions: setQuickActions};
})(window, window.jQuery);
