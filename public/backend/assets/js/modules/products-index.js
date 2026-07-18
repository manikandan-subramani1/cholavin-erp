(function (window, $) {
    'use strict';

    if (!$ || !$('#products-module').length) return;

    var $module = $('#products-module');
    var initialSearch = new URLSearchParams(window.location.search).get('search') || '';
    var selectedProduct = null;

    function filters() {
        return {
            status: $('#product-status-filter').val(),
            homepage: $('#product-homepage-filter').val(),
            category_id: $('#product-category-filter').val()
        };
    }

    function safe(value) {
        return $('<div>').text(value == null ? '' : value).html();
    }

    function money(value) {
        return '\u20B9' + Number(value || 0).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }

    function visitUrl(url) {
        if (!url) return;
        if (window.CholavinNavigation) window.CholavinNavigation.visit(url);
        else window.location.assign(url);
    }

    function renderStock(value, type, row) {
        if (type !== 'display') return value;
        return '<div class="product-stock-cell"><strong>' + Number(value || 0).toLocaleString('en-IN', {maximumFractionDigits: 3}) + '</strong>' + (row.stock_status || '') + '</div>';
    }

    function openProductDrawer(product) {
        if (!window.CholavinShell || !product) return;
        selectedProduct = product;
        var sku = product.sku || product.barcode || '-';
        var html = ''
            + '<div class="product-drawer-profile">'
            + '<div class="product-drawer-hero"><img src="' + safe(product.image_url) + '" alt=""><div><span class="erp-eyebrow">Item / Product Master</span><h4>' + safe(product.name) + '</h4><p>' + safe(sku) + ' &middot; ' + safe(product.category_name || 'Uncategorised') + '</p></div></div>'
            + '<div class="product-drawer-kpis">'
            + '<article><small>Selling Price</small><strong>' + money(product.sale_price || product.price) + '</strong><span>' + safe(product.unit || '-') + '</span></article>'
            + '<article><small>Purchase Price</small><strong>' + money(product.purchase_price) + '</strong><span>Cost basis</span></article>'
            + '<article><small>Current Stock</small><strong>' + Number(product.current_stock || 0).toLocaleString('en-IN', {maximumFractionDigits: 3}) + '</strong><span>Scoped stock</span></article>'
            + '<article><small>Stock Value</small><strong>' + money(product.stock_value) + '</strong><span>Average cost value</span></article>'
            + '</div>'
            + '<div class="party-drawer-tabs"><button class="is-active" type="button">Overview</button><button type="button">Pricing</button><button type="button">Stock</button><button type="button">Barcode</button><button type="button">Audit</button></div>'
            + '<div class="product-drawer-meta">'
            + '<span><i class="ri-barcode-line"></i><strong>Barcode</strong><small>' + safe(product.barcode || '-') + '</small></span>'
            + '<span><i class="ri-stack-line"></i><strong>Reorder Level</strong><small>' + Number(product.reorder_level || 0).toLocaleString('en-IN', {maximumFractionDigits: 3}) + '</small></span>'
            + '<span><i class="ri-price-tag-3-line"></i><strong>Homepage</strong><small>' + (product.show_on_homepage ? 'Shown' : 'Hidden') + '</small></span>'
            + '<span><i class="ri-checkbox-circle-line"></i><strong>Status</strong><small>' + (product.is_active ? 'Active' : 'Hidden') + '</small></span>'
            + '</div>'
            + '<div class="party-drawer-actions">'
            + '<button class="btn btn-primary" type="button" data-product-drawer-action="stock"><i class="ri-stack-line"></i>Stock History</button>'
            + '<button class="btn btn-secondary" type="button" data-product-drawer-action="transfer"><i class="ri-arrow-left-right-line"></i>Transfer Stock</button>'
            + '<button class="btn btn-secondary" type="button" data-product-drawer-action="duplicate"><i class="ri-file-copy-line"></i>Duplicate Item</button>'
            + '</div></div>';
        window.CholavinShell.openDrawer({title: product.name || 'Item Details', html: html});
    }

    function installQuickActions() {
        if (!window.CholavinShell) return;
        var actions = [];
        if ($module.data('create-url')) actions.push({label: 'Add Item', icon: 'ri-add-line', handler: function () { visitUrl($module.data('create-url')); }});
        if ($('#products-pdf').length) actions.push({label: 'Export PDF', icon: 'ri-file-pdf-2-line', handler: function () { $('#products-pdf').trigger('click'); }});
        if ($module.data('stock-url')) actions.push({label: 'Stock Overview', icon: 'ri-stack-line', handler: function () { visitUrl($module.data('stock-url')); }});
        if ($module.data('transfer-url')) actions.push({label: 'Stock Transfer', icon: 'ri-arrow-left-right-line', handler: function () { visitUrl($module.data('transfer-url')); }});
        window.CholavinShell.setQuickActions(actions);
    }

    var table = initializeDataTable({
        selector: '#products-table',
        url: $module.data('index-url'),
        filters: filters,
        columns: [
            {data: 'image_preview', orderable: false, searchable: false},
            {data: 'name'},
            {data: 'category_name', orderable: false, searchable: false},
            {data: 'variant_name', orderable: false, searchable: false},
            {data: 'grade_name', orderable: false, searchable: false},
            {data: 'unit_name', orderable: false, searchable: false},
            {data: 'purchase_price'},
            {data: 'sale_price'},
            {data: 'godown_stock', orderable: false, searchable: false, render: renderStock},
            {data: 'shop_stock', orderable: false, searchable: false, render: renderStock},
            {data: 'is_active', name: 'is_active'},
            {data: 'action', orderable: false, searchable: false}
        ],
        order: [[1, 'asc']]
    });

    installQuickActions();

    if (initialSearch) {
        $('#product-search').val(initialSearch);
        table.search(initialSearch).draw();
    }

    $('#products-table tbody').off('.products')
        .on('click.products', 'a, button.delete-product', function (event) { event.stopPropagation(); })
        .on('click.products', '[data-product-action="view"]', function (event) {
        event.preventDefault();
        event.stopPropagation();
        var row = table.row($(this).closest('tr')).data();
        openProductDrawer(row);
    }).on('click.products', 'tr', function () {
        var row = table.row(this).data();
        if (row) openProductDrawer(row);
    });

    $(document).off('click.productsDrawer').on('click.productsDrawer', '[data-product-drawer-action]', function () {
        var action = $(this).data('product-drawer-action');
        if (action === 'stock') visitUrl($module.data('stock-url') + '?search=' + encodeURIComponent(selectedProduct ? selectedProduct.sku || selectedProduct.name : ''));
        if (action === 'transfer') visitUrl($module.data('transfer-url'));
        if (action === 'duplicate') visitUrl($module.data('create-url') + '?duplicate=' + encodeURIComponent(selectedProduct ? selectedProduct.id : ''));
    });

    $(document).on('click.products', '.delete-product', function (event) {
        event.preventDefault();
        event.stopPropagation();
        var url = $(this).data('url');
        Swal.fire({
            title: 'Delete product?',
            text: 'Products with stock or transactions must be deactivated instead.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Delete'
        }).then(function (result) {
            if (!result.isConfirmed) return;
            CholavinAjax.request({
                url: url,
                method: 'DELETE',
                onSuccess: function (response) {
                    toastr.success(response.message);
                    table.ajax.reload(null, false);
                    if (window.CholavinShell) window.CholavinShell.closeDrawer();
                }
            });
        });
    });

    var timer;
    $('#product-search').on('input.products', function () {
        var value = $(this).val();
        window.clearTimeout(timer);
        timer = window.setTimeout(function () { table.search(value).draw(); }, 300);
    });
    $('#product-status-filter,#product-category-filter,#product-variant-filter,#product-grade-filter,#product-brand-filter,#product-unit-filter,#product-stock-filter,#product-code-filter,#product-barcode-filter').on('change.products', function () {
        table.ajax.reload(null, false);
    });
    $('#reset-product-filters').on('click.products', function () {
        $('#product-filters').trigger('reset');
        table.search('').ajax.reload(null, false);
    });
    $('#products-pdf').on('click.products', function () {
        var params = filters();
        params.search = $('#product-search').val();
        window.location.href = $module.data('pdf-url') + '?' + new URLSearchParams(params).toString();
    });
})(window, window.jQuery);`r`n

