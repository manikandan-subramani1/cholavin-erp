(function (window, $) {
    'use strict';

    if (!$ || !$('#product-form').length) return;

    var $form = $('#product-form');

    $('#product-image').on('change.productForm', function () {
        if (this.files && this.files[0]) {
            $('#imagePreview').attr('src', URL.createObjectURL(this.files[0]));
        }
    });

    $form.validate({
        ignore: [],
        rules: {
            name: {required: true, minlength: 2, maxlength: 190},
            sub_title: {maxlength: 190}, sku: {maxlength: 100}, barcode: {maxlength: 100},
            description: {maxlength: 10000}, image: {extension: 'jpg|jpeg|png|webp'},
            price: {number: true, min: 0}, purchase_price: {number: true, min: 0},
            sale_price: {number: true, min: 0}, opening_stock: {number: true, min: 0},
            reorder_level: {number: true, min: 0}, unit: {maxlength: 50},
            sort_order: {digits: true, min: 0}
        },
        errorElement: 'span',
        errorClass: 'error text-danger',
        highlight: function (element) { $(element).addClass('is-invalid'); },
        unhighlight: function (element) { $(element).removeClass('is-invalid'); },
        submitHandler: function (element) {
            submitFormUsingAjax(element, {
                reset: false,
                onSuccess: function () {
                    window.CholavinNavigation.visit($form.data('index-url'));
                }
            });
        }
    });
})(window, window.jQuery);
