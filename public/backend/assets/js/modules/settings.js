(function (window, $) {
    'use strict';
    var $form = $('#settings-form');
    if (!$ || !$form.length) return;
    var $module = $('#settings-module');
    if (window.CholavinShell && $module.length) {
        window.CholavinShell.setQuickActions([
            {label: 'Save Settings', icon: 'ri-save-line', target: '#settings-form .btn-brand', variant: 'primary'},
            {label: 'Branches', icon: 'ri-store-2-line', url: $module.data('locations-url')},
            {label: 'Backup', icon: 'ri-database-2-line', url: $module.data('maintenance-url')},
            {label: 'Business Profile', icon: 'ri-building-4-line', target: '#settings-brand'}
        ]);
    }

    $form.validate({
        ignore: [],
        rules: {
            company_name: { required: true, minlength: 2, maxlength: 150 }, tagline: { maxlength: 190 },
            contact_phone: { maxlength: 40 }, whatsapp_number: { maxlength: 40 }, contact_email: { email: true, maxlength: 190 }, company_address: { maxlength: 1000 },
            facebook: { url: true, maxlength: 500 }, instagram: { url: true, maxlength: 500 }, youtube: { url: true, maxlength: 500 }, linkedin: { url: true, maxlength: 500 },
            mail_host: { maxlength: 190 }, mail_port: { digits: true, min: 1, max: 65535 }, mail_username: { maxlength: 190 }, mail_password: { maxlength: 500 },
            mail_from_address: { email: true, maxlength: 190 }, mail_from_name: { maxlength: 190 },
            brand_logo: { extension: 'png|jpg|jpeg|webp' }, auth_brand_image: { extension: 'png|jpg|jpeg|webp' }
        },
        errorElement: 'span', errorClass: 'error text-danger',
        highlight: function (element) { $(element).addClass('is-invalid'); }, unhighlight: function (element) { $(element).removeClass('is-invalid'); },
        submitHandler: function (element) {
            submitFormUsingAjax(element, {
                reset: false, onSuccess: function (response) {
                    var images = response.data.images || {};
                    if (images.brand_logo) $('#brand-logo-preview').attr('src', images.brand_logo);
                    if (images.auth_brand_image) $('#auth-image-preview').attr('src', images.auth_brand_image);
                    $form.find('input[type="file"]').val('');
                }
            });
        }
    });
    $form.on('change', 'input[type="file"]', function () { if (!this.files || !this.files[0]) return; var target = this.name === 'brand_logo' ? '#brand-logo-preview' : '#auth-image-preview'; $(target).attr('src', URL.createObjectURL(this.files[0])); });
})(window, window.jQuery);
