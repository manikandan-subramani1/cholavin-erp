(function (window, $) {
    'use strict';

    if (!$) return;

    $(function () {
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
                error.insertAfter(element);
            },
            highlight: function (element) {
                $(element).addClass('is-invalid').removeClass('is-valid');
            },
            unhighlight: function (element) {
                $(element).removeClass('is-invalid').addClass('is-valid');
            },
            submitHandler: function (element) {
                feedback.addClass('d-none').text('');

                window.submitFormUsingAjax(element, {
                    reset: false,
                    onSuccess: function (response) {
                        window.location.assign(response.data.redirect);
                    }
                }).fail(function (xhr) {
                    var message = xhr.responseJSON?.message;
                    var firstError = Object.values(xhr.responseJSON?.errors || {})[0];

                    feedback
                        .removeClass('d-none')
                        .text(message || firstError?.[0] || 'Unable to sign in. Please check your details.');
                });
            }
        });
    });
})(window, window.jQuery);
