(function (window, $) {
    'use strict';

    if (!$ || !$('#products-module').length) return;

    var $module = $('#products-module');
    var initialSearch = new URLSearchParams(window.location.search).get('search') || '';

    function filters() {
        return {
            status: $('#product-status-filter').val(),
            homepage: $('#product-homepage-filter').val(),
            category_id: $('#product-category-filter').val()
        };
    }

    var table = initializeDataTable({
        selector: '#products-table',
        url: $module.data('index-url'),
        filters: filters,
        columns: [
            {data: 'DT_RowIndex'},
            {data: 'image_preview', orderable: false, searchable: false},
            {data: 'name'}, {data: 'price'}, {data: 'unit'},
            {data: 'is_active', name: 'is_active'},
            {data: 'show_on_homepage', name: 'show_on_homepage'},
            {data: 'sort_order'}, {data: 'action'}
        ],
        order: [[2, 'asc']]
    });

    if (initialSearch) {
        $('#product-search').val(initialSearch);
        table.search(initialSearch).draw();
    }

    $(document).on('click.products', '.delete-product', function () {
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
    $('#product-status-filter,#product-homepage-filter,#product-category-filter').on('change.products', function () {
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
})(window, window.jQuery);
