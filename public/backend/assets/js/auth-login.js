(function (window, $) {
    'use strict';

    if (!$) return;

    function setFeedback($feedback, type, message) {
        $feedback
            .removeClass('d-none alert-danger alert-success alert-info')
            .addClass('alert-' + type)
            .text(message);
    }

    $(function () {
        $(document).on('click.authPassword', '[data-auth-password-toggle]', function () {
            var $button = $(this);
            var $input = $($button.attr('data-auth-password-toggle'));
            if (!$input.length) return;

            var visible = $input.attr('type') === 'text';
            $input.attr('type', visible ? 'password' : 'text');
            $button.attr('aria-label', visible ? 'Show password' : 'Hide password');
            $button.find('i').attr('class', visible ? 'ri-eye-line' : 'ri-eye-off-line');
        });

        $(document).on('submit.authAjax', 'form[data-auth-ajax]', function (event) {
            if (typeof window.submitFormUsingAjax !== 'function') return;

            event.preventDefault();
            var element = this;
            var $feedback = $('#auth-form-feedback');
            setFeedback($feedback, 'info', $(element).find('[data-loading-label]').data('loading-label') || 'Please wait...');

            window.submitFormUsingAjax(element, {
                reset: false,
                onSuccess: function (response) {
                    setFeedback($feedback, 'success', response.message || 'Completed successfully.');
                    var redirect = response.data?.redirect || response.redirect;
                    if (redirect) {
                        window.setTimeout(function () { window.location.assign(redirect); }, 350);
                    }
                }
            }).fail(function (xhr) {
                var firstError = Object.values(xhr.responseJSON?.errors || {})[0];
                setFeedback($feedback, 'danger', firstError?.[0] || xhr.responseJSON?.message || 'The request could not be completed.');
            });
        });

        $('#godown_id').on('change.authContext', function () {
            var $form = $('#auth-context-form');
            var $shop = $('#shop_id');
            var godownId = $(this).val();
            if (!$form.length || !godownId) {
                $shop.html('<option value="">Select shop</option>');
                return;
            }

            $shop.prop('disabled', true).html('<option value="">Loading shops...</option>');
            $.ajax({url: $form.data('shops-url'), method: 'GET', data: {godown_id: godownId}, dataType: 'json'})
                .done(function (response) {
                    $shop.html('<option value="">Select shop</option>');
                    $.each(response.data?.shops || [], function (_, shop) {
                        $('<option>', {value: shop.id, text: shop.name + ' (' + shop.code + ')'}).appendTo($shop);
                    });
                })
                .fail(function () {
                    $shop.html('<option value="">Unable to load shops</option>');
                })
                .always(function () { $shop.prop('disabled', false); });
        });

        var form = $('#login-form');
        var feedback = $('#login-feedback');

        if (!form.length || !$.fn.validate) return;

        form.validate({
            errorClass: 'is-invalid',
            validClass: 'is-valid',
            errorElement: 'span',
            rules: {
                login: {required: true, maxlength: 190},
                password: {required: true, maxlength: 255}
            },
            messages: {
                login: {required: 'Enter your username, email address, or mobile number.'},
                password: {required: 'Enter your password.'}
            },
            errorPlacement: function (error, element) {
                error.addClass('invalid-feedback');
                error.insertAfter(element.closest('.auth-password-group').length ? element.closest('.auth-password-group') : element);
            },
            highlight: function (element) {
                $(element).addClass('is-invalid').removeClass('is-valid');
            },
            unhighlight: function (element) {
                $(element).removeClass('is-invalid').addClass('is-valid');
            },
            submitHandler: function (element) {
                setFeedback(feedback, 'info', 'Signing in...');

                window.submitFormUsingAjax(element, {
                    reset: false,
                    onSuccess: function (response) {
                        setFeedback(feedback, 'success', 'Login successful. Loading workspace...');
                        window.setTimeout(function () {
                            window.location.assign(response.data.redirect);
                        }, 250);
                    }
                }).fail(function (xhr) {
                    if (xhr.status === 429) {
                        window.location.assign(form.data('locked-url'));
                        return;
                    }

                    var message = xhr.responseJSON?.message;
                    var firstError = Object.values(xhr.responseJSON?.errors || {})[0];

                    setFeedback(feedback, 'danger', message || firstError?.[0] || 'Unable to sign in. Please check your details.');
                });
            }
        });
    });
})(window, window.jQuery);
