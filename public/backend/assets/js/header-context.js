(function (window, $) {
    'use strict';

    if (!$) return;

    $(function () {
        var $context = $('#erp-business-context');
        var $shop = $('#header-shop-context');
        var $godown = $('#header-godown-context');
        var $financialYear = $('#header-financial-year-context');

        if (!$context.length) return;

        function notify(type, message) {
            if (window.toastr && typeof window.toastr[type] === 'function') {
                window.toastr[type](message);
            }
        }

        function initializeSelect2($select) {
            if (!$select.length || !$.fn.select2) return;

            $select.select2({
                width: '100%',
                placeholder: $select.data('placeholder') || 'Search locations',
                allowClear: String($select.data('allow-clear')) === 'true',
                minimumResultsForSearch: 0,
                dropdownCssClass: 'erp-context-dropdown',
                language: {
                    noResults: function () { return 'No matching locations found'; },
                    searching: function () { return 'Searching locations...'; }
                }
            }).on('select2:open.erpContext', function () {
                window.setTimeout(function () {
                    $('.select2-container--open .select2-search__field').trigger('focus');
                }, 0);
            });
        }

        function syncSelect2($select) {
            if ($select.length && $.fn.select2 && $select.data('select2')) {
                $select.trigger('change.select2');
            }
        }

        function canEnable($select) {
            if (!$select.length || String($select.data('can-switch')) !== '1') return false;
            return $select.is($shop)
                ? $select.find('option').length > 1
                : $select.find('option').filter(function () { return $(this).val() !== ''; }).length > 1;
        }

        function setBusy(isBusy) {
            [$shop, $godown, $financialYear].forEach(function ($select) {
                if (!$select.length) return;
                $select.prop('disabled', isBusy || !canEnable($select));
                syncSelect2($select);
            });

            $context.toggleClass('is-switching', isBusy);
        }

        function refreshContextContent(data) {
            $(window).trigger('business-context:changed', [data]);

            if (!$.fn.DataTable) return;
            $('.page-content table').each(function () {
                if ($.fn.DataTable.isDataTable(this)) {
                    $(this).DataTable().ajax.reload(null, false);
                }
            });
        }

        function remember($select) {
            $select.attr('data-previous', $select.val() || '');
        }

        function restore($select, previous) {
            $select.val(previous);
            syncSelect2($select);
        }

        function replaceShopOptions(items, activeShopId) {
            if (!$shop.length) return;

            $shop.empty();

            if (String($shop.data('allow-all')) === 'true' && !$godown.val()) {
                $('<option>', {value: '', text: 'All shops', selected: !activeShopId}).appendTo($shop);
            }

            $.each(items || [], function (_, item) {
                $('<option>', {
                    value: item.id,
                    text: item.name + ' (' + item.code + ')',
                    selected: Number(item.id) === Number(activeShopId)
                }).appendTo($shop);
            });

            remember($shop);
            syncSelect2($shop);
        }

        function switchContext($select, url, payload, afterSuccess) {
            var previous = $select.attr('data-previous') || '';
            setBusy(true);

            window.CholavinAjax.request({
                url: url,
                method: 'POST',
                data: payload,
                showLoader: false,
                onSuccess: function (response) {
                    if (typeof afterSuccess === 'function') afterSuccess(response);
                    remember($select);
                    notify('success', response.message);
                    refreshContextContent(response.data);
                },
                onError: function () {
                    restore($select, previous);
                },
                onComplete: function () {
                    setBusy(false);
                }
            });
        }

        $shop.on('change.erpContext', function () {
            switchContext($shop, $context.data('switch-shop-url'), {
                shop_id: $shop.val() ? Number($shop.val()) : null
            });
        });

        $godown.on('change.erpContext', function () {
            switchContext($godown, $context.data('switch-godown-url'), {
                godown_id: $godown.val() ? Number($godown.val()) : null
            }, function (response) {
                replaceShopOptions(response.data.shops, response.data.active_shop_id);
            });
        });

        $financialYear.on('change.erpContext', function () {
            switchContext($financialYear, $context.data('switch-financial-year-url'), {
                financial_year_id: Number($financialYear.val())
            });
        });

        initializeSelect2($shop);
        initializeSelect2($godown);
        initializeSelect2($financialYear);

        $context.on('click.erpContext', '.erp-context-field', function (event) {
            var $select = $(this).find('.erp-context-select');

            if (!$select.length || $select.prop('disabled') || !$.fn.select2 || $(event.target).closest('.select2-container').length) {
                return;
            }

            $select.select2('open');
        });

        remember($shop);
        remember($godown);
        remember($financialYear);
        setBusy(false);
    });
})(window, window.jQuery);
