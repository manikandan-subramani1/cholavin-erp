(function (window, document, $) {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var context = document.getElementById('erp-business-context');
        if (!context) return;

        var shop = document.getElementById('header-shop-context');
        var godown = document.getElementById('header-godown-context');
        var token = document.querySelector('meta[name="csrf-token"]')?.content || '';

        function notify(type, message) {
            if (window.toastr && typeof window.toastr[type] === 'function') {
                window.toastr[type](message);
            } else if (type === 'error') {
                console.error(message);
            }
        }

        function initializeSelect2(element) {
            if (!element || !$ || !$.fn.select2) return;

            $(element).select2({
                width: '100%',
                placeholder: element.dataset.placeholder || 'Search locations',
                allowClear: element.dataset.allowClear === 'true',
                minimumResultsForSearch: 0,
                dropdownCssClass: 'erp-context-dropdown',
                language: {
                    noResults: function () { return 'No matching locations found'; },
                    searching: function () { return 'Searching locations...'; }
                }
            });

            $(element).on('select2:open', function () {
                window.setTimeout(function () {
                    document.querySelector('.select2-container--open .select2-search__field')?.focus();
                }, 0);
            });
        }

        function syncSelect2(element) {
            if (element && $ && $.fn.select2 && $(element).data('select2')) {
                $(element).trigger('change.select2');
            }
        }

        function canEnable(element) {
            if (!element || element.dataset.canSwitch !== '1') return false;

            if (element === shop) {
                return element.options.length > 1;
            }

            return Array.from(element.options).filter(function (option) {
                return option.value !== '';
            }).length > 1;
        }

        function setBusy(isBusy) {
            if (shop) {
                shop.disabled = isBusy || !canEnable(shop);
                syncSelect2(shop);
            }

            if (godown) {
                godown.disabled = isBusy || !canEnable(godown);
                syncSelect2(godown);
            }

            context.classList.toggle('is-switching', isBusy);
        }

        async function postContext(url, payload) {
            var response = await fetch(url, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify(payload)
            });
            var json = await response.json().catch(function () { return {}; });

            if (!response.ok || !json.status) {
                var firstError = Object.values(json.errors || {})[0];
                throw new Error(json.message || firstError?.[0] || 'Unable to change the working location.');
            }

            return json;
        }

        function refreshContextContent(data) {
            var tables = window.jQuery && window.jQuery.fn.DataTable
                ? window.jQuery('.context-data-table').filter(function () {
                    return window.jQuery.fn.DataTable.isDataTable(this);
                })
                : null;

            window.dispatchEvent(new CustomEvent('business-context:changed', {detail: data}));

            if (tables && tables.length) {
                tables.each(function () {
                    window.jQuery(this).DataTable().ajax.reload(null, false);
                });
                return;
            }

            window.location.reload();
        }

        shop?.addEventListener('change', async function () {
            var previous = shop.dataset.previous || '';
            setBusy(true);

            try {
                var response = await postContext(context.dataset.switchShopUrl, {
                    shop_id: Number(shop.value)
                });

                godown.innerHTML = '';
                godown.add(new Option('All inventory locations', '', false, !response.data.active_godown_id));
                (response.data.godowns || []).forEach(function (item) {
                    var option = new Option(item.name + ' (' + item.code + ')', item.id);
                    option.selected = Number(item.id) === Number(response.data.active_godown_id);
                    godown.add(option);
                });

                shop.dataset.previous = shop.value;
                godown.dataset.previous = godown.value;
                setBusy(false);
                syncSelect2(godown);
                notify('success', response.message);
                refreshContextContent(response.data);
            } catch (error) {
                shop.value = previous;
                syncSelect2(shop);
                setBusy(false);
                notify('error', error.message);
            }
        });

        godown?.addEventListener('change', async function () {
            var previous = godown.dataset.previous || '';
            setBusy(true);

            try {
                var response = await postContext(context.dataset.switchGodownUrl, {
                    godown_id: godown.value ? Number(godown.value) : null
                });
                godown.dataset.previous = godown.value;
                setBusy(false);
                notify('success', response.message);
                refreshContextContent(response.data);
            } catch (error) {
                godown.value = previous;
                syncSelect2(godown);
                setBusy(false);
                notify('error', error.message);
            }
        });

        initializeSelect2(shop);
        initializeSelect2(godown);

        if (shop) shop.dataset.previous = shop.value;
        if (godown) godown.dataset.previous = godown.value;
        setBusy(false);
    });
})(window, document, window.jQuery);
