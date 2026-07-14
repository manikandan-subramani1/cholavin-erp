(function (window, document) {
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
            shop.disabled = true;
            godown.disabled = true;

            try {
                var response = await postContext(context.dataset.switchShopUrl, {
                    shop_id: Number(shop.value)
                });

                godown.innerHTML = '<option value="">No godown</option>';
                (response.data.godowns || []).forEach(function (item) {
                    var option = new Option('#' + item.id + ' — ' + item.name + ' (' + item.code + ')', item.id);
                    option.selected = Number(item.id) === Number(response.data.active_godown_id);
                    godown.add(option);
                });

                notify('success', response.message);
                refreshContextContent(response.data);
            } catch (error) {
                shop.value = previous;
                shop.disabled = false;
                godown.disabled = godown.options.length <= 1;
                notify('error', error.message);
            }
        });

        godown?.addEventListener('change', async function () {
            var previous = godown.dataset.previous || '';
            godown.disabled = true;

            try {
                var response = await postContext(context.dataset.switchGodownUrl, {
                    godown_id: godown.value ? Number(godown.value) : null
                });
                notify('success', response.message);
                refreshContextContent(response.data);
            } catch (error) {
                godown.value = previous;
                godown.disabled = false;
                notify('error', error.message);
            }
        });

        if (shop) shop.dataset.previous = shop.value;
        if (godown) godown.dataset.previous = godown.value;
    });
})(window, document);
